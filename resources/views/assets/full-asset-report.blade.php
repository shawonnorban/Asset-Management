<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Asset Report</title>
<style>
    body {
        font-family: DejaVu Sans;
        font-size: 11px;
    }

    .header {
        text-align: center;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #000;
        padding: 4px;
    }

    th {
        background-color: #f2f2f2;
    }

    .footer {
        margin-top: 40px;
        text-align: right;
    }
</style>
</head>
<body>

<div class="header">
    <h2>PT. Astra Inovasi Teknologi</h2>
    <h4>FULL ASSET DATA REPORT</h4>
    <p>Printed: {{ $date }}</p>
    <p>Total Assets: {{ $total }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Brand</th>
            <th>Category</th>
            <th>Location</th>
            <th>Assignee</th>
            <th>Received</th>
        </tr>
    </thead>
    <tbody>
        @foreach($assets as $index => $asset)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $asset->asset_code }}</td>
            <td>{{ $asset->asset_name }}</td>
            <td>{{ $asset->brand ?? '-' }}</td>
            <td>{{ $asset->category->category_name ?? '-' }}</td>
            <td>{{ $asset->location->location_name ?? '-' }}</td>
            <td>{{ $asset->employee->name ?? '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($asset->added_date)->format('d-m-Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <p>Approved by,</p>
    <br><br><br>
    <p>_________________________</p>
    <p>Manager</p>
</div>

</body>
</html>