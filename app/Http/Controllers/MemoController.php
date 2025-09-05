<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Memo/Index', [
            'items' => Memo::orderBy('descripcion')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|unique:memos',
            'estado' => 'nullable|in:Habilitado,No habilitado'
        ]);
        
        Memo::create([
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 'Habilitado'
        ]);
        
        return redirect()->route('memo.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Memo $memo)
    {
        return inertia('Memo/Show', [
            'memo' => $memo
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Memo $memo)
    {
        $request->validate([
            'descripcion' => ['required', Rule::unique('memos')->ignore($memo->id)],
            'estado' => 'nullable|in:Habilitado,No habilitado'
        ]);
        
        $memo->update([
            'descripcion' => $request->descripcion,
            'estado' => $request->estado ?? 'Habilitado'
        ]);
        
        return redirect()->route('memo.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memo $memo)
    {
        $memo->delete();
        return redirect()->route('memo.index');
    }
}
