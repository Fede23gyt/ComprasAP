<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Presupuesto;
use App\Models\Proveedor;
use App\Models\DetalleOferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OfertaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Oferta::with(['presupuesto', 'proveedor', 'presupuesto.notaPedido'])
            ->orderBy('fecha_oferta', 'desc')
            ->orderBy('numero_oferta', 'desc');

        // Filtros
        if ($request->has('busqueda') && !empty($request->busqueda)) {
            $busqueda = $request->busqueda;
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_oferta', 'like', "%{$busqueda}%")
                  ->orWhere('observaciones', 'like', "%{$busqueda}%")
                  ->orWhereHas('proveedor', function ($q) use ($busqueda) {
                      $q->where('razon_social', 'like', "%{$busqueda}%")
                        ->orWhere('nombre_fantasia', 'like', "%{$busqueda}%");
                  })
                  ->orWhereHas('presupuesto', function ($q) use ($busqueda) {
                      $q->where('numero_presupuesto', 'like', "%{$busqueda}%");
                  });
            });
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('proveedor') && !empty($request->proveedor)) {
            $query->whereHas('proveedor', function ($q) use ($request) {
                $q->where('razon_social', 'like', "%{$request->proveedor}%")
                  ->orWhere('nombre_fantasia', 'like', "%{$request->proveedor}%");
            });
        }

        if ($request->has('presupuesto_id') && !empty($request->presupuesto_id)) {
            $query->where('presupuesto_id', $request->presupuesto_id);
        }

        // Si no es admin, solo mostrar ofertas de presupuestos de sus oficinas
        if (!$user->hasRole('administrador') && !$user->hasRole('secretario') && !$user->hasRole('director')) {
            $query->whereHas('presupuesto.notaPedido', function ($q) use ($user) {
                $q->whereIn('oficina_id', $user->oficinas->pluck('id'));
            });
        }

        $ofertas = $query->paginate(25);

        // Transformar las ofertas para incluir atributos calculados
        $ofertas->getCollection()->transform(function ($oferta) {
            $oferta->estado_efectivo = $oferta->estado_efectivo;
            $oferta->estado_efectivo_texto = $oferta->estado_efectivo_texto;
            $oferta->puede_modificar = $oferta->puedeModificar();
            return $oferta;
        });

        $estadisticas = [
            'total' => Oferta::count(),
            'pendientes' => Oferta::where('estado', 'pendiente')->count(),
            'evaluando' => Oferta::where('estado', 'evaluando')->count(),
            'aprobadas' => Oferta::where('estado', 'aprobada')->count(),
            'rechazadas' => Oferta::where('estado', 'rechazada')->count(),
            'adjudicadas' => Oferta::where('estado', 'adjudicada')->count(),
            'no_adjudicadas' => Oferta::where('estado', 'no_adjudicada')->count(),
        ];

        // Filtrar presupuestos según permisos del usuario
        $presupuestosQuery = Presupuesto::where('estado', 'aprobado')
            ->with('notasPedido.oficina');

        if (!$user->hasRole('administrador') && !$user->hasRole('secretario') && !$user->hasRole('director')) {
            $oficinasIds = $user->oficinas->pluck('id');
            $presupuestosQuery->whereHas('notasPedido', function ($q) use ($oficinasIds) {
                $q->whereIn('oficina_id', $oficinasIds);
            });
        }

        return inertia('Ofertas/Index', [
            'title' => 'Gestión de Ofertas',
            'ofertas' => $ofertas,
            'estadisticas' => $estadisticas,
            'filtros' => $request->only(['busqueda', 'estado', 'proveedor', 'presupuesto_id']),
            'presupuestos' => $presupuestosQuery->get(),
            'proveedores' => Proveedor::where('estado', '1')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        // Obtener presupuestos aprobados según permisos del usuario
        $presupuestosQuery = Presupuesto::where('estado', 'aprobado')
            ->with(['notasPedido.oficina', 'ofertas']);

        // Si no es admin/secretario/director, filtrar por oficinas asignadas
        if (!$user->hasRole('administrador') && !$user->hasRole('secretario') && !$user->hasRole('director')) {
            $oficinasIds = $user->oficinas->pluck('id');
            $presupuestosQuery->whereHas('notasPedido', function ($q) use ($oficinasIds) {
                $q->whereIn('oficina_id', $oficinasIds);
            });
        }

        $presupuestos = $presupuestosQuery->get();

        return inertia('Ofertas/Create', [
            'title' => 'Nueva Oferta',
            'presupuestos' => $presupuestos,
            'proveedores' => Proveedor::where('estado', '1')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('=== INICIO GUARDADO OFERTA ===');
        \Log::info('Datos recibidos:', $request->all());

        $validator = Validator::make($request->all(), [
            'presupuesto_id' => 'required|exists:presupuestos,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha_oferta' => 'required|date',
            'plazo_entrega' => 'nullable|integer|min:1',
            'forma_pago' => 'nullable|string|max:255',
            'validez_oferta' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.det_presupuesto_id' => 'required|exists:det_presupuesto,id',
            'detalles.*.renglon' => 'required|integer|min:1',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.marca' => 'nullable|string|max:255',
            'detalles.*.modelo' => 'nullable|string|max:255',
            'detalles.*.caracteristicas_tecnicas' => 'nullable|string',
            'detalles.*.garantia' => 'nullable|string|max:255',
            'detalles.*.tiempo_entrega' => 'nullable|integer|min:0',
            'detalles.*.observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Errores de validación:', $validator->errors()->toArray());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        \Log::info('Validación pasada correctamente');

        // Validar que el presupuesto pueda recibir ofertas
        $presupuesto = Presupuesto::with('notasPedido.oficina')->findOrFail($request->presupuesto_id);
        \Log::info('Presupuesto encontrado:', ['id' => $presupuesto->id]);

        if (!$presupuesto->puedeRecibirOfertas()) {
            \Log::error('Presupuesto no puede recibir ofertas');
            return redirect()->back()
                ->with('error', 'Este presupuesto no puede recibir ofertas. Debe estar aprobado y no vencido.')
                ->withInput();
        }

        // Validar que el usuario tenga acceso al presupuesto (excepto admin/secretario/director)
        $user = $request->user();
        \Log::info('Usuario:', ['id' => $user->id, 'roles' => $user->roles->pluck('nombre')]);

        if (!$user->hasRole('administrador') && !$user->hasRole('secretario') && !$user->hasRole('director')) {
            $oficinasIds = $user->oficinas->pluck('id');
            $tieneAcceso = $presupuesto->notasPedido->whereIn('oficina_id', $oficinasIds)->isNotEmpty();

            if (!$tieneAcceso) {
                \Log::error('Usuario no tiene acceso al presupuesto');
                return redirect()->back()
                    ->with('error', 'No tiene permiso para cargar ofertas a este presupuesto.')
                    ->withInput();
            }
        }

        try {
            \Log::info('Iniciando transacción de base de datos');
            DB::beginTransaction();

            \Log::info('Creando oferta');
            $oferta = Oferta::create([
                'presupuesto_id' => $request->presupuesto_id,
                'proveedor_id' => $request->proveedor_id,
                'fecha_oferta' => $request->fecha_oferta,
                'plazo_entrega' => $request->plazo_entrega,
                'forma_pago' => $request->forma_pago,
                'validez_oferta' => $request->validez_oferta,
                'observaciones' => $request->observaciones,
                'estado' => 'pendiente'
            ]);
            \Log::info('Oferta creada con ID:', ['id' => $oferta->id]);

            $montoTotal = 0;
            foreach ($request->detalles as $index => $detalleData) {
                \Log::info("Creando detalle {$index}", $detalleData);
                $detalle = DetalleOferta::create([
                    'oferta_id' => $oferta->id,
                    'det_presupuesto_id' => $detalleData['det_presupuesto_id'],
                    'renglon' => $detalleData['renglon'],
                    'insumo_id' => $detalleData['insumo_id'],
                    'cantidad' => $detalleData['cantidad'],
                    'precio_unitario' => $detalleData['precio_unitario'],
                    'marca' => $detalleData['marca'] ?? null,
                    'modelo' => $detalleData['modelo'] ?? null,
                    'caracteristicas_tecnicas' => $detalleData['caracteristicas_tecnicas'] ?? null,
                    'garantia' => $detalleData['garantia'] ?? null,
                    'tiempo_entrega' => $detalleData['tiempo_entrega'] ?? null,
                    'observaciones' => $detalleData['observaciones'] ?? null
                ]);

                $montoTotal += $detalle->subtotal;
                \Log::info("Detalle {$index} creado", ['id' => $detalle->id, 'subtotal' => $detalle->subtotal]);
            }

            \Log::info('Actualizando monto total de oferta', ['monto_total' => $montoTotal]);
            $oferta->update(['monto_total' => $montoTotal]);

            \Log::info('Haciendo commit de la transacción');
            DB::commit();

            \Log::info('=== OFERTA GUARDADA EXITOSAMENTE ===');
            return redirect()->route('ofertas.index')
                ->with('success', 'Oferta creada exitosamente');

        } catch (\Exception $e) {
            \Log::error('Error al guardar oferta:', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear la oferta: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Oferta $oferta)
    {
        $oferta->load([
            'presupuesto',
            'proveedor',
            'detalles.insumo',
            'presupuesto.notaPedido',
            'presupuesto.detalles.insumo'
        ]);

        // Agregar atributos calculados
        $oferta->estado_efectivo = $oferta->estado_efectivo;
        $oferta->estado_efectivo_texto = $oferta->estado_efectivo_texto;
        $oferta->puede_modificar = $oferta->puedeModificar();

        return inertia('Ofertas/Show', [
            'title' => 'Detalle de Oferta',
            'oferta' => $oferta
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Oferta $oferta)
    {
        // Verificar si la oferta puede ser modificada
        if (!$oferta->puedeModificar()) {
            return redirect()->route('ofertas.show', $oferta->id)
                ->with('error', 'Esta oferta no puede ser modificada. Estado actual: ' . $oferta->estado_texto);
        }

        $user = $request->user();
        $oferta->load(['detalles.insumo']);

        // Filtrar presupuestos según permisos del usuario
        $presupuestosQuery = Presupuesto::where('estado', 'aprobado')
            ->with('notasPedido.oficina');

        if (!$user->hasRole('administrador') && !$user->hasRole('secretario') && !$user->hasRole('director')) {
            $oficinasIds = $user->oficinas->pluck('id');
            $presupuestosQuery->whereHas('notasPedido', function ($q) use ($oficinasIds) {
                $q->whereIn('oficina_id', $oficinasIds);
            });
        }

        return inertia('Ofertas/Edit', [
            'title' => 'Editar Oferta',
            'oferta' => $oferta,
            'presupuestos' => $presupuestosQuery->get(),
            'proveedores' => Proveedor::where('estado', '1')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Oferta $oferta)
    {
        // Verificar si la oferta puede ser modificada
        if (!$oferta->puedeModificar()) {
            return redirect()->route('ofertas.show', $oferta->id)
                ->with('error', 'Esta oferta no puede ser modificada. Estado actual: ' . $oferta->estado_texto);
        }

        $validator = Validator::make($request->all(), [
            'presupuesto_id' => 'required|exists:presupuestos,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'fecha_oferta' => 'required|date',
            'plazo_entrega' => 'nullable|integer|min:1',
            'forma_pago' => 'nullable|string|max:255',
            'validez_oferta' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.det_presupuesto_id' => 'required|exists:det_presupuesto,id',
            'detalles.*.renglon' => 'required|integer|min:1',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.marca' => 'nullable|string|max:255',
            'detalles.*.modelo' => 'nullable|string|max:255',
            'detalles.*.caracteristicas_tecnicas' => 'nullable|string',
            'detalles.*.garantia' => 'nullable|string|max:255',
            'detalles.*.tiempo_entrega' => 'nullable|integer|min:0',
            'detalles.*.observaciones' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $oferta->update([
                'presupuesto_id' => $request->presupuesto_id,
                'proveedor_id' => $request->proveedor_id,
                'fecha_oferta' => $request->fecha_oferta,
                'plazo_entrega' => $request->plazo_entrega,
                'forma_pago' => $request->forma_pago,
                'validez_oferta' => $request->validez_oferta,
                'observaciones' => $request->observaciones
            ]);

            // Eliminar detalles existentes
            $oferta->detalles()->delete();

            $montoTotal = 0;
            foreach ($request->detalles as $detalleData) {
                $detalle = DetalleOferta::create([
                    'oferta_id' => $oferta->id,
                    'det_presupuesto_id' => $detalleData['det_presupuesto_id'],
                    'renglon' => $detalleData['renglon'],
                    'insumo_id' => $detalleData['insumo_id'],
                    'cantidad' => $detalleData['cantidad'],
                    'precio_unitario' => $detalleData['precio_unitario'],
                    'marca' => $detalleData['marca'] ?? null,
                    'modelo' => $detalleData['modelo'] ?? null,
                    'caracteristicas_tecnicas' => $detalleData['caracteristicas_tecnicas'] ?? null,
                    'garantia' => $detalleData['garantia'] ?? null,
                    'tiempo_entrega' => $detalleData['tiempo_entrega'] ?? null,
                    'observaciones' => $detalleData['observaciones'] ?? null
                ]);

                $montoTotal += $detalle->subtotal;
            }

            $oferta->update(['monto_total' => $montoTotal]);

            DB::commit();

            return redirect()->route('ofertas.show', $oferta->id)
                ->with('success', 'Oferta actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar la oferta: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Oferta $oferta)
    {
        // Verificar si la oferta puede ser eliminada
        if (!$oferta->puedeModificar()) {
            return redirect()->back()
                ->with('error', 'Esta oferta no puede ser eliminada. Estado actual: ' . $oferta->estado_texto);
        }

        try {
            DB::beginTransaction();

            $oferta->detalles()->delete();
            $oferta->delete();

            DB::commit();

            return redirect()->route('ofertas.index')
                ->with('success', 'Oferta eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar la oferta: ' . $e->getMessage());
        }
    }

    /**
     * Cambiar estado de la oferta
     */
    public function cambiarEstado(Request $request, Oferta $oferta)
    {
        $request->validate([
            'estado' => 'required|in:pending,evaluando,aprobada,rechazada,adjudicada',
            'observaciones' => 'nullable|string'
        ]);

        try {
            $oferta->cambiarEstado($request->estado, $request->observaciones);

            return redirect()->back()->with('success', 'Estado de la oferta actualizado exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener ofertas por presupuesto para comparativa
     */
    public function porPresupuesto(Presupuesto $presupuesto)
    {
        $ofertas = Oferta::with(['proveedor', 'detalles.insumo'])
            ->where('presupuesto_id', $presupuesto->id)
            ->whereIn('estado', ['aprobada', 'evaluando'])
            ->orderBy('total_final')
            ->get();

        return response()->json([
            'ofertas' => $ofertas,
            'presupuesto' => $presupuesto->load('notaPedido')
        ]);
    }

    /**
     * API para búsqueda de ofertas
     */
    public function apiBuscar(Request $request)
    {
        $query = Oferta::with(['proveedor', 'presupuesto']);

        if ($request->has('q') && !empty($request->q)) {
            $query->where('numero_oferta', 'like', "%{$request->q}%")
                  ->orWhereHas('proveedor', function ($q) use ($request) {
                      $q->where('razon_social', 'like', "%{$request->q}%")
                        ->orWhere('nombre_fantasia', 'like', "%{$request->q}%");
                  });
        }

        if ($request->has('presupuesto_id') && !empty($request->presupuesto_id)) {
            $query->where('presupuesto_id', $request->presupuesto_id);
        }

        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        $ofertas = $query->orderBy('fecha_oferta', 'desc')->limit(50)->get();

        return response()->json($ofertas);
    }

    /**
     * Mostrar ofertas de un presupuesto
     */
    public function ofertasPresupuesto(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'notasPedido.oficina',
            'tipoCompra',
            'ofertas.proveedor',
            'ofertas.detalles'
        ]);

        $estadisticas = [
            'total_ofertas' => $presupuesto->ofertas->count(),
            'ofertas_pendientes' => $presupuesto->ofertas->where('estado', 'pendiente')->count(),
            'ofertas_evaluando' => $presupuesto->ofertas->where('estado', 'evaluando')->count(),
            'ofertas_aprobadas' => $presupuesto->ofertas->where('estado', 'aprobada')->count(),
            'monto_presupuestado' => $presupuesto->total_presupuesto,
            'monto_menor_oferta' => $presupuesto->ofertas->min('monto_total'),
            'monto_mayor_oferta' => $presupuesto->ofertas->max('monto_total'),
        ];

        return inertia('Presupuestos/OfertasPresupuesto', [
            'title' => "Ofertas del Presupuesto {$presupuesto->numero_presupuesto}/{$presupuesto->ejercicio}",
            'presupuesto' => $presupuesto,
            'estadisticas' => $estadisticas
        ]);
    }

    /**
     * Mostrar vista de evaluación de ofertas por presupuesto
     */
    public function evaluarOfertas(Presupuesto $presupuesto)
    {
        // Verificar que el presupuesto pueda recibir ofertas
        if (!$presupuesto->puedeRecibirOfertas()) {
            return redirect()->back()->with('error', 'Este presupuesto no puede recibir ofertas en su estado actual.');
        }

        $presupuesto->load([
            'detalles.insumo.clasificacionEconomica',
            'notaPedido.oficina',
            'notasPedido.oficina',
            'tipoCompra',
            'ofertas.proveedor',
            'ofertas.detalles.insumo'
        ]);

        // Organizar ofertas por renglón para facilitar la comparación
        $ofertasPorRenglon = [];

        foreach ($presupuesto->detalles as $detallePresupuesto) {
            $renglon = $detallePresupuesto->renglon;
            $ofertasPorRenglon[$renglon] = [
                'presupuesto_detalle' => $detallePresupuesto,
                'ofertas' => []
            ];

            // Obtener todas las ofertas que tienen este renglón
            foreach ($presupuesto->ofertas as $oferta) {
                $tieneEsteRenglon = $oferta->detalles->where('det_presupuesto_id', $detallePresupuesto->id)->isNotEmpty();
                if ($tieneEsteRenglon) {
                    $ofertasPorRenglon[$renglon]['ofertas'][] = $oferta;
                }
            }

            // Ordenar ofertas por precio del renglón (menor a mayor)
            usort($ofertasPorRenglon[$renglon]['ofertas'], function ($ofertaA, $ofertaB) use ($detallePresupuesto) {
                $detalleA = $ofertaA->detalles->where('det_presupuesto_id', $detallePresupuesto->id)->first();
                $detalleB = $ofertaB->detalles->where('det_presupuesto_id', $detallePresupuesto->id)->first();

                $precioA = $detalleA ? $detalleA->precio_unitario : PHP_FLOAT_MAX;
                $precioB = $detalleB ? $detalleB->precio_unitario : PHP_FLOAT_MAX;

                return $precioA <=> $precioB;
            });
        }

        return inertia('Presupuestos/EvaluarOfertas', [
            'title' => "Evaluación de Ofertas - Presupuesto {$presupuesto->identificador_completo}",
            'presupuesto' => $presupuesto,
            'ofertasPorRenglon' => $ofertasPorRenglon,
            'estadisticas' => $this->calcularEstadisticasEvaluacion($presupuesto)
        ]);
    }

    /**
     * Seleccionar ofertas para adjudicación
     */
    public function seleccionarOfertas(Request $request, Presupuesto $presupuesto)
    {
        $request->validate([
            'selecciones' => 'required|array|min:1',
            'selecciones.*.detalle_oferta_id' => 'required|exists:detalle_ofertas,id',
            'selecciones.*.motivo' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Marcar todas las ofertas del presupuesto como evaluadas
            $presupuesto->ofertas()->update([
                'evaluada' => true,
                'fecha_evaluacion' => now(),
                'evaluado_por' => auth()->id()
            ]);

            // Procesar las selecciones
            foreach ($request->selecciones as $seleccion) {
                $detalleOferta = DetalleOferta::find($seleccion['detalle_oferta_id']);

                if ($detalleOferta) {
                    // Marcar este detalle como seleccionado
                    $detalleOferta->update([
                        'seleccionado' => true,
                        'motivo_seleccion' => $seleccion['motivo'] ?? 'Mejor precio/calidad',
                        'fecha_seleccion' => now()
                    ]);

                    // Marcar la oferta padre como seleccionada si tiene al menos un item seleccionado
                    $detalleOferta->oferta->update([
                        'seleccionada' => true,
                        'estado' => 'aprobada'
                    ]);

                    // Marcar otras ofertas del mismo renglón como no seleccionadas
                    DetalleOferta::where('det_presupuesto_id', $detalleOferta->det_presupuesto_id)
                        ->where('id', '!=', $detalleOferta->id)
                        ->update([
                            'seleccionado' => false,
                            'motivo_seleccion' => null,
                            'fecha_seleccion' => null
                        ]);
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Ofertas seleccionadas correctamente. Ya puede proceder a generar las órdenes de compra.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al seleccionar ofertas: ' . $e->getMessage());
        }
    }

    /**
     * Calcular estadísticas para la evaluación
     */
    private function calcularEstadisticasEvaluacion(Presupuesto $presupuesto)
    {
        $stats = [
            'total_renglones' => $presupuesto->detalles->count(),
            'renglones_con_ofertas' => 0,
            'renglones_sin_ofertas' => 0,
            'total_ofertas' => $presupuesto->ofertas->count(),
            'proveedores_participantes' => $presupuesto->ofertas->unique('proveedor_id')->count(),
            'oferta_mas_baja' => null,
            'oferta_mas_alta' => null,
            'ahorro_potencial' => 0
        ];

        foreach ($presupuesto->detalles as $detalle) {
            $tieneOfertas = $presupuesto->ofertas->flatMap->detalles
                ->where('det_presupuesto_id', $detalle->id)
                ->count() > 0;

            if ($tieneOfertas) {
                $stats['renglones_con_ofertas']++;
            } else {
                $stats['renglones_sin_ofertas']++;
            }
        }

        // Calcular precios más altos y más bajos
        $precios = $presupuesto->ofertas->flatMap->detalles->pluck('precio_unitario');
        if ($precios->isNotEmpty()) {
            $stats['oferta_mas_baja'] = $precios->min();
            $stats['oferta_mas_alta'] = $precios->max();
        }

        return $stats;
    }

    /**
     * Adjudicar ofertas por renglón
     */
    public function adjudicarOfertas(Request $request, Presupuesto $presupuesto)
    {
        $request->validate([
            'adjudicaciones' => 'required|array|min:1',
            'adjudicaciones.*.detalle_oferta_id' => 'required|exists:detalle_ofertas,id',
            'adjudicaciones.*.renglon' => 'required|integer',
            'adjudicaciones.*.observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $ofertasAdjudicadas = collect();

            // Procesar cada adjudicación
            foreach ($request->adjudicaciones as $adjudicacion) {
                $detalleOferta = DetalleOferta::with('oferta')->find($adjudicacion['detalle_oferta_id']);

                if ($detalleOferta) {
                    // Marcar este detalle como seleccionado/adjudicado
                    $detalleOferta->update([
                        'seleccionado' => true,
                        'motivo_seleccion' => $adjudicacion['observaciones'] ?? 'Mejor precio/calidad',
                        'fecha_seleccion' => now()
                    ]);

                    // Agregar la oferta a la colección de ofertas adjudicadas
                    $ofertasAdjudicadas->push($detalleOferta->oferta);

                    // Desmarcar otros detalles del mismo renglón
                    DetalleOferta::where('det_presupuesto_id', $detalleOferta->det_presupuesto_id)
                        ->where('id', '!=', $detalleOferta->id)
                        ->update([
                            'seleccionado' => false,
                            'motivo_seleccion' => null,
                            'fecha_seleccion' => null
                        ]);
                }
            }

            // Marcar ofertas como adjudicadas (las que tienen al menos un renglón seleccionado)
            foreach ($ofertasAdjudicadas->unique('id') as $oferta) {
                $oferta->update([
                    'estado' => 'adjudicada',
                    'evaluada' => true,
                    'fecha_evaluacion' => now(),
                    'evaluado_por' => auth()->id()
                ]);
            }

            // Marcar otras ofertas del presupuesto como no adjudicadas
            $presupuesto->ofertas()
                ->whereNotIn('id', $ofertasAdjudicadas->pluck('id'))
                ->update([
                    'estado' => Oferta::ESTADO_NO_ADJUDICADA,
                    'evaluada' => true,
                    'fecha_evaluacion' => now(),
                    'evaluado_por' => auth()->id(),
                    'motivo_rechazo' => 'No adjudicada - Otra oferta seleccionada'
                ]);

            // Actualizar estado del presupuesto
            $presupuesto->update([
                'estado' => Presupuesto::ESTADO_ADJUDICADO
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Ofertas adjudicadas correctamente. Ya puede generar las órdenes de compra.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al adjudicar ofertas:', [
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al adjudicar ofertas: ' . $e->getMessage());
        }
    }

    /**
     * Anular oferta
     */
    public function anular(Request $request, Oferta $oferta)
    {
        $request->validate([
            'motivo' => 'required|string|max:1000'
        ]);

        try {
            $oferta->update([
                'estado' => 'anulada',
                'motivo_rechazo' => $request->motivo,
                'fecha_evaluacion' => now(),
                'evaluado_por' => auth()->id()
            ]);

            return redirect()->back()->with('success', 'Oferta anulada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al anular la oferta: ' . $e->getMessage());
        }
    }
}
