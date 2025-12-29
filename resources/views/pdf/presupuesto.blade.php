<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Cotización</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.3;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .header-left, .header-center, .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 33.33%;
        }
        
        .header-left {
            text-align: left;
        }
        
        .header-center {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .header-right {
            text-align: right;
        }
        
        .logo {
            max-height: 60px;
            max-width: 100px;
        }
        
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0;
            border: 2px solid #000;
            padding: 10px;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        
        .info-table .label {
            font-weight: bold;
            width: 15%;
        }
        
        .info-table .value {
            width: 35%;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        
        .details-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .details-table .number {
            text-align: right;
        }
        
        .details-table .center {
            text-align: center;
        }
        
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            height: 80px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 50px;
        }
        
        .footer-info {
            margin-top: 20px;
            font-size: 10px;
        }
        
        .break-word {
            word-break: break-word;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/MSA_escudoazul.jpg') }}" alt="Escudo MSA" class="logo">
        </div>
        <div class="header-center">
            Municipalidad de la Ciudad de Salta
        </div>
        <div class="header-right">
            <img src="{{ public_path('images/LogoPDF.png') }}" alt="Logo PDF" class="logo">
        </div>
    </div>

    <!-- Title -->
    <div class="title">
        FORMULARIO DE COTIZACIÓN
        @if(isset($tipo_documento) && $tipo_documento !== 'ORIGINAL')
            <div style="font-size: 12px; color: #666; margin-top: 5px;">
                ({{ $tipo_documento }})
            </div>
        @endif
    </div>

    <!-- Información del presupuesto -->
    <table class="info-table">
        <tr>
            <td class="label">Presupuesto:</td>
            <td class="value">{{ $presupuesto->numero_presupuesto }}/{{ $presupuesto->ejercicio }}</td>
            <td class="label">Fecha:</td>
            <td class="value">{{ \Carbon\Carbon::parse($presupuesto->fecha_presupuesto)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Tipo de Compra:</td>
            <td class="value">{{ $presupuesto->tipoCompra->descripcion ?? 'N/A' }}</td>
            <td class="label">Nro. Llamado:</td>
            <td class="value">{{ $presupuesto->nro_llamado }}</td>
        </tr>
        <tr>
            <td class="label">Estado:</td>
            <td class="value">{{ ucfirst($presupuesto->estado) }}</td>
            <td class="label">Usuario:</td>
            <td class="value">{{ $presupuesto->usuario->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <!-- Notas de Pedido asociadas -->
    @if($presupuesto->notasPedido->count() > 0)
    <div style="margin-bottom: 15px;">
        <strong>Notas de Pedido asociadas:</strong>
        @foreach($presupuesto->notasPedido as $nota)
            {{ $nota->numero_nota }}/{{ $nota->ejercicio_nota }}{{ !$loop->last ? ', ' : '' }}
        @endforeach
    </div>
    @endif

    <!-- Información adicional -->
    @if($presupuesto->lugar_entrega || $presupuesto->domicilio_entrega || $presupuesto->dependencia)
    <table class="info-table">
        @if($presupuesto->lugar_entrega)
        <tr>
            <td class="label">Lugar de Entrega:</td>
            <td colspan="3" class="value">{{ $presupuesto->lugar_entrega }}</td>
        </tr>
        @endif
        @if($presupuesto->domicilio_entrega)
        <tr>
            <td class="label">Domicilio de Entrega:</td>
            <td colspan="3" class="value">{{ $presupuesto->domicilio_entrega }}</td>
        </tr>
        @endif
        @if($presupuesto->dependencia)
        <tr>
            <td class="label">Dependencia:</td>
            <td colspan="3" class="value">{{ $presupuesto->dependencia }}</td>
        </tr>
        @endif
    </table>
    @endif

    <!-- Condiciones -->
    @if($presupuesto->plazo_entrega || $presupuesto->forma_pago || $presupuesto->plazo_pago || $presupuesto->validez_oferta)
    <table class="info-table">
        @if($presupuesto->plazo_entrega)
        <tr>
            <td class="label">Plazo de Entrega:</td>
            <td class="value">{{ $presupuesto->plazo_entrega }} días</td>
            @if($presupuesto->validez_oferta)
            <td class="label">Validez de Oferta:</td>
            <td class="value">{{ $presupuesto->validez_oferta }} días</td>
            @else
            <td colspan="2"></td>
            @endif
        </tr>
        @endif
        @if($presupuesto->forma_pago)
        <tr>
            <td class="label">Forma de Pago:</td>
            <td class="value">{{ $presupuesto->forma_pago }}</td>
            @if($presupuesto->plazo_pago)
            <td class="label">Plazo de Pago:</td>
            <td class="value">{{ $presupuesto->plazo_pago }}</td>
            @else
            <td colspan="2"></td>
            @endif
        </tr>
        @endif
    </table>
    @endif

    <!-- Detalle de insumos -->
    <table class="details-table">
        <thead>
            <tr>
                <th style="width: 8%;">Renglón</th>
                <th style="width: 12%;">Código</th>
                <th style="width: 35%;">Descripción</th>
                <th style="width: 8%;">Unidad</th>
                <th style="width: 10%;">Cantidad</th>
                <th style="width: 12%;">Precio Unit.</th>
                <th style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($presupuesto->detalles as $detalle)
            <tr>
                <td class="center">{{ $detalle->renglon }}</td>
                <td class="center">{{ $detalle->insumo->codigo ?? 'N/A' }}</td>
                <td class="break-word">{{ $detalle->insumo->descripcion ?? 'N/A' }}</td>
                <td class="center">{{ $detalle->unidad_medida }}</td>
                <td class="number">{{ number_format($detalle->cantidad, 2, ',', '.') }}</td>
                <td class="number">${{ number_format($detalle->precio_unitario, 2, ',', '.') }}</td>
                <td class="number">${{ number_format($detalle->subtotal, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f0f0f0;">
                <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL:</td>
                <td class="number" style="font-weight: bold;">${{ number_format($presupuesto->detalles->sum('subtotal'), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Observaciones -->
    @if($presupuesto->observaciones)
    <div style="margin-top: 15px; border: 1px solid #000; padding: 10px;">
        <strong>Observaciones:</strong><br>
        <div style="white-space: pre-wrap;">{{ $presupuesto->observaciones }}</div>
    </div>
    @endif

    <!-- Comentarios -->
    @if($presupuesto->comentario)
    <div style="margin-top: 15px; border: 1px solid #000; padding: 10px;">
        <strong>Comentarios:</strong><br>
        <div style="white-space: pre-wrap;">{{ $presupuesto->comentario }}</div>
    </div>
    @endif

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Firma y Sello del Proveedor</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Aclaración</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-info">
        <p><strong>IMPORTANTE:</strong> Cotizar cifras con dos decimales. Cotizar precio unitario y total. Adjuntar copia de inscripción en ARCA.</p>
        <p><strong>Generado el:</strong> {{ $fecha_actual }}</p>
        <p><strong>Total de items:</strong> {{ $total_items }}</p>
    </div>
</body>
</html>