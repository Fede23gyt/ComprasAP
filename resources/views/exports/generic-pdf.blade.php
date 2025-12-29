<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Reporte' }}</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Reporte del Sistema' }}</h1>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de registros: {{ is_countable($data) ? count($data) : 'N/A' }}</p>
    </div>

    @if(is_array($data) || is_object($data))
        <table class="table">
            <thead>
                <tr>
                    @if(!empty($data))
                        @php
                            $firstItem = is_array($data) ? $data[0] : $data->first();
                            $columns = is_object($firstItem) ? array_keys($firstItem->toArray()) : array_keys((array)$firstItem);
                        @endphp
                        @foreach($columns as $column)
                            <th>{{ ucfirst(str_replace('_', ' ', $column)) }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr>
                    @php
                        $itemArray = is_object($item) ? $item->toArray() : (array)$item;
                    @endphp
                    @foreach($itemArray as $value)
                        <td>
                            @if(is_numeric($value) && !is_string($value))
                                {{ number_format($value, 2, ',', '.') }}
                            @elseif(is_bool($value))
                                {{ $value ? 'Sí' : 'No' }}
                            @else
                                {{ $value ?? '' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: #6B7280; margin-top: 50px;">
            No hay datos disponibles para mostrar.
        </p>
    @endif

    <div class="footer">
        Sistema de Compras AP - {{ config('app.name') }}
    </div>
</body>
</html>