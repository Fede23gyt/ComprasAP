<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Presupuestos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #10B981; }
        .header p { margin: 5px 0; color: #6B7280; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background-color: #10B981; color: white; padding: 8px; text-align: left; }
        .table td { padding: 8px; border: 1px solid #E5E7EB; }
        .table tr:nth-child(even) { background-color: #F9FAFB; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #6B7280; font-size: 10px; }
        .summary { margin-top: 20px; padding: 15px; background-color: #F3F4F6; border-radius: 5px; }
        .status-badge { 
            padding: 2px 8px; 
            border-radius: 12px; 
            font-size: 10px; 
            font-weight: bold; 
        }
        .status-pendiente { background-color: #F59E0B; color: white; }
        .status-aprobado { background-color: #10B981; color: white; }
        .status-rechazado { background-color: #EF4444; color: white; }
        .status-vencido { background-color: #6B7280; color: white; }
        .status-borrador { background-color: #9CA3AF; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Presupuestos</h1>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ count($data) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Número</th>
                <th>Oficina</th>
                <th>Nota de Pedido</th>
                <th>Proveedor</th>
                <th>Estado</th>
                <th class="text-right">Importe</th>
                <th>Fecha Vencimiento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $presupuesto)
            <tr>
                <td>{{ $presupuesto->numero }}</td>
                <td>{{ $presupuesto->oficina->nombre ?? '' }}</td>
                <td>{{ $presupuesto->notaPedido->numero ?? '' }}</td>
                <td>{{ $presupuesto->proveedor->nombre ?? '' }}</td>
                <td>
                    @php
                        $estadoClasses = [
                            'borrador' => 'status-borrador',
                            'pendiente' => 'status-pendiente',
                            'aprobado' => 'status-aprobado',
                            'rechazado' => 'status-rechazado',
                            'vencido' => 'status-vencido'
                        ];
                        
                        $estadosLabels = [
                            'borrador' => 'Borrador',
                            'pendiente' => 'Pendiente',
                            'aprobado' => 'Aprobado',
                            'rechazado' => 'Rechazado',
                            'vencido' => 'Vencido'
                        ];
                    @endphp
                    <span class="status-badge {{ $estadoClasses[$presupuesto->estado] ?? 'status-borrador' }}">
                        {{ $estadosLabels[$presupuesto->estado] ?? $presupuesto->estado }}
                    </span>
                </td>
                <td class="text-right">${{ number_format($presupuesto->importe_total, 2, ',', '.') }}</td>
                <td class="text-center">{{ $presupuesto->fecha_vencimiento?->format('d/m/Y') ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(count($data) > 0)
    <div class="summary">
        <strong>Resumen:</strong><br>
        Total de presupuestos: {{ count($data) }}<br>
        Importe total: ${{ number_format($data->sum('importe_total'), 2, ',', '.') }}<br>
        Promedio por presupuesto: ${{ number_format($data->avg('importe_total'), 2, ',', '.') }}<br>
        
        @php
            $estadosCount = $data->groupBy('estado')->map->count();
        @endphp
        <br>
        <strong>Por estado:</strong><br>
        @foreach($estadosCount as $estado => $count)
            {{ $estadosLabels[$estado] ?? $estado }}: {{ $count }}<br>
        @endforeach
    </div>
    @endif

    <div class="footer">
        Sistema de Compras AP - {{ config('app.name') }}
    </div>
</body>
</html>