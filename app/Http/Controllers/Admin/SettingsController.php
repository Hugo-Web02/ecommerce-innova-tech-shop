<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        if (! Schema::hasTable('store_settings')) {
            $settings = (object) [
                'store_name' => 'InnovaTechShop',
                'email' => null,
                'phone' => null,
                'address' => null,
                'timezone' => 'America/Mexico_City',
                'currency' => 'MXN',
            ];

            return view('admin.settings.edit', ['settings' => $settings, 'migrationPending' => true]);
        }

        $settings = StoreSetting::firstOrCreate(['id' => 1]);

        return view('admin.settings.edit', ['settings' => $settings, 'migrationPending' => false]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('store_settings')) {
            return back()->withErrors(['settings' => 'Para guardar la configuración, ejecuta primero la migración pendiente con php artisan migrate.']);
        }

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'currency' => ['required', 'string', 'size:3', 'in:MXN,USD,EUR'],
        ]);

        StoreSetting::updateOrCreate(['id' => 1], $validated);

        return redirect()->route('admin.settings.edit')->with('status', 'Configuración guardada correctamente.');
    }
}
