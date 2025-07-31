<?php

namespace App\Http\Controllers;

use App\Models\Memo;
use Illuminate\Http\Request;

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
        $request->validate(['descripcion' => 'required|unique:memos']);
        Memo::create($request->all());
        return redirect()->route('memo.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Memo $memo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Memo $memo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memo $memo)
    {
        //
    }
}
