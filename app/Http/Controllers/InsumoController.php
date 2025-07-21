<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInsumoRequest;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Carga los insumos de nivel superior con sus hijos y pagina los resultados
        $insumos = Insumo::whereNull('parent_id')
            ->with('children') // Eager load para evitar problemas N+1
            ->paginate(10);

        return Inertia::render('Insumos/Index', [
            'insumos' => $insumos,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Pasamos todos los insumos para poder seleccionarlos como 'padre' en un dropdown
        return Inertia::render('Insumos/Create', [
            'insumosList' => Insumo::all(['id', 'descripcion']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInsumoRequest $request)
    {
        Insumo::create($request->validated());
        return redirect()->route('insumos.index')->with('success', 'Insumo creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Insumo $insumo): Response
    {
        return Inertia::render('Insumos/Edit', [
            'insumo' => $insumo,
            'insumosList' => Insumo::where('id', '!=', $insumo->id)->get(['id', 'descripcion']), // Evitar que un insumo sea su propio padre
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInsumoRequest $request, Insumo $insumo)
    {
        $insumo->update($request->validated());
        return redirect()->route('insumos.index')->with('success', 'Insumo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Insumo $insumo)
    {
        $insumo->delete();
        return redirect()->route('insumos.index')->with('success', 'Insumo eliminado exitosamente.');
    }
}