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
        $users = [
            ['Administrador Innova', 'admin@innovatech.test', 'admin'],
            ['Administrador Operaciones', 'operaciones@innovatech.test', 'admin'],
            ['Cliente Demo', 'cliente@innovatech.test', 'customer'],
            ['Cliente Compras', 'compras@innovatech.test', 'customer'],
            ['Cliente Soporte', 'soporte@innovatech.test', 'customer'],
            ['Cliente Mayorista', 'mayorista@innovatech.test', 'customer'],
        ];

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                ]
            );
        }

        $categoryNames = ['Laptops', 'Componentes', 'Perifericos', 'Monitores', 'Accesorios'];
        $categories = collect($categoryNames)->mapWithKeys(function (string $name) {
            return [$name => Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            )];
        });

        $tagNames = ['Gaming', 'Oferta', 'Nuevo', 'Productividad', 'Envio rapido'];
        $tags = collect($tagNames)->map(fn (string $name) => Tag::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        ));

        $products = [
            ['Laptops', 'Laptop InnovaBook Pro 14', 18999, 8, 'Equipo portatil con procesador moderno, pantalla Full HD y almacenamiento SSD para trabajo, escuela y productividad.'],
            ['Componentes', 'SSD NVMe 1TB Velocity', 1699, 20, 'Unidad de estado solido NVMe de alta velocidad para mejorar tiempos de carga y rendimiento general.'],
            ['Perifericos', 'Teclado mecanico RGB NovaKeys', 1299, 15, 'Teclado mecanico con iluminacion RGB, switches tactiles y construccion resistente para gaming y oficina.'],
            ['Monitores', 'Monitor UltraWide 29 pulgadas', 5499, 6, 'Monitor panoramico con excelente espacio de trabajo para multitarea, diseno y entretenimiento.'],
            ['Accesorios', 'Hub USB-C 7 en 1', 899, 18, 'Adaptador compacto con HDMI, USB, lector de tarjetas y carga rapida para laptops modernas.'],
            ['Perifericos', 'Mouse inalambrico Precision X', 749, 25, 'Mouse ergonomico con sensor preciso, bateria de larga duracion y conexion inalambrica estable.'],
            ['Laptops', 'Laptop Gamer Titan RTX 15', 27999, 5, 'Laptop gamer con GPU dedicada, pantalla de alta tasa de refresco y enfriamiento avanzado para juegos exigentes.'],
            ['Componentes', 'Memoria RAM DDR5 32GB Dual Kit', 2499, 12, 'Kit de memoria DDR5 de alto rendimiento para estaciones de trabajo, gaming y multitarea intensiva.'],
            ['Monitores', 'Monitor 4K CreatorView 27 pulgadas', 7999, 7, 'Monitor 4K con excelente definicion, colores precisos y diseno enfocado en productividad y creacion de contenido.'],
        ];

        foreach ($products as [$categoryName, $name, $price, $stock, $description]) {
            $product = Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ]
            );

            $product->tags()->sync($tags->random(2)->pluck('id'));
        }
    }
}
