<?php

/**
 * @brief Migración para crear la tabla 'configuraciones' del restaurante.
 *
 * Esta tabla almacena la configuración global del restaurante como un registro
 * singleton (un solo registro). Permite al Administrador definir el límite
 * máximo de sillas que el local puede soportar y el porcentaje de umbral a
 * partir del cual se disparan alertas visuales de proximidad al límite.
 *
 * @field id_configuracion  PK autoincremental.
 * @field max_sillas        Límite máximo de sillas permitidas. 0 = sin límite
 *                          (se usará la suma de sillas de todas las mesas).
 * @field umbral_alerta     Porcentaje (1-100) de ocupación a partir del cual
 *                          se muestra una alerta al Maitre/Admin. Default: 80%.
 * @field timestamps        Campos created_at y updated_at de Laravel.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @brief Crea la tabla 'configuraciones' e inserta el registro inicial.
     *
     * Se inserta un registro por defecto con max_sillas = 0 (sin límite)
     * y umbral_alerta = 80 (alerta al 80% de ocupación), para que el sistema
     * funcione desde el primer momento sin necesidad de configuración previa.
     */
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id('id_configuracion');
            $table->integer('max_sillas')->default(0);      // 0 = sin límite, usa suma de mesas
            $table->integer('umbral_alerta')->default(80);   // porcentaje para activar alerta
            $table->timestamps();
        });

        // Insertamos el registro singleton con valores por defecto para que la
        // configuración esté disponible inmediatamente después de la migración
        DB::table('configuraciones')->insert([
            'max_sillas'    => 0,
            'umbral_alerta' => 80,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /**
     * @brief Elimina la tabla 'configuraciones'.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};
