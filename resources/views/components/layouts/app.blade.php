<!DOCTYPE html>
@php($storeSettings = \App\Models\StoreSetting::all())
@php($accountTheme = auth()->check() ? \App\Models\AccountPreference::preferences(auth()->id())['theme'] : 'light')
<html lang="{{ app()->getLocale() }}" data-theme="{{ $accountTheme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' | '.$storeSettings['store_name'] : $storeSettings['store_name'] }}</title>
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
                    <span><span class="block font-semibold">{{ __('Admin panel') }}</span><span class="text-xs text-slate-400">{{ $storeSettings['store_name'] }}</span></span>
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
                        <a href="{{ route('home') }}" class="hidden text-slate-600 hover:text-blue-700 sm:inline">{{ __('View store') }}</a>
                        <details class="group relative">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 shadow-sm">
                                <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-100 text-xs font-bold text-blue-800">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="max-w-32 truncate">{{ auth()->user()->name }}</span><span aria-hidden="true">⌄</span>
                            </summary>
                            <div class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-xl">
                                <div class="border-b border-slate-100 px-4 py-3"><p class="font-semibold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
                                <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.profile') }}">{{ __('My profile link') }}</a>
                                <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.orders') }}">{{ __('My orders link') }}</a>
                                <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.preferences') }}">{{ __('Settings') }}</a>
                                <a class="block border-t border-slate-100 px-4 py-2.5 font-semibold text-blue-700 hover:bg-blue-50" href="{{ route('admin.dashboard') }}">{{ __('Admin panel') }}</a>
                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">@csrf<button class="w-full px-4 py-2.5 text-left text-red-700 hover:bg-red-50">{{ __('Sign out') }}</button></form>
                            </div>
                        </details>
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
            <a href="{{ route('home') }}" class="text-xl font-bold text-blue-700">{{ $storeSettings['store_name'] }}</a>

            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('home') }}" class="font-medium text-slate-700 hover:text-blue-700">{{ __('Catalog') }}</a>
                <a href="{{ route('cart.index') }}" class="font-medium text-slate-700 hover:text-blue-700">
                    {{ __('Cart') }} ({{ collect(session('cart', []))->sum('quantity') }})
                </a>

                @auth
                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 shadow-sm hover:border-blue-300">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-100 text-xs font-bold text-blue-800">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="max-w-28 truncate">{{ auth()->user()->name }}</span><span aria-hidden="true">⌄</span>
                        </summary>
                        <div class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-sm shadow-xl">
                            <div class="border-b border-slate-100 px-4 py-3"><p class="font-semibold text-slate-800">{{ auth()->user()->name }}</p><p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p></div>
                            <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.profile') }}">{{ __('My profile link') }}</a>
                            <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.orders') }}">{{ __('My orders link') }}</a>
                            <a class="block px-4 py-2.5 hover:bg-slate-50" href="{{ route('account.preferences') }}">{{ __('Settings') }}</a>
                            @if(auth()->user()->isAdmin())<a class="block border-t border-slate-100 px-4 py-2.5 font-semibold text-blue-700 hover:bg-blue-50" href="{{ route('admin.dashboard') }}">{{ __('Admin panel') }}</a>@endif
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100">@csrf<button class="w-full px-4 py-2.5 text-left text-red-700 hover:bg-red-50">{{ __('Sign out') }}</button></form>
                        </div>
                    </details>
                @else
                    <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-blue-700">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="rounded bg-blue-700 px-3 py-2 font-semibold text-white">{{ __('Register') }}</a>
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
            <div><span class="font-semibold">{{ $storeSettings['store_name'] }}</span><span class="block text-xs">{{ __('Store contact') }}</span>
                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    @if($storeSettings['email'])<a class="hover:text-blue-700" href="mailto:{{ $storeSettings['email'] }}">{{ $storeSettings['email'] }}</a>@endif
                    @if($storeSettings['phone'])<a class="hover:text-blue-700" href="tel:{{ $storeSettings['phone'] }}">{{ $storeSettings['phone'] }}</a>@endif
                    @if($storeSettings['address'])<span>{{ $storeSettings['address'] }}</span>@endif
                </div>
            </div>
            <span>{{ __('Store footer') }}</span>
        </div>
    </footer>
    @endif
</body>
</html>
