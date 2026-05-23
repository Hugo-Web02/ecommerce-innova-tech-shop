# InnovaTechShop - Sistema Ecommerce basico en Laravel

## Introduccion

**InnovaTechShop** es un ecommerce basico desarrollado con Laravel para aplicar conocimientos de frameworks web, MVC, Eloquent ORM, autenticacion, autorizacion por roles, manejo de archivos estaticos, carrito de compras, checkout simulado y pruebas automatizadas con PHPUnit.

El sistema permite navegar un catalogo de productos tecnologicos, filtrar por categoria o etiqueta, consultar el detalle de cada producto, agregar productos al carrito y completar una compra simulada. Tambien incluye un panel administrativo protegido para gestionar productos.

## Tecnologias utilizadas

- Laravel 13
- PHP 8.3
- MySQL
- Eloquent ORM
- Blade
- Tailwind CSS con Vite
- PHPUnit
- Git y GitHub

## Configuracion de base de datos

En `.env` se configura la conexion MySQL. Por seguridad, la contrasena real no debe publicarse en GitHub:

```env
DB_CONNECTION=mysql
DB_HOST=mysql-hugo20271015.alwaysdata.net
DB_PORT=3306
DB_DATABASE=hugo20271015_innova_tech_shop
DB_USERNAME=hugo20271015
DB_PASSWORD=tu_password_seguro
```

Comandos para preparar la aplicacion:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Para levantar el servidor:

```bash
php artisan serve
npm run dev
```

## Usuarios de prueba

El seeder crea dos usuarios:

```txt
Admin:
email: admin@innovatech.test
password: password

Cliente:
email: cliente@innovatech.test
password: password
```

## Funcionalidades desarrolladas

### Catalogo de productos

El catalogo se muestra en `/` mediante `CatalogController`. Incluye:

- Cuadricula de productos con Blade y Tailwind.
- Vista de detalle en `/products/{product}`.
- Busqueda por nombre o descripcion.
- Filtro por categoria.
- Filtro por etiqueta.
- Filtro por precio maximo desde el controlador.
- Paginacion.

Fragmento relevante:

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

### CRUD de productos

El panel de administracion esta en `/admin/products` y se protege con `auth` y `admin`.

Permite:

- Crear productos.
- Editar productos.
- Eliminar productos.
- Asignar categoria.
- Asignar etiquetas.
- Activar o desactivar productos.
- Subir imagenes a `storage/app/public/products`.
- Registrar cambios de precio en logs.

Validacion principal:

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

### Autenticacion y roles

Se implemento autenticacion basica propia con:

- Registro en `/register`.
- Inicio de sesion en `/login`.
- Cierre de sesion en `/logout`.
- Campo `role` en usuarios.
- Rol `admin`.
- Rol `customer`.
- Middleware `EnsureUserIsAdmin`.

Middleware:

```php
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'admin', 403);

        return $next($request);
    }
}
```

Registro del alias en `bootstrap/app.php`:

```php
$middleware->alias([
    'admin' => EnsureUserIsAdmin::class,
]);
```

### Carrito de compras

El carrito usa la sesion de Laravel. Incluye:

- Agregar productos.
- Actualizar cantidades.
- Eliminar productos.
- Calcular subtotal.
- Calcular IVA.
- Calcular total.

Calculo:

```php
$cart = session('cart', []);
$subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
$tax = round($subtotal * 0.16, 2);
$total = $subtotal + $tax;
```

### Checkout simulado

El checkout requiere usuario autenticado y esta disponible en `/checkout`.

Flujo:

1. El cliente agrega productos al carrito.
2. Revisa subtotal, IVA y total.
3. Confirma el pago simulado.
4. Se crea un pedido en `orders`.
5. Se crean las partidas en `order_items`.
6. Se descuenta stock.
7. Se limpia el carrito.
8. Se redirige a `/checkout/success`.

Fragmento:

```php
$order = $request->user()->orders()->create([
    'subtotal' => $totals['subtotal'],
    'tax' => $totals['tax'],
    'total' => $totals['total'],
    'status' => 'paid',
]);
```

## Base de datos

Se agregaron migraciones para:

- `users`: se agrego `role`.
- `categories`: categorias del catalogo.
- `tags`: etiquetas.
- `products`: productos con precio, stock, imagen y estado.
- `product_tag`: relacion muchos a muchos.
- `orders`: pedidos.
- `order_items`: productos comprados en cada pedido.

Relacion del modelo `Product`:

```php
class Product extends Model
{
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

Consulta avanzada para productos mas vendidos:

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

## Vistas Blade y archivos estaticos

Se crearon vistas en:

- `resources/views/components/layouts/app.blade.php`
- `resources/views/auth`
- `resources/views/catalog`
- `resources/views/cart`
- `resources/views/checkout`
- `resources/views/admin/products`

Tambien se agregaron archivos estaticos:

- `public/css/store.css`
- `public/js/store.js`

Ejemplo de tarjeta de producto:

```blade
<article class="flex flex-col overflow-hidden rounded border bg-white shadow-sm">
    <a href="{{ route('products.show', $product) }}">
        <img src="{{ Storage::url($product->image_path) }}" alt="{{ $product->name }}">
    </a>

    <div class="p-5">
        <h2 class="text-lg font-bold">{{ $product->name }}</h2>
        <p class="text-sm text-slate-600">{{ Str::limit($product->description, 100) }}</p>
        <span class="text-xl font-bold">${{ number_format($product->price, 2) }}</span>
    </div>
</article>
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

## Tests

Se agregaron pruebas con PHPUnit:

- `ProductModelTest`: relaciones y route key del modelo.
- `AuthenticationTest`: registro e inicio de sesion.
- `AdminProductAccessTest`: acceso protegido por rol.
- `CartCheckoutTest`: carrito y checkout.
- `ExampleTest`: respuesta correcta del catalogo.

Ejemplo con patron AAA:

```php
public function test_customer_cannot_open_product_create_screen(): void
{
    // Arrange
    $customer = User::factory()->create(['role' => 'customer']);

    // Act
    $response = $this->actingAs($customer)->get('/admin/products/create');

    // Assert
    $response->assertForbidden();
}
```

Resultado verificado:

```txt
php artisan test
Tests: 9 passed
Assertions: 12
```

La cobertura con `php artisan test --coverage` requiere instalar o habilitar **Xdebug** o **PCOV** en PHP.

## Comandos utiles

Ejecutar migraciones y seeders:

```bash
php artisan migrate:fresh --seed
```

Crear enlace de storage para imagenes:

```bash
php artisan storage:link
```

Ejecutar pruebas:

```bash
php artisan test
```

Compilar assets:

```bash
npm install
npm run build
```

Nota: Vite 8 requiere Node.js 20.19+ o 22.12+. Si `npm run build` falla con Node 20.18.0, actualiza Node y vuelve a ejecutar los comandos anteriores.

Si Git muestra advertencia de propiedad dudosa en Windows:

```bash
git config --global --add safe.directory "C:/Users/PC One/Herd/ecommerce-innova-tech-shop"
```

## Flujo recomendado con Git

```bash
git checkout -b feature/ecommerce-basico
git add .
git commit -m "Desarrollado ecommerce basico en Laravel"
git push origin feature/ecommerce-basico
```

## Conclusion

InnovaTechShop cumple con los requisitos principales de la practica: catalogo, CRUD de productos, categorias, etiquetas, subida de imagenes, autenticacion, roles, middleware, carrito, checkout simulado, migraciones, seeders, relaciones Eloquent, auditoria basica, vistas Blade con Tailwind, archivos estaticos y pruebas automatizadas.
