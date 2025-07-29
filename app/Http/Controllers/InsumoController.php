<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInsumoRequest; // Asegúrate de que este Request exista y sea correcto
use App\Models\Insumo;
use Inertia\Inertia;
use Inertia\Response;

class InsumoController extends Controller
{
    /**
     * Muestra la lista de insumos en una estructura de árbol.
     */
    public function index(): Response
    {
        // Cargamos los insumos como un arbol para la tabla
        // y también una lista plana para el dropdown de "padres" en el modal.
        $insumos = Insumo::tree()->get()->toTree();
        $padres = Insumo::orderBy('descripcion')->get(['id', 'descripcion']);

        return Inertia::render('Insumos/Index', [
            'insumos' => $insumos,
            'padres'  => $padres,
        ]);
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
}
