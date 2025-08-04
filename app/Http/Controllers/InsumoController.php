<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreInsumoRequest; // Asegúrate de que este Request exista y sea correcto
use App\Models\Insumo;
use App\Models\ClasifEconomica;
use Illuminate\Database\Eloquent\Model;
use Inertia\Inertia;
use Inertia\Response;
use function PHPUnit\Framework\isTrue;

class InsumoController extends Controller
{
    /**
     * Muestra la lista de insumos
     */
    public function index(Request $request)
    {
      $query = Insumo::with('clasifEconomica'); // <-- aquí agregás la relación

      if ($request->has('search') && $request->search) {
        $search = strtolower($request->search);
        $query->where(function ($q) use ($search) {
          $q->whereRaw('LOWER(descripcion) LIKE ?', ["%{$search}%"])
            ->orWhereRaw('LOWER(codigo) LIKE ?', ["%{$search}%"]);
        });
      }

      $insumos = $query->orderBy('descripcion')->paginate(40)->withQueryString();

      $clasificaciones = ClasifEconomica::orderBy('codigo')->get(['codigo', 'descripcion']);

      return inertia('Insumos/Index', compact('insumos', '$clasificaciones'));
    }

    /**
     * Guarda un nuevo insumo en la base de datos.
     */
    public function store(StoreInsumoRequest $request)
    {
        Insumo::create($request->validated());
        return redirect()->route('insumos.index')->with('success', 'Insumo creado exitosamente.');
    }

    /**
     * Actualiza un insumo existente.
     */
    public function update(StoreInsumoRequest $request, Insumo $insumo)
    {
      $insumo->update($request->validated());
      return redirect()->route('insumos.index')->with('success', 'Insumo actualizado exitosamente.');
    }

    /**
     * Elimina un insumo.
     */
    public function destroy(Insumo $insumo)
    {
        $insumo->delete();
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado exitosamente.');
    }
    public function toggleRegistrable(Insumo $insumo)
    {
      $insumo->registrable = !$insumo->registrable;
      $insumo->save();
      return redirect()->back();
    }

  public function create()
  {
    return inertia('Oficinas/Create', [
      'padres' => Oficina::habilitadas()->orderBy('nombre')->get(['id', 'nombre']),
    ]);


  }


}
