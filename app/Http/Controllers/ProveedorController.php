<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $query = Proveedor::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre_proveedor', 'like', "%{$search}%")
                  ->orWhere('razon_social', 'like', "%{$search}%")
                  ->orWhere('cuit', 'like', "%{$search}%");
            });
        }

        if ($request->has('tipo') && $request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('estado') && $request->estado !== '') {
            $query->where('estado', (bool) $request->estado);
        }

        $proveedores = $query->orderBy('nombre_proveedor')->paginate(10)->withQueryString();

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
            'filters' => $request->only(['search', 'tipo', 'estado']),
            'tipos' => Proveedor::getTipos(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Proveedores/Create', [
            'tipos' => Proveedor::getTipos(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cuit' => 'required|string|size:13|unique:proveedores,cuit',
            'nombre_proveedor' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(Proveedor::getTipos())],
            'domicilio' => 'required|string',
            'telefono_fijo' => 'nullable|string|max:20',
            'telefono_celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'estado' => 'boolean',
        ], [
            'cuit.required' => 'El CUIT es obligatorio.',
            'cuit.size' => 'El CUIT debe tener el formato 99-99999999-9.',
            'cuit.unique' => 'Ya existe un proveedor con este CUIT.',
            'nombre_proveedor.required' => 'El nombre del proveedor es obligatorio.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'tipo.required' => 'El tipo de IVA es obligatorio.',
            'domicilio.required' => 'El domicilio es obligatorio.',
        ]);

        $cuitNumeros = preg_replace('/[^0-9]/', '', $validated['cuit']);
        if (!Proveedor::validarCuit($cuitNumeros)) {
            return back()->withErrors(['cuit' => 'El CUIT ingresado no es válido.']);
        }

        Proveedor::create($validated);

        return redirect()->route('nomencladores.proveedores.index')->with('success', 'Proveedor creado exitosamente.');
    }

    public function show(Proveedor $proveedor)
    {
        return Inertia::render('Proveedores/Show', [
            'proveedor' => $proveedor,
        ]);
    }

    public function edit(Proveedor $proveedor)
    {
        return Inertia::render('Proveedores/Edit', [
            'proveedor' => $proveedor,
            'tipos' => Proveedor::getTipos(),
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $validated = $request->validate([
            'cuit' => 'required|string|size:13|unique:proveedores,cuit,' . $proveedor->id,
            'nombre_proveedor' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'tipo' => ['required', Rule::in(Proveedor::getTipos())],
            'domicilio' => 'required|string',
            'telefono_fijo' => 'nullable|string|max:20',
            'telefono_celular' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'estado' => 'boolean',
        ], [
            'cuit.required' => 'El CUIT es obligatorio.',
            'cuit.size' => 'El CUIT debe tener el formato 99-99999999-9.',
            'cuit.unique' => 'Ya existe un proveedor con este CUIT.',
            'nombre_proveedor.required' => 'El nombre del proveedor es obligatorio.',
            'razon_social.required' => 'La razón social es obligatoria.',
            'tipo.required' => 'El tipo de IVA es obligatorio.',
            'domicilio.required' => 'El domicilio es obligatorio.',
        ]);

        $cuitNumeros = preg_replace('/[^0-9]/', '', $validated['cuit']);
        if (!Proveedor::validarCuit($cuitNumeros)) {
            return back()->withErrors(['cuit' => 'El CUIT ingresado no es válido.']);
        }

        $proveedor->update($validated);

        return redirect()->route('nomencladores.proveedores.index')->with('success', 'Proveedor actualizado exitosamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
    }
}
