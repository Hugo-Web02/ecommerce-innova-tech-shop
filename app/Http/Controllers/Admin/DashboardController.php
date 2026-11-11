<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $timezone = StoreSetting::query()->value('timezone') ?: 'America/Mexico_City';
        $startOfDay = Carbon::now($timezone)->startOfDay();
        $startOfDayUtc = $startOfDay->copy()->utc();
        $endOfDayUtc = $startOfDay->copy()->endOfDay()->utc();

        return view('admin.dashboard', [
            'currency' => StoreSetting::query()->value('currency') ?: 'MXN',
            'timezone' => $timezone,
            'salesToday' => Order::where('status', 'paid')->whereBetween('created_at', [$startOfDayUtc, $endOfDayUtc])->sum('total'),
            'ordersToday' => Order::whereBetween('created_at', [$startOfDayUtc, $endOfDayUtc])->count(),
            'productCount' => Product::count(),
            'customerCount' => User::where('role', 'customer')->count(),
            'recentOrders' => Order::with('user')->latest()->limit(5)->get(),
            'bestSellers' => OrderItem::query()
                ->select('product_id', DB::raw('SUM(quantity) as units_sold'))
                ->with('product.category')
                ->groupBy('product_id')
                ->orderByDesc('units_sold')
                ->limit(5)
                ->get(),
        ]);
    }
}
