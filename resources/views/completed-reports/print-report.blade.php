<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Asset Repair Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .container {
            border: 1px solid #000;
            padding: 15px;
        }

        .header {
            text-align: center;
        }

        .header h2 {
            margin: 0;
        }

        hr {
            margin: 12px 0;
            border: 0;
            border-top: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 4px 2px;
            vertical-align: top;
        }

        .label {
            width: 30%;
            font-weight: bold;
        }

        .separator {
            width: 3%;
            text-align: center;
        }

        .value {
            width: 67%;
        }

        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h2>ASSET REPAIR REPORT</h2>
        <p>Asset Management System</p>
    </div>

    <hr>

    <table>
        <tr>
            <td class="label">Report Title</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->title }}</td>
        </tr>
        <tr>
            <td class="label">Report Description</td>
            <td class="separator">:</td>
            <td class="value">{!! $issueReport->description !!}</td>
        </tr>
        <tr>
            <td class="label">Asset Name</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->asset?->asset_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Asset Code</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->asset?->asset_code ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Brand</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->asset?->brand ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Category</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->asset?->category?->category_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Location</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->asset?->location?->location_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Report Date</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->created_at->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Completed Date</td>
            <td class="separator">:</td>
            <td class="value">{{ $issueReport->updated_at->format('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <hr>

    <div class="section-title">Repair Analysis</div>
    <table>
        <tr>
            <td class="label">Admin Analysis</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $feedback?->decision_analysis ?? '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">User Feedback</td>
            <td class="separator">:</td>
            <td class="value">
                {{ $feedbackReply?->feedback_reply ?? '-' }}
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>
            Dicetak pada: {{ now()->format('d-m-Y H:i') }} <br>
            Oleh Sistem Inventory
        </p>
    </div>

</div>
</body>
</html>
