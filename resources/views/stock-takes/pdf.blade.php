<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Stock Take Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }
        h2, h3 {
            text-align: center;
            margin: 0;
        }
        h3 {
            margin-bottom: 10px;
        }
        hr {
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .no-border td {
            border: none;
            padding: 4px;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>

<h2>ASSET STOCK TAKE REPORT</h2>
<h3>Asset Management System</h3>
<hr>

<table class="no-border">
    <tr>
        <td width="30%">Stock Take Code</td>
        <td width="2%">:</td>
        <td>{{ $stockTake->stock_take_code }}</td>
    </tr>
    <tr>
        <td>Stock Take Name</td>
        <td>:</td>
        <td>{{ $stockTake->name }}</td>
    </tr>
    <tr>
        <td>Stock Take Date</td>
        <td>:</td>
        <td>{{ \Carbon\Carbon::parse($stockTake->stock_take_date)->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td>Status</td>
        <td>:</td>
        <td>{{ $stockTake->status }}</td>
    </tr>
    <tr>
        <td>Officer</td>
        <td>:</td>
        <td>{{ $stockTake->user->name }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="13%">Asset Code</th>
            <th width="18%">Asset Name</th>
            <th width="10%">Physical Status</th>
            <th width="14%">Location</th>
            <th width="14%">Employee</th>
            <th width="14%">Department</th>
            <th width="13%">Note</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($details as $row)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td>{{ $row->asset->asset_code }}</td>
                <td>{{ $row->asset->asset_name }}</td>
                <td class="center">{{ $row->physical_status }}</td>
                <td>{{ $row->location->location_name ?? '-' }}</td>
                <td>{{ $row->employee->name ?? '-' }}</td>
                <td>{{ $row->employee->department->name ?? '-' }}</td>
                <td>{{ $row->note ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="center">No stock take data</td>
            </tr>
        @endforelse
    </tbody>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td width="60%"></td>
        <td class="center">
            Dicetak pada:<br>
            {{ now()->format('d-m-Y') }}
        </td>
    </tr>
</table>

</body>
</html>
