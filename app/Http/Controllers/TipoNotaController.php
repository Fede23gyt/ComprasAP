<?php

namespace App\Http\Controllers;

use App\Models\TipoNota;
use Illuminate\Http\Request;

class TipoNotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('TipoNota/Index', [
            'items' => TipoNota::orderBy('descripcion')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return inertia('TipoNota/Index', [
            'items' => TipoNota::orderBy('descripcion')->get(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(TipoNota $tipoNota)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoNota $tipoNota)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoNota $tipoNota)
    {
        //
    }
}
