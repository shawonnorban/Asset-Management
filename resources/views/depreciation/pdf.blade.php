<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Depreciation Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        h2, h3 {
            text-align: center;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
        }
        th {
            background: #f0f0f0;
        }
        .no-border td {
            border: none;
            padding: 4px;
        }
        .right {
            text-align: right;
        }
    </style>
</head>
<body>

<h2>ASSET DEPRECIATION REPORT</h2>
<h3>Asset Management System</h3>

<hr>
<table class="no-border">
    <tr>
        <td width="30%">Asset Code</td>
        <td width="2%">:</td>
        <td>{{ $asset->asset_code }}</td>
    </tr>
    <tr>
        <td>Asset Name</td>
        <td>:</td>
        <td>{{ $asset->asset_name }}</td>
    </tr>
    <tr>
        <td>Category</td>
        <td>:</td>
        <td>{{ $asset->category->category_name ?? '-' }}</td>
    </tr>
    <tr>
        <td>Location</td>
        <td>:</td>
        <td>{{ $asset->location->location_name ?? '-' }}</td>
    </tr>
</table>

<h4>Asset Depreciation Parameters</h4>
<table>
    <tr>
        <th width="30%">Depreciation Method</th>
        <td>{{ $setting->method }}</td>
    </tr>
    <tr>
        <th>Tax Group</th>
        <td>{{ $setting->taxDepreciationGroup->name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Useful Life</th>
        <td>{{ $setting->taxDepreciationGroup->useful_life_years ?? 0 }} Years</td>
    </tr>
    <tr>
        <th>Acquisition Cost</th>
        <td class="right">
            Rp {{ number_format($setting->acquisition_cost, 0, ',', '.') }}
        </td>
    </tr>
    <tr>
        <th>Salvage Value</th>
        <td class="right">
            Rp {{ number_format($setting->salvage_value ?? 0, 0, ',', '.') }}
        </td>
    </tr>
    <tr>
        <th>In-Service Date</th>
        <td>
            {{ \Carbon\Carbon::parse($setting->in_service_date)->format('d-m-Y') }}
        </td>
    </tr>
</table>

<h4>Monthly Depreciation History</h4>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Period</th>
            <th>Method</th>
            <th>Monthly Expense</th>
            <th>Accumulated</th>
            <th>Ending Book Value</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($history as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($row->period)->format('m-Y') }}</td>
                <td>{{ $row->method }}</td>
                <td class="right">
                    Rp {{ number_format($row->monthly_expense, 0, ',', '.') }}
                </td>
                <td class="right">
                    Rp {{ number_format($row->accumulated_depreciation, 0, ',', '.') }}
                </td>
                <td class="right">
                    Rp {{ number_format($row->ending_book_value, 0, ',', '.') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" align="center">
                    No depreciation data yet
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<br><br>

<table class="no-border">
    <tr>
        <td width="70%"></td>
        <td align="center">
            Dicetak pada:<br>
            {{ now()->format('d-m-Y') }}
        </td>
    </tr>
</table>

</body>
</html>