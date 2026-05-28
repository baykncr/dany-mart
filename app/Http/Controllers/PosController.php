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
     * 2. Ambil association_categories dari kategori dominan
     * 3. Sort berdasarkan priority_order kategori asosiasi
     * 4. Dari tiap kategori asosiasi, ambil produk paling laris (stock > 0)
     *    yang BELUM ada di cart
     * 5. Kembalikan maks 2 produk rekomendasi
     */
    public function recommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cart'          => 'required|array|min:1',
            'cart.*.product_id' => 'required|integer|exists:products,id',
            'cart.*.quantity'   => 'required|integer|min:1',
        ]);

        $cartItems    = $validated['cart'];
        $cartProductIds = collect($cartItems)->pluck('product_id')->toArray();

        // ── Step 1: Hitung kategori dominan ────────────────────────────
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

        arsort($categoryCount); // tertinggi di atas
        $dominantCategoryId = array_key_first($categoryCount);
        $dominantCategory   = Category::find($dominantCategoryId);

        if (!$dominantCategory) {
            return response()->json(['recommendations' => []]);
        }

        // ── Step 2 & 3: Ambil association categories, sort by priority ──
        $associationIds = $dominantCategory->association_categories ?? [];

        if (empty($associationIds)) {
            // Fallback: ambil produk terlaris global yang bukan dari kategori dominan
            return response()->json([
                'recommendations' => $this->globalBestSellers($cartProductIds, $dominantCategoryId),
            ]);
        }

        $associatedCategories = Category::whereIn('id', $associationIds)
            ->orderBy('priority_order')
            ->get();

        // ── Step 4 & 5: Cari produk terlaris dari tiap kategori asosiasi ─
        $recommendations = [];

        foreach ($associatedCategories as $assocCategory) {
            if (count($recommendations) >= 2) break;

            // Produk terlaris dari kategori ini (berdasarkan total order_items)
            // yang stock > 0 dan belum di cart
            $product = Product::where('category_id', $assocCategory->id)
                ->where('stock', '>', 0)
                ->whereNotIn('id', $cartProductIds)
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
            }
        }

        // Fallback jika rekomendasi kurang dari 2
        if (count($recommendations) < 2) {
            $existingIds = array_merge(
                $cartProductIds,
                collect($recommendations)->pluck('id')->toArray()
            );
            $needed  = 2 - count($recommendations);
            $globals = $this->globalBestSellers($existingIds, $dominantCategoryId, $needed);
            $recommendations = array_merge($recommendations, $globals);
        }

        return response()->json([
            'recommendations'  => $recommendations,
            'dominant_category' => [
                'id'   => $dominantCategory->id,
                'name' => $dominantCategory->name,
            ],
        ]);
    }

    /**
     * Fallback: produk terlaris global (di luar kategori & cart yang ada).
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