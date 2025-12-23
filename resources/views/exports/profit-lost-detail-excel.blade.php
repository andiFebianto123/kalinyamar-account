<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h1, .report-header h2 {
            margin: 0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th, .report-table td {
            border: 1px solid #000;
            padding: 6px 10px;
        }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <table>
    <tr>
        <td colspan="2" style="text-align:center; font-weight:bold;">LAPORAN LABA RUGI</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center; font-weight:bold;">({{ $profit_lost->clientPo->job_name }})</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;">({{ $profit_lost->clientPo->category }})</td>
    </tr>
</table>

<br>

<table border="1">
    <tr>
        <td width="60%"><b>A. Pendapatan PO (exc PPn)</b></td>
        <td align="right"><b>{{ $report['price_po_excl_ppn'] }}</b></td>
    </tr>

    <tr>
        <td colspan="2"><b>B. Biaya Pekerjaan</b></td>
    </tr>
    <tr>
        <td>Jenis Biaya</td>
        <td align="right">Nilai (Rp)</td>
    </tr>
    <tr>
        <td>Material</td>
        <td align="right">{{ $report['price_material'] }}</td>
    </tr>
    <tr>
        <td>Biaya Subkont</td>
        <td align="right">{{ $report['price_subkon'] }}</td>
    </tr>
    <tr>
        <td>Biaya Tenaga Kerja Langsung (BTKL)</td>
        <td align="right">{{ $report['price_btkl'] }}</td>
    </tr>
    <tr>
        <td>Biaya lainnya</td>
        <td align="right">{{ $report['price_other'] }}</td>
    </tr>
    <tr>
        <td>Biaya lewat Tahun</td>
        <td align="right">{{ $report['price_profit_lost_project'] }}</td>
    </tr>
    <tr>
        <td><b>Total Biaya</b></td>
        <td align="right"><b>{{ $report['price_total'] }}</b></td>
    </tr>

    <tr>
        <td><b>C. Laba Rugi PO</b></td>
        <td align="right"><b>{{ $report['price_profit_lost_po'] }}</b></td>
    </tr>
    <tr>
        <td><b>D. Beban Umum</b></td>
        <td align="right"><b>{{ $report['price_general'] }}</b></td>
    </tr>
    <tr>
        <td><b>E. Laba Akhir</b></td>
        <td align="right"><b>{{ $report['price_profit_final'] }}</b></td>
    </tr>
</table>

</body>
</html>
