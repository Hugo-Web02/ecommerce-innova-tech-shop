<x-layouts.app title="Productos">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4"><div><p class="text-sm font-medium text-blue-700">Catálogo</p><h2 class="mt-1 text-2xl font-bold tracking-tight">Productos</h2><p class="mt-1 text-sm text-slate-500">Administra el inventario, precios y disponibilidad.</p></div><a href="{{ route('admin.products.create') }}" class="rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800">+ Nuevo producto</a></div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([['Total productos', $productCount, 'En el catálogo'], ['En stock', $inStockCount, 'Con unidades disponibles'], ['Agotados', $outOfStockCount, 'Sin unidades disponibles'], ['Categorías', $categoryCount, 'Categorías del catálogo']] as [$label, $value, $description])
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><p class="mt-3 text-2xl font-bold">{{ $value }}</p><p class="mt-1 text-xs text-slate-500">{{ $description }}</p></article>
        @endforeach
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <form method="GET" class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-4">
            <input name="search" value="{{ request('search') }}" placeholder="Buscar producto..." class="min-w-48 flex-1 rounded-lg border-slate-200 text-sm focus:border-blue-500 focus:ring-blue-500">
            <select name="category" class="rounded-lg border-slate-200 text-sm"><option value="">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="status" class="rounded-lg border-slate-200 text-sm"><option value="">Todos los estados</option><option value="active" @selected(request('status') === 'active')>Activo</option><option value="inactive" @selected(request('status') === 'inactive')>Inactivo</option></select>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filtrar</button>
        </form>
        <div class="overflow-x-auto"><table class="w-full min-w-[780px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Producto</th><th class="px-5 py-3">Categoría</th><th class="px-5 py-3">Precio</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($products as $product)
                <tr class="hover:bg-slate-50/70"><td class="px-5 py-3"><div class="flex items-center gap-3">@if($product->imageUrl())<img src="{{ $product->imageUrl() }}" alt="" class="h-11 w-11 rounded-lg border border-slate-200 object-cover">@else<span class="grid h-11 w-11 place-items-center rounded-lg bg-blue-50 text-blue-700">▦</span>@endif<div><p class="font-semibold text-slate-900">{{ $product->name }}</p><p class="text-xs text-slate-500">{{ $product->tags->pluck('name')->join(', ') ?: 'Sin etiquetas' }}</p></div></div></td><td class="px-5 py-3 text-slate-600">{{ $product->category?->name }}</td><td class="px-5 py-3 font-medium">${{ number_format((float) $product->price, 2) }} {{ $currency }}</td><td class="px-5 py-3"><span @class(['font-medium', 'text-amber-700' => $product->stock <= 5, 'text-slate-700' => $product->stock > 5])>{{ $product->stock }} uds.</span></td><td class="px-5 py-3"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-50 text-emerald-700' => $product->is_active, 'bg-slate-100 text-slate-600' => !$product->is_active])>{{ $product->is_active ? 'Activo' : 'Inactivo' }}</span></td><td class="px-5 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold hover:border-blue-300 hover:text-blue-700">Editar</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}">@csrf @method('DELETE')<button class="rounded-lg border border-red-100 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50" data-confirm="¿Eliminar producto?">Eliminar</button></form></div></td></tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No se encontraron productos con esos filtros.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $products->links() }}</div>
    </section>
</x-layouts.app>
