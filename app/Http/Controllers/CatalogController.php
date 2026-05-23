<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'tags'])
            ->active()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', fn ($query) => $query->where('slug', $request->string('category')));
            })
            ->when($request->filled('tag'), function ($query) use ($request): void {
                $query->whereHas('tags', fn ($query) => $query->where('slug', $request->string('tag')));
            })
            ->when($request->filled('max_price'), function ($query) use ($request): void {
                $query->where('price', '<=', $request->float('max_price'));
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('catalog.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        return view('catalog.show', [
            'product' => $product->load(['category', 'tags']),
        ]);
    }
}
