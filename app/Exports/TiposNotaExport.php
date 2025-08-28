<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TiposNotaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $tiposNota;

    public function __construct($tiposNota)
    {
        $this->tiposNota = $tiposNota;
    }

    public function collection()
    {
        return $this->tiposNota;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Descripción',
            'Estado',
            'Fecha Creación',
            'Fecha Actualización'
        ];
    }

    public function map($tipoNota): array
    {
        return [
            $tipoNota->id,
            $tipoNota->descripcion,
            $tipoNota->estado,
            $tipoNota->created_at?->format('d/m/Y H:i:s'),
            $tipoNota->updated_at?->format('d/m/Y H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para los headers
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A90E2']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 40,  // Descripción
            'C' => 15,  // Estado
            'D' => 20,  // Fecha Creación
            'E' => 20,  // Fecha Actualización
        ];
    }
}