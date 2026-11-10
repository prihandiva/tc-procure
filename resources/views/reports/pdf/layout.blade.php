<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
            @bottom-right {
                content: "Halaman " counter(page) " dari " counter(pages);
                font-size: 8pt;
                color: #64748b;
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #312e81;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .brand-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e1b4b;
            letter-spacing: 0.5px;
        }
        .sub-title {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }
        .report-name {
            font-size: 12pt;
            font-weight: bold;
            color: #312e81;
            text-align: right;
        }
        .report-meta {
            font-size: 8pt;
            color: #64748b;
            text-align: right;
            margin-top: 2px;
        }
        .filter-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 8pt;
            margin-bottom: 12px;
        }
        .filter-label {
            font-weight: bold;
            color: #334155;
        }
        .filter-value {
            color: #475569;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #312e81;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 7px;
            border: 1px solid #1e1b4b;
        }
        table.data-table td {
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        table.data-table tr.total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #64748b; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-amber { background-color: #fef3c7; color: #92400e; }
        .badge-rose { background-color: #ffe4e6; color: #9f1239; }
        .badge-indigo { background-color: #e0e7ff; color: #3730a3; }

        .signature-table {
            width: 100%;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
        }
        .signature-line {
            margin-top: 45px;
            border-top: 1px solid #475569;
            width: 70%;
            margin-left: auto;
            margin-right: auto;
            font-weight: bold;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <div class="brand-title">TC-PROCURE</div>
                <div class="sub-title">Sistem Pengadaan & Verifikasi Konsumsi Training Center</div>
            </td>
            <td style="vertical-align: middle;">
                <div class="report-name">{{ $reportTitle }}</div>
                <div class="report-meta">Dicetak: {{ $generatedAt }} WIB | Oleh: {{ $user->name }}</div>
            </td>
        </tr>
    </table>

    <!-- Applied Filters Bar -->
    <div class="filter-box">
        <span class="filter-label">Filter Laporan:</span>
        <span class="filter-value">{{ $filterDesc }}</span>
        &bull;
        <span class="filter-label">Total Data:</span>
        <span class="filter-value text-bold">{{ count($data) }} Baris</span>
    </div>

    <!-- Main Content Table -->
    @yield('content')

    <!-- Signatures -->
    <table class="signature-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div>Disiapkan Oleh:</div>
                <div class="signature-line">{{ $user->name }}</div>
                <div class="text-muted">{{ $user->role?->label() }}</div>
            </td>
            <td>
                <div>Diverifikasi:</div>
                <div class="signature-line">Tim TC</div>
                <div class="text-muted">Supervisor Pengadaan</div>
            </td>
            <td>
                <div>Mengetahui:</div>
                <div class="signature-line">Pimpinan Training Center</div>
                <div class="text-muted">Head of Training Center</div>
            </td>
        </tr>
    </table>
</body>
</html>

