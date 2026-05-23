# InnovaTechShop - Ecommerce basico en Laravel

## Introduccion

**InnovaTechShop** es una practica de desarrollo web orientada a construir un sistema ecommerce basico con Laravel, MySQL, Blade, Tailwind CSS, Eloquent, middleware, autenticacion y pruebas automatizadas con PHPUnit.

El objetivo del proyecto es aplicar buenas practicas de frameworks web en un caso real: catalogo de productos, administracion de inventario, autenticacion de usuarios, roles, carrito de compras, checkout simulado, base de datos relacional, seeders, factories y tests siguiendo el patron AAA: **Arrange, Act, Assert**.

> Estado del repositorio: el proyecto parte de una instalacion Laravel y este documento describe la arquitectura, modulos y codigo clave que se desarrollan para cumplir los requisitos de la practica.

## Tecnologias utilizadas

- **Laravel 13** como framework principal.
- **PHP 8.3** para la logica de backend.
- **MySQL** como motor de base de datos.
- **Eloquent ORM** para modelos y relaciones.
- **Blade** para vistas reutilizables.
- **Tailwind CSS** para estilos del catalogo y componentes.
- **Laravel Breeze o autenticacion nativa** para registro e inicio de sesion.
- **PHPUnit** para pruebas unitarias y feature.
- **Git y GitHub** para control de versiones, ramas y pull requests.

## Configuracion de base de datos

La conexion definida para la practica usa MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=mysql-hugo20271015.alwaysdata.net
DB_PORT=3306
DB_DATABASE=hugo20271015_innova_tech_shop
DB_USERNAME=hugo20271015
DB_PASSWORD=tu_password_seguro
```

Despues de configurar el archivo `.env`, se ejecutan las migraciones y seeders:

```bash
php artisan migrate --seed
```

## Modulos principales

### 1. Gestion de productos

El sistema contempla un CRUD completo para productos:

- Crear productos desde el panel de administrador.
- Listar productos en catalogo publico.
- Consultar detalle de producto.
- Editar nombre, descripcion, precio, stock, categoria, etiquetas e imagen.
- Eliminar productos cuando ya no se vendan.
- Buscar productos por nombre o descripcion.
- Filtrar por categoria, etiqueta y rango de precio.

Modelo esperado:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_path',
        'is_active',
    ];

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
}
```

Ejemplo de validacion en controlador:

```php
$validated = $request->validate([
    'category_id' => ['required', 'exists:categories,id'],
    'name' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string'],
    'price' => ['required', 'numeric', 'min:0'],
    'stock' => ['required', 'integer', 'min:0'],
    'image' => ['nullable', 'image', 'max:2048'],
    'tags' => ['array'],
]);
```

### 2. Categorias y etiquetas

Las categorias permiten agrupar productos por familia, por ejemplo:

- Laptops
- Componentes
- Accesorios
- Monitores
- Perifericos

Las etiquetas agregan busquedas mas flexibles:

- gaming
- oferta
- nuevo
- productividad
- envio rapido

Relaciones principales:

```php
// Category.php
public function products()
{
    return $this->hasMany(Product::class);
}

// Tag.php
public function products()
{
    return $this->belongsToMany(Product::class);
}
```

### 3. Catalogo con Blade y Tailwind CSS

La pagina principal muestra una cuadricula de productos con imagen, nombre, precio y boton para ver detalle o agregar al carrito.

Ejemplo simplificado de tarjeta:

```blade
<article class="rounded border bg-white p-4 shadow-sm">
    <img
        src="{{ Storage::url($product->image_path) }}"
        alt="{{ $product->name }}"
        class="h-48 w-full rounded object-cover"
    >

    <h2 class="mt-4 text-lg font-semibold text-gray-900">
        {{ $product->name }}
    </h2>

    <p class="mt-2 text-sm text-gray-600">
        {{ Str::limit($product->description, 90) }}
    </p>

    <div class="mt-4 flex items-center justify-between">
        <span class="font-bold text-blue-700">
            ${{ number_format($product->price, 2) }}
        </span>

        <a href="{{ route('products.show', $product) }}" class="text-sm text-blue-600">
            Ver detalle
        </a>
    </div>
</article>
```

