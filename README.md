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

La estructura de seeders y factories quedo organizada asi:

- `database/seeders/DatabaseSeeder.php`: se modifico para crear usuarios, categorias, etiquetas y productos iniciales.
- `database/factories/UserFactory.php`: se modifico para incluir el campo `role` con valor por defecto `customer`.
- `database/factories/CategoryFactory.php`: se creo para generar categorias de prueba.
- `database/factories/TagFactory.php`: se creo para generar etiquetas de prueba.
- `database/factories/ProductFactory.php`: se creo para generar productos asociados a una categoria.

El seeder principal concentra los datos necesarios para levantar la tienda con informacion inicial. Primero crea usuarios, despues categorias, luego etiquetas y finalmente productos. Cada producto se guarda con una categoria asignada y se relaciona con etiquetas mediante la tabla pivote `product_tag`.

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

Resumen de datos generados por los seeders:

- 2 usuarios administradores.
- 4 usuarios clientes.
- 5 categorias: Laptops, Componentes, Perifericos, Monitores y Accesorios.
- 5 etiquetas: Gaming, Oferta, Nuevo, Productividad y Envio rapido.
- 9 productos con categoria asignada.
- Relaciones entre productos y etiquetas mediante `product_tag`.

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

## Pruebas manuales realizadas

Durante la revision manual del proyecto se verifico lo siguiente:

- Los productos generados por el seeder se muestran correctamente en el catalogo principal.
- El catalogo carga productos con su categoria, precio, stock y estado activo.
- La vista de detalle de producto funciona usando el `slug` del producto en la URL.
- El login funciona para usuarios administradores y clientes.
- Los clientes pueden navegar el catalogo, agregar productos al carrito y avanzar al checkout.
- Solo los usuarios con rol `admin` pueden acceder a la gestion de productos en `/admin/products`.
- El panel administrativo permite editar productos existentes.
- Se verifico la carga de imagen local desde el formulario de producto.
- Se agrego y verifico un segundo campo para cargar imagenes mediante URL de internet.
- El carrito calcula subtotal, IVA y total.

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

## Conclusion

El proyecto InnovaTechShop ahora trabaja con una base de datos MySQL remota en AlwaysData. Se actualizo `.env`, se elimino la configuracion de SQLite en memoria para PHPUnit, se hicieron los seeders idempotentes y se agregaron los usuarios y productos solicitados. La base fue migrada y poblada correctamente con Laravel.
