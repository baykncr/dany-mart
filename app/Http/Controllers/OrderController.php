<?php

namespace App\Http\Controllers;

use App\DTOs\CartItemDTO;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * POST /pos/checkout
     * Proses checkout: validasi → transaksi DB → return receipt.
     */
    public function store(Request $request): JsonResponse
    {
        // ── Validasi HTTP Request ─────────────────────────────────────────
        $validated = $request->validate([
            'cart'                    => 'required|array|min:1|max:100',
            'cart.*.product_id'       => 'required|integer|exists:products,id',
            'cart.*.quantity'         => 'required|integer|min:1|max:999',
            'cart.*.unit_price'       => 'required|integer|min:0',
            'cart.*.subtotal'         => 'required|integer|min:0',
            'total_amount'            => 'required|integer|min:1',
            'payment_method'          => 'required|string|in:cash,qris',
            'payment_amount'          => 'required|integer|min:0',
        ]);

        // Pastikan tidak ada duplikat product_id dalam satu cart
        $productIds = array_column($validated['cart'], 'product_id');
        if (count($productIds) !== count(array_unique($productIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Cart tidak valid: terdapat duplikat produk. Silakan muat ulang halaman kasir.',
            ], 422);
        }

        // Konversi ke DTOs
        $cartItems = array_map(
            fn(array $item) => CartItemDTO::fromArray($item),
            $validated['cart']
        );

        try {
            $receipt = $this->orderService->processCheckout(
                cashier:       $request->user(),
                cartItems:     $cartItems,
                totalAmount:   (int) $validated['total_amount'],
                paymentMethod: $validated['payment_method'],
                paymentAmount: (int) $validated['payment_amount'],
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan.',
                'receipt' => $receipt,
            ], 201);

        } catch (\Exception $e) {
            // Log untuk debugging server-side
            Log::error('Order checkout failed', [
                'user_id' => $request->user()->id,
                'error'   => $e->getMessage(),
                'cart'    => $validated['cart'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}