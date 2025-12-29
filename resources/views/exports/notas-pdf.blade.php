<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Notas de Pedido</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #3B82F6; }
        .header p { margin: 5px 0; color: #6B7280; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background-color: #3B82F6; color: white; padding: 8px; text-align: left; }
        .table td { padding: 8px; border: 1px solid #E5E7EB; }
        .table tr:nth-child(even) { background-color: #F9FAFB; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #6B7280; font-size: 10px; }
        .summary { margin-top: 20px; padding: 15px; background-color: #F3F4F6; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Notas de Pedido</h1>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ count($data) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Oficina</th>
                <th>Tipo de Nota</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th class="text-right">Importe</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $nota)
            <tr>
                <td>{{ $nota->numero }}</td>
                <td>{{ $nota->oficina->nombre ?? '' }}</td>
                <td>{{ $nota->tipoNota->descripcion ?? '' }}</td>
                <td>{{ $nota->usuario->name ?? '' }}</td>
                <td>
                    @php
                        $estados = [
                            'borrador' => 'Borrador',
                            'pendiente' => 'Pendiente',
                            'confirmada' => 'Confirmada',
                            'rechazada' => 'Rechazada',
                            'en_proceso' => 'En Proceso',
                            'completada' => 'Completada'
                        ];
                    @endphp
                    {{ $estados[$nota->estado] ?? $nota->estado }}
                </td>
                <td class="text-right">${{ number_format($nota->importe_total, 2, ',', '.') }}</td>
                <td class="text-center">{{ $nota->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($data) > 0)
    <div class="summary">
        <strong>Resumen:</strong><br>
        Total de notas: {{ count($data) }}<br>
        Importe total: ${{ number_format($data->sum('importe_total'), 2, ',', '.') }}<br>
        Promedio por nota: ${{ number_format($data->avg('importe_total'), 2, ',', '.') }}
    </div>
    @endif

    <div class="footer">
        Sistema de Compras AP - {{ config('app.name') }}
    </div>
</body>
</html>