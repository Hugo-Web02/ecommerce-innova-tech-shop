<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BeerCatalogSeeder extends Seeder
{
    /**
     * Prepare beer categories and tags without inserting sample products.
     */
    public function run(): void
    {
        $legacyCategorySlugs = ['laptops', 'componentes', 'perifericos', 'monitores', 'accesorios'];
        $legacyTagSlugs = ['gaming', 'oferta', 'nuevo', 'productividad', 'envio-rapido'];

        // Keep any old product records for order history while removing them from the storefront.
        Product::whereHas('category', fn ($query) => $query->whereIn('slug', $legacyCategorySlugs))
            ->update(['is_active' => false]);

        // Delete obsolete taxonomy rows only when no product references them.
        Category::whereIn('slug', $legacyCategorySlugs)->whereDoesntHave('products')->delete();
        Tag::whereIn('slug', $legacyTagSlugs)->whereDoesntHave('products')->delete();

        collect(['Cervezas', 'Combos', 'Sin alcohol'])->each(
            fn (string $name) => Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )
        );

        collect([
            'Corona', 'Equis Lager', 'Victoria', 'Pacífico', 'Modelo',
            'Mega 1.2 L', '1/4 L', 'Lata 355 ml', '1/2 L', 'Caguama 940 ml', 'Six pack', '12 pack',
        ])->each(fn (string $name) => Tag::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        ));
    }
}