Vista de detalle:

```blade
<section class="grid gap-8 md:grid-cols-2">
    <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="rounded">

    <div>
        <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
        <p class="mt-4 text-gray-700">{{ $product->description }}</p>
        <p class="mt-6 text-2xl font-bold">${{ number_format($product->price, 2) }}</p>

        <form method="POST" action="{{ route('cart.store', $product) }}" class="mt-6">
            @csrf
            <button class="rounded bg-blue-600 px-4 py-2 text-white">
                Agregar al carrito
            </button>
        </form>
    </div>
</section>
```

### 4. Usuarios, autenticacion y roles

El sistema maneja dos roles principales:

- **Admin:** puede crear, editar y eliminar productos, categorias, etiquetas y revisar pedidos.
- **Cliente:** puede navegar el catalogo, agregar productos al carrito y realizar checkout.

Una forma simple de implementarlo es agregar un campo `role` en la tabla `users`:

```php
$table->string('role')->default('customer');
```

Middleware de autorizacion:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return $next($request);
    }
}
```

Rutas protegidas:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('products', Admin\ProductController::class);
    Route::resource('categories', Admin\CategoryController::class);
    Route::resource('tags', Admin\TagController::class);
});
```

### 5. Carrito de compras

El carrito permite:

- Agregar productos.
- Eliminar productos.
- Actualizar cantidades.
- Calcular subtotal.
- Calcular impuestos.
- Calcular total.

Ejemplo de calculo:

```php
$subtotal = collect(session('cart', []))->sum(function ($item) {
    return $item['price'] * $item['quantity'];
});

$tax = $subtotal * 0.16;
$total = $subtotal + $tax;
```

Estructura recomendada en sesion:

```php
session([
    'cart' => [
        $product->id => [
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => 1,
        ],
    ],
]);
```

### 6. Checkout simulado

El checkout registra una orden y muestra un mensaje de compra exitosa. No se conecta con una pasarela real de pago; se simula la aprobacion para cumplir el flujo completo.

Flujo esperado:

1. El cliente inicia sesion.
2. Revisa productos del carrito.
3. Confirma compra.
4. El sistema crea un pedido con sus productos.
5. Se descuenta stock.
6. Se limpia el carrito.
7. Se muestra una pantalla de exito.

Ejemplo de creacion de pedido:

```php
$order = $request->user()->orders()->create([
    'subtotal' => $subtotal,
    'tax' => $tax,
    'total' => $total,
    'status' => 'paid',
]);

foreach ($cart as $productId => $item) {
    $order->items()->create([
        'product_id' => $productId,
        'quantity' => $item['quantity'],
        'unit_price' => $item['price'],
        'total' => $item['price'] * $item['quantity'],
    ]);
}
```

## Base de datos

Tablas principales:

- `users`: usuarios registrados y rol.
- `categories`: categorias de productos.
- `tags`: etiquetas reutilizables.
- `products`: informacion comercial del producto.
- `product_tag`: tabla pivote para relacion muchos a muchos.
- `orders`: pedidos realizados por clientes.
- `order_items`: productos incluidos en cada pedido.

Ejemplo de migracion para productos:

```php
Schema::create('products', function (Blueprint $table) {
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

Consulta avanzada: productos mas vendidos.

```php
$bestSellers = Product::query()
    ->select('products.*')
    ->selectRaw('SUM(order_items.quantity) as sold_units')
    ->join('order_items', 'products.id', '=', 'order_items.product_id')
    ->groupBy('products.id')
    ->orderByDesc('sold_units')
    ->take(10)
    ->get();
```

## Seguridad: protocolo AAA

### Authentication

La autenticacion se implementa con registro, login, logout y, de forma deseable, verificacion de email.

Comando recomendado si se instala Breeze:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

### Authorization

La autorizacion se maneja mediante middleware de roles. Las rutas administrativas deben requerir usuario autenticado y rol `admin`.

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('admin/products', ProductController::class);
});
```

