<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 * Store-wide preferences persisted in Laravel's existing cache store.
 * This deliberately uses the already configured database cache and needs no new table.
 */
class StoreSetting
{
    private const CACHE_KEY = 'innova.store_settings';

    public static function defaults(): array
    {
        return [
            'store_name' => 'InnovaTechShop',
            'email' => '',
            'phone' => '',
            'address' => '',
            'timezone' => 'America/Mexico_City',
            'currency' => 'MXN',
        ];
    }

    public static function all(): array
    {
        return array_replace(self::defaults(), Cache::get(self::CACHE_KEY, []));
    }

    public static function option(string $key, string $default): string
    {
        $value = self::all()[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function formatCurrency($amount, ?string $currency = null): string
    {
        $currency ??= self::option('currency', 'MXN');
        $symbol = $currency === 'EUR' ? '€' : '$';

        return $symbol.number_format((float) $amount, 2).' '.$currency;
    }

    public static function save(array $settings): void
    {
        Cache::forever(self::CACHE_KEY, array_replace(self::defaults(), $settings));
    }
}
