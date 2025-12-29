<?php

namespace App\Http\Controllers;

use App\Models\NotaPedido;
use App\Models\Presupuesto;
use App\Models\OrdenCompra;
use App\Models\Oferta;
use App\Models\Proveedor;
use App\Models\Oficina;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Mostrar página principal de reportes
     */
    public function index()
    {
        $user = Auth::user();

        // Estadísticas generales
        $estadisticas = [
            'notas_pedido' => [
                'total' => NotaPedido::count(),
                'pendientes' => NotaPedido::where('estado', 'pendiente')->count(),
                'aprobadas' => NotaPedido::where('estado', 'aprobado')->count(),
                'rechazadas' => NotaPedido::where('estado', 'rechazado')->count(),
            ],
            'presupuestos' => [
                'total' => Presupuesto::count(),
                'pendientes' => Presupuesto::where('estado', Presupuesto::ESTADO_PENDIENTE)->count(),
                'aprobados' => Presupuesto::where('estado', Presupuesto::ESTADO_APROBADO)->count(),
                'vencidos' => Presupuesto::where('fecha_vencimiento', '<', now())->count(),
            ],
            'ofertas' => [
                'total' => Oferta::count(),
                'evaluadas' => Oferta::where('evaluada', true)->count(),
                'seleccionadas' => Oferta::where('seleccionada', true)->count(),
            ],
            'ordenes_compra' => [
                'total' => OrdenCompra::count(),
                'pendientes' => OrdenCompra::where('estado', OrdenCompra::ESTADO_PENDIENTE)->count(),
                'aprobadas' => OrdenCompra::where('estado', OrdenCompra::ESTADO_APROBADA)->count(),
                'completadas' => OrdenCompra::where('estado', OrdenCompra::ESTADO_COMPLETADA)->count(),
            ]
        ];

        // Si no es supervisor, filtrar por oficinas del usuario
        if (!$user->isSupervisor()) {
            $oficinaIds = $user->oficinas->pluck('id');

            $estadisticas['notas_pedido'] = [
                'total' => NotaPedido::whereIn('oficina_id', $oficinaIds)->count(),
                'pendientes' => NotaPedido::whereIn('oficina_id', $oficinaIds)->where('estado', 'pendiente')->count(),
                'aprobadas' => NotaPedido::whereIn('oficina_id', $oficinaIds)->where('estado', 'aprobado')->count(),
                'rechazadas' => NotaPedido::whereIn('oficina_id', $oficinaIds)->where('estado', 'rechazado')->count(),
            ];

            $estadisticas['presupuestos'] = [
                'total' => Presupuesto::whereIn('oficina_id', $oficinaIds)->count(),
                'pendientes' => Presupuesto::whereIn('oficina_id', $oficinaIds)->where('estado', Presupuesto::ESTADO_PENDIENTE)->count(),
                'aprobados' => Presupuesto::whereIn('oficina_id', $oficinaIds)->where('estado', Presupuesto::ESTADO_APROBADO)->count(),
                'vencidos' => Presupuesto::whereIn('oficina_id', $oficinaIds)->where('fecha_vencimiento', '<', now())->count(),
            ];
        }

        // Estadísticas por mes (últimos 6 meses)
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = Carbon::now()->subMonths($i);
            $meses[] = [
                'mes' => $fecha->format('Y-m'),
                'nombre' => $fecha->format('M Y'),
                'notas_pedido' => NotaPedido::whereYear('created_at', $fecha->year)
                    ->whereMonth('created_at', $fecha->month)
                    ->when(!$user->isSupervisor(), function($query) use ($user) {
                        return $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
                    })
                    ->count(),
                'presupuestos' => Presupuesto::whereYear('created_at', $fecha->year)
                    ->whereMonth('created_at', $fecha->month)
                    ->when(!$user->isSupervisor(), function($query) use ($user) {
                        return $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
                    })
                    ->count(),
                'ordenes_compra' => OrdenCompra::whereYear('created_at', $fecha->year)
                    ->whereMonth('created_at', $fecha->month)
                    ->when(!$user->isSupervisor(), function($query) use ($user) {
                        return $query->whereHas('presupuesto', function($q) use ($user) {
                            $q->whereIn('oficina_id', $user->oficinas->pluck('id'));
                        });
                    })
                    ->count(),
            ];
        }

        return Inertia::render('Reportes/Index', [
            'estadisticas' => $estadisticas,
            'meses' => $meses,
            'user' => $user
        ]);
    }

    /**
     * Reporte de notas de pedido por oficina
     */
    public function notasPorOficina(Request $request)
    {
        $user = Auth::user();

        $query = NotaPedido::with(['oficina', 'usuario'])
            ->selectRaw('oficina_id, estado, COUNT(*) as total, SUM(monto_estimado) as monto_total')
            ->groupBy('oficina_id', 'estado');

        // Filtro temporal
        if ($request->fecha_inicio) {
            $query->where('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->where('created_at', '<=', $request->fecha_fin);
        }

        // Filtrar por oficinas del usuario si no es supervisor
        if (!$user->isSupervisor()) {
            $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
        }

        $datos = $query->get()
            ->groupBy('oficina_id')
            ->map(function ($notasPorOficina, $oficinaId) {
                $oficina = Oficina::find($oficinaId);
                return [
                    'oficina' => $oficina ? $oficina->nombre : 'Sin oficina',
                    'estados' => $notasPorOficina->keyBy('estado')->toArray()
                ];
            });

        return Inertia::render('Reportes/NotasPorOficina', [
            'datos' => $datos,
            'filtros' => $request->only(['fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Reporte de proveedores con ofertas
     */
    public function proveedoresConOfertas(Request $request)
    {
        $query = Proveedor::with(['ofertas' => function($q) use ($request) {
                if ($request->fecha_inicio) {
                    $q->where('fecha_oferta', '>=', $request->fecha_inicio);
                }
                if ($request->fecha_fin) {
                    $q->where('fecha_oferta', '<=', $request->fecha_fin);
                }
            }])
            ->whereHas('ofertas', function($q) use ($request) {
                if ($request->fecha_inicio) {
                    $q->where('fecha_oferta', '>=', $request->fecha_inicio);
                }
                if ($request->fecha_fin) {
                    $q->where('fecha_oferta', '<=', $request->fecha_fin);
                }
            })
            ->withCount([
                'ofertas as total_ofertas' => function($q) use ($request) {
                    if ($request->fecha_inicio) {
                        $q->where('fecha_oferta', '>=', $request->fecha_inicio);
                    }
                    if ($request->fecha_fin) {
                        $q->where('fecha_oferta', '<=', $request->fecha_fin);
                    }
                },
                'ofertas as ofertas_seleccionadas' => function($q) use ($request) {
                    $q->where('seleccionada', true);
                    if ($request->fecha_inicio) {
                        $q->where('fecha_oferta', '>=', $request->fecha_inicio);
                    }
                    if ($request->fecha_fin) {
                        $q->where('fecha_oferta', '<=', $request->fecha_fin);
                    }
                }
            ])
            ->get()
            ->map(function ($proveedor) {
                $montoTotal = $proveedor->ofertas->sum('monto_oferta');
                $montoSeleccionado = $proveedor->ofertas->where('seleccionada', true)->sum('monto_oferta');
                $tasaExito = $proveedor->total_ofertas > 0 ?
                    ($proveedor->ofertas_seleccionadas / $proveedor->total_ofertas) * 100 : 0;

                return [
                    'proveedor' => $proveedor,
                    'estadisticas' => [
                        'total_ofertas' => $proveedor->total_ofertas,
                        'ofertas_seleccionadas' => $proveedor->ofertas_seleccionadas,
                        'tasa_exito' => round($tasaExito, 2),
                        'monto_total' => $montoTotal,
                        'monto_seleccionado' => $montoSeleccionado
                    ]
                ];
            })
            ->sortByDesc('estadisticas.total_ofertas');

        return Inertia::render('Reportes/ProveedoresConOfertas', [
            'datos' => $query->values(),
            'filtros' => $request->only(['fecha_inicio', 'fecha_fin'])
        ]);
    }

    /**
     * Reporte de órdenes de compra por estado
     */
    public function ordenesCompraPorEstado(Request $request)
    {
        $user = Auth::user();

        $query = OrdenCompra::with(['presupuesto.oficina', 'proveedor'])
            ->when($request->fecha_inicio, function($q) use ($request) {
                return $q->where('fecha_orden', '>=', $request->fecha_inicio);
            })
            ->when($request->fecha_fin, function($q) use ($request) {
                return $q->where('fecha_orden', '<=', $request->fecha_fin);
            })
            ->when($request->oficina_id, function($q) use ($request) {
                return $q->whereHas('presupuesto', function($subQ) use ($request) {
                    $subQ->where('oficina_id', $request->oficina_id);
                });
            })
            ->when(!$user->isSupervisor(), function($q) use ($user) {
                return $q->whereHas('presupuesto', function($subQ) use ($user) {
                    $subQ->whereIn('oficina_id', $user->oficinas->pluck('id'));
                });
            });

        // Agrupar por estado
        $estadisticas = $query->selectRaw('estado, COUNT(*) as total, SUM(monto_total) as monto_total')
            ->groupBy('estado')
            ->get()
            ->keyBy('estado');

        // Obtener detalles por estado
        $ordenesPorEstado = [];
        foreach (OrdenCompra::getEstados() as $estado => $nombre) {
            $ordenesPorEstado[$estado] = [
                'nombre' => $nombre,
                'total' => $estadisticas->get($estado)->total ?? 0,
                'monto_total' => $estadisticas->get($estado)->monto_total ?? 0
            ];
        }

        return Inertia::render('Reportes/OrdenesCompraPorEstado', [
            'datos' => $ordenesPorEstado,
            'filtros' => $request->only(['fecha_inicio', 'fecha_fin', 'oficina_id']),
            'oficinas' => $user->isSupervisor() ? Oficina::all() : $user->oficinas
        ]);
    }

    /**
     * Reporte de insumos más solicitados
     */
    public function insumosMasSolicitados(Request $request)
    {
        $query = DB::table('det_presupuesto')
            ->join('presupuestos', 'det_presupuesto.presupuesto_id', '=', 'presupuestos.id')
            ->join('insumos', 'det_presupuesto.insumo_id', '=', 'insumos.id')
            ->join('oficinas', 'presupuestos.oficina_id', '=', 'oficinas.id')
            ->select(
                'insumos.id',
                'insumos.nombre',
                'insumos.codigo',
                'insumos.unidad_medida',
                DB::raw('COUNT(*) as veces_solicitado'),
                DB::raw('SUM(det_presupuesto.cantidad) as cantidad_total'),
                DB::raw('AVG(det_presupuesto.precio_unitario) as precio_promedio')
            )
            ->groupBy('insumos.id', 'insumos.nombre', 'insumos.codigo', 'insumos.unidad_medida');

        // Filtros
        if ($request->fecha_inicio) {
            $query->where('presupuestos.created_at', '>=', $request->fecha_inicio);
        }

        if ($request->fecha_fin) {
            $query->where('presupuestos.created_at', '<=', $request->fecha_fin);
        }

        if ($request->oficina_id) {
            $query->where('presupuestos.oficina_id', $request->oficina_id);
        }

        // Filtrar por oficinas del usuario si no es supervisor
        $user = Auth::user();
        if (!$user->isSupervisor()) {
            $query->whereIn('presupuestos.oficina_id', $user->oficinas->pluck('id'));
        }

        $datos = $query->orderByDesc('veces_solicitado')
            ->limit(50)
            ->get();

        return Inertia::render('Reportes/InsumosMasSolicitados', [
            'datos' => $datos,
            'filtros' => $request->only(['fecha_inicio', 'fecha_fin', 'oficina_id']),
            'oficinas' => $user->isSupervisor() ? Oficina::all() : $user->oficinas
        ]);
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportarCSV(Request $request)
    {
        $tipo = $request->get('tipo');

        switch ($tipo) {
            case 'notas_por_oficina':
                return $this->exportarNotasPorOficinaCSV($request);
            case 'proveedores_ofertas':
                return $this->exportarProveedoresOfertasCSV($request);
            case 'ordenes_por_estado':
                return $this->exportarOrdenesCompraPorEstadoCSV($request);
            case 'insumos_solicitados':
                return $this->exportarInsumosSolicitadosCSV($request);
            default:
                return response()->json(['error' => 'Tipo de reporte no válido'], 400);
        }
    }

    private function exportarNotasPorOficinaCSV($request)
    {
        // Implementar exportación CSV
        return response()->json(['message' => 'Funcionalidad en desarrollo']);
    }

    private function exportarProveedoresOfertasCSV($request)
    {
        // Implementar exportación CSV
        return response()->json(['message' => 'Funcionalidad en desarrollo']);
    }

    private function exportarOrdenesCompraPorEstadoCSV($request)
    {
        // Implementar exportación CSV
        return response()->json(['message' => 'Funcionalidad en desarrollo']);
    }

    private function exportarInsumosSolicitadosCSV($request)
    {
        // Implementar exportación CSV
        return response()->json(['message' => 'Funcionalidad en desarrollo']);
    }
}
