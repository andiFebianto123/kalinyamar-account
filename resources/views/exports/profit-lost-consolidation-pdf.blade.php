<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi Konsolidasi</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        table th {
            /* background: #f2f2f2; */
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h3>Laporan Laba Rugi Konsolidasi</h3>

    <table>
        <thead>
            <!-- <tr>
                <th>Keterangan</th>
                <th>Biaya</th>
                <th>Biaya (Bold)</th>
            </tr> -->
        </thead>
        <tbody>
            @foreach ($data as $header)
                <tr>
                    <td><strong>{{$header['name']}}</strong></td>
                    <td></td>
                    <td class="text-right bold">{{ $header['total'] }}</td>
                </tr>
                @foreach ($header['item'] as $item)
                    <tr>
                        <td style="padding-left: 8px;">{{$item->name}}</td>
                        <td class="text-right">{{ $item->total }}</td>
                        <td></td>
                    </tr>
                @endforeach
            @endforeach
            {{-- <tr>
                <td><strong>Pendapatan Usaha</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_1'] }}</td>
            </tr>
            <tr>
                <td style="padding-left: 8px;">Pendapatan Kontrak</td>
                <td class="text-right">{{ $data['total_acct_2'] }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 8px;">Pendapatan Non-Kontrak</td>
                <td class="text-right">{{ $data['total_acct_3'] }}</td>
                <td></td>
            </tr>

            <tr>
                <td><strong>Beban Usaha</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_4'] }}</td>
            </tr>
            <tr>
                <td style="padding-left: 8px;">Beban Proyek (Kontrak)</td>
                <td class="text-right">{{ $data['total_acct_5'] }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="padding-left: 8px;">Beban Operasional (Umum)</td>
                <td class="text-right">{{ $data['total_acct_6'] }}</td>
                <td></td>
            </tr>

            <tr>
                <td><strong>Laba Usaha</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_7'] }}</td>
            </tr>

            <tr>
                <td><strong>Pendapatan/Beban Lain-lain</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_8'] }}</td>
            </tr>
            <tr>
                <td style="padding-left: 8px;">Pendapatan Bunga Bank</td>
                <td class="text-right">{{ $data['total_acct_9'] }}</td>
                <td></td>
            </tr>

            <tr>
                <td><strong>Laba Sebelum Pajak</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_10'] }}</td>
            </tr>
            <tr>
                <td>Beban Pajak</td>
                <td class="text-right">{{ $data['total_acct_11'] }}</td>
                <td></td>
            </tr>

            <tr>
                <td><strong>Laba Bersih</strong></td>
                <td></td>
                <td class="text-right bold">{{ $data['total_acct_12'] }}</td>
            </tr> --}}
        </tbody>
    </table>
</body>
</html>
