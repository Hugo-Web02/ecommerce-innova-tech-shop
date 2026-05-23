<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrador Innova',
            'email' => 'admin@innovatech.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Cliente Demo',
            'email' => 'cliente@innovatech.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        $categoryNames = ['Laptops', 'Componentes', 'Perifericos', 'Monitores', 'Accesorios'];
        $categories = collect($categoryNames)->mapWithKeys(function (string $name) {
            return [$name => Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ])];
        });

        $tagNames = ['Gaming', 'Oferta', 'Nuevo', 'Productividad', 'Envio rapido'];
        $tags = collect($tagNames)->map(fn (string $name) => Tag::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]));

        $products = [
            ['Laptops', 'Laptop InnovaBook Pro 14', 18999, 8, 'Equipo portatil con procesador moderno, pantalla Full HD y almacenamiento SSD para trabajo, escuela y productividad.'],
            ['Componentes', 'SSD NVMe 1TB Velocity', 1699, 20, 'Unidad de estado solido NVMe de alta velocidad para mejorar tiempos de carga y rendimiento general.'],
            ['Perifericos', 'Teclado mecanico RGB NovaKeys', 1299, 15, 'Teclado mecanico con iluminacion RGB, switches tactiles y construccion resistente para gaming y oficina.'],
            ['Monitores', 'Monitor UltraWide 29 pulgadas', 5499, 6, 'Monitor panoramico con excelente espacio de trabajo para multitarea, diseno y entretenimiento.'],
            ['Accesorios', 'Hub USB-C 7 en 1', 899, 18, 'Adaptador compacto con HDMI, USB, lector de tarjetas y carga rapida para laptops modernas.'],
            ['Perifericos', 'Mouse inalambrico Precision X', 749, 25, 'Mouse ergonomico con sensor preciso, bateria de larga duracion y conexion inalambrica estable.'],
        ];

        foreach ($products as [$categoryName, $name, $price, $stock, $description]) {
            $product = Product::create([
                'category_id' => $categories[$categoryName]->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'is_active' => true,
            ]);

            $product->tags()->sync($tags->random(2)->pluck('id'));
        }
    }
}
