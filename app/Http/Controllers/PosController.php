<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    /**
     * Render halaman POS dengan semua produk & kategori.
     */
    public function index(): Response
    {
        $products = Product::with('category:id,name,code')
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'code'           => $p->code,
                'name'           => $p->name,
                'unit'           => $p->unit,
                'selling_price'  => $p->selling_price,
                'stock'          => $p->stock,
                'photo'          => $p->photo
                    ? asset('storage/' . $p->photo)
                    : null,
                'category_id'    => $p->category_id,
                'category'       => $p->category,
            ]);

        $categories = Category::orderBy('priority_order')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Pos/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }

    /**
     * Engine Rekomendasi — dipanggil saat kasir klik "Checkout".
     *
     * Algoritma:
     * 1. Hitung kategori dominan dari cart items
     * 2. Tentukan jumlah rekomendasi: 1 jika ada 1 kategori dominan jelas,
     *    2 jika ada tie (2+ kategori dengan quantity yang sama di posisi tertinggi)
     * 3. Untuk tiap kategori dominan, ambil association_categories-nya
     * 4. Sort berdasarkan priority_order kategori asosiasi
     * 5. Dari tiap kategori asosiasi, ambil produk paling laris (stock > 0)
     *    yang BELUM ada di cart — fallback ke produk ke-2, ke-3 dst dalam
     *    kategori yang SAMA (tidak lompat ke kategori lain)
     * 6. Kembalikan maks 2 produk rekomendasi
     */
    public function recommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart'                  => 'required|array|min:1',
            'cart.*.product_id'     => 'required|integer|exists:products,id',
            'cart.*.quantity'       => 'required|integer|min:1',
        ]);

        $cartItems      = $validated['cart'];
        $cartProductIds = collect($cartItems)->pluck('product_id')->toArray();

        // ── Step 1: Hitung jumlah quantity per kategori ──────────────────
        $categoryCount = [];
        foreach ($cartItems as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) continue;
            $catId = $product->category_id;
            $categoryCount[$catId] = ($categoryCount[$catId] ?? 0) + $item['quantity'];
        }

        if (empty($categoryCount)) {
            return response()->json(['recommendations' => []]);
        }

        arsort($categoryCount); // nilai tertinggi di atas

        // ── Step 2: Tentukan jumlah rekomendasi berdasarkan tie ──────────
        // Ambil nilai tertinggi, lalu kumpulkan semua kategori dengan nilai tsb.
        $maxCount      = reset($categoryCount);
        $topCategoryIds = array_keys(array_filter($categoryCount, fn($v) => $v === $maxCount));
        $maxRecommendations = min(count($topCategoryIds), 2); // maks 2

        // Gunakan kategori pertama sebagai "dominant" untuk label di response
        $dominantCategoryId = $topCategoryIds[0];
        $dominantCategory   = Category::find($dominantCategoryId);

        if (!$dominantCategory) {
            return response()->json(['recommendations' => []]);
        }

        // ── Step 3–5: Kumpulkan rekomendasi dari tiap kategori dominan ───
        $recommendations    = [];
        $usedProductIds     = $cartProductIds; // track produk yang sudah direkomendasikan

        foreach ($topCategoryIds as $index => $catId) {
            if (count($recommendations) >= $maxRecommendations) break;

            $category = Category::find($catId);
            if (!$category) continue;

            $associationIds = $category->association_categories ?? [];

            if (empty($associationIds)) {
                // Fallback: best-seller global di luar kategori ini
                $globals = $this->globalBestSellers($usedProductIds, $catId, 1);
                if (!empty($globals)) {
                    $recommendations[] = $globals[0];
                    $usedProductIds[]  = $globals[0]['id'];
                }
                continue;
            }

            $associatedCategories = Category::whereIn('id', $associationIds)
                ->orderBy('priority_order')
                ->get();

            $found = false;
            foreach ($associatedCategories as $assocCategory) {
                // Tetap di dalam kategori asosiasi ini; fallback ke produk ke-2, ke-3 dst
                // dengan cara mengambil semua kandidat (stock > 0, not in cart/used)
                // diurutkan terlaris, lalu ambil yang pertama — ini sudah meng-cover
                // fallback stok karena WHERE stock > 0 memastikan hanya produk tersedia.
                $product = Product::where('category_id', $assocCategory->id)
                    ->where('stock', '>', 0)
                    ->whereNotIn('id', $usedProductIds)
                    ->withSum('orderItems', 'quantity')
                    ->orderByDesc('order_items_sum_quantity')
                    ->orderBy('name') // tie-breaker
                    ->first();

                if ($product) {
                    $recommendations[] = [
                        'id'            => $product->id,
                        'name'          => $product->name,
                        'code'          => $product->code,
                        'unit'          => $product->unit,
                        'selling_price' => $product->selling_price,
                        'stock'         => $product->stock,
                        'photo'         => $product->photo
                            ? asset('storage/' . $product->photo)
                            : null,
                        'category'      => [
                            'id'   => $assocCategory->id,
                            'name' => $assocCategory->name,
                        ],
                        'total_sold'    => $product->order_items_sum_quantity ?? 0,
                    ];
                    $usedProductIds[] = $product->id;
                    $found = true;
                    break; // satu produk per slot dominan kategori
                }
                // Jika tidak ada produk dengan stok di kategori asosiasi ini,
                // lanjut ke kategori asosiasi berikutnya (masih dalam lingkup
                // asosiasi kategori dominan yang sama — tidak lompat ke global)
            }

            // Jika seluruh kategori asosiasi habis tanpa hasil, baru fallback global
            if (!$found) {
                $globals = $this->globalBestSellers($usedProductIds, $catId, 1);
                if (!empty($globals)) {
                    $recommendations[] = $globals[0];
                    $usedProductIds[]  = $globals[0]['id'];
                }
            }
        }

        // ── Fallback tambahan jika slot masih kurang dari maxRecommendations ──
        // (hanya terjadi jika seluruh asosiasi + global tidak cukup produk)
        if (count($recommendations) < $maxRecommendations) {
            $needed  = $maxRecommendations - count($recommendations);
            $globals = $this->globalBestSellers($usedProductIds, $dominantCategoryId, $needed);
            foreach ($globals as $g) {
                $recommendations[] = $g;
                $usedProductIds[]  = $g['id'];
            }
        }

        return response()->json([
            'recommendations'   => $recommendations,
            'dominant_category' => [
                'id'   => $dominantCategory->id,
                'name' => $dominantCategory->name,
            ],
            'top_categories'    => collect($topCategoryIds)
                ->map(fn($id) => Category::find($id))
                ->filter()
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
                ->values(),
        ]);
    }

    /**
     * Fallback: produk terlaris global (di luar kategori & produk yang sudah dipakai).
     */
    private function globalBestSellers(array $excludeProductIds, int $excludeCategoryId, int $limit = 2): array
    {
        return Product::where('stock', '>', 0)
            ->where('category_id', '!=', $excludeCategoryId)
            ->whereNotIn('id', $excludeProductIds)
            ->withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'code'          => $p->code,
                'unit'          => $p->unit,
                'selling_price' => $p->selling_price,
                'stock'         => $p->stock,
                'photo'         => $p->photo
                    ? asset('storage/' . $p->photo)
                    : null,
                'category'      => $p->category ? [
                    'id'   => $p->category->id,
                    'name' => $p->category->name,
                ] : null,
                'total_sold'    => $p->order_items_sum_quantity ?? 0,
            ])
            ->toArray();
    }
}