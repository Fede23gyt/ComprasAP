<?php

namespace App\Http\Controllers;

use App\Models\NotaPedido;
use App\Models\Presupuesto;
use App\Models\Oferta;
use App\Models\Adjudicacion;
use App\Models\OrdenCompra;
use App\Models\Oficina;
use App\Models\User;
use App\Exports\NotasExport;
use App\Exports\PresupuestosExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        // Estadísticas básicas según permisos
        $stats = [
            'total_notas' => $this->getTotalNotas($user),
            'total_presupuestos' => $this->getTotalPresupuestos($user),
            'total_ofertas' => $this->getTotalOfertas($user),
            'total_adjudicaciones' => $this->getTotalAdjudicaciones($user),
            'total_ordenes' => $this->getTotalOrdenes($user),
        ];

        return Inertia::render('Reportes/Index', [
            'stats' => $stats,
            'canAccessFullReports' => in_array($roleName, ['administrador', 'secretario', 'director'])
        ]);
    }

    /**
     * Reporte de mis notas de pedido
     */
    public function misNotas(Request $request)
    {
        $user = auth()->user();
        
        $query = NotaPedido::with(['oficina', 'tipoNota', 'usuario'])
            ->where('usuario_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $notas = $query->paginate(20);

        return Inertia::render('Reportes/MisNotas', [
            'notas' => $notas,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'estado']),
            'estados' => [
                'borrador' => 'Borrador',
                'pendiente' => 'Pendiente',
                'confirmada' => 'Confirmada',
                'rechazada' => 'Rechazada',
                'en_proceso' => 'En Proceso',
                'completada' => 'Completada'
            ]
        ]);
    }

    /**
     * Reporte de todas las notas de pedido (solo admin/secretario/director)
     */
    public function todasNotas(Request $request)
    {
        $user = auth()->user();
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            abort(403, 'No tiene permisos para ver este reporte');
        }

        $query = NotaPedido::with(['oficina', 'tipoNota', 'usuario']);

        // Filtrado por oficinas del usuario si no es administrador
        if ($roleName !== 'administrador') {
            $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
        }

        // Aplicar filtros básicos
        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('oficina_id') && $request->oficina_id) {
            $query->where('oficina_id', $request->oficina_id);
        }

        // Aplicar filtros avanzados
        if ($request->has('importe_min') && $request->importe_min) {
            $query->where('importe_total', '>=', $request->importe_min);
        }

        if ($request->has('importe_max') && $request->importe_max) {
            $query->where('importe_total', '<=', $request->importe_max);
        }

        if ($request->has('tipo_nota_id') && $request->tipo_nota_id) {
            $query->where('tipo_nota_id', $request->tipo_nota_id);
        }

        if ($request->has('busqueda') && $request->busqueda) {
            $query->where('observaciones', 'like', '%' . $request->busqueda . '%');
        }

        // Ordenamiento
        $ordenarPor = $request->get('ordenar_por', 'created_at');
        $ordenDireccion = $request->get('orden_direccion', 'desc');
        $query->orderBy($ordenarPor, $ordenDireccion);

        $perPage = $request->get('per_page', 20);
        $notas = $query->paginate($perPage);

        $oficinas = Oficina::where('activo', true)->get();
        $tiposNota = \App\Models\TipoNota::where('estado', 'activo')->get();

        return Inertia::render('Reportes/TodasNotas', [
            'notas' => $notas,
            'oficinas' => $oficinas,
            'tiposNota' => $tiposNota,
            'filters' => $request->only([
                'fecha_desde', 'fecha_hasta', 'estado', 'oficina_id',
                'importe_min', 'importe_max', 'tipo_nota_id', 'ordenar_por',
                'orden_direccion', 'per_page', 'busqueda'
            ]),
            'estados' => [
                'borrador' => 'Borrador',
                'pendiente' => 'Pendiente',
                'confirmada' => 'Confirmada',
                'rechazada' => 'Rechazada',
                'en_proceso' => 'En Proceso',
                'completada' => 'Completada'
            ]
        ]);
    }

    /**
     * Reporte de notas por oficina
     */
    public function porOficina(Request $request)
    {
        $user = auth()->user();
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            abort(403, 'No tiene permisos para ver este reporte');
        }

        $query = Oficina::withCount(['notasPedido as total_notas'])
            ->with(['notasPedido' => function ($q) use ($request) {
                if ($request->has('fecha_desde') && $request->fecha_desde) {
                    $q->whereDate('created_at', '>=', $request->fecha_desde);
                }
                if ($request->has('fecha_hasta') && $request->fecha_hasta) {
                    $q->whereDate('created_at', '<=', $request->fecha_hasta);
                }
                if ($request->has('estado') && $request->estado) {
                    $q->where('estado', $request->estado);
                }
            }])
            ->where('activo', true)
            ->orderBy('nombre');

        // Si no es administrador, filtrar por oficinas del usuario
        if ($roleName !== 'administrador') {
            $query->whereIn('id', $user->oficinas->pluck('id'));
        }

        $oficinas = $query->get();

        // Calcular estadísticas adicionales
        $oficinas->each(function ($oficina) use ($request) {
            $oficina->notas_por_estado = $this->getNotasPorEstado($oficina->id, $request);
            $oficina->monto_total = $this->getMontoTotalNotas($oficina->id, $request);
        });

        return Inertia::render('Reportes/PorOficina', [
            'oficinas' => $oficinas,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'estado']),
            'estados' => [
                'borrador' => 'Borrador',
                'pendiente' => 'Pendiente',
                'confirmada' => 'Confirmada',
                'rechazada' => 'Rechazada',
                'en_proceso' => 'En Proceso',
                'completada' => 'Completada'
            ]
        ]);
    }

    /**
     * Reporte de estadísticas generales
     */
    public function estadisticas(Request $request)
    {
        $user = auth()->user();
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            abort(403, 'No tiene permisos para ver este reporte');
        }

        $periodo = $request->get('periodo', 'month');
        $fechaDesde = $request->get('fecha_desde', now()->subMonth()->format('Y-m-d'));
        $fechaHasta = $request->get('fecha_hasta', now()->format('Y-m-d'));

        $stats = [
            'notas_por_estado' => $this->getNotasPorEstadoGlobal($fechaDesde, $fechaHasta),
            'notas_por_mes' => $this->getNotasPorMes($fechaDesde, $fechaHasta),
            'presupuestos_por_estado' => $this->getPresupuestosPorEstado($fechaDesde, $fechaHasta),
            'ofertas_por_estado' => $this->getOfertasPorEstado($fechaDesde, $fechaHasta),
            'adjudicaciones_por_estado' => $this->getAdjudicacionesPorEstado($fechaDesde, $fechaHasta),
            'ordenes_por_estado' => $this->getOrdenesPorEstado($fechaDesde, $fechaHasta),
            'top_oficinas' => $this->getTopOficinas($fechaDesde, $fechaHasta),
            'top_insumos' => $this->getTopInsumos($fechaDesde, $fechaHasta),
        ];

        return Inertia::render('Reportes/Estadisticas', [
            'stats' => $stats,
            'filters' => compact('periodo', 'fechaDesde', 'fechaHasta')
        ]);
    }

    /**
     * Exportar reporte a diferentes formatos
     */
    public function exportar(Request $request, $tipo, $formato = 'excel')
    {
        $user = auth()->user();
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        // Validar permisos
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            abort(403, 'No tiene permisos para exportar reportes');
        }

        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        switch ($tipo) {
            case 'notas':
                $data = $this->exportNotas($fechaDesde, $fechaHasta);
                $filename = 'reporte_notas_' . date('Ymd_His');
                break;
            
            case 'presupuestos':
                $data = $this->exportPresupuestos($fechaDesde, $fechaHasta);
                $filename = 'reporte_presupuestos_' . date('Ymd_His');
                break;
            
            case 'ofertas':
                $data = $this->exportOfertas($fechaDesde, $fechaHasta);
                $filename = 'reporte_ofertas_' . date('Ymd_His');
                break;
            
            case 'adjudicaciones':
                $data = $this->exportAdjudicaciones($fechaDesde, $fechaHasta);
                $filename = 'reporte_adjudicaciones_' . date('Ymd_His');
                break;
            
            case 'ordenes':
                $data = $this->exportOrdenes($fechaDesde, $fechaHasta);
                $filename = 'reporte_ordenes_' . date('Ymd_His');
                break;
            
            default:
                abort(404, 'Tipo de reporte no válido');
        }

        if ($formato === 'pdf') {
            return $this->exportToPdf($data, $filename);
        }

        return $this->exportToExcel($data, $filename);
    }

    /**
     * Métodos auxiliares para estadísticas
     */
    private function getTotalNotas($user)
    {
        $query = NotaPedido::query();
        
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            $query->where('usuario_id', $user->id);
        } elseif ($roleName !== 'administrador') {
            $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
        }

        return $query->count();
    }

    private function getTotalPresupuestos($user)
    {
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            return 0;
        }

        $query = Presupuesto::query();
        
        if ($roleName !== 'administrador') {
            $query->whereIn('oficina_id', $user->oficinas->pluck('id'));
        }

        return $query->count();
    }

    private function getTotalOfertas($user)
    {
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            return 0;
        }

        return Oferta::count();
    }

    private function getTotalAdjudicaciones($user)
    {
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            return 0;
        }

        $query = Adjudicacion::query();
        
        if ($roleName !== 'administrador') {
            $query->whereHas('presupuesto', function ($q) use ($user) {
                $q->whereIn('oficina_id', $user->oficinas->pluck('id'));
            });
        }

        return $query->count();
    }

    private function getTotalOrdenes($user)
    {
        $roleName = is_object($user->role) ? $user->role->name : $user->role;
        
        if (!in_array($roleName, ['administrador', 'secretario', 'director'])) {
            return 0;
        }

        $query = OrdenCompra::query();
        
        if ($roleName !== 'administrador') {
            $query->whereHas('adjudicacion.presupuesto', function ($q) use ($user) {
                $q->whereIn('oficina_id', $user->oficinas->pluck('id'));
            });
        }

        return $query->count();
    }

    private function getNotasPorEstado($oficinaId, $request)
    {
        $query = NotaPedido::where('oficina_id', $oficinaId);

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        return $query->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');
    }

    private function getMontoTotalNotas($oficinaId, $request)
    {
        $query = NotaPedido::where('oficina_id', $oficinaId);

        if ($request->has('fecha_desde') && $request->fecha_desde) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta') && $request->fecha_hasta) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        return $query->sum('importe_total');
    }

    private function getNotasPorEstadoGlobal($fechaDesde, $fechaHasta)
    {
        return NotaPedido::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function getNotasPorMes($fechaDesde, $fechaHasta)
    {
        return NotaPedido::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as mes'), DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
    }

    private function getPresupuestosPorEstado($fechaDesde, $fechaHasta)
    {
        return Presupuesto::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function getOfertasPorEstado($fechaDesde, $fechaHasta)
    {
        return Oferta::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function getAdjudicacionesPorEstado($fechaDesde, $fechaHasta)
    {
        return Adjudicacion::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function getOrdenesPorEstado($fechaDesde, $fechaHasta)
    {
        return OrdenCompra::whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function getTopOficinas($fechaDesde, $fechaHasta)
    {
        return NotaPedido::with('oficina')
            ->whereBetween('created_at', [$fechaDesde, $fechaHasta])
            ->select('oficina_id', DB::raw('count(*) as total_notas'), DB::raw('sum(importe_total) as monto_total'))
            ->groupBy('oficina_id')
            ->orderBy('total_notas', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopInsumos($fechaDesde, $fechaHasta)
    {
        return DB::table('det_nota_pedido')
            ->join('nota_pedidos', 'det_nota_pedido.nota_pedido_id', '=', 'nota_pedidos.id')
            ->join('insumos', 'det_nota_pedido.insumo_id', '=', 'insumos.id')
            ->whereBetween('nota_pedidos.created_at', [$fechaDesde, $fechaHasta])
            ->select('insumos.codigo', 'insumos.descripcion', 
                DB::raw('sum(det_nota_pedido.cantidad) as total_cantidad'),
                DB::raw('sum(det_nota_pedido.subtotal) as total_monto'))
            ->groupBy('insumos.id', 'insumos.codigo', 'insumos.descripcion')
            ->orderBy('total_cantidad', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Métodos de exportación
     */
    private function exportNotas($fechaDesde, $fechaHasta)
    {
        return NotaPedido::with(['oficina', 'tipoNota', 'usuario'])
            ->when($fechaDesde, function ($q) use ($fechaDesde) {
                $q->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($q) use ($fechaHasta) {
                $q->whereDate('created_at', '<=', $fechaHasta);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function exportPresupuestos($fechaDesde, $fechaHasta)
    {
        return Presupuesto::with(['notaPedido.oficina'])
            ->when($fechaDesde, function ($q) use ($fechaDesde) {
                $q->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($q) use ($fechaHasta) {
                $q->whereDate('created_at', '<=', $fechaHasta);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function exportOfertas($fechaDesde, $fechaHasta)
    {
        return Oferta::with(['presupuesto', 'proveedor'])
            ->when($fechaDesde, function ($q) use ($fechaDesde) {
                $q->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($q) use ($fechaHasta) {
                $q->whereDate('created_at', '<=', $fechaHasta);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function exportAdjudicaciones($fechaDesde, $fechaHasta)
    {
        return Adjudicacion::with(['presupuesto', 'proveedor', 'usuarioAdjudicador'])
            ->when($fechaDesde, function ($q) use ($fechaDesde) {
                $q->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($q) use ($fechaHasta) {
                $q->whereDate('created_at', '<=', $fechaHasta);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function exportOrdenes($fechaDesde, $fechaHasta)
    {
        return OrdenCompra::with(['adjudicacion', 'proveedor', 'usuarioGenerador'])
            ->when($fechaDesde, function ($q) use ($fechaDesde) {
                $q->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($q) use ($fechaHasta) {
                $q->whereDate('created_at', '<=', $fechaHasta);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    private function exportToExcel($data, $filename)
    {
        $exportClass = $this->getExportClass($data);
        
        if ($exportClass) {
            return Excel::download(new $exportClass($data), $filename . '.xlsx');
        }
        
        // Fallback para datos genéricos
        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromCollection {
            protected $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function collection()
            {
                return collect($this->data);
            }
        }, $filename . '.xlsx');
    }

    private function exportToPdf($data, $filename)
    {
        $view = $this->getPdfView($data);
        
        if ($view) {
            return Pdf::loadView($view, ['data' => $data])
                ->setPaper('a4', 'landscape')
                ->download($filename . '.pdf');
        }
        
        // Fallback para datos genéricos
        return Pdf::loadView('exports.generic-pdf', ['data' => $data, 'title' => $filename])
            ->setPaper('a4', 'landscape')
            ->download($filename . '.pdf');
    }

    private function getExportClass($data)
    {
        if (!empty($data) && is_object($data->first())) {
            $firstItem = $data->first();
            
            switch (get_class($firstItem)) {
                case 'App\\Models\\NotaPedido':
                    return NotasExport::class;
                case 'App\\Models\\Presupuesto':
                    return PresupuestosExport::class;
                // Agregar más casos según sea necesario
            }
        }
        
        return null;
    }

    private function getPdfView($data)
    {
        if (!empty($data) && is_object($data->first())) {
            $firstItem = $data->first();
            
            switch (get_class($firstItem)) {
                case 'App\\Models\\NotaPedido':
                    return 'exports.notas-pdf';
                case 'App\\Models\\Presupuesto':
                    return 'exports.presupuestos-pdf';
                // Agregar más casos según sea necesario
            }
        }
        
        return null;
    }
}