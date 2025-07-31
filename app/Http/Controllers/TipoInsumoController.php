<?php

namespace App\Http\Controllers;

use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('TipoInsumo/Index', [
            'items' => TipoInsumo::orderBy('descripcion')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['descripcion' => 'required|unique:TipoInsumo']);
        TipoInsumo::create($request->all());
        return redirect()->route('TipoInsumo.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoInsumo $tipoInsumo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoInsumo $tipoInsumo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoInsumo $tipoInsumo)
    {
        //
    }
}
