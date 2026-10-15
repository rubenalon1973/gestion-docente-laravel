<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listado de profesores</title>
    <style>
        @page { margin: 14mm 10mm 15mm; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 8px; line-height: 1.3; }
        h1 { color: #4c1d95; font-size: 19px; margin: 0 0 2px; }
        .meta { color: #64748b; margin: 0 0 11px; }
        table { border-collapse: collapse; table-layout: fixed; width: 100%; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #312e81; color: #fff; font-size: 7px; padding: 6px 4px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #cbd5e1; overflow-wrap: break-word; padding: 5px 4px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .name { width: 10%; }
        .email { width: 15%; }
        .phone { width: 8%; }
        .location { width: 8%; }
        .subjects { width: 24%; }
        .status { width: 9%; }
        .footer { bottom: -9mm; color: #94a3b8; font-size: 7px; left: 0; position: fixed; right: 0; text-align: center; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <h1>Listado de profesores</h1>
    <p class="meta">{{ $rows->count() }} resultados - Generado el {{ $generatedAt }}</p>

    <table>
        <thead>
            <tr>
                @foreach (array_keys($rows->first()) as $heading)
                    <th class="{{ match ($heading) {
                        'Apellidos', 'Nombre' => 'name',
                        'Correo electrónico' => 'email',
                        'Teléfono' => 'phone',
                        'Localidad', 'Provincia', 'País' => 'location',
                        'Materias' => 'subjects',
                        default => 'status',
                    } }}">{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>@foreach ($row as $value)<td>{{ $value }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Gestión Docente - Página <span class="page-number"></span></div>
</body>
</html>
