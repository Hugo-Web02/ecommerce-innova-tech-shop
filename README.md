# InnovaTechShop - Ecommerce basico en Laravel

## Resumen del proyecto

**InnovaTechShop** es un sistema ecommerce basico desarrollado con Laravel. El proyecto incluye catalogo de productos, autenticacion, roles, panel administrativo, carrito de compras, checkout simulado, migraciones, seeders, relaciones Eloquent, vistas Blade con Tailwind CSS y pruebas con PHPUnit.

En esta continuacion del proyecto se cambio la base de datos local SQLite por una base de datos **MySQL remota en AlwaysData**. La aplicacion ya no queda configurada para usar `database/database.sqlite`; ahora trabaja con la conexion MySQL definida en `.env`.

## Tecnologias utilizadas

- Laravel 13
- PHP 8.3
- MySQL en AlwaysData
- Eloquent ORM
- Blade
- Tailwind CSS con Vite
- PHPUnit
- Git y GitHub

## Configuracion de base de datos MySQL

La conexion principal se configuro en `.env` con MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=mysql-hugo20271015.alwaysdata.net
DB_PORT=3306
DB_DATABASE=hugo20271015_innova_tech_shop
DB_USERNAME=hugo20271015
DB_PASSWORD=********
```

La contrasena real esta colocada en el archivo `.env` local para que Laravel pueda conectarse. En este README se oculta con asteriscos para no exponer credenciales.

Despues de cambiar `.env`, se limpio la cache de configuracion:

```bash
php artisan config:clear
```

Luego se ejecutaron migraciones y seeders directamente sobre MySQL:

```bash
php artisan migrate --seed --force
```

Resultado de la carga inicial en MySQL:

```txt
users: 6
admins: 2
customers: 4
products: 9
categories: 5
tags: 5
```

## Tablas creadas

Las migraciones crean tablas propias del ecommerce y tablas internas de Laravel.

Tablas principales:

- `users`: usuarios registrados. Incluye el campo `role` para distinguir administradores y clientes.
- `categories`: categorias de productos.
- `tags`: etiquetas de productos.
- `products`: productos del catalogo.
- `product_tag`: tabla pivote entre productos y etiquetas.
- `orders`: pedidos generados en el checkout.
- `order_items`: detalle de productos dentro de cada pedido.

Tablas internas de Laravel:

- `migrations`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`
- `password_reset_tokens`

Fragmento de migracion para productos:

```php
Schema::create('products', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description');
    $table->decimal('price', 10, 2);
    $table->unsignedInteger('stock')->default(0);
    $table->string('image_path')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

## Seeders actualizados

El seeder se modifico para ser idempotente usando `updateOrCreate`. Esto evita errores por emails o slugs duplicados si se ejecuta varias veces.

Fragmento:

```php
User::updateOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'password' => Hash::make('password'),
        'role' => $role,
    ]
);
```

Tambien se uso `updateOrCreate` para categorias, etiquetas y productos:

```php
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
```

## Usuarios creados

El proyecto ahora carga 6 usuarios iniciales:

Administradores:

- `admin@innovatech.test`
- `operaciones@innovatech.test`

Clientes:

- `cliente@innovatech.test`
- `compras@innovatech.test`
- `soporte@innovatech.test`
- `mayorista@innovatech.test`

Todos los usuarios de prueba usan:

```txt
password
```

## Productos creados

Productos originales:

- Laptop InnovaBook Pro 14
- SSD NVMe 1TB Velocity
- Teclado mecanico RGB NovaKeys
- Monitor UltraWide 29 pulgadas
- Hub USB-C 7 en 1
- Mouse inalambrico Precision X

Productos nuevos agregados:

- Laptop Gamer Titan RTX 15
- Memoria RAM DDR5 32GB Dual Kit
- Monitor 4K CreatorView 27 pulgadas

Fragmento del arreglo de productos:

```php
$products = [
    ['Laptops', 'Laptop InnovaBook Pro 14', 18999, 8, 'Equipo portatil...'],
    ['Componentes', 'SSD NVMe 1TB Velocity', 1699, 20, 'Unidad de estado solido...'],
    ['Perifericos', 'Teclado mecanico RGB NovaKeys', 1299, 15, 'Teclado mecanico...'],
    ['Laptops', 'Laptop Gamer Titan RTX 15', 27999, 5, 'Laptop gamer con GPU dedicada...'],
    ['Componentes', 'Memoria RAM DDR5 32GB Dual Kit', 2499, 12, 'Kit de memoria DDR5...'],
    ['Monitores', 'Monitor 4K CreatorView 27 pulgadas', 7999, 7, 'Monitor 4K...'],
];
```

## Modelos y relaciones Eloquent

Modelo `Product`:

```php
public function category()
{
    return $this->belongsTo(Category::class);
}

public function tags()
{
    return $this->belongsToMany(Tag::class);
}

