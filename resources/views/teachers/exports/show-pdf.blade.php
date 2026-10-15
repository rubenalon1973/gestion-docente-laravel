<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha del profesor</title>
    <style>
        @page { margin: 18mm 16mm 18mm; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
        h1 { color: #4c1d95; font-size: 22px; margin: 0 0 3px; }
        h2 { border-bottom: 1px solid #cbd5e1; color: #312e81; font-size: 13px; margin: 18px 0 8px; padding-bottom: 4px; }
        p { margin: 0; }
        .meta { color: #64748b; margin-bottom: 14px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { border-bottom: 1px solid #e2e8f0; padding: 5px 7px; vertical-align: top; width: 50%; }
        .label { color: #64748b; display: block; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .items { margin: 0; padding-left: 17px; }
        .items li { margin-bottom: 5px; }
        .empty { color: #64748b; font-style: italic; }
        .footer { bottom: -11mm; color: #94a3b8; font-size: 8px; left: 0; position: fixed; right: 0; text-align: center; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <h1>{{ $name }}</h1>
    <p class="meta">Ficha de profesor - Generada el {{ $generatedAt }}</p>

    @foreach ([
        'Datos personales' => $personal,
        'Datos de contacto' => $contact,
        'Perfil profesional' => $professional,
    ] as $title => $fields)
        <h2>{{ $title }}</h2>
        <table class="grid">
            @foreach (array_chunk($fields, 2, true) as $row)
                <tr>
                    @foreach ($row as $label => $value)
                        <td><span class="label">{{ $label }}</span>{{ $value }}</td>
                    @endforeach
                    @if (count($row) === 1)<td></td>@endif
                </tr>
            @endforeach
        </table>
    @endforeach

    @foreach ([
        'Materias' => $subjects,
        'Disponibilidad' => $availabilities,
        'Formación académica' => $qualifications,
        'Programas, herramientas e inteligencia artificial' => $tools,
        'Tarifas' => $prices,
    ] as $title => $items)
        <h2>{{ $title }}</h2>
        @if ($items === [])
            <p class="empty">No hay información registrada.</p>
        @else
            <ul class="items">
                @foreach ($items as $item)<li>{{ $item }}</li>@endforeach
            </ul>
        @endif
    @endforeach

    <div class="footer">Gestión Docente - Página <span class="page-number"></span></div>
</body>
</html>
