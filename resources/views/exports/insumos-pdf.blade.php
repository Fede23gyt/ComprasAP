<?php
{{-- resources/views/exports/insumos-pdf.blade.php --}}
  <!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Insumos</title>
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
      margin: 0;
      color: #333;
      font-size: 24px;
    }
    .info {
      margin-bottom: 20px;
    }
    .info p {
      margin: 5px 0;
      color: #666;
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
      background-color: #f2f2f2;
      font-weight: bold;
      color: #333;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    .registrable-si {
      color: #22c55e;
      font-weight: bold;
    }
    .registrable-no {
      color: #ef4444;
      font-weight: bold;
    }
    .footer {
      margin-top: 30px;
      text-align: center;
      color: #666;
      font-size: 10px;
      border-top: 1px solid #ddd;
      padding-top: 10px;
    }
    .summary {
      background-color: #f8f9fa;
      padding: 15px;
      margin-bottom: 20px;
      border-left: 4px solid #3b82f6;
    }
  </style>
</head>
<body>
<div class="header">
  <h1>Listado de Insumos</h1>
</div>

<div class="info">
  <p><strong>Fecha de generación:</strong> {{ $fecha }}</p>
  <p><strong>Total de registros:</strong> {{ $total }}</p>
  @if($search)
    <p><strong>Filtrado por:</strong> "{{ $search }}"</p>
  @endif
</div>

<div class="summary">
  <strong>Resumen:</strong>
  @php
    $registrables = $insumos->where('registrable', true)->count();
    $noRegistrables = $insumos->where('registrable', false)->count();
  @endphp
  <span style="color: #22c55e;">{{ $registrables }} Registrables</span> |
  <span style="color: #ef4444;">{{ $noRegistrables }} No Registrables</span>
</div>

<table>
  <thead>
  <tr>
    <th style="width: 10%;">Código</th>
    <th style="width: 35%;">Descripción</th>
    <th style="width: 15%;">Clasificación</th>
    <th style="width: 25%;">Nombre Clasificación</th>
    <th style="width: 15%;">Registrable</th>
  </tr>
  </thead>
  <tbody>
  @forelse($insumos as $insumo)
    <tr>
      <td>{{ $insumo->codigo }}</td>
      <td>{{ $insumo->descripcion }}</td>
      <td>{{ $insumo->clasificacion }}</td>
      <td>{{ $insumo->clasificacionEconomica->descripcion ?? '-' }}</td>
      <td class="{{ $insumo->registrable ? 'registrable-si' : 'registrable-no' }}">
        {{ $insumo->registrable ? 'Sí' : 'No' }}
      </td>
    </tr>
  @empty
    <tr>
      <td colspan="5" style="text-align: center; color: #666; font-style: italic;">
        No se encontraron insumos
      </td>
    </tr>
  @endforelse
  </tbody>
</table>

<div class="footer">
  <p>Documento generado automáticamente el {{ $fecha }}</p>
  <p>Sistema de Gestión de Insumos</p>
</div>
</body>
</html>