### Auditoria

Para actividades criticas, como cambios de precio, se pueden registrar logs:

```php
if ($product->isDirty('price')) {
    Log::info('Cambio de precio de producto', [
        'product_id' => $product->id,
        'old_price' => $product->getOriginal('price'),
        'new_price' => $product->price,
        'user_id' => auth()->id(),
    ]);
}
```

## Tests con PHPUnit

Los tests deben seguir el patron AAA:

- **Arrange:** preparar usuario, producto, categoria o datos de prueba.
- **Act:** ejecutar la accion del sistema.
- **Assert:** verificar el resultado esperado.

### Test unitario de modelo

```php
public function test_product_belongs_to_category(): void
{
    // Arrange
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    // Act
    $productCategory = $product->category;

    // Assert
    $this->assertTrue($productCategory->is($category));
}
```

### Test feature de ruta protegida

```php
public function test_customer_cannot_create_products(): void
{
    // Arrange
    $customer = User::factory()->create(['role' => 'customer']);

    // Act
    $response = $this->actingAs($customer)->get('/admin/products/create');

    // Assert
    $response->assertForbidden();
}
```

### Test feature de compra

```php
public function test_authenticated_customer_can_checkout(): void
{
    // Arrange
    $user = User::factory()->create(['role' => 'customer']);
    $product = Product::factory()->create(['stock' => 5, 'price' => 100]);

    // Act
    $response = $this
        ->actingAs($user)
        ->withSession([
            'cart' => [
                $product->id => [
                    'name' => $product->name,
                    'price' => 100,
                    'quantity' => 1,
                ],
            ],
        ])
        ->post('/checkout');

    // Assert
    $response->assertRedirect('/checkout/success');
}
```

Comando para correr pruebas:

```bash
php artisan test
```

Comando para cobertura:

```bash
php artisan test --coverage
```

## Archivos estaticos

Los archivos CSS y JS se organizan en:

- `resources/css/app.css`
- `resources/js/app.js`
- `public/` para archivos publicos como imagenes generadas o assets estaticos.
- `storage/app/public` para imagenes subidas por usuarios.

Para exponer imagenes subidas:

```bash
php artisan storage:link
```

## Estructura recomendada de rutas

```php
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});
```

## Buenas practicas aplicadas

- Separacion de responsabilidades mediante controladores, modelos y vistas.
- Uso de migraciones para versionar la base de datos.
- Uso de seeders y factories para datos de prueba.
- Validacion de datos antes de crear o actualizar registros.
- Middleware para proteger rutas administrativas.
- Logs para auditoria de cambios importantes.
- Tests independientes y descriptivos.
- Uso de ramas por funcionalidad, por ejemplo `feature/products`, `feature/cart` y `feature/checkout`.
- Commits claros, por ejemplo `Añadido CRUD de productos` o `Agregado checkout simulado`.

## Comandos utiles

Instalar dependencias:

```bash
composer install
npm install
```

Generar llave de aplicacion:

```bash
php artisan key:generate
```

Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

Levantar servidor local:

```bash
php artisan serve
```

Compilar assets:

```bash
npm run dev
```

Ejecutar tests:

```bash
php artisan test
```

## Flujo con Git y GitHub

Crear una rama para cada modulo:

```bash
git checkout -b feature/productos
```

Guardar cambios:

```bash
git add .
git commit -m "Documentado reporte de practica ecommerce"
```

Subir rama:

```bash
git push origin feature/productos
```

Si Git muestra advertencia de propiedad dudosa en Windows, ejecutar:

```bash
git config --global --add safe.directory "C:/Users/PC One/Herd/ecommerce-innova-tech-shop"
```

## Conclusion

InnovaTechShop integra los elementos esenciales de un ecommerce academico: productos, categorias, etiquetas, autenticacion, roles, carrito, checkout simulado, migraciones, relaciones Eloquent, auditoria basica y pruebas. La practica permite demostrar dominio de Laravel como framework web moderno y refuerza el uso de buenas practicas de desarrollo, seguridad, versionamiento y testing.
