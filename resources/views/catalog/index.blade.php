<x-layouts.app title="Catalogo">
    <section class="mb-8">
        <h1 class="text-3xl font-bold">Catalogo de tecnologia</h1>
        <p class="mt-2 max-w-2xl text-slate-600">Productos de computo, accesorios y perifericos listos para comprar con checkout simulado.</p>
    </section>

    <form method="GET" action="{{ route('home') }}" class="mb-8 grid gap-3 rounded border bg-white p-4 shadow-sm md:grid-cols-5">
        <input name="search" value="{{ request('search') }}" placeholder="Buscar productos" class="rounded border-slate-300 md:col-span-2">

        <select name="category" class="rounded border-slate-300">
            <option value="">Todas las categorias</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>

        <select name="tag" class="rounded border-slate-300">
            <option value="">Todas las etiquetas</option>
            @foreach($tags as $tag)
                <option value="{{ $tag->slug }}" @selected(request('tag') === $tag->slug)>{{ $tag->name }}</option>
            @endforeach
        </select>

        <button class="rounded bg-blue-700 px-4 py-2 font-semibold text-white">Filtrar</button>
    </form>

    <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($products as $product)
            <article class="flex flex-col overflow-hidden rounded border bg-white shadow-sm">
                <a href="{{ route('products.show', $product) }}" class="block">
                    @if($product->image_path)
                        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-52 w-full object-cover">
                    @else
                        <div class="flex h-52 w-full items-center justify-center bg-slate-200 text-sm font-semibold text-slate-500">Sin imagen</div>
                    @endif
                </a>

                <div class="flex flex-1 flex-col p-5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold uppercase text-blue-700">{{ $product->category->name }}</span>
                        <span class="text-xs text-slate-500">Stock: {{ $product->stock }}</span>
                    </div>

                    <h2 class="mt-2 text-lg font-bold">{{ $product->name }}</h2>
                    <p class="mt-2 flex-1 text-sm text-slate-600">{{ Str::limit($product->description, 100) }}</p>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xl font-bold">${{ number_format($product->price, 2) }}</span>
                        <a href="{{ route('products.show', $product) }}" class="rounded border px-3 py-2 text-sm font-semibold text-blue-700">Ver detalle</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded border bg-white p-6 text-center text-slate-600">No se encontraron productos.</p>
        @endforelse
    </section>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</x-layouts.app>
