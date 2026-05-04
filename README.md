# 🍽️ La Mesa — Sistema de Gestión de Restaurante

> Sistema web de gestión integral para restaurantes, desarrollado con **Laravel 10** y **PostgreSQL**. Cubre reservaciones, pedidos, cocina, menú, personal y reportes con control de acceso por rol.

---

## Tabla de contenidos

1. [Descripción general](#descripción-general)
2. [Tecnologías](#tecnologías)
3. [Roles y permisos](#roles-y-permisos)
4. [Estructura del proyecto](#estructura-del-proyecto)
5. [Modelos y relaciones](#modelos-y-relaciones)
6. [Flujos principales](#flujos-principales)
7. [Autenticación](#autenticación)
8. [API REST](#api-rest)
9. [Instalación y configuración](#instalación-y-configuración)
10. [Variables de entorno](#variables-de-entorno)
11. [Constantes de estado](#constantes-de-estado)
12. [Scopes disponibles](#scopes-disponibles)

---

## Descripción general

**La Mesa** es una aplicación web multi-rol para la operación completa de un restaurante. Cada usuario tiene un rol que determina exactamente qué secciones puede ver y qué acciones puede realizar.

El sistema reemplaza el guard de autenticación estándar de Laravel por uno propio basado en sesiones PHP, y usa **SHA-256** para el hash de contraseñas en lugar de bcrypt, manteniendo compatibilidad con una base de datos PostgreSQL preexistente.

### Módulos principales

| Módulo | Descripción |
|---|---|
| Autenticación | Login, registro y logout con SHA-256 |
| Mesas | CRUD de mesas con capacidad y validación |
| Reservaciones | Solicitud (cliente), asignación (maitre) y verificación de cupo |
| Menú | CRUD de platos por categoría (entrada, plato fuerte, bebida) |
| Pedidos | Comandas con múltiples órdenes por pedido |
| Cocina | Panel en tiempo real con cambio de estados de órdenes |
| Empleados | Gestión del personal con asignación de roles |
| Reportes | KPIs, platos más pedidos, ingresos, distribución de estados |
| API REST | Endpoints para mesas y reservaciones con JSON |

---

## Tecnologías

| Capa | Tecnología |
|---|---|
| Framework | Laravel 10 (PHP 8.1+) |
| Base de datos | PostgreSQL |
| Plantillas | Blade (sin frameworks CSS externos) |
| Autenticación | Sesiones PHP + SHA-256 (sin Auth de Laravel) |
| API | Laravel Sanctum (rutas `/api`) |
| Front-end | CSS propio con variables CSS, Axios, Vite |
| Fuentes | Playfair Display + DM Sans (Google Fonts) |

---

## Roles y permisos

El middleware `CheckRole` verifica el valor guardado en `session('rol')` contra los roles permitidos en cada ruta.

| Rol | Acceso |
|---|---|
| **Administrador** | Mesas, empleados, menú (CRUD completo), reportes, pedidos (vista), cocina |
| **Maitre** | Reservaciones (crear, asignar, verificar, cupo), menú (CRUD) |
| **Mesero** | Pedidos (crear, ver, entregar), menú (solo lectura) |
| **Cocinero** | Panel de cocina (ver órdenes, cambiar estado), menú (solo lectura) |
| **Cliente** | Solicitar reservación, ver historial propio |

### Middleware de autenticación

```
auth.session.custom  →  AuthSessionCustom   (verifica session('usuario_id'))
role:Administrador   →  CheckRole           (verifica session('rol'))
```

---

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php          ← Login, registro, logout
│   │   ├── MesaController.php          ← CRUD mesas
│   │   ├── ReservacionController.php   ← Reservaciones (maitre + cliente)
│   │   ├── MenuController.php          ← CRUD platos
│   │   ├── PedidoController.php        ← Pedidos + cocina + entrega
│   │   ├── EmpleadoController.php      ← Gestión de personal
│   │   ├── ReporteController.php       ← Estadísticas
│   │   └── Api/
│   │       ├── MesaController.php      ← API REST mesas
│   │       └── ReservacionController.php ← API REST reservaciones
│   └── Middleware/
│       ├── AuthSessionCustom.php       ← Guard de sesión propio
│       └── CheckRole.php               ← Guard de rol
├── Models/
│   ├── Usuario.php        ← Extiende Authenticatable (SHA-256)
│   ├── Rol.php            ← Roles del sistema
│   ├── Mesa.php           ← Mesas del restaurante
│   ├── Reservacion.php    ← Reservaciones (estados 0/1/2)
│   ├── Horario.php        ← Asignación mesa-tiempo a una reservación
│   ├── Plato.php          ← Platos del menú
│   ├── Tipo.php           ← Categorías (entrada, plato fuerte, bebida)
│   ├── Pedido.php         ← Comanda que agrupa órdenes
│   ├── Orden.php          ← Ítem individual de un pedido (estados 0-3)
│   ├── Preparacion.php    ← Registro de qué cocinero preparó qué orden
│   └── Especialidad.php   ← Especialidades de cocineros por plato
resources/views/
├── layouts/app.blade.php              ← Plantilla base con sidebar
├── login.blade.php / registro.blade.php
├── dashboard.blade.php
├── mesas/       (index, create, edit)
├── reservaciones/ (proximas, asignar, verificar, cupo, solicitar, historial-cliente)
├── menu/        (index, create, edit)
├── pedidos/     (index, create, show, cocina, listas)
├── empleados/   (index, create, edit)
└── reportes/    (index, reservaciones, pedidos)
routes/
├── web.php      ← Rutas web agrupadas por rol
└── api.php      ← Rutas REST (mesas, reservaciones)
```

---

## Modelos y relaciones

### Mapa de relaciones

```
Usuario ──────── roles          (BelongsToMany via actuaciones)
Usuario ──────── pedidosComoCliente  (HasMany → Pedido)
Usuario ──────── pedidosComoMesero   (HasMany → Pedido)
Usuario ──────── reservaciones   (HasMany → Reservacion)

Mesa ─────────── horarios        (HasMany → Horario)
Reservacion ──── horario         (HasOne  → Horario)
Reservacion ──── cliente         (BelongsTo → Usuario)
Horario ─────── mesa             (BelongsTo → Mesa)
Horario ─────── reservacion      (BelongsTo → Reservacion)

Tipo ─────────── platos          (HasMany → Plato)
Plato ──────────tipo             (BelongsTo → Tipo)
Plato ─────────── ordenes        (HasMany → Orden)

Pedido ──────── cliente          (BelongsTo → Usuario)
Pedido ──────── mesero           (BelongsTo → Usuario)
Pedido ──────── ordenes          (HasMany → Orden)
Orden ──────────plato            (BelongsTo → Plato)
Orden ──────────pedido           (BelongsTo → Pedido)
```

### Tablas de la base de datos

| Tabla | Clave primaria | Descripción |
|---|---|---|
| `usuarios` | `id_usuario` | Usuarios del sistema |
| `roles` | `id_rol` | Roles disponibles |
| `actuaciones` | — | Tabla pivote usuario-rol |
| `mesas` | `id_mesa` | Mesas del restaurante |
| `reservaciones` | `id_reservacion` | Reservaciones de clientes |
| `horarios` | `id_horario` | Horario asignado a cada reservación |
| `tipos` | `id` | Categorías de plato |
| `platos` | `id_plato` | Platos del menú |
| `pedidos` | `id_pedido` | Comandas |
| `ordenes` | `id_orden` | Ítems de una comanda |
| `preparaciones` | `id_preparacion` | Qué cocinero preparó qué orden |
| `especialidades` | `id_especialidad` | Especialidades de cocineros |

---

## Flujos principales

### 1. Login

```
Usuario → POST /login (nombre, password)
       → AuthController::login()
           hash SHA-256 de password
           → SELECT WHERE encode(clave,'hex') = hash
           → Si no existe: redirect back con error
           → Si existe: Session::put(usuario_id, usuario_nombre, rol)
           → redirect /dashboard
```

### 2. Solicitar reservación (Cliente)

```
Cliente → GET /solicitar-reservacion
        → Vista con dropdown de mesas y horarios disponibles
        → POST /solicitar (fecha, hora, mesa_id, cantidad)
        → DB::transaction:
            INSERT reservaciones (cliente_id, cantidad, estado=PENDIENTE)
            INSERT horarios (mesa_id, reservacion_id, inicio, duracion='01:30:00')
        → redirect /mis-reservaciones
```

### 3. Asignar mesa (Maitre)

```
Maitre → GET /reservaciones/asignar
       → sinAsignar() + disponibles() con suficiente capacidad
       → POST /reservaciones/asignar (reservacion_id, mesa_id)
           Verifica cupo: SUM ocupados en ventana ±2h ≤ capacidadTotal
           Si supera: redirect con error de cupo
           DB::transaction:
               INSERT horarios (mesa, reservacion, inicio=now, duracion)
               UPDATE reservaciones SET estado=ASIGNADA
       → redirect asignar
```

### 4. Crear pedido (Mesero)

```
Mesero → GET /pedidos/crear → lista de clientes y platos
       → POST /pedidos (cliente_id, platos[], cantidades[])
       → DB::transaction:
           INSERT pedidos (cliente_id, mesero_id=session)
           foreach plato:
               INSERT ordenes (plato_id, pedido_id, estado=PENDIENTE, cantidad, solicitado=now)
       → redirect /pedidos
```

### 5. Gestión en cocina (Cocinero)

```
Cocinero → GET /cocina → Orden::enCocina() (estado 0 y 1)
         → POST /ordenes/{id}/estado (estado=1 EN_PREPARACION)
               UPDATE ordenes SET estado=1
               INSERT preparaciones (cocinero_id, orden_id)
         → POST /ordenes/{id}/estado (estado=2 LISTA)
               UPDATE ordenes SET estado=2
```

### 6. Entrega (Mesero)

```
Mesero → GET /entregas/listas → Orden::listas() (estado=2)
       → POST /ordenes/{id}/entregar
             UPDATE ordenes SET estado=ENTREGADA (3)
       → redirect listas
```

---

## Autenticación

El sistema no usa `Auth::login()` de Laravel. En su lugar mantiene la sesión manualmente:

```php
// Login — AuthController
$hashHex = hash('sha256', $request->password);

$usuario = Usuario::where('nombre', $request->nombre)
    ->whereRaw("encode(clave, 'hex') = ?", [$hashHex])
    ->first();

Session::put('usuario_id',     $usuario->id_usuario);
Session::put('usuario_nombre', $usuario->nombre);
Session::put('rol',            $rol); // nombre del rol

// Registro — inserta con sha256 nativo de PostgreSQL
DB::selectOne(
    "INSERT INTO usuarios (nombre, clave, fecha_clave)
     VALUES (?, sha256((?::text)::bytea), NOW())
     RETURNING id_usuario",
    [$request->nombre, $request->password]
);
```

El middleware `AuthSessionCustom` protege todas las rutas verificando `session()->has('usuario_id')`. El middleware `CheckRole` compara `session('rol')` con los roles permitidos en cada grupo de rutas.

### Configurar `config/auth.php`

Para que el modelo `Usuario` funcione como guard:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Usuario::class,
    ],
],
```

---

## API REST

Los endpoints REST están bajo el prefijo `/api` y usan el middleware `api`.

### Mesas

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/mesas` | Listar todas las mesas |
| POST | `/api/mesas` | Crear nueva mesa (`capacidad`, `estado`) |
| GET | `/api/mesas/{id}` | Ver mesa por ID |
| PUT | `/api/mesas/{id}` | Actualizar mesa |
| DELETE | `/api/mesas/{id}` | Eliminar mesa |

### Reservaciones

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/reservaciones` | Listar reservaciones con horario y mesa |
| POST | `/api/reservaciones` | Crear reservación (`mesa_id`, `fecha_hora`, `cliente_nombre`, `cantidad`) |
| GET | `/api/reservaciones/{id}` | Ver reservación |
| PUT | `/api/reservaciones/{id}` | Actualizar |
| DELETE | `/api/reservaciones/{id}` | Eliminar |

---

## Instalación y configuración

### Requisitos previos

- PHP 8.1 o superior
- PostgreSQL 14+
- Composer
- Node.js + npm (para assets)

### Pasos

```bash
# 1. Clonar el repositorio
git clone <url-repo>
cd restaurante

# 2. Instalar dependencias PHP
composer install

# 3. Copiar variables de entorno
cp .env.example .env

# 4. Generar clave de aplicación
php artisan key:generate

# 5. Configurar la base de datos en .env (ver sección siguiente)

# 6. Registrar el middleware CheckRole en app/Http/Kernel.php
#    (ya incluido en el proyecto)

# 7. Compilar assets
npm install
npm run build

# 8. Iniciar servidor de desarrollo
php artisan serve
```

> **Importante:** Este proyecto conecta a una base de datos PostgreSQL ya existente con su propio esquema. No usa las migraciones de Laravel para las tablas principales del restaurante.

---

## Variables de entorno

Editar el archivo `.env` con estos valores mínimos:

```env
APP_NAME="La Mesa"
APP_ENV=local
APP_KEY=           # generado con php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=Restaurante
DB_USERNAME=postgres
DB_PASSWORD=tu_password

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

### Cambiar driver de base de datos en `config/database.php`

```php
'default' => env('DB_CONNECTION', 'pgsql'),
```

---

## Constantes de estado

### Orden (`app/Models/Orden.php`)

| Constante | Valor | Descripción |
|---|---|---|
| `Orden::ESTADO_PENDIENTE` | `0` | Recién creada, no vista en cocina |
| `Orden::ESTADO_EN_PREPARACION` | `1` | El cocinero la tomó |
| `Orden::ESTADO_LISTA` | `2` | Lista para ser entregada |
| `Orden::ESTADO_ENTREGADA` | `3` | Entregada al cliente |

### Reservacion (`app/Models/Reservacion.php`)

| Constante | Valor | Descripción |
|---|---|---|
| `Reservacion::ESTADO_PENDIENTE` | `0` | Solicitada, sin mesa |
| `Reservacion::ESTADO_CONFIRMADA` | `1` | Confirmada, sin mesa asignada |
| `Reservacion::ESTADO_ASIGNADA` | `2` | Mesa y horario asignados |

### Rol (`app/Models/Rol.php`)

| Constante | Valor |
|---|---|
| `Rol::ADMINISTRADOR` | `'Administrador'` |
| `Rol::MAITRE` | `'Maitre'` |
| `Rol::MESERO` | `'Mesero'` |
| `Rol::COCINERO` | `'Cocinero'` |
| `Rol::CLIENTE` | `'Cliente'` |

---

## Scopes disponibles

### Mesa

```php
Mesa::disponibles()             // Mesas sin horario activo en ventana ±2h
Mesa::conCapacidad(4)           // Mesas con ≥ N sillas
Mesa::capacidadTotal()          // Suma total de sillas (método estático)
```

### Horario

```php
Horario::activos()              // Horarios en ventana ±2 horas de ahora
Horario::hoy()                  // Horarios del día actual
Horario::proximos(30)           // Horarios en los próximos N minutos
```

### Reservacion

```php
Reservacion::sinAsignar()       // Estado PENDIENTE o CONFIRMADA
Reservacion::proximas()         // Con horario en el futuro
```

### Orden

```php
Orden::enCocina()               // Estado PENDIENTE o EN_PREPARACION
Orden::listas()                 // Estado LISTA (pendientes de entrega)
```

---

## Decisiones de diseño notables

**SHA-256 en lugar de bcrypt** — La base de datos existente almacena las contraseñas con `sha256()` de PostgreSQL en formato binario (`bytea`). El login compara usando `encode(clave, 'hex')` para convertir a hexadecimal y comparar con el hash generado en PHP.

**Sin Auth facade** — Al no usar `bcrypt`, el guard estándar de Laravel no puede verificar contraseñas. Se usa `AuthSessionCustom` como middleware de sesión y `CheckRole` para permisos.

**Transacciones DB::transaction** — Las operaciones que involucran múltiples tablas (registro, crear reservación, asignar mesa, crear pedido) se envuelven en transacciones para garantizar consistencia.

**Tipos de plato restringidos** — El modelo `Tipo` tiene un hook `saving` que valida que `nombre` sea exactamente `'entrada'`, `'plato fuerte'` o `'bebida'`, manteniendo la integridad del catálogo.

**Validación de cupo** — Antes de asignar una mesa, se suma la cantidad de personas de todas las reservaciones con horario en la ventana actual (±2 horas) y se compara con `Mesa::capacidadTotal()`.

---

## Licencia

Violeta Fajardo, Mateo Madrigal, Angy Bautista
