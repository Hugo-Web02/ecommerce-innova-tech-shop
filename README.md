# InnovaTechShop - Reporte de practica 3.2

## Sistema Ecommerce basico en Laravel

## Introduccion

**InnovaTechShop** es un sistema ecommerce basico desarrollado en Laravel. El proyecto tiene como objetivo aplicar conocimientos de frameworks web en un caso practico: administracion de productos, catalogo publico, autenticacion, roles, carrito de compras, checkout simulado, base de datos con SQLite, migraciones, seeders, relaciones Eloquent, vistas Blade con Tailwind CSS y pruebas automatizadas con PHPUnit.

El sistema permite que un cliente navegue productos tecnologicos, consulte detalles, agregue articulos al carrito y complete una compra simulada. Tambien incluye un panel administrativo protegido para gestionar productos.

## Tecnologias utilizadas

- Laravel 13
- PHP 8.3
- SQLite
- Eloquent ORM
- Blade
- Tailwind CSS con Vite
- PHPUnit
- Git y GitHub

## Configuracion real de base de datos

En esta implementacion se utilizo **SQLite** como base de datos local del proyecto. SQLite guarda la informacion en un archivo fisico, por lo que no necesita servidor externo, usuario, contrasena, host ni puerto.

El archivo utilizado por el proyecto es:

```txt
database/database.sqlite
```

En el archivo `.env` la conexion activa esta configurada asi:

```env
DB_CONNECTION=sqlite
```

No aparece `DB_DATABASE=database/database.sqlite` en `.env`. Esto funciona porque Laravel ya trae una configuracion por defecto en `config/database.php`. En la conexion `sqlite`, Laravel usa `DB_DATABASE` si existe; si no existe, automaticamente toma el archivo `database/database.sqlite`.

Fragmento relevante de `config/database.php`:

```php
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
],
```

Esto significa que el proyecto funciona con SQLite de la siguiente manera:

1. `.env` indica que la conexion es `sqlite`.
2. Laravel revisa si existe `DB_DATABASE`.
3. Como no se definio `DB_DATABASE`, usa el valor por defecto.
4. Ese valor por defecto apunta a `database/database.sqlite`.
5. Las migraciones crean las tablas dentro de ese archivo.
6. Los seeders insertan usuarios, categorias, etiquetas y productos iniciales.

## SQLite fisico y SQLite en memoria

El proyecto usa SQLite de dos formas diferentes:

- **SQLite fisico:** es el archivo `database/database.sqlite`. Se usa cuando se ejecuta la aplicacion normalmente.
- **SQLite en memoria:** se usa solo en pruebas automatizadas con PHPUnit.

En `phpunit.xml` se configura SQLite en memoria:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

La base `:memory:` no es un archivo. Se crea temporalmente en RAM al iniciar las pruebas y desaparece al terminar. Esto permite ejecutar `php artisan test` sin alterar los datos reales guardados en `database/database.sqlite`.

## Tablas creadas en database.sqlite

Al ejecutar las migraciones se crean las tablas principales del ecommerce y algunas tablas internas de Laravel.

Tablas propias del ecommerce:

- `users`: usuarios registrados. Se agrego el campo `role` para diferenciar `admin` y `customer`.
- `categories`: categorias del catalogo.
- `tags`: etiquetas para clasificar productos.
- `products`: productos con categoria, nombre, slug, descripcion, precio, stock, imagen y estado.
- `product_tag`: tabla pivote para relacionar productos y etiquetas.
- `orders`: pedidos generados durante el checkout.
- `order_items`: productos incluidos en cada pedido.

Tablas internas de Laravel:

- `migrations`: historial de migraciones ejecutadas.
- `sessions`: sesiones de usuarios.
- `cache` y `cache_locks`: almacenamiento de cache.
- `jobs`, `job_batches` y `failed_jobs`: soporte para colas.
- `password_reset_tokens`: recuperacion de contrasenas.

Ejemplo de migracion de productos:

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

## Seeders y datos iniciales

El archivo `DatabaseSeeder.php` inserta datos iniciales para probar el ecommerce sin capturar todo manualmente.

