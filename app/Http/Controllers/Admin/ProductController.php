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
        } elseif ($request->filled('image_url')) {
            $validated['image_path'] = $request->string('image_url')->trim()->toString();
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags'], $validated['image'], $validated['image_url']);

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
            if ($product->hasLocalImage()) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('products', 'public');
        } elseif ($request->filled('image_url')) {
            if ($product->hasLocalImage()) {
                Storage::disk('public')->delete($product->image_path);
            }

            $validated['image_path'] = $request->string('image_url')->trim()->toString();
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags'], $validated['image'], $validated['image_url']);

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
        if ($product->hasLocalImage()) {
            Storage::disk('public')->delete($product->image_path);
        }

        Product::destroy($product->id);

        return redirect()->route('admin.products.index')->with('status', 'Producto eliminado correctamente.');
    }

    private function formData(): array
    {
        return [
            'categories' => Category::orderBy('name', 'asc')->get(),
            'tags' => Tag::orderBy('name', 'asc')->get(),
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
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'tags' => ['array'],
            'tags.*' => ['exists:tags,id'],
        ], [
            'image.uploaded' => 'La imagen local no se pudo subir. Revisa que pese menos que el limite de PHP o usa una URL de internet.',
            'image.max' => 'La imagen local no debe pesar mas de 10 MB.',
            'image.mimes' => 'La imagen local debe ser jpg, jpeg, png, webp o gif.',
            'image_url.url' => 'La URL de imagen debe ser una direccion valida.',
        ]) + ['is_active' => false];
    }
}
