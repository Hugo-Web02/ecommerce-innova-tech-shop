<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with(['category', 'tags'])->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags'], $validated['image']);

        $product = Product::create($validated);
        $product->tags()->sync($tags);

        return redirect()->route('admin.products.index')->with('status', 'Producto creado correctamente.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            ...$this->formData(),
            'product' => $product->load('tags'),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validated($request, $product);
        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags'], $validated['image']);

        $oldPrice = $product->price;
        $product->fill($validated);

        if ($product->isDirty('price')) {
            Log::info('Cambio de precio de producto', [
                'product_id' => $product->id,
                'old_price' => $oldPrice,
                'new_price' => $product->price,
                'user_id' => $request->user()->id,
            ]);
        }

        $product->save();
        $product->tags()->sync($tags);

        return redirect()->route('admin.products.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Producto eliminado correctamente.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($product),
            ],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
        ]) + ['is_active' => false];
    }
}
