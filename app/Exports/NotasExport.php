<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NotasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $notas;

    public function __construct($notas)
    {
        $this->notas = $notas;
    }

    public function collection()
    {
        return $this->notas;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número',
            'Oficina',
            'Tipo de Nota',
            'Usuario',
            'Estado',
            'Importe Total',
            'Fecha Creación',
            'Fecha Actualización',
            'Observaciones'
        ];
    }

    public function map($nota): array
    {
        return [
            $nota->id,
            $nota->numero,
            $nota->oficina?->nombre ?? '',
            $nota->tipoNota?->descripcion ?? '',
            $nota->usuario?->name ?? '',
            $this->getEstadoLabel($nota->estado),
            $nota->importe_total ? '$' . number_format($nota->importe_total, 2, ',', '.') : '$0,00',
            $nota->created_at?->format('d/m/Y H:i:s'),
            $nota->updated_at?->format('d/m/Y H:i:s'),
            $nota->observaciones ?? ''
        ];
    }

    private function getEstadoLabel($estado): string
    {
        $estados = [
            'borrador' => 'Borrador',
            'pendiente' => 'Pendiente',
            'confirmada' => 'Confirmada',
            'rechazada' => 'Rechazada',
            'en_proceso' => 'En Proceso',
            'completada' => 'Completada'
        ];

        return $estados[$estado] ?? $estado;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 15,  // Número
            'C' => 30,  // Oficina
            'D' => 25,  // Tipo de Nota
            'E' => 25,  // Usuario
            'F' => 15,  // Estado
            'G' => 15,  // Importe Total
            'H' => 20,  // Fecha Creación
            'I' => 20,  // Fecha Actualización
            'J' => 40,  // Observaciones
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            "A2:J{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
            "A2:A{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "G2:G{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            "H2:J{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Notas de Pedido';
    }
}