public function orderItems()
{
    return $this->hasMany(OrderItem::class);
}
```

El producto usa `slug` para las URLs:

```php
public function getRouteKeyName(): string
{
    return 'slug';
}
```

## Catalogo de productos

El catalogo se maneja con `CatalogController`. Permite listar productos activos, buscar por texto y filtrar por categoria, etiqueta y precio maximo.

```php
$products = Product::query()
    ->with(['category', 'tags'])
    ->active()
    ->when($request->filled('search'), function ($query) use ($request): void {
        $search = $request->string('search')->toString();

        $query->where(function ($query) use ($search): void {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    })
    ->latest()
    ->paginate(9)
    ->withQueryString();
```

Vistas:

- `resources/views/catalog/index.blade.php`
- `resources/views/catalog/show.blade.php`

## CRUD administrativo

El administrador puede crear, editar y eliminar productos. Las rutas estan protegidas con `auth` y `admin`.

Validacion:

```php
return $request->validate([
    'category_id' => ['required', 'exists:categories,id'],
    'name' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string'],
    'price' => ['required', 'numeric', 'min:0'],
    'stock' => ['required', 'integer', 'min:0'],
    'image' => ['nullable', 'image', 'max:2048'],
    'tags' => ['array'],
    'tags.*' => ['exists:tags,id'],
]);
```

Auditoria de cambio de precio:

```php
if ($product->isDirty('price')) {
    Log::info('Cambio de precio de producto', [
        'product_id' => $product->id,
        'old_price' => $oldPrice,
        'new_price' => $product->price,
        'user_id' => $request->user()->id,
    ]);
}
```

## Autenticacion y roles

Se implementaron roles:

- `admin`: acceso al panel administrativo.
- `customer`: acceso como cliente.

Middleware:

```php
public function handle(Request $request, Closure $next): Response
{
    abort_unless($request->user()?->role === 'admin', 403);

    return $next($request);
}
```

## Carrito de compras

El carrito se guarda en sesion. Permite agregar productos, actualizar cantidades, eliminar productos y calcular totales.

```php
$cart = session('cart', []);
$subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
$tax = round($subtotal * 0.16, 2);
$total = $subtotal + $tax;
```

## Checkout simulado

El checkout crea un pedido, registra sus productos, descuenta inventario y limpia el carrito.

```php
$order = $request->user()->orders()->create([
    'subtotal' => $totals['subtotal'],
    'tax' => $totals['tax'],
    'total' => $totals['total'],
    'status' => 'paid',
]);
```

## Rutas principales

```txt
GET    /                         CatalogController@index
GET    /products/{product}       CatalogController@show
GET    /cart                     CartController@index
POST   /cart/{product}           CartController@store
PATCH  /cart/{product}           CartController@update
DELETE /cart/{product}           CartController@destroy
GET    /checkout                 CheckoutController@create
POST   /checkout                 CheckoutController@store
GET    /checkout/success         CheckoutController@success
GET    /admin/products           Admin\ProductController@index
POST   /admin/products           Admin\ProductController@store
GET    /admin/products/create    Admin\ProductController@create
PUT    /admin/products/{product} Admin\ProductController@update
DELETE /admin/products/{product} Admin\ProductController@destroy
```

## Pruebas

El proyecto conserva pruebas con PHPUnit. La configuracion de PHPUnit ya no fuerza SQLite en memoria, por lo que hereda la conexion definida en `.env`.

Importante: al usar MySQL real en PHPUnit, se debe tener cuidado con pruebas que refrescan la base de datos. Lo recomendable en un proyecto real seria usar una base MySQL separada exclusivamente para testing.

## Comandos utiles

Limpiar configuracion:

```bash
php artisan config:clear
```

Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed --force
```

Verificar conteos con Tinker:

```bash
php artisan tinker
```

```php
[
    'users' => App\Models\User::count(),
    'admins' => App\Models\User::where('role', 'admin')->count(),
    'customers' => App\Models\User::where('role', 'customer')->count(),
    'products' => App\Models\Product::count(),
]
```

## Resultado actual en MySQL

La base de datos MySQL de AlwaysData quedo migrada y poblada con:

- 6 usuarios.
- 2 administradores.
- 4 clientes.
- 9 productos.
- 5 categorias.
- 5 etiquetas.

## RESULTADOS VISUALES
<img width="1200" height="555" alt="image" src="https://github.com/user-attachments/assets/681d0d7b-87fb-4051-b92f-12cc9a350baa" />

<img width="1131" height="547" alt="image" src="https://github.com/user-attachments/assets/766ed753-3b21-4222-ad42-66996e73591a" />

<img width="1908" height="778" alt="image" src="https://github.com/user-attachments/assets/632518bc-87eb-43ee-8f93-2d3106f5b0f9" />

<img width="1899" height="640" alt="image" src="https://github.com/user-attachments/assets/fcb95569-a326-482a-86e3-0903830dec6b" />

<img width="579" height="865" alt="image" src="https://github.com/user-attachments/assets/5ca6ad8d-6ecb-4e29-9e15-3e6895b7f79f" />

<img width="921" height="502" alt="image" src="https://github.com/user-attachments/assets/02160d1c-e84c-4837-8727-6828c1db226f" />