Usuarios creados:

```txt
Administrador:
email: admin@innovatech.test
password: password

Cliente:
email: cliente@innovatech.test
password: password
```

Tambien se crean categorias:

- Laptops
- Componentes
- Perifericos
- Monitores
- Accesorios

Y etiquetas:

- Gaming
- Oferta
- Nuevo
- Productividad
- Envio rapido

Productos iniciales insertados:

- Laptop InnovaBook Pro 14
- SSD NVMe 1TB Velocity
- Teclado mecanico RGB NovaKeys
- Monitor UltraWide 29 pulgadas
- Hub USB-C 7 en 1
- Mouse inalambrico Precision X

Fragmento del seeder:

```php
$products = [
    ['Laptops', 'Laptop InnovaBook Pro 14', 18999, 8, 'Equipo portatil con procesador moderno...'],
    ['Componentes', 'SSD NVMe 1TB Velocity', 1699, 20, 'Unidad de estado solido NVMe...'],
    ['Perifericos', 'Teclado mecanico RGB NovaKeys', 1299, 15, 'Teclado mecanico con iluminacion RGB...'],
];
```

## Modelos y relaciones Eloquent

Se implementaron modelos para representar las entidades principales del ecommerce:

- `User`
- `Category`
- `Tag`
- `Product`
- `Order`
- `OrderItem`

El modelo `Product` se relaciona con categorias, etiquetas y partidas de pedido.

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

Tambien se configuro el slug como llave para rutas:

```php
public function getRouteKeyName(): string
{
    return 'slug';
}
```

Gracias a esto, la URL del producto usa un texto legible en lugar del id numerico.

## Catalogo de productos

El catalogo publico se maneja desde `CatalogController`. Muestra productos activos, carga sus categorias y etiquetas, permite buscar por nombre o descripcion y filtrar por categoria, etiqueta o precio maximo.

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

Vistas principales:

- `resources/views/catalog/index.blade.php`: cuadricula de productos.
- `resources/views/catalog/show.blade.php`: detalle de producto.

## CRUD administrativo de productos

El CRUD de productos esta protegido para usuarios administradores. Sus vistas se encuentran en:

```txt
resources/views/admin/products
```

Funcionalidades:

- Crear productos.
- Editar productos.
- Eliminar productos.
- Asignar categoria.
- Asignar etiquetas.
- Definir precio y stock.
- Activar o desactivar productos.
- Subir imagenes.
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

## Autenticacion y roles

Se implemento autenticacion basica con:

- Registro de usuario.
- Inicio de sesion.
- Cierre de sesion.
- Rol `admin`.
- Rol `customer`.

El campo `role` se agrego a la tabla `users` mediante una migracion:

```php
Schema::table('users', function (Blueprint $table): void {
    $table->string('role')->default('customer')->after('password');
});
```

El middleware `EnsureUserIsAdmin` protege las rutas administrativas:

```php
public function handle(Request $request, Closure $next): Response
{
    abort_unless($request->user()?->role === 'admin', 403);

    return $next($request);
}
```

Registro del middleware en `bootstrap/app.php`:

```php
$middleware->alias([
    'admin' => EnsureUserIsAdmin::class,
]);
```

## Carrito de compras

El carrito se implemento con sesiones de Laravel. Cada producto agregado se guarda temporalmente en `session('cart')` con id, slug, nombre, precio, cantidad e imagen.

Funcionalidades:

- Agregar productos.
- Actualizar cantidades.
- Eliminar productos.
- Calcular subtotal.
- Calcular IVA.
- Calcular total.

Calculo del carrito:

```php
$cart = session('cart', []);
$subtotal = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);
$tax = round($subtotal * 0.16, 2);
$total = $subtotal + $tax;
```

## Checkout simulado

El checkout requiere que el usuario haya iniciado sesion. El flujo no usa una pasarela real, sino una simulacion de pago aprobado.

Proceso:

