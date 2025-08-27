<table>
    <tr>
        <td><strong>Pendapatan Usaha</strong></td>
        <td></td>
        <td>{{ $data['total_acct_1'] }}</td>
    </tr>
    <tr>
        <td>Pendapatan Kontrak</td>
        <td>{{ $data['total_acct_2'] }}</td>
        <td></td>
    </tr>
    <tr>
        <td>Pendapatan Non-Kontrak</td>
        <td>{{ $data['total_acct_3'] }}</td>
        <td></td>
    </tr>

    <tr>
        <td><strong>Beban Usaha</strong></td>
        <td></td>
        <td>{{ $data['total_acct_4'] }}</td>
    </tr>
    <tr>
        <td>Beban Proyek (Kontrak)</td>
        <td>{{ $data['total_acct_5'] }}</td>
        <td></td>
    </tr>
    <tr>
        <td>Beban Operasional (Umum)</td>
        <td>{{ $data['total_acct_6'] }}</td>
        <td></td>
    </tr>
    <tr>
        <td>Laba Usaha</td>
        <td></td>
        <td>{{ $data['total_acct_7'] }}</td>
    </tr>
    <tr>
        <td><strong>Pendapatan/Beban Lain-lain</strong></td>
        <td></td>
        <td>{{ $data['total_acct_8'] }}</td>
    </tr>

    <tr>
        <td>Pendapatan Bunga Bank</td>
        <td>{{ $data['total_acct_9'] }}</td>
        <td></td>
    </tr>

    <tr>
        <td><strong>Laba Sebelum Pajak</strong></td>
        <td></td>
        <td>{{ $data['total_acct_10'] }}</td>
    </tr>

    <tr>
        <td>Beban Pajak</td>
        <td>{{ $data['total_acct_11'] }}</td>
        <td></td>
    </tr>

    <tr>
        <td><strong>Laba Bersih</strong></td>
        <td></td>
        <td><strong>{{ $data['total_acct_12'] }}</strong></td>
    </tr>
</table>
