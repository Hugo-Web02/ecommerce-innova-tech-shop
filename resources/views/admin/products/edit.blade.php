<x-layouts.app title="Editar producto">
    <h1 class="text-2xl font-bold">Editar producto</h1>

    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-6 rounded border bg-white p-6 shadow-sm">
        @method('PUT')
        @include('admin.products.form')
    </form>
</x-layouts.app>