1. El cliente revisa su carrito.
2. Confirma el pago simulado.
3. Se crea un registro en `orders`.
4. Se crean registros en `order_items`.
5. Se descuenta stock de cada producto.
6. Se limpia la sesion del carrito.
7. Se muestra pantalla de compra exitosa.

Fragmento principal:

```php
$order = $request->user()->orders()->create([
    'subtotal' => $totals['subtotal'],
    'tax' => $totals['tax'],
    'total' => $totals['total'],
    'status' => 'paid',
]);
```

## Vistas Blade y archivos estaticos

Se crearon vistas Blade para reutilizar estructura y mostrar la tienda:

- `resources/views/components/layouts/app.blade.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`
- `resources/views/catalog/index.blade.php`
- `resources/views/catalog/show.blade.php`
- `resources/views/cart/index.blade.php`
- `resources/views/checkout/create.blade.php`
- `resources/views/checkout/success.blade.php`
- `resources/views/admin/products/index.blade.php`
- `resources/views/admin/products/create.blade.php`
- `resources/views/admin/products/edit.blade.php`
- `resources/views/admin/products/form.blade.php`

Archivos estaticos agregados:

- `public/css/store.css`
- `public/js/store.js`

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

## Pruebas automatizadas

Se agregaron pruebas con PHPUnit usando SQLite en memoria. Esto permite validar el comportamiento sin modificar `database/database.sqlite`.

Pruebas creadas:

- `ProductModelTest`: prueba relaciones del modelo y uso de slug.
- `AuthenticationTest`: prueba registro e inicio de sesion.
- `AdminProductAccessTest`: prueba acceso protegido al panel admin.
- `CartCheckoutTest`: prueba carrito y checkout.
- `ExampleTest`: prueba respuesta del catalogo.

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

Resultado obtenido:

```txt
php artisan test
Tests: 9 passed
Assertions: 12
```

## Capturas recomendadas para el reporte

1. `.env` mostrando `DB_CONNECTION=sqlite`.
2. `config/database.php` mostrando `database_path('database.sqlite')`.
3. `database/database.sqlite` abierto en VS Code con las tablas.
4. Tabla `products` con los productos insertados por el seeder.
5. Tabla `users` con admin y cliente.
6. Tabla `categories`.
7. Tabla `tags`.
8. Tabla `orders` despues de una compra.
9. Tabla `order_items` despues de una compra.
10. Catalogo principal con cuadricula de productos.
11. Filtros del catalogo.
12. Vista de detalle de producto.
13. Formulario de login.
14. Formulario de registro.
15. Panel administrativo de productos.
16. Formulario de crear producto.
17. Formulario de editar producto.
18. Carrito con subtotal, IVA y total.
19. Pantalla de checkout.
20. Pantalla de compra exitosa.
21. Terminal ejecutando `php artisan migrate --seed`.
22. Terminal ejecutando `php artisan route:list`.
23. Terminal ejecutando `php artisan test` con pruebas aprobadas.

## Comandos utiles

Crear o actualizar tablas y cargar datos:

```bash
php artisan migrate --seed
```

Reiniciar la base SQLite y volver a cargar datos:

```bash
php artisan migrate:fresh --seed
```

Ejecutar pruebas:

```bash
php artisan test
```

Levantar el servidor:

```bash
php artisan serve
```

Compilar assets:

```bash
npm install
npm run build
```

Nota: Vite 8 requiere Node.js 20.19+ o 22.12+. Si `npm run build` falla con Node 20.18.0, se debe actualizar Node.

## Conclusion

InnovaTechShop implementa un ecommerce basico funcional en Laravel. La base de datos se resolvio con SQLite mediante el archivo `database/database.sqlite`, lo cual permite trabajar localmente sin servidor externo. Laravel usa esta base porque `.env` define `DB_CONNECTION=sqlite` y `config/database.php` apunta por defecto a `database_path('database.sqlite')`.

El proyecto integra catalogo, CRUD de productos, categorias, etiquetas, autenticacion, roles, middleware, carrito, checkout simulado, migraciones, seeders, relaciones Eloquent, vistas Blade, archivos estaticos y pruebas automatizadas con SQLite en memoria.
