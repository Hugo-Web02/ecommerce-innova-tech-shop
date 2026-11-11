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
    @if(request()->routeIs('admin.*'))
        <div class="min-h-screen lg:flex">
            <aside class="flex w-full shrink-0 flex-col bg-slate-950 text-slate-200 lg:min-h-screen lg:w-64">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 border-b border-white/10 px-6 py-6 text-white">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-blue-600 font-bold">IT</span>
                    <span><span class="block font-semibold">Admin Panel</span><span class="text-xs text-slate-400">InnovaTechShop</span></span>
                </a>
                <nav class="flex gap-2 overflow-x-auto p-4 lg:flex-1 lg:flex-col" aria-label="Navegación de administración">
                    @php($adminNavigation = [
                        ['admin.dashboard', 'Dashboard', '⌂'],
                        ['admin.products.index', 'Productos', '▦'],
                        ['admin.payments.index', 'Pagos', '＄'],
                        ['admin.users.index', 'Usuarios', '♙'],
                        ['admin.settings.edit', 'Configuración', '⚙'],
                    ])
                    @foreach($adminNavigation as [$routeName, $label, $icon])
                        <a href="{{ route($routeName) }}" @class([
                            'flex shrink-0 items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                            'bg-blue-600 text-white shadow-lg shadow-blue-950/30' => request()->routeIs($routeName) || ($routeName === 'admin.products.index' && request()->routeIs('admin.products.*')),
                            'text-slate-300 hover:bg-white/10 hover:text-white' => !request()->routeIs($routeName) && !($routeName === 'admin.products.index' && request()->routeIs('admin.products.*')),
                        ])>
                            <span class="w-5 text-center text-base" aria-hidden="true">{{ $icon }}</span>{{ $label }}
                        </a>
                    @endforeach
                </nav>
                <div class="hidden border-t border-white/10 p-5 text-xs text-slate-400 lg:block">Sesión: {{ auth()->user()->name }}</div>
            </aside>
            <div class="min-w-0 flex-1">
                <header class="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4 sm:px-8">
                    <div><p class="text-sm text-slate-500">Administración</p><h1 class="text-lg font-semibold text-slate-900">{{ $title ?? 'Panel de administración' }}</h1></div>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('home') }}" class="hidden text-slate-600 hover:text-blue-700 sm:inline">Ver tienda</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button class="font-semibold text-slate-600 hover:text-blue-700">Salir</button></form>
                    </div>
                </header>
                <main class="mx-auto max-w-7xl px-4 py-7 sm:px-8 sm:py-9">
                    @if(session('status'))
                        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            <p class="font-semibold">Revisa los datos ingresados.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
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
                        <a href="{{ route('admin.dashboard') }}" class="font-medium text-slate-700 hover:text-blue-700">Admin</a>
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
    @endif
</body>
</html>
