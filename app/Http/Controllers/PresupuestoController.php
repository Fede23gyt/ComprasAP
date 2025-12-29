<?php

namespace App\Http\Controllers;

use App\Models\DetPresupuesto;
use App\Models\DetNotaPedido;
use App\Models\NotaPedido;
use App\Models\Presupuesto;
use App\Models\TipoCompra;
use App\Models\Memo;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\DetalleOferta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PresupuestoController extends Controller
{
    /**
     * Mostrar listado de presupuestos
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $numero = $request->get('numero', '');
        $ejercicio = $request->get('ejercicio', '');
        $estado = $request->get('estado', '');
        $notaPedidoId = $request->get('nota_pedido_id', '');

        $user = Auth::user();

        // Query base
        $query = Presupuesto::with(['notaPedido', 'usuario', 'tipoCompra'])
            ->withCount('ofertas');

        // Aplicar filtros
        if ($numero) {
            $query->where('numero_presupuesto', 'LIKE', "%{$numero}%");
        }

        if ($ejercicio) {
            $query->where('ejercicio', $ejercicio);
        }

        if ($estado) {
            $query->where('estado', $estado);
        }


        if ($notaPedidoId) {
            $query->where('nota_pedido_id', $notaPedidoId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('observaciones', 'LIKE', "%{$search}%")
                    ->orWhereHas('notaPedido', function ($subq) use ($search) {
                        $subq->where('descripcion', 'LIKE', "%{$search}%")
                            ->orWhere('expediente', 'LIKE', "%{$search}%");
                    });
            });
        }

        $presupuestos = $query->orderBy('ejercicio', 'desc')
            ->orderBy('numero_presupuesto', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Obtener datos para filtros
        $notasPedido = NotaPedido::confirmadas()->orderBy('ejercicio_nota', 'desc')->get();

        return Inertia::render('Presupuestos/Index', [
            'title' => 'Gestión de Presupuestos',
            'presupuestos' => $presupuestos,
            'notasPedido' => $notasPedido,
            'filters' => [
                'search' => $search,
                'numero' => $numero,
                'ejercicio' => $ejercicio,
                'estado' => $estado,
                'nota_pedido_id' => $notaPedidoId,
            ],
        ]);
    }

    /**
     * Mostrar presupuestos por nota de pedido
     */
    public function porNotaPedido(Request $request, NotaPedido $notaPedido)
    {
        $search = $request->get('search', '');
        $estado = $request->get('estado', '');

        $query = Presupuesto::with(['usuario'])
            ->where('nota_pedido_id', $notaPedido->id);

        // Aplicar filtros
        if ($estado) {
            $query->where('estado', $estado);
        }

        if ($search) {
            $query->where('observaciones', 'LIKE', "%{$search}%");
        }

        $presupuestos = $query->orderBy('ejercicio', 'desc')
            ->orderBy('numero_presupuesto', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Presupuestos/PorNotaPedido', [
            'title' => "Presupuestos - Nota {$notaPedido->numero_nota}/{$notaPedido->ejercicio_nota}",
            'presupuestos' => $presupuestos,
            'notaPedido' => $notaPedido->load(['oficina', 'tipoNota']),
            'filters' => [
                'search' => $search,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(Request $request)
    {
        $notasPedidoIds = $request->get('notas_pedido_ids', []);
        $ordenRenglones = $request->get('orden_renglones');

        $tiposCompra = TipoCompra::habilitados()->orderBy('descripcion')->get();

        // Paso 1: Si no hay notas seleccionadas, mostrar selector
        if (empty($notasPedidoIds)) {
            // Solo mostrar notas que tengan al menos un insumo disponible para presupuestar
            $notasPedido = NotaPedido::confirmadas()
                ->with(['oficina'])
                ->whereHas('detalles', function($query) {
                    $query->where('presupuestado', false);
                })
                ->get()
                ->filter(function($nota) {
                    // Verificar que no todos los insumos estén en presupuestos activos
                    return $this->notaTieneInsumosDisponibles($nota);
                })
                ->sortByDesc('ejercicio_nota')
                ->sortByDesc('numero_nota')
                ->values();

            $memos = Memo::where('estado', 'Habilitado')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion']);

            return Inertia::render('Presupuestos/Create', [
                'title' => 'Nuevo Presupuesto',
                'notasPedido' => $notasPedido,
                'tiposCompra' => $tiposCompra,
                'ejercicioActual' => date('Y'),
                'memos' => $memos,
                'step' => 'select_notes',
            ]);
        }

        // Paso 2: Si hay múltiples notas pero no se ha definido el orden, mostrar opciones de orden
        if (count($notasPedidoIds) > 1 && ! $ordenRenglones) {
            $notasSeleccionadas = NotaPedido::with(['oficina'])
                ->whereIn('id', $notasPedidoIds)
                ->get();

            $memos = Memo::where('estado', 'Habilitado')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'descripcion']);

            return Inertia::render('Presupuestos/Create', [
                'title' => 'Nuevo Presupuesto - Orden de Renglones',
                'notasSeleccionadas' => $notasSeleccionadas,
                'notasPedidoIds' => $notasPedidoIds,
                'tiposCompra' => $tiposCompra,
                'ejercicioActual' => date('Y'),
                'memos' => $memos,
                'step' => 'select_order',
            ]);
        }

        // Paso 3: Mostrar formulario de creación con las notas y orden definidos
        $notasSeleccionadas = NotaPedido::with([
                'detalles' => function($query) {
                    // Incluir todos los detalles para poder filtrar después
                    $query->orderBy('renglon');
                },
                'detalles.insumo.clasificacionEconomica',
                'oficina'
            ])
            ->whereIn('id', $notasPedidoIds)
            ->get();

        // Filtrar solo insumos disponibles (no asignados a presupuestos activos)
        foreach ($notasSeleccionadas as $nota) {
            $nota->detalles = $nota->detalles->filter(function($detalle) {
                // Si no está presupuestado, está disponible
                if (!$detalle->presupuestado) {
                    return true;
                }

                // Si está presupuestado, verificar si está solo en presupuestos rechazados
                $presupuestosActivos = DetPresupuesto::where('insumo_id', $detalle->insumo_id)
                    ->whereHas('presupuesto', function($query) {
                        $query->where('estado', '!=', Presupuesto::ESTADO_RECHAZADO);
                    })
                    ->exists();

                return !$presupuestosActivos;
            });
        }

        // Verificar que todas las notas estén confirmadas
        foreach ($notasSeleccionadas as $nota) {
            if (! $nota->isConfirmada()) {
                return redirect()->route('presupuestos.create')
                    ->with('error', "La nota {$nota->numero_nota}/{$nota->ejercicio_nota} no está confirmada.");
            }
        }

        // Obtener próximo número de llamado si hay presupuestos existentes para estas notas
        $proximoNroLlamado = $this->calcularProximoNroLlamado($notasPedidoIds);

        $memos = Memo::where('estado', 'Habilitado')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion']);

        return Inertia::render('Presupuestos/Create', [
            'title' => 'Nuevo Presupuesto',
            'notasSeleccionadas' => $notasSeleccionadas,
            'notasPedidoIds' => $notasPedidoIds,
            'ordenRenglones' => $ordenRenglones,
            'tiposCompra' => $tiposCompra,
            'ejercicioActual' => date('Y'),
            'proximoNroLlamado' => $proximoNroLlamado,
            'memos' => $memos,
            'step' => 'create_budget',
        ]);
    }

    /**
     * Guardar nuevo presupuesto
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'notas_pedido_ids' => 'required|array|min:1',
            'notas_pedido_ids.*' => 'exists:nota_pedido,id',
            'tipo_compra_id' => 'required|exists:tipo_compra,id',
            'lugar_entrega' => 'nullable|string|max:255',
            'domicilio_entrega' => 'nullable|string|max:255',
            'dependencia' => 'nullable|string|max:255',
            'nro_llamado' => 'required|integer|min:1',
            'plazo_pago' => 'nullable|string|max:255',
            'comentario' => 'nullable|string|max:2000',
            'orden_renglones' => 'nullable|in:codigo_insumo,nota_pedido_insumo',
            'plazo_entrega' => 'nullable|integer|min:1',
            'forma_pago' => 'nullable|string|max:100',
            'validez_oferta' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string|max:1000',
            'detalles' => 'required|array|min:1',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.renglon' => 'required|integer|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.unidad_medida' => 'nullable|string|max:20',
            'detalles.*.especificaciones_tecnicas' => 'nullable|string|max:1000',
            'detalles.*.marca' => 'nullable|string|max:100',
            'detalles.*.modelo' => 'nullable|string|max:100',
            'detalles.*.observaciones' => 'nullable|string|max:1000',
            'detalles.*.nota_pedido_id' => 'nullable|exists:nota_pedido,id',
            'memo_id' => 'nullable|exists:memos,id',
        ]);

        DB::transaction(function () use ($validated) {
            $ejercicio = date('Y');
            $numeroPresupuesto = Presupuesto::generarProximoNumero($ejercicio);

            // Crear el presupuesto
            $presupuesto = Presupuesto::create([
                'nota_pedido_id' => $validated['notas_pedido_ids'][0], // Mantener compatibilidad con nota principal
                'tipo_compra_id' => $validated['tipo_compra_id'],
                'memo_id' => $validated['memo_id'] ?? null,
                'ejercicio' => $ejercicio,
                'numero_presupuesto' => $numeroPresupuesto,
                'fecha_presupuesto' => now()->toDateString(),
                'lugar_entrega' => $validated['lugar_entrega'] ?? null,
                'domicilio_entrega' => $validated['domicilio_entrega'] ?? null,
                'dependencia' => $validated['dependencia'] ?? null,
                'nro_llamado' => $validated['nro_llamado'],
                'plazo_pago' => $validated['plazo_pago'] ?? null,
                'comentario' => $validated['comentario'] ?? null,
                'orden_renglones' => $validated['orden_renglones'] ?? null,
                'plazo_entrega' => $validated['plazo_entrega'] ?? null,
                'forma_pago' => $validated['forma_pago'] ?? null,
                'validez_oferta' => $validated['validez_oferta'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'user_id' => Auth::id(),
                'estado' => Presupuesto::ESTADO_BORRADOR,
                'total_presupuesto' => 0.00, // Inicializar en 0, se calculará después
            ]);

            // Asociar las notas de pedido al presupuesto con orden
            foreach ($validated['notas_pedido_ids'] as $index => $notaPedidoId) {
                $presupuesto->notasPedido()->attach($notaPedidoId, ['orden' => $index + 1]);
            }

            // Crear los detalles
            foreach ($validated['detalles'] as $detalle) {
                DetPresupuesto::create([
                    'presupuesto_id' => $presupuesto->id,
                    'insumo_id' => $detalle['insumo_id'],
                    'renglon' => $detalle['renglon'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'total_renglon' => $detalle['cantidad'] * $detalle['precio_unitario'], // Calcular explícitamente
                    'unidad_medida' => $detalle['unidad_medida'] ?? null,
                    'especificaciones_tecnicas' => $detalle['especificaciones_tecnicas'] ?? null,
                    'marca' => $detalle['marca'] ?? null,
                    'modelo' => $detalle['modelo'] ?? null,
                    'observaciones' => $detalle['observaciones'] ?? null,
                ]);
            }

            // Marcar insumos como presupuestados en las notas de pedido asociadas
            foreach ($validated['detalles'] as $detalle) {
                // Para cada insumo del presupuesto, marcarlo como presupuestado en todas las notas asociadas
                foreach ($validated['notas_pedido_ids'] as $notaPedidoId) {
                    DetNotaPedido::where('nota_pedido_id', $notaPedidoId)
                        ->where('insumo_id', $detalle['insumo_id'])
                        ->where('presupuestado', false)
                        ->update(['presupuestado' => true]);
                }
            }

            // Calcular el total automáticamente
            $presupuesto->calcularTotal();
        });

        return redirect()->route('presupuestos.index')
            ->with('success', 'Presupuesto creado correctamente.');
    }

    /**
     * Mostrar presupuesto específico
     */
    public function show(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'notaPedido.oficina',
            'notaPedido.tipoNota',
            'usuario',
            'tipoCompra',
            'memo',
            'detalles.insumo.clasificacionEconomica',
        ])->loadCount('ofertas');

        return Inertia::render('Presupuestos/Show', [
            'title' => "Presupuesto {$presupuesto->numero_presupuesto}/{$presupuesto->ejercicio}",
            'presupuesto' => $presupuesto,
            'auth' => [
                'user' => auth()->user()->load('roles'),
            ],
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Presupuesto $presupuesto)
    {
        if (! $presupuesto->puedeEditar()) {
            return redirect()->route('presupuestos.index')
                ->with('error', 'No se puede editar un presupuesto que no está en borrador.');
        }

        $presupuesto->load([
            'notaPedido.detalles.insumo.clasificacionEconomica',
            'detalles.insumo.clasificacionEconomica',
        ]);

        return Inertia::render('Presupuestos/Edit', [
            'title' => "Editar Presupuesto {$presupuesto->numero_presupuesto}/{$presupuesto->ejercicio}",
            'presupuesto' => $presupuesto,
        ]);
    }

    /**
     * Actualizar presupuesto
     */
    public function update(Request $request, Presupuesto $presupuesto)
    {
        if (! $presupuesto->puedeEditar()) {
            return redirect()->route('presupuestos.index')
                ->with('error', 'No se puede editar un presupuesto que no está en borrador.');
        }

        $validated = $request->validate([
            'plazo_entrega' => 'nullable|integer|min:1',
            'forma_pago' => 'nullable|string|max:100',
            'validez_oferta' => 'nullable|integer|min:1',
            'observaciones' => 'nullable|string|max:1000',
            'detalles' => 'required|array|min:1',
            'detalles.*.id' => 'nullable|exists:det_presupuesto,id',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.renglon' => 'required|integer|min:1',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.unidad_medida' => 'nullable|string|max:20',
            'detalles.*.especificaciones_tecnicas' => 'nullable|string|max:1000',
            'detalles.*.marca' => 'nullable|string|max:100',
            'detalles.*.modelo' => 'nullable|string|max:100',
            'detalles.*.observaciones' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated, $presupuesto) {
            // Actualizar el presupuesto
            $presupuesto->update([
                'plazo_entrega' => $validated['plazo_entrega'] ?? null,
                'forma_pago' => $validated['forma_pago'] ?? null,
                'validez_oferta' => $validated['validez_oferta'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // Obtener IDs de detalles actuales
            $detallesActuales = $presupuesto->detalles()->pluck('id')->toArray();
            $detallesEnviados = array_filter(array_column($validated['detalles'], 'id'));

            // Eliminar detalles que ya no están
            $detallesAEliminar = array_diff($detallesActuales, $detallesEnviados);
            if (! empty($detallesAEliminar)) {
                DetPresupuesto::whereIn('id', $detallesAEliminar)->delete();
            }

            // Actualizar o crear detalles
            foreach ($validated['detalles'] as $detalle) {
                $datos = [
                    'presupuesto_id' => $presupuesto->id,
                    'insumo_id' => $detalle['insumo_id'],
                    'renglon' => $detalle['renglon'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'unidad_medida' => $detalle['unidad_medida'] ?? null,
                    'especificaciones_tecnicas' => $detalle['especificaciones_tecnicas'] ?? null,
                    'marca' => $detalle['marca'] ?? null,
                    'modelo' => $detalle['modelo'] ?? null,
                    'observaciones' => $detalle['observaciones'] ?? null,
                ];

                if (isset($detalle['id']) && $detalle['id']) {
                    DetPresupuesto::where('id', $detalle['id'])->update($datos);
                } else {
                    DetPresupuesto::create($datos);
                }
            }

            // Recalcular el total
            $presupuesto->calcularTotal();
        });

        return redirect()->route('presupuestos.index')
            ->with('success', 'Presupuesto actualizado correctamente.');
    }

    /**
     * Enviar/Confirmar presupuesto (borrador → enviado)
     */
    public function enviar(Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeEnviar()) {
            return redirect()->back()
                ->with('error', 'No se puede confirmar este presupuesto. Debe estar en borrador y tener al menos un detalle.');
        }

        try {
            $presupuesto->enviar();
            return redirect()->back()
                ->with('success', 'Presupuesto confirmado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al confirmar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Aprobar presupuesto (enviado → aprobado, solo directores)
     */
    public function aprobar(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeAprobar()) {
            return redirect()->back()
                ->with('error', 'No se puede aprobar este presupuesto. Debe estar en estado enviado.');
        }

        try {
            $observaciones = $request->input('observaciones');
            $presupuesto->aprobar(auth()->id(), $observaciones);
            return redirect()->back()
                ->with('success', 'Presupuesto aprobado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al aprobar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Rechazar presupuesto (enviado → rechazado)
     */
    public function rechazar(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->isEnviado()) {
            return redirect()->back()
                ->with('error', 'Solo se pueden rechazar presupuestos en estado enviado.');
        }

        $request->validate([
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $presupuesto->rechazar($request->input('motivo'));
            return redirect()->back()
                ->with('success', 'Presupuesto rechazado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al rechazar el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Marcar presupuesto como desierto
     */
    public function marcarDesierto(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeMarcarDesierto()) {
            return redirect()->back()
                ->with('error', 'No se puede marcar como desierto este presupuesto.');
        }

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:1000',
        ]);

        try {
            $presupuesto->marcarComoDesierto($validated['motivo'] ?? null);
            return redirect()->back()
                ->with('success', 'Presupuesto marcado como desierto correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Cerrar presupuesto
     */
    public function cerrar(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeCerrar()) {
            return redirect()->back()
                ->with('error', 'No se puede cerrar este presupuesto.');
        }

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:1000',
        ]);

        try {
            $presupuesto->cerrar($validated['motivo'] ?? null);
            return redirect()->back()
                ->with('success', 'Presupuesto cerrado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Adjudicar presupuesto
     */
    public function adjudicar(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeAdjudicar()) {
            return redirect()->back()
                ->with('error', 'No se puede adjudicar este presupuesto.');
        }

        $validated = $request->validate([
            'numero_orden_compra' => 'required|string|max:50',
            'ejercicio_orden_compra' => 'required|integer|min:2020|max:2030',
        ]);

        try {
            $presupuesto->adjudicar(
                $validated['numero_orden_compra'],
                $validated['ejercicio_orden_compra']
            );
            return redirect()->back()
                ->with('success', 'Presupuesto adjudicado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Adjudicar presupuesto parcialmente
     */
    public function adjudicarParcial(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeAdjudicarParcial()) {
            return redirect()->back()
                ->with('error', 'No se puede adjudicar parcialmente este presupuesto.');
        }

        $validated = $request->validate([
            'numero_orden_compra' => 'required|string|max:50',
            'ejercicio_orden_compra' => 'required|integer|min:2020|max:2030',
        ]);

        try {
            $presupuesto->adjudicarParcial(
                $validated['numero_orden_compra'],
                $validated['ejercicio_orden_compra']
            );
            return redirect()->back()
                ->with('success', 'Presupuesto adjudicado parcialmente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Reabrir presupuesto
     */
    public function reabrir(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeReabrir()) {
            return redirect()->back()
                ->with('error', 'No se puede reabrir este presupuesto.');
        }

        try {
            $renglonesNoAdjudicados = null;
            if ($presupuesto->isAdjudicadoParcial()) {
                $validated = $request->validate([
                    'renglones_no_adjudicados' => 'required|array|min:1',
                    'renglones_no_adjudicados.*' => 'integer|min:1',
                ]);
                $renglonesNoAdjudicados = $validated['renglones_no_adjudicados'];
            }

            $nuevoPresupuesto = $presupuesto->reabrir($renglonesNoAdjudicados);

            return redirect()->route('presupuestos.show', $nuevoPresupuesto)
                ->with('success', 'Presupuesto reabierto como nuevo llamado.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Generar órdenes de compra desde ofertas seleccionadas
     */
    public function generarOrdenesCompra(Request $request, Presupuesto $presupuesto)
    {
        \Log::info('=== INICIO GENERACIÓN DE ÓRDENES ===');
        \Log::info('Datos recibidos:', $request->all());

        $request->validate([
            'selecciones' => 'required|array|min:1',
            'motivos' => 'array'
        ]);

        // Validar que el presupuesto esté aprobado
        if (!$presupuesto->isAprobado()) {
            \Log::error('Presupuesto no está aprobado', ['estado' => $presupuesto->estado]);
            return redirect()->back()
                ->with('error', 'El presupuesto debe estar aprobado para generar órdenes de compra.');
        }

        // Validar que haya selecciones
        if (empty($request->selecciones)) {
            \Log::error('No hay selecciones');
            return redirect()->back()
                ->with('error', 'Debe seleccionar al menos una oferta para generar órdenes de compra.');
        }

        \Log::info('Validaciones pasadas. Total de selecciones:', ['count' => count($request->selecciones)]);

        try {
            DB::beginTransaction();

            // Obtener los detalles seleccionados
            \Log::info('IDs de detalles seleccionados:', array_values($request->selecciones));
            $detallesSeleccionados = DetalleOferta::whereIn('id', array_values($request->selecciones))
                ->with(['oferta.proveedor', 'detallePresupuesto'])
                ->get();
            \Log::info("Detalles encontrados: {$detallesSeleccionados->count()}");

            // Marcar como seleccionados
            foreach ($detallesSeleccionados as $detalle) {
                $renglon = null;
                foreach ($request->selecciones as $renglonKey => $detalleId) {
                    if ($detalleId == $detalle->id) {
                        $renglon = $renglonKey;
                        break;
                    }
                }

                \Log::info("Marcando detalle {$detalle->id} como seleccionado");
                $detalle->update([
                    'seleccionado' => true,
                    'motivo_seleccion' => $request->motivos[$renglon] ?? null,
                    'fecha_seleccion' => now()
                ]);

                // Marcar la oferta como evaluada
                $detalle->oferta->update([
                    'evaluada' => true,
                    'fecha_evaluacion' => now(),
                    'evaluado_por' => auth()->id()
                ]);
            }

            // Agrupar por proveedor para crear órdenes de compra
            $detallesPorProveedor = $detallesSeleccionados->groupBy('oferta.proveedor_id');
            \Log::info("Proveedores encontrados: {$detallesPorProveedor->count()}");
            $ordenesGeneradas = [];

            foreach ($detallesPorProveedor as $proveedorId => $detalles) {
                $primeraOferta = $detalles->first()->oferta;
                $montoTotal = $detalles->sum('subtotal');

                // Crear orden de compra (auto-aprobada)
                $ordenCompra = OrdenCompra::create([
                    'presupuesto_id' => $presupuesto->id,
                    'proveedor_id' => $proveedorId,
                    'oferta_id' => $primeraOferta->id,
                    'fecha_orden' => now(),
                    'fecha_entrega_estimada' => now()->addDays($primeraOferta->plazo_entrega ?? 30),
                    'estado' => OrdenCompra::ESTADO_APROBADA,
                    'fecha_aprobacion' => now(),
                    'usuario_aprobador_id' => auth()->id(),
                    'monto_total' => $montoTotal,
                    'forma_pago' => $primeraOferta->forma_pago,
                    'plazo_entrega' => $primeraOferta->plazo_entrega,
                    'lugar_entrega' => $presupuesto->lugar_entrega,
                    'usuario_generador_id' => auth()->id()
                ]);

                // Crear detalles de la orden de compra
                foreach ($detalles as $detalle) {
                    OrdenCompraDetalle::create([
                        'orden_compra_id' => $ordenCompra->id,
                        'det_presupuesto_id' => $detalle->det_presupuesto_id,
                        'insumo_id' => $detalle->detallePresupuesto->insumo_id,
                        'renglon' => $detalle->detallePresupuesto->renglon,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'subtotal' => $detalle->subtotal,
                        'descripcion' => $detalle->descripcion,
                        'marca' => $detalle->marca,
                        'modelo' => $detalle->modelo,
                        'garantia' => $detalle->garantia,
                        'cantidad_recibida' => 0
                    ]);
                }

                // Marcar oferta como seleccionada
                \Log::info("Marcando oferta {$primeraOferta->id} como seleccionada");
                $primeraOferta->update([
                    'seleccionada' => true,
                    'estado' => 'adjudicada'
                ]);

                $ordenesGeneradas[] = $ordenCompra;
            }

            // Determinar el estado final del presupuesto
            $totalRenglones = $presupuesto->detalles->count();
            $renglonesAdjudicados = count($request->selecciones);

            if ($renglonesAdjudicados >= $totalRenglones) {
                $presupuesto->update(['estado' => Presupuesto::ESTADO_ADJUDICADO]);
            } else {
                $presupuesto->update(['estado' => Presupuesto::ESTADO_ADJUDICADO_PARCIAL]);
            }

            DB::commit();

            $mensaje = count($ordenesGeneradas) === 1
                ? 'Orden de compra generada correctamente.'
                : count($ordenesGeneradas) . ' órdenes de compra generadas correctamente.';

            \Log::info("=== ÓRDENES GENERADAS EXITOSAMENTE ===", [
                'total' => count($ordenesGeneradas),
                'ids' => collect($ordenesGeneradas)->pluck('id')->toArray()
            ]);

            return redirect()->route('ordenes-compra.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error al generar órdenes de compra:', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al generar órdenes de compra: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si todos los renglones han sido adjudicados
     */
    private function verificarRenglonesAdjudicados(Presupuesto $presupuesto): bool
    {
        $totalRenglones = $presupuesto->detalles->count();
        $renglonesAdjudicados = $presupuesto->ofertas()
            ->where('seleccionada', true)
            ->withCount(['detalles' => function($query) {
                $query->where('seleccionado', true);
            }])
            ->get()
            ->sum('detalles_count');

        return $renglonesAdjudicados >= $totalRenglones;
    }


    /**
     * Comparar presupuestos de una nota de pedido
     */
    public function comparar(NotaPedido $notaPedido)
    {
        $presupuestos = Presupuesto::with(['detalles.insumo'])
            ->where('nota_pedido_id', $notaPedido->id)
            ->whereIn('estado', [Presupuesto::ESTADO_ENVIADO, Presupuesto::ESTADO_APROBADO])
            ->get();

        $comparacion = [];

        foreach ($presupuestos as $presupuesto) {
            $comparacion[] = [
                'presupuesto' => $presupuesto,
                'total' => $presupuesto->total_presupuesto,
                'detalles' => $presupuesto->detalles->groupBy('insumo_id'),
            ];
        }

        return Inertia::render('Presupuestos/Comparar', [
            'title' => "Comparación de Presupuestos - Nota {$notaPedido->numero_nota}/{$notaPedido->ejercicio_nota}",
            'notaPedido' => $notaPedido->load(['oficina', 'tipoNota']),
            'comparacion' => $comparacion,
        ]);
    }

    /**
     * Imprimir presupuesto
     */
    public function imprimir(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'notaPedido.oficina',
            'notaPedido.tipoNota',
            'usuario',
            'detalles.insumo.clasificacionEconomica',
        ]);

        return Inertia::render('Presupuestos/Imprimir', [
            'title' => "Presupuesto {$presupuesto->numero_presupuesto}/{$presupuesto->ejercicio}",
            'presupuesto' => $presupuesto,
        ]);
    }

    /**
     * Generar PDF del presupuesto
     */
    public function pdf(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'notasPedido.oficina',
            'notasPedido.usuario',
            'notasPedido.tipoNota',
            'tipoCompra',
            'detalles.insumo.clasificacionEconomica',
            'usuario'
        ]);

        // Determinar el tipo de documento basado en el estado
        $tipoDocumento = 'BORRADOR';
        if ($presupuesto->isAprobado()) {
            $tipoDocumento = 'ORIGINAL';
        } elseif ($presupuesto->isEnviado()) {
            $tipoDocumento = 'REIMPRESO';
        }

        $data = [
            'presupuesto' => $presupuesto,
            'fecha_actual' => now()->format('d/m/Y H:i'),
            'total_items' => $presupuesto->detalles->count(),
            'tipo_documento' => $tipoDocumento
        ];

        $pdf = Pdf::loadView('pdf.presupuesto', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = "formulario-cotizacion-{$presupuesto->numero_presupuesto}-{$presupuesto->ejercicio}.pdf";
        return $pdf->stream($filename);
    }

    /**
     * Marcar presupuesto como desierta
     */
    public function marcarDesierta(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeMarcarDesierta()) {
            return redirect()->back()
                ->with('error', 'No se puede marcar este presupuesto como desierta.');
        }

        $validated = $request->validate([
            'motivo' => 'nullable|string|max:1000',
        ]);

        $presupuesto->marcarComoDesierta($validated['motivo'] ?? null);

        return redirect()->back()
            ->with('success', 'Presupuesto marcado como desierta correctamente.');
    }



    /**
     * Calcular el próximo número de llamado para las notas de pedido seleccionadas
     */
    private function calcularProximoNroLlamado(array $notasPedidoIds): int
    {
        // Buscar el máximo número de llamado existente para estas notas
        $maxNroLlamado = Presupuesto::whereHas('notasPedido', function ($query) use ($notasPedidoIds) {
            $query->whereIn('nota_pedido_id', $notasPedidoIds);
        })
            ->max('nro_llamado') ?? 0;

        return $maxNroLlamado + 1;
    }

    /**
     * Verificar si una nota de pedido tiene insumos disponibles para presupuestar
     * (no completamente asignados a presupuestos activos)
     */
    private function notaTieneInsumosDisponibles(NotaPedido $nota): bool
    {
        foreach ($nota->detalles as $detalle) {
            // Si el detalle no está marcado como presupuestado, está disponible
            if (!$detalle->presupuestado) {
                return true;
            }

            // Si está marcado como presupuestado, verificar si todos los presupuestos
            // que incluyen este insumo están rechazados (entonces está disponible)
            $presupuestosActivos = DetPresupuesto::where('insumo_id', $detalle->insumo_id)
                ->whereHas('presupuesto', function($query) {
                    $query->where('estado', '!=', Presupuesto::ESTADO_RECHAZADO);
                })
                ->exists();

            // Si no hay presupuestos activos con este insumo, está disponible
            if (!$presupuestosActivos) {
                return true;
            }
        }

        return false;
    }

    /**
     * Anular un presupuesto
     */
    public function anular(Request $request, Presupuesto $presupuesto)
    {
        if (!$presupuesto->puedeAnular()) {
            return redirect()->back()
                ->with('error', 'Este presupuesto no puede ser anulado. Solo se pueden anular presupuestos en estado borrador o aprobado que no hayan sido enviados para adjudicar.');
        }

        try {
            $numeroPresupuesto = "{$presupuesto->numero_presupuesto}/{$presupuesto->ejercicio}";

            if ($presupuesto->anular()) {
                return redirect()->back()
                    ->with('success', "Presupuesto {$numeroPresupuesto} anulado correctamente. Los insumos han sido liberados y están disponibles para nuevos presupuestos.");
            } else {
                return redirect()->back()
                    ->with('error', 'No se pudo anular el presupuesto.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al anular el presupuesto: ' . $e->getMessage());
        }
    }

    /**
     * Obtener detalles del presupuesto en formato JSON (para API)
     */
    public function obtenerDetalles(Presupuesto $presupuesto)
    {
        $presupuesto->load([
            'detalles.insumo.clasificacionEconomica',
            'notasPedido.oficina',
            'tipoCompra'
        ]);

        return response()->json([
            'id' => $presupuesto->id,
            'numero_presupuesto' => $presupuesto->numero_presupuesto,
            'ejercicio' => $presupuesto->ejercicio,
            'estado' => $presupuesto->estado,
            'fecha_presupuesto' => $presupuesto->fecha_presupuesto,
            'monto_total' => $presupuesto->monto_total,
            'detalles' => $presupuesto->detalles->map(function ($detalle) {
                return [
                    'id' => $detalle->id,
                    'renglon' => $detalle->renglon,
                    'insumo_id' => $detalle->insumo_id,
                    'insumo' => [
                        'id' => $detalle->insumo->id,
                        'codigo' => $detalle->insumo->codigo,
                        'descripcion' => $detalle->insumo->descripcion,
                    ],
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'especificaciones_tecnicas' => $detalle->especificaciones_tecnicas,
                    'observaciones' => $detalle->observaciones,
                    'subtotal' => $detalle->subtotal,
                ];
            }),
        ]);
    }
}
