<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionRestaurante;
use App\Models\Horario;
use App\Models\Mesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * @brief Controlador de configuración de capacidad del restaurante.
 *
 * Permite al Administrador definir el límite máximo de sillas que el
 * restaurante puede soportar y el porcentaje de umbral de alerta para
 * ser notificado cuando la ocupación se acerque al límite establecido.
 *
 * Acceso restringido al rol Administrador mediante middleware 'role:Administrador'.
 */
class ConfiguracionController extends Controller
{
    /**
     * @brief Muestra el formulario de configuración de capacidad del restaurante.
     *
     * Carga la configuración actual (o crea una con valores por defecto si
     * no existe), calcula estadísticas del restaurante (mesas, sillas, ocupación
     * actual) y genera los datos de alerta para mostrar en la vista.
     *
     * @return View  Vista admin/configuracion con los datos de configuración y
     *               estadísticas del restaurante para que el admin pueda tomar
     *               decisiones informadas al definir el límite de capacidad.
     *
     * @pre  Usuario autenticado con rol Administrador.
     * @post Se muestra el formulario con valores actuales y métricas.
     */
    public function edit(): View
    {
        // Obtenemos la configuración singleton actual del restaurante;
        // si no existe, se crea automáticamente con valores por defecto
        $config = ConfiguracionRestaurante::actual();

        // Calculamos las estadísticas del restaurante para mostrar en el
        // formulario de configuración, permitiendo al admin ver el estado
        // actual antes de modificar los límites
        $sillasActuales = Mesa::capacidadTotal(); // Suma de todas las sillas de las mesas registradas
        $numMesas = Mesa::count(); // Total de mesas registradas en el sistema

        // Contamos los asientos ocupados en la ventana actual de 2 horas
        // para mostrar la ocupación en tiempo real al administrador
        $ocupados = (int) DB::table('horarios')
            ->join('reservaciones', 'horarios.reservacion_id', '=', 'reservaciones.id_reservacion')
            ->whereRaw("horarios.inicio BETWEEN NOW() - INTERVAL '2 hours' AND NOW() + INTERVAL '2 hours'")
            ->sum('reservaciones.cantidad');

        // Verificamos si la ocupación actual supera el umbral de alerta
        // configurado para mostrar alertas visuales en el formulario
        $alerta = $config->verificarAlerta($ocupados);

        return view('admin.configuracion', compact(
            'config',
            'sillasActuales',
            'numMesas',
            'ocupados',
            'alerta'
        ));
    }

    /**
     * @brief Actualiza la configuración de capacidad del restaurante.
     *
     * Valida los datos del formulario y actualiza el registro singleton de
     * configuración con los nuevos valores de max_sillas y umbral_alerta.
     *
     * @param  Request $request  Campos: max_sillas (entero ≥ 0),
     *                           umbral_alerta (entero 1-100).
     * @return RedirectResponse  Redirige al formulario con mensaje de éxito.
     *
     * @pre  Los campos deben pasar la validación (max_sillas ≥ 0, umbral 1-100).
     * @post Se actualiza la configuración y se redirige con confirmación.
     */
    public function update(Request $request): RedirectResponse
    {
        // Validamos los datos del formulario para asegurar que max_sillas sea
        // un entero no negativo (0 = sin límite) y que umbral_alerta esté
        // entre 1 y 100 como porcentaje válido
        $request->validate([
            'max_sillas' => ['required', 'integer', 'min:0'],
            'umbral_alerta' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'max_sillas.required' => 'El límite de sillas es obligatorio.',
            'max_sillas.integer' => 'El límite debe ser un número entero.',
            'max_sillas.min' => 'El límite no puede ser negativo. Usa 0 para desactivar.',
            'umbral_alerta.required' => 'El umbral de alerta es obligatorio.',
            'umbral_alerta.integer' => 'El umbral debe ser un número entero.',
            'umbral_alerta.min' => 'El umbral mínimo es 1%.',
            'umbral_alerta.max' => 'El umbral máximo es 100%.',
        ]);

        // Obtenemos la configuración singleton y la actualizamos con los
        // nuevos valores proporcionados por el Administrador
        $config = ConfiguracionRestaurante::actual();
        $config->update([
            'max_sillas' => $request->max_sillas,
            'umbral_alerta' => $request->umbral_alerta,
        ]);

        return redirect()->route('configuracion.edit')
            ->with('success', 'Configuración de capacidad actualizada correctamente.');
    }
}
