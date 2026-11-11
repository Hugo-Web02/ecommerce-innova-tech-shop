<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class AccountPreference
{
    public static function profile(int $userId): array
    {
        return array_replace(['phone' => '', 'address' => ''], Cache::get("account.{$userId}.profile", []));
    }

    public static function preferences(int $userId): array
    {
        return array_replace([
            'email_promotions' => false,
            'email_order_updates' => true,
            'sms_order_updates' => false,
            'theme' => 'light',
            'language' => 'es',
        ], Cache::get("account.{$userId}.preferences", []));
    }

    public static function saveProfile(int $userId, array $profile): void
    {
        Cache::forever("account.{$userId}.profile", $profile);
    }

    public static function savePreferences(int $userId, array $preferences): void
    {
        Cache::forever("account.{$userId}.preferences", $preferences);
    }
}
