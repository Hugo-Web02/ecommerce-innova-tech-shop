<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'InnovaTechShop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/store.css') }}">
    <script src="{{ asset('js/store.js') }}" defer></script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <header class="border-b bg-white">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold text-blue-700">InnovaTechShop</a>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-slate-700 hover:text-blue-700">Catalogo</a>
                <a href="{{ route('cart.index') }}" class="font-medium text-slate-700 hover:text-blue-700">
                    Carrito ({{ collect(session('cart', []))->sum('quantity') }})
                </a>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.products.index') }}" class="font-medium text-slate-700 hover:text-blue-700">Admin</a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="font-medium text-slate-700 hover:text-blue-700">Salir</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-blue-700">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded bg-blue-700 px-3 py-2 font-semibold text-white">Registro</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        @if(session('status'))
            <div class="mb-6 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Revisa los datos ingresados.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-4 py-6 text-sm text-slate-600">
            <span>InnovaTechShop - Ecommerce Laravel</span>
            <span>Catalogo, carrito y checkout simulado</span>
        </div>
    </footer>
</body>
</html>
