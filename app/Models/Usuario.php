<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @brief Modelo principal de usuario del sistema.
 *
 * Mapea la tabla 'usuarios' existente en PostgreSQL.
 * La contraseña se almacena como SHA-256 (bytea), igual que
 * en el proyecto anterior, para mantener compatibilidad total
 * con los datos ya existentes.
 *
 * Relaciones:
 *  - BelongsToMany Rol (tabla pivot: actuaciones)
 *  - HasMany Pedido (como cliente)
 *  - HasMany Pedido (como mesero)
 *  - HasMany Reservacion (como cliente)
 */
class Usuario extends Authenticatable
{
    /*------------------------------------------------------------------
     | Configuración de tabla
     ------------------------------------------------------------------*/
    protected $table      = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public    $timestamps = false;          // la tabla no tiene created_at/updated_at

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'nombre',
        'clave',
        'fecha_clave',
    ];

    /**
     * Atributos ocultos en serialización JSON.
     */
    protected $hidden = [
        'clave',
    ];

    /*------------------------------------------------------------------
     | Autenticación personalizada
     ------------------------------------------------------------------*/

    /**
     * @brief Nombre de la columna usada como "username" para login.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id_usuario';
    }

    /**
     * @brief Devuelve la contraseña hasheada almacenada.
     * No se usa bcrypt; el hash SHA-256 se compara en AuthController.
     */
    public function getAuthPassword(): string
    {
        return $this->clave;
    }

    /*------------------------------------------------------------------
     | Relaciones
     ------------------------------------------------------------------*/

    /**
     * @brief Roles asignados al usuario (many-to-many via actuaciones).
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'actuaciones',     // tabla pivot
            'usuario_id',      // FK hacia usuarios
            'rol_id'           // FK hacia roles
        );
    }

    /**
     * @brief Pedidos en los que este usuario es el CLIENTE.
     * @return HasMany
     */
    public function pedidosComoCliente(): HasMany
    {
        return $this->hasMany(Pedido::class, 'cliente_id', 'id_usuario');
    }

    /**
     * @brief Pedidos en los que este usuario es el MESERO.
     * @return HasMany
     */
    public function pedidosComoMesero(): HasMany
    {
        return $this->hasMany(Pedido::class, 'mesero_id', 'id_usuario');
    }

    /**
     * @brief Reservaciones solicitadas por este usuario (como cliente).
     * @return HasMany
     */
    public function reservaciones(): HasMany
    {
        return $this->hasMany(Reservacion::class, 'cliente_id', 'id_usuario');
    }

    /*------------------------------------------------------------------
     | Helpers
     ------------------------------------------------------------------*/

    /**
     * @brief Devuelve el nombre del primer rol asignado al usuario.
     * @return string  Ej: "Administrador", "Mesero", "Cliente"
     */
    public function getRolNombre(): string
    {
        return $this->roles()->value('nombre') ?? 'Sin rol';
    }

    /**
     * @brief Verifica si el usuario tiene un rol específico.
     * @param  string $rol  Nombre del rol (case-insensitive)
     * @return bool
     */
    public function tieneRol(string $rol): bool
    {
        return $this->roles()
            ->whereRaw('LOWER(roles.nombre) = ?', [strtolower($rol)])
            ->exists();
    }

    /**
     * @brief Genera el hash SHA-256 de una contraseña en texto plano.
     * Usado en AuthController y al crear/actualizar usuarios.
     * @param  string $password  Contraseña en texto plano
     * @return string            Expresión SQL para PostgreSQL (no un valor PHP)
     *
     * NOTA: El hash real se genera en la capa SQL con sha256(password::bytea).
     * Este método sólo es un recordatorio de la estrategia usada.
     */
    public static function hashPassword(string $password): string
    {
        // SHA-256 puro en PHP — el resultado es igual al de PostgreSQL sha256()
        return hash('sha256', $password, true); // raw binary, igual que bytea
    }
}