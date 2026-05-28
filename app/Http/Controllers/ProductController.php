<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::with('category:id,name,code')
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($subQuery) => 
                    $subQuery->where('name', 'like', "%$s%")
                        ->orWhere('code', 'like', "%$s%")
                )
            )
            ->when($request->category_id, fn($q, $id) =>
                $q->where('category_id', $id)
            )
            ->orderBy('name');

        $products = $query->paginate(12)->withQueryString()
            ->through(fn($p) => [
                'id'             => $p->id,
                'code'           => $p->code,
                'name'           => $p->name,
                'unit'           => $p->unit,
                'purchase_price' => $p->purchase_price,
                'selling_price'  => $p->selling_price,
                'stock'          => $p->stock,
                'photo'          => $p->photo ? Storage::url($p->photo) : null,
                'category'       => $p->category,
            ]);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'code']),
            'filters'    => $request->only(['search', 'category_id']),
        ]);
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'code'           => 'required|string|max:50|unique:products,code',
            'name'           => 'required|string|max:200',
            'unit'           => 'required|string|max:30',
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:1',
            'stock'          => 'required|integer|min:0',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        Product::create($validated);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => 'required|exists:categories,id',
            'code'           => "required|string|max:50|unique:products,code,{$product->id}",
            'name'           => 'required|string|max:200',
            'unit'           => 'required|string|max:30',
            'purchase_price' => 'required|integer|min:0',
            'selling_price'  => 'required|integer|min:1',
            'stock'          => 'required|integer|min:0',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($product->photo) Storage::disk('public')->delete($product->photo);
            $validated['photo'] = $request->file('photo')->store('products', 'public');
        }

        $product->update($validated);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->count() > 0) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        if ($product->photo) Storage::disk('public')->delete($product->photo);
        $product->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }
}