<?php


namespace App\Http\Controllers;

use App\Models\Oficina;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OficinaController extends Controller
{
    public function index()
    {
        $oficinas = Oficina::tree()->get()->toTree();
        $padres   = Oficina::where('estado', 'Habilitada')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return inertia('Oficinas/Index', compact('oficinas', 'padres'));
    }

    public function toggle(Oficina $oficina)
    {
        $oficina->estado = $oficina->estado === 'Habilitada' ? 'No habilitada' : 'Habilitada';
        $oficina->save();
        return redirect()->back();
    }

    public function create()
    {
        return inertia('Oficinas/Create', [
            'padres' => Oficina::habilitadas()->orderBy('nombre')->get(['id', 'nombre']),
        ]);


    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_oficina' => 'required|string|max:20|unique:oficinas',
            'abreviacion' => 'required|string|max:20',
            'parent_id' => 'nullable|exists:oficinas,id',
            'estado' => 'required|in:Habilitada,No habilitada',
        ]);

        Oficina::create($request->all());

        return redirect()->route('oficinas.index');
    }

    public function edit(Oficina $oficina)
    {
        return inertia('Oficinas/Edit', [
            'oficina' => $oficina,
            'padres'  => Oficina::habilitadas()->where('id', '!=', $oficina->id)->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(Request $request, Oficina $oficina)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo_oficina' => 'required|string|max:20|unique:oficinas,codigo_oficina,'.$oficina->id,
            'abreviacion' => 'required|string|max:20',
            'parent_id' => 'nullable|exists:oficinas,id',
            'estado' => 'required|in:Habilitada,No habilitada',
        ]);

        $oficina->update($request->all());

        return redirect()->route('oficinas.index');
    }


}
