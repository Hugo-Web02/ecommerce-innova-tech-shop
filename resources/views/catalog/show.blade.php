<x-layouts.app title="{{ $product->name }}">
    <section class="grid gap-8 md:grid-cols-2">
        <div class="overflow-hidden rounded border bg-white">
            @if($product->image_path)
                <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full min-h-96 w-full object-cover">
            @else
                <div class="flex min-h-96 items-center justify-center bg-slate-200 font-semibold text-slate-500">Sin imagen</div>
            @endif
        </div>

        <div>
            <p class="text-sm font-semibold uppercase text-blue-700">{{ $product->category->name }}</p>
            <h1 class="mt-2 text-3xl font-bold">{{ $product->name }}</h1>
            <p class="mt-4 text-slate-700">{{ $product->description }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($product->tags as $tag)
                    <span class="rounded bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-700">{{ $tag->name }}</span>
                @endforeach
            </div>

            <p class="mt-6 text-3xl font-bold">${{ number_format($product->price, 2) }}</p>
            <p class="mt-1 text-sm text-slate-500">Disponibles: {{ $product->stock }}</p>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-6 flex max-w-sm gap-3">
                @csrf
                <input name="quantity" type="number" min="1" max="{{ max($product->stock, 1) }}" value="1" class="w-24 rounded border-slate-300">
                <button class="flex-1 rounded bg-blue-700 px-4 py-2 font-semibold text-white" @disabled($product->stock === 0)>
                    Agregar al carrito
                </button>
            </form>
        </div>
    </section>
</x-layouts.app>
