<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra - {{ $orden->numero_orden }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 30px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 5px;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .header-subtitle {
            font-size: 10px;
            margin-bottom: 2px;
        }

        .header-date {
            text-align: right;
            font-size: 10px;
            margin-bottom: 15px;
        }

        .presupuesto-info {
            margin-bottom: 15px;
            font-size: 10px;
        }

        .presupuesto-line {
            margin-bottom: 3px;
        }

        .proveedor-section {
            margin-bottom: 10px;
        }

        .proveedor-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .proveedor-intro {
            margin-top: 10px;
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 10px;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-words {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .signature-section {
            position: absolute;
            bottom: 130px;
            left: 30px;
            right: 30px;
            display: table;
            width: calc(100% - 60px);
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .signature-right {
            margin-left: 4%;
        }

        .conditions {
            position: absolute;
            bottom: 20px;
            left: 30px;
            right: 30px;
            border-top: 2px solid #000;
            padding-top: 10px;
            font-size: 9px;
        }

        .condition-line {
            margin-bottom: 3px;
        }

        .page-container {
            position: relative;
            min-height: 100vh;
            padding-bottom: 180px;
        }
    </style>
</head>
<body>
    <div class="page-container">
    <!-- Encabezado -->
    <div class="header">
        <div class="header-title">
            Orden de Compra Nro {{ $orden->numero_orden }} Ejercicio: {{ $orden->fecha_orden->format('Y') }}
        </div>
        <div class="header-subtitle">
            Original
        </div>
        <div class="header-subtitle">
            Responsable:
            @if($orden->presupuesto && $orden->presupuesto->notasPedido->isNotEmpty())
                {{ $orden->presupuesto->notasPedido->first()->oficina->nombre ?? 'Oficina Municipal' }}
            @else
                Oficina Municipal
            @endif
        </div>
    </div>

    <div class="header-date">
        Salta, {{ $orden->fecha_orden->format('d') }} de {{ $orden->fecha_orden->locale('es')->translatedFormat('F') }} de {{ $orden->fecha_orden->format('Y') }}
    </div>

    <!-- Información del Presupuesto -->
    <div class="presupuesto-info">
        <div class="presupuesto-line">
            <strong>Presupuesto {{ $orden->presupuesto && $orden->presupuesto->tipoCompra ? $orden->presupuesto->tipoCompra->nombre : 'Contratacion Directa' }} Nro {{ $orden->presupuesto->numero_presupuesto ?? 'N/A' }}</strong>
            @if($orden->presupuesto && $orden->presupuesto->notasPedido->isNotEmpty())
                Llamado Nro: {{ $orden->presupuesto->notasPedido->first()->numero_nota }}
            @endif
        </div>
        <div class="presupuesto-line">
            <strong>Expediente:</strong> {{ $orden->numero_orden }}-{{ $orden->fecha_orden->format('Y') }}
            <span style="margin-left: 100px;"><strong>Imputacion Nro:</strong>
            @if($orden->presupuesto && $orden->presupuesto->notasPedido->isNotEmpty())
                {{ $orden->presupuesto->notasPedido->first()->oficina->codigo_oficina ?? 'N/A' }}
            @else
                N/A
            @endif
            </span>
        </div>
        <div class="presupuesto-line">
            <strong>Autorizado por:</strong> {{ $orden->usuarioAprobador->name ?? $orden->usuarioGenerador->name ?? 'N/A' }}
            <span style="margin-left: 50px;"><strong>Resolucion Nro:</strong> {{ $orden->numero_orden }}/{{ $orden->fecha_orden->format('y') }}</span>
        </div>
    </div>

    <!-- Información del Proveedor -->
    <div class="proveedor-section">
        <div class="proveedor-title">
            Sres. {{ $orden->proveedor->id }}-{{ strtoupper($orden->proveedor->razon_social ?? 'N/A') }}
        </div>
        <div>
            con domicilio en {{ strtoupper($orden->proveedor->domicilio ?? 'N/A') }}
        </div>
        <div class="proveedor-intro">
            Sírvase Ud. proveer los siguientes elementos:
        </div>
    </div>

    <!-- Detalle de Items -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">Reng</th>
                <th style="width: 5%;">Clns</th>
                <th style="width: 60%;">Descripcion Insumo</th>
                <th style="width: 10%;">Cantidad</th>
                <th style="width: 10%;">Precio</th>
                <th style="width: 10%;">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $detalle)
            <tr>
                <td class="text-center">{{ str_pad($detalle->renglon, 4, '0', STR_PAD_LEFT) }}</td>
                <td class="text-center">{{ $detalle->insumo->clasificacionEconomica->codigo ?? '' }}</td>
                <td>
                    {{ $detalle->descripcion ?: ($detalle->insumo->descripcion ?? $detalle->insumo->nombre ?? 'N/A') }}
                    <br><small>Cod. Insumo: {{ $detalle->insumo->codigo ?? 'N/A' }}
                    @if($detalle->marca || $detalle->modelo)
                        - {{ $detalle->marca }} {{ $detalle->modelo }}
                    @endif
                    </small>
                </td>
                <td class="text-right">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total -->
    <div class="total-section">
        <div class="total-words">
            Son pesos: {{ $total_en_letras ?? 'N/A' }}
        </div>
        <div style="font-weight: bold;">
            Importe Total {{ number_format($orden->monto_total, 2, ',', '.') }}
        </div>
    </div>

    <!-- Condiciones -->
    <div class="conditions">
        <div class="condition-line">
            <strong>Condiciones de Pago:</strong> {{ $orden->forma_pago ?? 'N/A' }}
            @if($orden->condiciones_pago)
                - {{ $orden->condiciones_pago }}
            @endif
        </div>
        <div class="condition-line">
            <strong>Lugar de Entrega:</strong> {{ $orden->lugar_entrega ?? 'N/A' }}
        </div>
        <div class="condition-line">
            <strong>Plazo de Entrega:</strong> {{ $orden->plazo_entrega ? $orden->plazo_entrega . ' días' : 'DE INICIO INMEDIATO' }}
        </div>
        @if($orden->observaciones)
        <div class="condition-line" style="margin-top: 10px;">
            <strong>Observaciones:</strong> {{ $orden->observaciones }}
        </div>
        @endif
    </div>

    <!-- Firmas -->
    <div class="signature-section">
        <div class="signature-box">
            Contratacion
        </div>
        <div class="signature-box signature-right">
            Proveedor
        </div>
    </div>

    </div><!-- Fin page-container -->
</body>
</html>