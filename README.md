
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
```
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
