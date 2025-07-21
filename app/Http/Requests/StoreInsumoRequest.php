<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInsumoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Opcional: Cambiar por lógica de autorización real
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'parent_id'        => ['nullable', 'exists:insumos,id'],
            'descripcion'      => ['required', 'string', 'max:255'],
            'codigo'           => ['required', 'string', 'max:20', Rule::unique('insumos')->ignore($this->insumo)],
            'clasificacion'    => ['nullable', 'string', 'max:255'],
            'presentacion'     => ['nullable', 'string', 'max:50'],
            'unidad_solicitud' => ['nullable', 'string', 'max:50'],
            'precio_insumo'    => ['nullable', 'numeric', 'min:0'],
            'precio_testigo'   => ['required', 'boolean'],
            'inventariable'    => ['required', 'boolean'],
            'registrable'      => ['required', 'boolean'],
            'rend_tribunal'    => ['required', 'boolean'],
            'conversion'       => ['required', 'numeric', 'min:0'],
            'fecha_ultima'     => ['nullable', 'date'],
            'tipo'             => ['nullable', 'string', 'max:255'],
            'codigo_grupo'     => ['nullable', 'string', 'max:20'],
        ];
    }
}