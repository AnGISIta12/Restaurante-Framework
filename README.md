# ACERCA DE LARAVEL
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
## 📂 Estructura del Proyecto

```bash
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── MesaController.php
│   │   ├── ReservacionController.php
│   │   ├── MenuController.php
│   │   ├── PedidoController.php
│   │   ├── OrdenController.php
│   │   ├── EmpleadoController.php
│   │   └── ReporteController.php
│   └── Middleware/
│       └── CheckRole.php          ← guard por rol
├── Models/
│   ├── Usuario.php
│   ├── Rol.php
│   ├── Mesa.php
│   ├── Reservacion.php
│   ├── Horario.php
│   ├── Pedido.php
│   ├── Orden.php
│   ├── Plato.php
│   └── Tipo.php
resources/views/
├── layouts/app.blade.php          ← plantilla base
├── auth/
├── dashboard/
├── mesas/
├── reservaciones/
├── menu/
├── pedidos/
└── reportes/

# Modelos Eloquent — Restaurante Framework (Laravel)

## Modelos generados

| Modelo | Tabla BD | Descripción |
|--------|----------|-------------|
| `Usuario` | `usuarios` | Usuario del sistema (reemplaza al `User` de Laravel) |
| `Rol` | `roles` | Roles: Administrador, Maitre, Mesero, Cocinero, Cliente |
| `Mesa` | `mesas` | Mesas del restaurante con número de sillas |
| `Reservacion` | `reservaciones` | Reservaciones de clientes (estados: 0/1/2) |
| `Horario` | `horarios` | Asignación de mesa + hora a una reservación |
| `Tipo` | `tipos` | Categorías de plato (entrada, plato fuerte, bebida…) |
| `Plato` | `platos` | Platos del menú con precio y tiempo de preparación |
| `Pedido` | `pedidos` | Comanda que agrupa órdenes de un cliente |
| `Orden` | `ordenes` | Ítem individual dentro de un pedido (estados: 0/1/2/3) |

---

## Pasos para integrar en el proyecto

### 1. Configurar la conexión PostgreSQL en `.env`

```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=Restaurante
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 2. Configurar `config/database.php`

Cambiar el driver por defecto:
```php
'default' => env('DB_CONNECTION', 'pgsql'),
```

### 3. Registrar el modelo `Usuario` como guard de autenticación

En `config/auth.php`:
```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Usuario::class,
    ],
],
```

### 4. Registrar el middleware `CheckRole` en `bootstrap/app.php` (Laravel 11) o en `Kernel.php` (Laravel 10)

**Laravel 10** — en `app/Http/Kernel.php`, dentro de `$middlewareAliases`:
```php
'role' => \App\Http\Middleware\CheckRole::class,
```

**Laravel 11** — en `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

### 5. Eliminar el modelo `User.php` por defecto de Laravel

```bash
rm app/Models/User.php
```

---

## Mapa de relaciones

```
Usuario ─────────────── roles (BelongsToMany via actuaciones)
Usuario ─────────────── pedidosComoCliente (HasMany → Pedido)
Usuario ─────────────── pedidosComoMesero (HasMany → Pedido)
Usuario ─────────────── reservaciones (HasMany → Reservacion)

Mesa ────────────────── horarios (HasMany → Horario)
Reservacion ─────────── horario (HasOne → Horario)
Reservacion ─────────── cliente (BelongsTo → Usuario)
Horario ─────────────── mesa (BelongsTo → Mesa)
Horario ─────────────── reservacion (BelongsTo → Reservacion)

Tipo ────────────────── platos (HasMany → Plato)
Plato ───────────────── tipo (BelongsTo → Tipo)
Plato ───────────────── ordenes (HasMany → Orden)

Pedido ──────────────── cliente (BelongsTo → Usuario)
Pedido ──────────────── mesero (BelongsTo → Usuario)
Pedido ──────────────── ordenes (HasMany → Orden)
Orden ───────────────── plato (BelongsTo → Plato)
Orden ───────────────── pedido (BelongsTo → Pedido)
```

---

## Autenticación SHA-256

La contraseña NO usa bcrypt (Laravel default). Se usa SHA-256 puro
para mantener compatibilidad con la BD existente.

En `AuthController` el login se hace así:
```php
$hash = hash('sha256', $request->password, true); // binary

$usuario = Usuario::where('nombre', $request->nombre)
    ->whereRaw('clave = ?', [$hash])
    ->first();
```

Al crear un usuario nuevo:
```php
DB::statement(
    "INSERT INTO usuarios (nombre, clave, fecha_clave)
     VALUES (?, sha256((?::text)::bytea), NOW())",
    [$nombre, $password]
);
```

---

## Constantes útiles

```php
// Estados de Orden
Orden::ESTADO_PENDIENTE       // 0
Orden::ESTADO_EN_PREPARACION  // 1
Orden::ESTADO_LISTA           // 2
Orden::ESTADO_ENTREGADA       // 3

// Estados de Reservacion
Reservacion::ESTADO_PENDIENTE   // 0
Reservacion::ESTADO_CONFIRMADA  // 1
Reservacion::ESTADO_ASIGNADA    // 2

// Nombres de rol
Rol::ADMINISTRADOR  // 'Administrador'
Rol::MAITRE         // 'Maitre'
Rol::MESERO         // 'Mesero'
Rol::COCINERO       // 'Cocinero'
Rol::CLIENTE        // 'Cliente'
```

---

## Scopes disponibles

```php
// Mesas disponibles ahora con capacidad suficiente
Mesa::disponibles()->conCapacidad(4)->get();

// Horarios de hoy
Horario::hoy()->with('mesa', 'reservacion.cliente')->get();

// Horarios que empiezan en los próximos 30 min
Horario::proximos(30)->get();

// Órdenes en cocina del cocinero activo
Orden::enCocina()->with('plato', 'pedido')->orderBy('solicitado')->get();

// Reservaciones sin asignar
Reservacion::sinAsignar()->with('cliente')->get();
```
