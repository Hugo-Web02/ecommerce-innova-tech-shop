@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="block">
        <span class="text-sm font-medium">Nombre</span>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" required class="mt-1 w-full rounded border-slate-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium">Categoria</span>
        <select name="category_id" required class="mt-1 w-full rounded border-slate-300">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id ?? 0) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="block">
        <span class="text-sm font-medium">Precio</span>
        <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required class="mt-1 w-full rounded border-slate-300">
    </label>

    <label class="block">
        <span class="text-sm font-medium">Stock</span>
        <input name="stock" type="number" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="mt-1 w-full rounded border-slate-300">
    </label>

    <label class="block md:col-span-2">
        <span class="text-sm font-medium">Descripcion</span>
        <textarea name="description" rows="5" required class="mt-1 w-full rounded border-slate-300">{{ old('description', $product->description ?? '') }}</textarea>
    </label>

    <label class="block">
        <span class="text-sm font-medium">Imagen</span>
        <input name="image" type="file" accept="image/*" class="mt-1 w-full rounded border border-slate-300 bg-white p-2">
    </label>

    <label class="flex items-center gap-2 pt-7">
        <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded">
        Producto activo
    </label>
</div>

<fieldset class="mt-5">
    <legend class="text-sm font-medium">Etiquetas</legend>
    <div class="mt-2 flex flex-wrap gap-3">
        @foreach($tags as $tag)
            <label class="flex items-center gap-2 rounded border bg-white px-3 py-2 text-sm">
                <input
                    name="tags[]"
                    type="checkbox"
                    value="{{ $tag->id }}"
                    @checked(in_array($tag->id, old('tags', isset($product) ? $product->tags->pluck('id')->all() : [])))
                    class="rounded"
                >
                {{ $tag->name }}
            </label>
        @endforeach
    </div>
</fieldset>

<div class="mt-6 flex gap-3">
    <button class="rounded bg-blue-700 px-4 py-2 font-semibold text-white">Guardar</button>
    <a href="{{ route('admin.products.index') }}" class="rounded border px-4 py-2 font-semibold">Cancelar</a>
</div>
