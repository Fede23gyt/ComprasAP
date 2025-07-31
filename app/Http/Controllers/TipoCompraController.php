<?php

namespace App\Http\Controllers;

use App\Models\TipoCompra;
use Illuminate\Http\Request;

class TipoCompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('TipoCompra/Index', [
            'items' => TipoCompra::orderBy('descripcion')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['descripcion' => 'required|unique:tipo_compra']);
        TipoCompra::create($request->all());
        return redirect()->route('tipo_compra.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoCompra $tipoCompra)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoCompra $tipoCompra)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoCompra $tipoCompra)
    {
        //
    }
}
