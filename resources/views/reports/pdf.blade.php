<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 20px 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .meta { font-size: 9px; color: #64748b; margin: 0 0 14px; }
        .head { border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        th { background: #0f172a; color: #fff; text-align: left; padding: 6px 5px; font-size: 8.5px; font-weight: 600; }
        td { padding: 5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .empty { padding: 24px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; }
        .footer { margin-top: 14px; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="head">
        <h1>{{ $title }}</h1>
        <p class="meta">
            {{ config('app.name', 'Asset Management') }}
            &middot; Generated {{ $generatedAt }}
            @isset($generatedBy) &middot; by {{ $generatedBy }} @endisset
            &middot; {{ count($rows) }} {{ Str::plural('record', count($rows)) }}
        </p>
    </div>

    @if (count($rows) === 0)
        <p class="empty">No records matched this report.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($headings as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell === null || $cell === '' ? '-' : $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="footer">Confidential &middot; {{ config('app.name', 'Asset Management') }}</p>
</body>
</html>
