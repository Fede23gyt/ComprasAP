<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomenclador de Tipos de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #333;
            margin: 0;
            font-size: 18px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .estado-habilitado {
            color: #28a745;
            font-weight: bold;
        }
        .estado-deshabilitado {
            color: #dc3545;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nomenclador de Tipos de Compra</h1>
        <p>Generado el: {{ $fecha }}</p>
        <p>Total de registros: {{ count($tiposCompra) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 60%">Descripción</th>
                <th style="width: 20%">Estado</th>
                <th style="width: 20%">Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tiposCompra as $tipo)
            <tr>
                <td>{{ $tipo->descripcion }}</td>
                <td class="{{ $tipo->estado === 'Habilitado' ? 'estado-habilitado' : 'estado-deshabilitado' }}">
                    {{ $tipo->estado }}
                </td>
                <td>{{ $tipo->created_at ? $tipo->created_at->format('d/m/Y') : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #666; font-style: italic;">
                    No hay tipos de compra registrados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Sistema de Gestión de Compras - Página {PAGENO} de {nb}</p>
    </div>
</body>
</html>