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

class PresupuestosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $presupuestos;

    public function __construct($presupuestos)
    {
        $this->presupuestos = $presupuestos;
    }

    public function collection()
    {
        return $this->presupuestos;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Número',
            'Oficina',
            'Nota de Pedido',
            'Proveedor',
            'Estado',
            'Importe Total',
            'Fecha Creación',
            'Fecha Vencimiento',
            'Observaciones'
        ];
    }

    public function map($presupuesto): array
    {
        return [
            $presupuesto->id,
            $presupuesto->numero,
            $presupuesto->oficina?->nombre ?? '',
            $presupuesto->notaPedido?->numero ?? '',
            $presupuesto->proveedor?->nombre ?? '',
            $this->getEstadoLabel($presupuesto->estado),
            $presupuesto->importe_total ? '$' . number_format($presupuesto->importe_total, 2, ',', '.') : '$0,00',
            $presupuesto->created_at?->format('d/m/Y H:i:s'),
            $presupuesto->fecha_vencimiento?->format('d/m/Y'),
            $presupuesto->observaciones ?? ''
        ];
    }

    private function getEstadoLabel($estado): string
    {
        $estados = [
            'borrador' => 'Borrador',
            'pendiente' => 'Pendiente',
            'aprobado' => 'Aprobado',
            'rechazado' => 'Rechazado',
            'vencido' => 'Vencido'
        ];

        return $estados[$estado] ?? $estado;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 15,  // Número
            'C' => 30,  // Oficina
            'D' => 15,  // Nota de Pedido
            'E' => 30,  // Proveedor
            'F' => 15,  // Estado
            'G' => 15,  // Importe Total
            'H' => 20,  // Fecha Creación
            'I' => 15,  // Fecha Vencimiento
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
                    'startColor' => ['rgb' => '10B981'],
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
        return 'Presupuestos';
    }
}