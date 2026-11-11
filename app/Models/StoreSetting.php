<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'email',
        'phone',
        'address',
        'timezone',
        'currency',
    ];

    public static function option(string $key, string $default): string
    {
        if (! Schema::hasTable((new static)->getTable())) {
            return $default;
        }

        $value = static::query()->value($key);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
