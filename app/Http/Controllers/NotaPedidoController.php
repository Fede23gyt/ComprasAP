<?php

namespace App\Http\Controllers;

use App\Models\NotaPedido;
use App\Models\DetNotaPedido;
use App\Models\Insumo;
use App\Models\Oficina;
use App\Models\TipoNota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotaPedidoController extends Controller
{
    /**
     * Mostrar listado de notas de pedido
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $numero = $request->get('numero', '');
        $oficinaId = $request->get('oficina_id', '');
        $expediente = $request->get('expediente', '');
        $estado = $request->get('estado', '');
        
        $user = Auth::user();
        
        // Query base - solo notas de oficinas donde el usuario tiene acceso
        $query = NotaPedido::with(['oficina', 'tipoNota', 'usuario'])
            ->whereHas('oficina', function ($q) use ($user) {
                // Si es admin/supervisor puede ver todas
                if ($user->role->name === 'administrador' || $user->role->name === 'supervisor') {
                    return $q;
                }
                // Si no, solo las de sus oficinas
                return $q->whereIn('id', $user->oficinas->pluck('id'));
            });

        // Aplicar filtros
        if ($numero) {
            $query->where('numero_nota', 'LIKE', "%{$numero}%");
        }
        
        if ($oficinaId) {
            $query->where('oficina_id', $oficinaId);
        }
        
        if ($expediente) {
            $query->where('expediente', 'LIKE', "%{$expediente}%");
        }
        
        if ($estado !== '') {
            $query->where('estado', $estado);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('descripcion', 'LIKE', "%{$search}%")
                  ->orWhere('expediente', 'LIKE', "%{$search}%")
                  ->orWhereHas('oficina', function ($subq) use ($search) {
                      $subq->where('nombre', 'LIKE', "%{$search}%");
                  });
            });
        }

        $notas = $query->orderBy('ejercicio_nota', 'desc')
                      ->orderBy('numero_nota', 'desc')
                      ->paginate(15)
                      ->withQueryString();

        // Obtener oficinas para el filtro
        $oficinas = $user->role->name === 'administrador' || $user->role->name === 'supervisor'
            ? Oficina::habilitadas()->orderBy('nombre')->get()
            : $user->oficinas;

        return Inertia::render('NotasPedido/Index', [
            'title' => 'Notas de Pedido',
            'notas' => $notas,
            'oficinas' => $oficinas,
            'filters' => [
                'search' => $search,
                'numero' => $numero,
                'oficina_id' => $oficinaId,
                'expediente' => $expediente,
                'estado' => $estado
            ]
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $user = Auth::user();
        
        // Obtener tipos de nota habilitados
        $tiposNota = TipoNota::where('estado', 'Habilitado')->orderBy('descripcion')->get();
        
        // Obtener oficinas disponibles para el usuario
        $oficinas = $user->role->name === 'administrador' || $user->role->name === 'supervisor'
            ? Oficina::habilitadas()->orderBy('nombre')->get()
            : $user->oficinas;

        return Inertia::render('NotasPedido/Create', [
            'title' => 'Nueva Nota de Pedido',
            'tiposNota' => $tiposNota,
            'oficinas' => $oficinas,
            'ejercicioActual' => date('Y')
        ]);
    }

    /**
     * Guardar nueva nota de pedido
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_nota_id' => 'required|exists:tipo_nota,id',
            'oficina_id' => 'required|exists:oficinas,id',
            'descripcion' => 'required|string|max:1000',
            'detalles' => 'required|array|min:1',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio' => 'required|numeric|min:0',
            'detalles.*.comentario' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated) {
            $ejercicio = date('Y');
            $numeroNota = NotaPedido::generarProximoNumero($ejercicio);

            // Crear la nota de pedido
            $nota = NotaPedido::create([
                'numero_nota' => $numeroNota,
                'ejercicio_nota' => $ejercicio,
                'fecha_nota' => now()->toDateString(),
                'oficina_id' => $validated['oficina_id'],
                'tipo_nota_id' => $validated['tipo_nota_id'],
                'descripcion' => $validated['descripcion'],
                'user_id' => Auth::id(),
                'estado' => NotaPedido::ESTADO_ABIERTA
            ]);

            // Crear los detalles
            foreach ($validated['detalles'] as $index => $detalle) {
                DetNotaPedido::create([
                    'nota_pedido_id' => $nota->id,
                    'renglon' => $index + 1,
                    'insumo_id' => $detalle['insumo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'comentario' => $detalle['comentario'] ?? null
                ]);
            }

            // El total se calcula automáticamente en el modelo
        });

        return redirect()->route('notas-pedido.index')
            ->with('success', 'Nota de pedido creada correctamente.');
    }

    /**
     * Mostrar nota específica
     */
    public function show(NotaPedido $notaPedido)
    {
        $notaPedido->load([
            'oficina',
            'tipoNota',
            'usuario',
            'confirmadoPor',
            'detalles.insumo.clasificacionEconomica'
        ]);

        return Inertia::render('NotasPedido/Show', [
            'title' => "Nota de Pedido {$notaPedido->numero_nota}/{$notaPedido->ejercicio_nota}",
            'nota' => $notaPedido
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(NotaPedido $notaPedido)
    {
        // Solo se puede editar si está abierta
        if (!$notaPedido->puedeEditar()) {
            return redirect()->route('notas-pedido.index')
                ->with('error', 'No se puede editar una nota que no está abierta.');
        }

        $user = Auth::user();
        
        $notaPedido->load([
            'oficina',
            'tipoNota',
            'detalles.insumo.clasificacionEconomica'
        ]);

        // Obtener tipos de nota habilitados
        $tiposNota = TipoNota::where('estado', 'Habilitado')->orderBy('descripcion')->get();
        
        // Obtener oficinas disponibles para el usuario
        $oficinas = $user->role->name === 'administrador' || $user->role->name === 'supervisor'
            ? Oficina::habilitadas()->orderBy('nombre')->get()
            : $user->oficinas;

        return Inertia::render('NotasPedido/Edit', [
            'title' => "Editar Nota {$notaPedido->numero_nota}/{$notaPedido->ejercicio_nota}",
            'nota' => $notaPedido,
            'tiposNota' => $tiposNota,
            'oficinas' => $oficinas
        ]);
    }

    /**
     * Actualizar nota de pedido
     */
    public function update(Request $request, NotaPedido $notaPedido)
    {
        // Solo se puede editar si está abierta
        if (!$notaPedido->puedeEditar()) {
            return redirect()->route('notas-pedido.index')
                ->with('error', 'No se puede editar una nota que no está abierta.');
        }

        $validated = $request->validate([
            'tipo_nota_id' => 'required|exists:tipo_nota,id',
            'oficina_id' => 'required|exists:oficinas,id',
            'descripcion' => 'required|string|max:1000',
            'expediente' => 'nullable|string|max:100',
            'detalles' => 'required|array|min:1',
            'detalles.*.id' => 'nullable|exists:det_notapedido,id',
            'detalles.*.insumo_id' => 'required|exists:insumos,id',
            'detalles.*.cantidad' => 'required|numeric|min:0.0001',
            'detalles.*.precio' => 'required|numeric|min:0',
            'detalles.*.comentario' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($validated, $notaPedido) {
            // Actualizar la nota
            $notaPedido->update([
                'tipo_nota_id' => $validated['tipo_nota_id'],
                'oficina_id' => $validated['oficina_id'],
                'descripcion' => $validated['descripcion'],
                'expediente' => $validated['expediente'] ?? null
            ]);

            // Obtener IDs de detalles actuales
            $detallesActuales = $notaPedido->detalles()->pluck('id')->toArray();
            $detallesEnviados = array_filter(array_column($validated['detalles'], 'id'));

            // Eliminar detalles que ya no están
            $detallesAEliminar = array_diff($detallesActuales, $detallesEnviados);
            if (!empty($detallesAEliminar)) {
                DetNotaPedido::whereIn('id', $detallesAEliminar)->delete();
            }

            // Actualizar o crear detalles
            foreach ($validated['detalles'] as $index => $detalle) {
                $datos = [
                    'nota_pedido_id' => $notaPedido->id,
                    'renglon' => $index + 1,
                    'insumo_id' => $detalle['insumo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'comentario' => $detalle['comentario'] ?? null
                ];

                if (isset($detalle['id']) && $detalle['id']) {
                    DetNotaPedido::where('id', $detalle['id'])->update($datos);
                } else {
                    DetNotaPedido::create($datos);
                }
            }
        });

        return redirect()->route('notas-pedido.index')
            ->with('success', 'Nota de pedido actualizada correctamente.');
    }

    /**
     * Confirmar nota de pedido
     */
    public function confirmar(NotaPedido $notaPedido)
    {
        if (!$notaPedido->puedeConfirmar()) {
            return redirect()->back()
                ->with('error', 'No se puede confirmar esta nota.');
        }

        $notaPedido->confirmar();

        return redirect()->back()
            ->with('success', 'Nota de pedido confirmada correctamente.');
    }

    /**
     * Rechazar nota de pedido
     */
    public function rechazar(NotaPedido $notaPedido)
    {
        if (!$notaPedido->puedeConfirmar()) {
            return redirect()->back()
                ->with('error', 'No se puede rechazar esta nota.');
        }

        $notaPedido->rechazar();

        return redirect()->back()
            ->with('success', 'Nota de pedido rechazada.');
    }

    /**
     * Reabrir nota confirmada (solo administradores/supervisores)
     */
    public function reabrir(NotaPedido $notaPedido)
    {
        $user = Auth::user();
        
        if (!in_array($user->role->name, ['administrador', 'supervisor'])) {
            return redirect()->back()
                ->with('error', 'No tiene permisos para reabrir notas.');
        }

        if (!$notaPedido->isConfirmada()) {
            return redirect()->back()
                ->with('error', 'Solo se pueden reabrir notas confirmadas.');
        }

        $notaPedido->reabrir();

        return redirect()->back()
            ->with('success', 'Nota de pedido reabierta correctamente.');
    }

    /**
     * API para obtener insumos (para el buscador)
     */
    public function buscarInsumos(Request $request)
    {
        $search = $request->get('search', '');
        
        $insumos = Insumo::with('clasificacionEconomica')
            ->where('registrable', true)
            ->where(function ($query) use ($search) {
                $query->where('codigo', 'LIKE', "%{$search}%")
                      ->orWhere('descripcion', 'LIKE', "%{$search}%");
            })
            ->orderBy('descripcion')
            ->limit(20)
            ->get();

        return response()->json($insumos);
    }
}
