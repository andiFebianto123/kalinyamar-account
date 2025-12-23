<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Tagihan Excel</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="3">RESUME STATUS PROYEK</th>
            </tr>
        </thead>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">1. TAGIHAN YANG BELUM DIBAYAR</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_1'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_1_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">2. TAGIHAN TERTUNDA (RUTIN)</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_2'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_2_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">3. TAGIHAN TERTUNDA (PROJECT)</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_3'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_3_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">4. TAGIHAN RETENSI</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_4'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_4_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">5. PEKERJAAN YANG BELUM SELESAI (RUTIN)</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_5'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_5_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="3">6. PEKERJAAN YANG BELUM SELESAI (PROJECT)</th>
            </tr>
            <tr>
                <th>No.</th>
                <th>Nama Perusahaan</th>
                <th>Nominal Include PPn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['list']['invoice_6'] as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td> {{ $item->price_total_include_ppn_str }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td>{{ $data['list']['invoice_6_total_str'] }}</td>
            </tr>
        </tfoot>
    </table>
    <table>
        <thead>
            <tr>
                <th colspan="2">GRAND TOTAL</th>
                <th>{{ $data['grand_total'] }}</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</body>
</html>
