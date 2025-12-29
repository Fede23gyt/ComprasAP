<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Pedido {{ $nota->numero_nota }}/{{ $nota->ejercicio_nota }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
            line-height: 1.3;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header-row {
            display: table-row;
        }
        .header-cell {
            display: table-cell;
            vertical-align: middle;
            width: 33.33%;
        }
        .header-left {
            text-align: left;
        }
        .header-center {
            text-align: center;
        }
        .header-right {
            text-align: right;
        }
        .header h1 {
            color: #000;
            margin: 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            color: #000;
            margin: 5px 0 0 0;
            font-size: 14px;
            font-weight: bold;
        }
        .logo {
            max-height: 60px;
            max-width: 100px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 3px 5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 15%;
        }
        .info-value {
            width: 35%;
        }
        .full-width {
            width: 100%;
        }
        .bordered-section {
            border: 1px solid #ccc;
        }
        .bordered-section .info-cell {
            border: 1px solid #ccc;
        }
        .bordered-section .info-label {
            background-color: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-section {
            margin-top: 15px;
            text-align: right;
        }
        .total-label {
            font-weight: bold;
            font-size: 12px;
        }
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-row {
            display: table-row;
        }
        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 20px 10px;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
        }
        .estado-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .estado-abierta {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .estado-confirmada {
            background-color: #d1edff;
            color: #0c63e4;
            border: 1px solid #74b9ff;
        }
        .estado-rechazada {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-row">
            <div class="header-cell header-left">
                <img src="{{ public_path('images/MSA_escudoazul.jpg') }}" alt="Escudo Municipal" class="logo">
            </div>
            <div class="header-cell header-center">
                <h1>Municipalidad de la Ciudad de Salta</h1>
                <h2>Nota de Pedido</h2>
            </div>
            <div class="header-cell header-right">
                <img src="{{ public_path('images/LogoPDF.png') }}" alt="Logo Salta" class="logo">
            </div>
        </div>
    </div>

    <!-- Información Principal -->
    <div class="info-section">
        <div class="info-row">
            <div class="info-cell info-label">Número:</div>
            <div class="info-cell info-value">{{ $nota->numero_nota }}/{{ $nota->ejercicio_nota }}</div>
            <div class="info-cell info-label">Fecha:</div>
            <div class="info-cell info-value">{{ $nota->fecha_nota ? $nota->fecha_nota->format('d/m/Y') : '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">Oficina:</div>
            <div class="info-cell info-value">{{ $nota->oficina->nombre ?? '-' }}</div>
            <div class="info-cell info-label">Estado:</div>
            <div class="info-cell info-value">
                <span class="estado-badge estado-{{ $nota->estado == 0 ? 'abierta' : ($nota->estado == 1 ? 'confirmada' : 'rechazada') }}">
                    {{ $nota->estado == 0 ? 'Abierta' : ($nota->estado == 1 ? 'Confirmada' : 'Rechazada') }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">Tipo de Nota:</div>
            <div class="info-cell info-value">{{ $nota->tipoNota->descripcion ?? '-' }}</div>
            <div class="info-cell info-label">Solicitante:</div>
            <div class="info-cell info-value">{{ $nota->usuario->name ?? '-' }}</div>
        </div>
        @if($nota->expediente)
        <div class="info-row">
            <div class="info-cell info-label">Expediente:</div>
            <div class="info-cell info-value full-width" colspan="3">{{ $nota->expediente }}</div>
        </div>
        @endif
    </div>

    <!-- Descripción -->
    @if($nota->descripcion)
    <div class="info-section bordered-section">
        <div class="info-row">
            <div class="info-cell info-label">Descripción:</div>
            <div class="info-cell info-value full-width" style="width: 85%;">{{ $nota->descripcion }}</div>
        </div>
    </div>
    @endif

    <!-- Detalle de Insumos -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Renglón</th>
                <th style="width: 12%;">Código</th>
                <th style="width: 35%;">Descripción</th>
                <th style="width: 12%;">Cantidad</th>
                <th style="width: 8%;">Unidad</th>
                <th style="width: 12%;">Precio Unit.</th>
                <th style="width: 13%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($nota->detalles as $detalle)
            <tr>
                <td class="text-center">{{ $detalle->renglon }}</td>
                <td>{{ $detalle->insumo->codigo ?? '-' }}</td>
                <td>{{ $detalle->insumo->descripcion ?? '-' }}</td>
                <td class="text-right">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                <td class="text-center">{{ $detalle->insumo->unidad_solicitud ?? '-' }}</td>
                <td class="text-right">${{ number_format($detalle->precio, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format($detalle->total_renglon, 2, ',', '.') }}</td>
            </tr>
            @if($detalle->comentario)
            <tr>
                <td></td>
                <td colspan="6" style="font-style: italic; color: #666; font-size: 9px;">
                    <strong>Observación:</strong> {{ $detalle->comentario }}
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="7" class="text-center" style="color: #666; font-style: italic;">
                    No hay insumos registrados en esta nota de pedido
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total -->
    <div class="total-section">
        <div class="total-label">
            TOTAL: ${{ number_format($nota->total_nota, 2, ',', '.') }}
        </div>
        <div style="font-size: 10px; margin-top: 5px;">
            Total de ítems: {{ $total_items }} | Total cantidad: {{ number_format($total_cantidad, 2, ',', '.') }}
        </div>
    </div>

    <!-- Información de confirmación -->
    @if($nota->estado == 1 && $nota->confirmadoPor)
    <div class="info-section bordered-section" style="margin-top: 20px;">
        <div class="info-row">
            <div class="info-cell info-label">Confirmada por:</div>
            <div class="info-cell info-value">{{ $nota->confirmadoPor->name }}</div>
            <div class="info-cell info-label">Fecha confirmación:</div>
            <div class="info-cell info-value">{{ $nota->fecha_confirmacion ? $nota->fecha_confirmacion->format('d/m/Y H:i') : '-' }}</div>
        </div>
    </div>
    @endif

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line">SOLICITANTE</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">AUTORIZADO POR</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">RECIBIDO POR</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="position: fixed; bottom: 10px; left: 15px; right: 15px; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">
        <p>Sistema de Gestión de Compras - Nota de Pedido generada el {{ $fecha_actual }}</p>
    </div>
</body>
</html>