<x-layouts.app title="Administrar productos">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold">Productos</h1>
        <a href="{{ route('admin.products.create') }}" class="rounded bg-blue-700 px-4 py-2 font-semibold text-white">Nuevo producto</a>
    </div>

    <div class="mt-6 overflow-hidden rounded border bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100">
                <tr>
                    <th class="p-3">Producto</th>
                    <th class="p-3">Categoria</th>
                    <th class="p-3">Precio</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Estado</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr class="border-t">
                        <td class="p-3 font-semibold">{{ $product->name }}</td>
                        <td class="p-3">{{ $product->category->name }}</td>
                        <td class="p-3">${{ number_format($product->price, 2) }}</td>
                        <td class="p-3">{{ $product->stock }}</td>
                        <td class="p-3">{{ $product->is_active ? 'Activo' : 'Inactivo' }}</td>
                        <td class="p-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="rounded border px-3 py-2 font-semibold">Editar</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded bg-red-600 px-3 py-2 font-semibold text-white" data-confirm="Eliminar producto?">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
</x-layouts.app>
