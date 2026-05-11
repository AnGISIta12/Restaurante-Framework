<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @brief Modelo Eloquent para la configuración global del restaurante.
 *
 * Esta tabla almacena un único registro (patrón singleton) con los parámetros
 * de capacidad del local. El Administrador puede definir un límite máximo de
 * sillas y un umbral de alerta porcentual para que el sistema avise cuando
 * la ocupación esté a punto de alcanzar el tope permitido.
 *
 * @property int    $id_configuracion  PK autoincremental.
 * @property int    $max_sillas        Límite máximo de sillas (0 = sin límite).
 * @property int    $umbral_alerta     Porcentaje de alerta (1-100).
 * @property string $created_at        Fecha de creación del registro.
 * @property string $updated_at        Última fecha de actualización.
 */
class ConfiguracionRestaurante extends Model
{
    protected $table      = 'configuraciones';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = ['max_sillas', 'umbral_alerta'];

    protected $casts = [
        'max_sillas'    => 'integer',
        'umbral_alerta' => 'integer',
    ];

    /**
     * @brief Obtiene el registro singleton de configuración.
     *
     * Si no existe ningún registro en la tabla (por ejemplo, si la migración
     * no insertó el seed), crea uno con valores por defecto: max_sillas = 0
     * (sin límite) y umbral_alerta = 80 (alerta al 80% de ocupación).
     *
     * @return self  Instancia única de configuración del restaurante.
     */
    public static function actual(): self
    {
        // Buscamos el primer (y único) registro de configuración en la tabla;
        // si no existe, lo creamos con valores por defecto para que el sistema
        // siempre tenga una configuración válida disponible
        return static::firstOrCreate([], [
            'max_sillas'    => 0,
            'umbral_alerta' => 80,
        ]);
    }

    /**
     * @brief Calcula el límite efectivo de sillas del restaurante.
     *
     * Si el Administrador configuró un valor mayor a 0 en max_sillas,
     * se usa ese valor como tope. Si es 0, se usa la capacidad calculada
     * automáticamente como la suma de sillas de todas las mesas registradas.
     *
     * @return int  Número máximo de sillas permitidas en el restaurante.
     */
    public function limiteEfectivo(): int
    {
        // Si max_sillas > 0, el admin definió un límite manual; caso contrario,
        // el límite es la suma de todas las sillas de las mesas existentes
        return $this->max_sillas > 0
            ? $this->max_sillas
            : Mesa::capacidadTotal();
    }

    /**
     * @brief Verifica si la ocupación actual supera el umbral de alerta.
     *
     * Compara el número de asientos ocupados contra el límite efectivo y
     * determina si se ha alcanzado o superado el porcentaje de umbral_alerta.
     * Retorna un array con la información necesaria para mostrar alertas
     * visuales en las vistas del Maitre y del Administrador.
     *
     * @param  int   $ocupados  Número de asientos actualmente ocupados.
     * @return array            [
     *                            'alerta'      => bool  — si se debe mostrar alerta,
     *                            'porcentaje'  => int   — % de ocupación actual,
     *                            'limite'      => int   — límite efectivo de sillas,
     *                            'restantes'   => int   — sillas disponibles,
     *                            'nivel'       => string — 'critico'|'advertencia'|'normal',
     *                          ]
     */
    public function verificarAlerta(int $ocupados): array
    {
        $limite    = $this->limiteEfectivo(); // Obtenemos el límite efectivo (manual o automático)
        $restantes = max(0, $limite - $ocupados); // Calculamos las sillas restantes disponibles, asegurándonos de no retornar un valor negativo
        $porcentaje = $limite > 0
            ? round($ocupados / $limite * 100) // Calculamos el porcentaje de ocupación actual respecto al límite efectivo
            : 0;

        // Determinamos el nivel de alerta según el porcentaje de ocupación:
        // - 'critico': la ocupación alcanzó o superó el 100% del límite
        // - 'advertencia': la ocupación alcanzó o superó el umbral configurado
        // - 'normal': la ocupación está por debajo del umbral
        if ($porcentaje >= 100) {
            $nivel = 'critico';
        } elseif ($porcentaje >= $this->umbral_alerta) {
            $nivel = 'advertencia';
        } else {
            $nivel = 'normal';
        }

        return [
            'alerta'     => $porcentaje >= $this->umbral_alerta, // true si se debe mostrar alerta visual
            'porcentaje' => $porcentaje,
            'limite'     => $limite,
            'restantes'  => $restantes,
            'nivel'      => $nivel,
        ];
    }
}
