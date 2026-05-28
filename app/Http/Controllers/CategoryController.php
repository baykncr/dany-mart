<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('products')
            ->orderBy('priority_order')
            ->get()
            ->map(fn($c) => [
                'id'                     => $c->id,
                'name'                   => $c->name,
                'code'                   => $c->code,
                'priority_order'         => $c->priority_order,
                'association_categories' => $c->association_categories ?? [],
                'products_count'         => $c->products_count,
            ]);

        $allCategories = Category::orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Categories/Index', [
            'categories'    => $categories,
            'allCategories' => $allCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:100|unique:categories,name',
            'code'                   => 'required|string|max:20|unique:categories,code|alpha_dash',
            'priority_order'         => 'required|integer|min:0|max:255',
            'association_categories' => 'nullable|array',
            'association_categories.*' => 'integer|exists:categories,id',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['association_categories'] = $validated['association_categories'] ?? [];

        Category::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'                   => "required|string|max:100|unique:categories,name,{$category->id}",
            'code'                   => "required|string|max:20|unique:categories,code,{$category->id}|alpha_dash",
            'priority_order'         => 'required|integer|min:0|max:255',
            'association_categories' => 'nullable|array',
            'association_categories.*' => 'integer|exists:categories,id',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['association_categories'] = $validated['association_categories'] ?? [];

        $validated['association_categories'] = array_filter(
            $validated['association_categories'], 
            fn($id) => (int)$id !== $category->id
    );

        $validated['association_categories'] = array_values($validated['association_categories']);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}