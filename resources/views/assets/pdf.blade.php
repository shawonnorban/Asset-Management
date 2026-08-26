<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset List</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background: #f0f0f0;
        }
        img {
            width: 80px;
        }
    </style>
</head>
<body>

<h2>ASSET INVENTORY LIST</h2>

<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="25%">Asset Code</th>
            <th width="40%">Asset Name</th>
            <th width="30%">QR Code</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($assets as $asset)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $asset->asset_code }}</td>
                <td>{{ $asset->asset_name }}</td>
                <td>
                    @php
                        $qrPath = public_path('storage/qrcode/' . $asset->asset_code . '.png');
                    @endphp

                    @if (file_exists($qrPath))
                        <img src="{{ $qrPath }}">
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<br><br>

<p style="text-align:right">
    Dicetak pada: {{ now()->format('d-m-Y') }}
</p>

</body>
</html>
