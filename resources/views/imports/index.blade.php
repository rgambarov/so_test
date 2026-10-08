<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <style>
        body { font: 16px/1.5 system-ui, sans-serif; background: #f4f6fa; color: #172033; margin: 0; }
        main { max-width: 1100px; margin: 40px auto; padding: 24px; }
        section { background: white; padding: 24px; border: 1px solid #dce2eb; border-radius: 12px; margin-bottom: 20px; }
        h1 { margin-top: 0; } button { background: #2455d6; color: white; border: 0; padding: 12px 18px; border-radius: 6px; cursor: pointer; }
        input { display: block; margin: 16px 0; } .muted { color: #566176; } .error { color: #ac2020; }
        .table-wrap { overflow-x: auto; } table { border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px; border-bottom: 1px solid #dce2eb; text-align: left; white-space: nowrap; }
    </style>
</head>
<body>
<main>
    <h1>{{ config('app.name') }}</h1>
    <p class="muted">Lead Import · Laravel 12 · PHP 8.2</p>
    @if (session()->has('import_result'))
        @php($result = session('import_result'))

        <section role="status">
            <h2>Import completed</h2>
            <p>
                Imported leads:
                <strong>{{ number_format($result['imported'], 0, '.', ' ') }}</strong>
            </p>
            <p>Import time: {{ $result['elapsed_seconds'] }} seconds</p>
            <p>Peak PHP memory: {{ $result['peak_memory_mb'] }} MB</p>
        </section>
    @endif
    <section>
        <p>Leads in the database: <strong>{{ number_format($count, 0, '.', ' ') }}</strong></p>
        <p>Select an XLSX file to validate its headers and preview the first {{ config('import.preview_rows') }} rows.</p>
        <form method="post" action="{{ route('imports.preview') }}" enctype="multipart/form-data">
            @csrf
            <label for="file">Leads file (.xlsx, up to 32 MB)</label>
            <input id="file" name="file" type="file" accept=".xlsx" required>
            @error('file') <p class="error" role="alert">{{ $message }}</p> @enderror
            <button type="submit">Check File</button>

            <button
                type="submit"
                formaction="{{ route('imports.store') }}"
            >
                Import Leads
            </button>
        </form>
        <p class="muted">
            Check File previews the first {{ config('import.preview_rows') }} rows.
            Import Leads saves all rows to the database.
        </p>
    </section>
    @if ($preview !== null)
        <section>
            <h2>Preview</h2>
            @if (count($preview) === 0)
                <p>The headers are valid, but the file contains no data rows.</p>
            @else
                <div class="table-wrap"><table>
                        <thead><tr>@foreach(config('import.headers') as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
                        <tbody>@foreach($preview as $row)<tr>@foreach($row as $value)<td>{{ $value ?? '—' }}</td>@endforeach</tr>@endforeach</tbody>
                    </table></div>
            @endif
        </section>
    @endif
</main>
</body>
</html>
