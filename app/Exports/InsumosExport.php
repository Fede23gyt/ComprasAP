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

class InsumosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $insumos;

    public function __construct($insumos)
    {
        $this->insumos = $insumos;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->insumos;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Código',
            'Descripción',
            'Clasificación',
            'Clasificación Económica',
            'Unidad Solicitud',
            'Precio',
            'Registrable',
            'Precio Testigo',
            'Inventariable',
            'Rinde Tribunal',
            'Conversión',
            'Fecha Última',
            'Creado',
            'Actualizado'
        ];
    }

    /**
     * @param mixed $insumo
     * @return array
     */
    public function map($insumo): array
    {
        return [
            $insumo->id,
            $insumo->codigo,
            $insumo->descripcion,
            $insumo->clasificacion,
            $insumo->clasificacionEconomica?->descripcion ?? '',
            $insumo->unidad_solicitud ?? '',
            $insumo->precio_insumo ? '$' . number_format($insumo->precio_insumo, 2, ',', '.') : '',
            $insumo->registrable ? 'Sí' : 'No',
            $insumo->precio_testigo ? 'Sí' : 'No',
            $insumo->inventariable ? 'Sí' : 'No',
            $insumo->rend_tribunal ? 'Sí' : 'No',
            $insumo->conversion ?? '',
            $insumo->fecha_ultima?->format('d/m/Y') ?? '',
            $insumo->created_at?->format('d/m/Y H:i:s'),
            $insumo->updated_at?->format('d/m/Y H:i:s')
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 15,  // Código
            'C' => 40,  // Descripción
            'D' => 15,  // Clasificación
            'E' => 30,  // Clasificación Económica
            'F' => 15,  // Unidad Solicitud
            'G' => 12,  // Precio
            'H' => 12,  // Registrable
            'I' => 12,  // Precio Testigo
            'J' => 12,  // Inventariable
            'K' => 15,  // Rinde Tribunal
            'L' => 10,  // Conversión
            'M' => 12,  // Fecha Última
            'N' => 18,  // Creado
            'O' => 18,  // Actualizado
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Obtener la última fila con datos
        $lastRow = $sheet->getHighestRow();

        return [
            // Estilo para los encabezados (fila 1)
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
            // Estilo para todas las celdas de datos
            "A2:O{$lastRow}" => [
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
            // Centrar columnas específicas
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
            "H2:K{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "L2:L{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            "M2:O{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Insumos';
    }
}