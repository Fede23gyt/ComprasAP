<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\Adjudicacion;
use App\Models\OrdenCompraDetalle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = OrdenCompra::with([
                'presupuesto.notasPedido.oficina',
                'proveedor',
                'oferta',
                'usuarioGenerador',
                'usuarioAprobador',
                'detalles.insumo'
            ])
            ->orderBy('created_at', 'desc');

        // Filtrado por oficinas del usuario si no es supervisor
        if (!$user->isSupervisor()) {
            $query->where(function($q) use ($user) {
                // Órdenes con presupuesto de sus oficinas
                $q->whereHas('presupuesto.notasPedido', function ($subq) use ($user) {
                    $subq->whereIn('oficina_id', $user->oficinas->pluck('id'));
                })
                // O si tiene adjudicación (flujo viejo)
                ->orWhereHas('adjudicacion.presupuesto', function ($subq) use ($user) {
                    $subq->whereIn('oficina_id', $user->oficinas->pluck('id'));
                });
            });
        }

        // Filtros
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_orden', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function ($q) use ($search) {
                      $q->where('razon_social', 'like', "%{$search}%");
                  })
                  ->orWhereHas('presupuesto', function ($q) use ($search) {
                      $q->where('numero_presupuesto', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $ordenes = $query->paginate(15)->through(function ($orden) {
            return [
                'id' => $orden->id,
                'numero_orden' => $orden->numero_orden,
                'fecha_orden' => $orden->fecha_orden?->format('d/m/Y'),
                'proveedor' => $orden->proveedor ? [
                    'id' => $orden->proveedor->id,
                    'razon_social' => $orden->proveedor->razon_social,
                    'cuit' => $orden->proveedor->cuit
                ] : null,
                'presupuesto' => $orden->presupuesto ? [
                    'id' => $orden->presupuesto->id,
                    'numero_presupuesto' => $orden->presupuesto->numero_presupuesto,
                    'ejercicio' => $orden->presupuesto->ejercicio
                ] : null,
                'monto_total' => $orden->monto_total,
                'estado' => $orden->estado,
                'fecha_entrega_estimada' => $orden->fecha_entrega_estimada?->format('d/m/Y'),
                'usuario_generador' => $orden->usuarioGenerador?->name,
                'created_at' => $orden->created_at->format('d/m/Y H:i')
            ];
        });

        return Inertia::render('OrdenesCompra/Index', [
            'ordenes' => $ordenes,
            'filters' => $request->only(['search', 'estado']),
            'estados' => [
                OrdenCompra::ESTADO_BORRADOR => 'Borrador',
                OrdenCompra::ESTADO_APROBADA => 'Aprobada',
                OrdenCompra::ESTADO_ENVIADA => 'Enviada',
                OrdenCompra::ESTADO_RECIBIDA => 'Recibida',
                OrdenCompra::ESTADO_COMPLETADA => 'Completada',
                OrdenCompra::ESTADO_ANULADA => 'Anulada'
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $adjudicacionId = $request->adjudicacion_id;
        
        if (!$adjudicacionId) {
            return redirect()->route('adjudicaciones.index')
                ->with('error', 'Debe seleccionar una adjudicación para crear la orden de compra');
        }

        $adjudicacion = Adjudicacion::with(['detalles.insumo', 'proveedor'])
            ->where('estado', 'aprobada')
            ->findOrFail($adjudicacionId);

        return Inertia::render('OrdenesCompra/Create', [
            'adjudicacion' => $adjudicacion,
            'formas_pago' => [
                'contado' => 'Contado',
                '30_dias' => '30 días',
                '60_dias' => '60 días',
                '90_dias' => '90 días',
                'cheque_diferido' => 'Cheque Diferido',
                'transferencia' => 'Transferencia Bancaria'
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'adjudicacion_id' => 'required|exists:adjudicaciones,id',
            'fecha_entrega_estimada' => 'required|date',
            'forma_pago' => 'required|string',
            'plazo_entrega' => 'required|integer|min:1',
            'lugar_entrega' => 'required|string',
            'condiciones_pago' => 'required|string',
            'garantias' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array',
            'detalles.*.adjudicacion_detalle_id' => 'required|exists:adjudicacion_detalles,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descripcion_adicional' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $adjudicacion = Adjudicacion::findOrFail($request->adjudicacion_id);

            // Crear orden de compra
            $orden = OrdenCompra::create([
                'adjudicacion_id' => $request->adjudicacion_id,
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'monto_total' => 0,
                'estado' => OrdenCompra::ESTADO_BORRADOR,
                'forma_pago' => $request->forma_pago,
                'plazo_entrega' => $request->plazo_entrega,
                'lugar_entrega' => $request->lugar_entrega,
                'condiciones_pago' => $request->condiciones_pago,
                'garantias' => $request->garantias,
                'observaciones' => $request->observaciones,
                'usuario_generador_id' => Auth::id()
            ]);

            $montoTotal = 0;

            // Crear detalles de la orden
            foreach ($request->detalles as $detalleData) {
                $adjudicacionDetalle = $adjudicacion->detalles()->find($detalleData['adjudicacion_detalle_id']);
                
                if ($adjudicacionDetalle) {
                    $subtotal = $detalleData['cantidad'] * $detalleData['precio_unitario'];
                    $montoTotal += $subtotal;

                    OrdenCompraDetalle::create([
                        'orden_compra_id' => $orden->id,
                        'adjudicacion_detalle_id' => $detalleData['adjudicacion_detalle_id'],
                        'insumo_id' => $adjudicacionDetalle->insumo_id,
                        'cantidad' => $detalleData['cantidad'],
                        'precio_unitario' => $detalleData['precio_unitario'],
                        'subtotal' => $subtotal,
                        'descripcion_adicional' => $detalleData['descripcion_adicional'] ?? null
                    ]);
                }
            }

            // Actualizar monto total
            $orden->update(['monto_total' => $montoTotal]);

            DB::commit();

            return redirect()->route('ordenes-compra.show', $orden->id)
                ->with('success', 'Orden de compra creada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $orden = OrdenCompra::with([
            'adjudicacion.presupuesto.oficina',
            'adjudicacion.proveedor',
            'usuarioGenerador',
            'usuarioAprobador',
            'detalles.insumo',
            'detalles.adjudicacionDetalle',
            'recepciones'
        ])->findOrFail($id);

        return Inertia::render('OrdenesCompra/Show', [
            'orden' => $orden,
            'estadisticas' => $orden->estadisticas
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $orden = OrdenCompra::with(['detalles.insumo', 'detalles.adjudicacionDetalle'])
            ->where('estado', OrdenCompra::ESTADO_BORRADOR)
            ->findOrFail($id);

        return Inertia::render('OrdenesCompra/Edit', [
            'orden' => $orden,
            'formas_pago' => [
                'contado' => 'Contado',
                '30_dias' => '30 días',
                '60_dias' => '60 días',
                '90_dias' => '90 días',
                'cheque_diferido' => 'Cheque Diferido',
                'transferencia' => 'Transferencia Bancaria'
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_BORRADOR)
            ->findOrFail($id);

        $request->validate([
            'fecha_entrega_estimada' => 'required|date',
            'forma_pago' => 'required|string',
            'plazo_entrega' => 'required|integer|min:1',
            'lugar_entrega' => 'required|string',
            'condiciones_pago' => 'required|string',
            'garantias' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array',
            'detalles.*.id' => 'required|exists:orden_compra_detalles,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descripcion_adicional' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $montoTotal = 0;

            // Actualizar detalles
            foreach ($request->detalles as $detalleData) {
                $detalle = OrdenCompraDetalle::find($detalleData['id']);
                
                if ($detalle && $detalle->orden_compra_id == $orden->id) {
                    $subtotal = $detalleData['cantidad'] * $detalleData['precio_unitario'];
                    $montoTotal += $subtotal;

                    $detalle->update([
                        'cantidad' => $detalleData['cantidad'],
                        'precio_unitario' => $detalleData['precio_unitario'],
                        'subtotal' => $subtotal,
                        'descripcion_adicional' => $detalleData['descripcion_adicional'] ?? null
                    ]);
                }
            }

            // Actualizar orden
            $orden->update([
                'fecha_entrega_estimada' => $request->fecha_entrega_estimada,
                'monto_total' => $montoTotal,
                'forma_pago' => $request->forma_pago,
                'plazo_entrega' => $request->plazo_entrega,
                'lugar_entrega' => $request->lugar_entrega,
                'condiciones_pago' => $request->condiciones_pago,
                'garantias' => $request->garantias,
                'observaciones' => $request->observaciones
            ]);

            DB::commit();

            return redirect()->route('ordenes-compra.show', $orden->id)
                ->with('success', 'Orden de compra actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al actualizar la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_BORRADOR)
            ->findOrFail($id);

        try {
            DB::beginTransaction();
            
            // Eliminar detalles primero
            $orden->detalles()->delete();
            $orden->delete();

            DB::commit();

            return redirect()->route('ordenes-compra.index')
                ->with('success', 'Orden de compra eliminada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar la orden de compra: ' . $e->getMessage());
        }
    }

    /**
     * Enviar orden para aprobación
     */
    public function enviarAprobacion($id)
    {
        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_BORRADOR)
            ->findOrFail($id);

        $orden->cambiarEstado(OrdenCompra::ESTADO_PENDIENTE, 'Enviada para aprobación');

        return redirect()->back()->with('success', 'Orden enviada para aprobación');
    }

    /**
     * Aprobar orden de compra
     */
    public function aprobar($id, Request $request)
    {
        $request->validate([
            'observaciones' => 'nullable|string'
        ]);

        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_PENDIENTE)
            ->findOrFail($id);

        $orden->update([
            'usuario_aprobador_id' => Auth::id(),
            'fecha_aprobacion' => now()
        ]);

        $orden->cambiarEstado(OrdenCompra::ESTADO_APROBADA, $request->observaciones);

        return redirect()->back()->with('success', 'Orden aprobada exitosamente');
    }

    /**
     * Rechazar orden de compra
     */
    public function rechazar($id, Request $request)
    {
        $request->validate([
            'observaciones' => 'required|string'
        ]);

        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_PENDIENTE)
            ->findOrFail($id);

        $orden->cambiarEstado(OrdenCompra::ESTADO_RECHAZADA, $request->observaciones);

        return redirect()->back()->with('success', 'Orden rechazada');
    }

    /**
     * Enviar orden al proveedor
     */
    public function enviarProveedor($id)
    {
        $orden = OrdenCompra::where('estado', OrdenCompra::ESTADO_APROBADA)
            ->findOrFail($id);

        $orden->cambiarEstado(OrdenCompra::ESTADO_ENVIADA, 'Enviada al proveedor');

        return redirect()->back()->with('success', 'Orden enviada al proveedor');
    }

    /**
     * Registrar recepción de items
     */
    public function registrarRecepcion($id, Request $request)
    {
        $request->validate([
            'detalles' => 'required|array',
            'detalles.*.id' => 'required|exists:orden_compra_detalles,id',
            'detalles.*.cantidad_recibida' => 'required|numeric|min:0',
            'numero_remito' => 'nullable|string',
            'observaciones' => 'nullable|string'
        ]);

        $orden = OrdenCompra::whereIn('estado', [
            OrdenCompra::ESTADO_ENVIADA, 
            OrdenCompra::ESTADO_RECIBIDA
        ])->findOrFail($id);

        try {
            DB::beginTransaction();

            // Registrar recepción
            $orden->registrarRecepcion($request->detalles);

            // Actualizar número de remito si se proporciona
            if ($request->numero_remito) {
                $orden->update([
                    'numero_remito' => $request->numero_remito,
                    'fecha_recepcion' => now()
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Recepción registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al registrar recepción: ' . $e->getMessage());
        }
    }

    /**
     * Registrar factura
     */
    public function registrarFactura($id, Request $request)
    {
        $request->validate([
            'numero_factura' => 'required|string',
            'monto_facturado' => 'required|numeric|min:0',
            'fecha_factura' => 'required|date'
        ]);

        $orden = OrdenCompra::whereIn('estado', [
            OrdenCompra::ESTADO_RECIBIDA, 
            OrdenCompra::ESTADO_COMPLETADA
        ])->findOrFail($id);

        try {
            $orden->registrarFactura(
                $request->numero_factura,
                $request->monto_facturado,
                $request->fecha_factura
            );

            return redirect()->back()->with('success', 'Factura registrada exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al registrar factura: ' . $e->getMessage());
        }
    }

    /**
     * Registrar pago
     */
    public function registrarPago($id, Request $request)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'fecha_pago' => 'required|date'
        ]);

        $orden = OrdenCompra::where('estado_pago', '!=', OrdenCompra::PAGO_COMPLETADO)
            ->findOrFail($id);

        try {
            $orden->registrarPago($request->monto, $request->fecha_pago);

            return redirect()->back()->with('success', 'Pago registrado exitosamente');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al registrar pago: ' . $e->getMessage());
        }
    }

    /**
     * Anular orden de compra
     */
    public function anular($id, Request $request)
    {
        $request->validate([
            'motivo' => 'required|string'
        ]);

        $orden = OrdenCompra::whereNotIn('estado', [
            OrdenCompra::ESTADO_COMPLETADA,
            OrdenCompra::ESTADO_ANULADA
        ])->findOrFail($id);

        $orden->cambiarEstado(OrdenCompra::ESTADO_ANULADA, $request->motivo);

        return redirect()->back()->with('success', 'Orden anulada exitosamente');
    }

    /**
     * Generar PDF de la orden de compra
     */
    public function imprimirPdf($id)
    {
        $orden = OrdenCompra::with([
            'presupuesto.notasPedido.oficina',
            'presupuesto.tipoCompra',
            'proveedor',
            'oferta',
            'usuarioGenerador',
            'usuarioAprobador',
            'detalles.insumo.clasificacionEconomica'
        ])->findOrFail($id);

        $data = [
            'orden' => $orden,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
            'usuario_generador' => auth()->user()->name ?? 'Sistema',
            'total_en_letras' => $this->convertirNumeroALetras($orden->monto_total)
        ];

        $pdf = Pdf::loadView('pdf.orden-compra', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream("OC_{$orden->numero_orden}.pdf");
    }

    /**
     * Convertir número a letras en español
     */
    private function convertirNumeroALetras($numero)
    {
        $entero = floor($numero);
        $decimales = round(($numero - $entero) * 100);

        $letras = $this->numeroALetras($entero);

        return strtoupper($letras) . " CON " . str_pad($decimales, 2, '0', STR_PAD_LEFT) . "/100.-";
    }

    private function numeroALetras($numero)
    {
        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero == 0) return 'CERO';
        if ($numero == 100) return 'CIEN';
        if ($numero == 1000000) return 'UN MILLON';

        $resultado = '';

        // Millones
        if ($numero >= 1000000) {
            $millones = floor($numero / 1000000);
            if ($millones == 1) {
                $resultado .= 'UN MILLON ';
            } else {
                $resultado .= $this->numeroALetras($millones) . ' MILLONES ';
            }
            $numero %= 1000000;
        }

        // Miles
        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            if ($miles == 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= $this->numeroALetras($miles) . ' MIL ';
            }
            $numero %= 1000;
        }

        // Centenas
        if ($numero >= 100) {
            $resultado .= $centenas[floor($numero / 100)] . ' ';
            $numero %= 100;
        }

        // Decenas y unidades
        if ($numero >= 10 && $numero < 20) {
            $resultado .= $especiales[$numero - 10];
        } else {
            if ($numero >= 20) {
                $resultado .= $decenas[floor($numero / 10)];
                if ($numero % 10 > 0) {
                    $resultado .= ' Y ' . $unidades[$numero % 10];
                }
            } else if ($numero > 0) {
                $resultado .= $unidades[$numero];
            }
        }

        return trim($resultado);
    }
}