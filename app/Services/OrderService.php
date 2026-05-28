<?php

namespace App\Services;

use App\DTOs\CartItemDTO;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStockHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Eksekusi seluruh proses checkout dalam satu DB transaction.
     *
     * @param  User          $cashier       User yang melakukan transaksi
     * @param  CartItemDTO[] $cartItems     Item-item yang dibeli
     * @param  int           $totalAmount   Total yang dihitung frontend (akan diverifikasi ulang)
     * @param  string        $paymentMethod 'cash' | 'qris'
     * @param  int           $paymentAmount Nominal yang dibayarkan pelanggan
     * @return array                        Receipt data untuk ditampilkan di frontend
     *
     * @throws \Exception  Jika stok tidak cukup, produk tidak ada, atau data tidak konsisten
     */
    public function processCheckout(
        User   $cashier,
        array  $cartItems,
        int    $totalAmount,
        string $paymentMethod,
        int    $paymentAmount,
    ): array {
        return DB::transaction(function () use (
            $cashier, $cartItems, $totalAmount, $paymentMethod, $paymentAmount
        ) {
            // ── STEP 0: Pre-flight validasi & kunci produk ────────────────
            $productIds = array_map(fn(CartItemDTO $i) => $i->productId, $cartItems);

            // Lock semua produk sekaligus untuk hindari race condition
            // (dua kasir checkout produk yang sama di waktu bersamaan)
            $products = Product::with('category:id,name')
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Pastikan semua produk ditemukan
            foreach ($cartItems as $item) {
                if (!$products->has($item->productId)) {
                    throw new \Exception(
                        "Produk dengan ID {$item->productId} tidak ditemukan di database."
                    );
                }
            }

            // ── STEP 1: Verifikasi ulang total di server ──────────────────
            $serverCalculatedTotal = 0;
            foreach ($cartItems as $item) {
                $product = $products->get($item->productId);

                // Pastikan unit_price di cart = harga jual aktual
                if ($item->unitPrice !== $product->selling_price) {
                    throw new \Exception(
                        "Harga produk '{$product->name}' berubah. " .
                        "Silakan muat ulang halaman kasir."
                    );
                }

                // Pastikan subtotal konsisten (mencegah manipulasi)
                if (!$item->isConsistent()) {
                    throw new \Exception(
                        "Data subtotal untuk '{$product->name}' tidak valid."
                    );
                }

                // Validasi stok mencukupi
                if ($product->stock < $item->quantity) {
                    throw new \Exception(
                        "Stok '{$product->name}' tidak mencukupi. " .
                        "Tersisa: {$product->stock} {$product->unit}, " .
                        "dibutuhkan: {$item->quantity} {$product->unit}."
                    );
                }

                $serverCalculatedTotal += $item->subtotal;
            }

            // Verifikasi total dari frontend cocok dengan kalkulasi server
            if ($serverCalculatedTotal !== $totalAmount) {
                throw new \Exception(
                    "Total pembayaran tidak sesuai. " .
                    "Server: Rp " . number_format($serverCalculatedTotal, 0, ',', '.') . ", " .
                    "diterima: Rp " . number_format($totalAmount, 0, ',', '.') . ". " .
                    "Silakan muat ulang halaman."
                );
            }

            // Validasi payment amount untuk metode cash
            if ($paymentMethod === 'cash' && $paymentAmount < $totalAmount) {
                throw new \Exception(
                    "Nominal tunai (Rp " . number_format($paymentAmount, 0, ',', '.') . ") " .
                    "kurang dari total belanja (Rp " . number_format($totalAmount, 0, ',', '.') . ")."
                );
            }

            // ── STEP 2: Buat record Order ─────────────────────────────────
            $changeAmount = match ($paymentMethod) {
                'cash'  => $paymentAmount - $totalAmount,
                'qris'  => 0,
                default => 0,
            };

            $order = Order::create([
                'user_id'        => $cashier->id,
                'order_number'   => Order::generateOrderNumber(),
                'total_amount'   => $totalAmount,
                'payment_method' => $paymentMethod,
                'payment_amount' => $paymentAmount,
                'change_amount'  => $changeAmount,
            ]);

            // ── STEP 3: Insert OrderItems (bulk) ──────────────────────────
            $orderItemsData      = [];
            $stockHistoriesData  = [];
            $now                 = now();

            foreach ($cartItems as $item) {
                $product  = $products->get($item->productId);
                $newStock = $product->stock - $item->quantity;

                // Akumulasi data untuk bulk insert
                $orderItemsData[] = [
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'subtotal'   => $item->subtotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $stockHistoriesData[] = [
                    'product_id'    => $product->id,
                    'user_id'       => $cashier->id,
                    'added_stock'   => -$item->quantity,
                    'current_stock' => $newStock,
                    'type'          => 'sale',
                    'note'          => "Penjualan {$order->order_number}",
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                // Update stok product (per-row, harus individual agar atomic)
                Product::where('id', $product->id)
                    ->where('stock', '>=', $item->quantity) // double-check
                    ->update(['stock' => $newStock]);

                // Verifikasi update berhasil
                $product->refresh();
                if ($product->stock !== $newStock) {
                    throw new \Exception(
                        "Gagal mengurangi stok '{$product->name}'. " .
                        "Kemungkinan ada transaksi lain yang berjalan bersamaan."
                    );
                }
            }

            // ── STEP 4: Bulk insert OrderItems & StockHistories ───────────
            OrderItem::insert($orderItemsData);
            ProductStockHistory::insert($stockHistoriesData);

            // ── STEP 5: Load relasi untuk receipt ─────────────────────────
            $order->load([
                'items.product:id,name,unit,photo,category_id',
                'items.product.category:id,name',
                'user:id,name',
            ]);

            // ── STEP 6: Bangun receipt data ───────────────────────────────
            return $this->buildReceiptData($order);
        });
    }

    /**
     * Format data order menjadi struktur receipt yang siap ditampilkan frontend.
     */
    private function buildReceiptData(Order $order): array
    {
        $items = $order->items->map(fn(OrderItem $item) => [
            'name'       => $item->product->name,
            'category'   => $item->product->category?->name ?? '',
            'qty'        => $item->quantity,
            'unit'       => $item->product->unit,
            'unit_price' => $item->unit_price,
            'subtotal'   => $item->subtotal,
        ])->toArray();

        return [
            'order_id'       => $order->id,
            'order_number'   => $order->order_number,
            'cashier_name'   => $order->user->name,
            'store_name'     => config('app.store_name', 'Dany Mart'),
            'store_tagline'  => config('app.store_tagline', 'Minimarket Terpercaya'),
            'transaction_at' => $order->created_at->setTimezone('Asia/Jakarta')
                                    ->format('d/m/Y H:i:s'),
            'items'          => $items,
            'item_count'     => $order->items->sum('quantity'),
            'total_amount'   => $order->total_amount,
            'payment_method' => $order->payment_method,
            'payment_amount' => $order->payment_amount,
            'change_amount'  => $order->change_amount,
        ];
    }
}