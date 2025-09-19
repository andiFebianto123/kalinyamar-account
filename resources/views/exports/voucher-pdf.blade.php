@php
    // dd($voucher);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Detail Voucher</title>
<style>
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        color: #333;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    .detail-container {
        width: 100%;
        border-collapse: collapse;
    }
    .detail-container td {
        padding: 5px;
        vertical-align: top;
    }
    .label {
        color: #555;
    }
    .value {
        font-weight: bold;
    }
</style>
</head>
<body>

<h2>Detail Voucher</h2>
<table class="detail-container">
    <tr>
        <td class="label">No Pengajuan Pembayaran</td><td class="value">{{$voucher->no_payment}}</td>
        <td class="label">Akun Biaya</td><td class="value">{{$voucher?->account?->code ." - ".$voucher?->account?->name}}</td>
    </tr>
    <tr>
        <td class="label">Kode Pekerjaan</td><td class="value">{{$voucher?->client_po?->work_code}}</td>
        <td class="label">Sumber Rekening</td><td class="value">{{$voucher?->account_source?->name}}</td>
    </tr>
    <tr>
        <td class="label">Nama Pekerjaan</td><td class="value">{{$voucher->job_name}}</td>
        <td class="label">Tanggal Voucher</td><td class="value">{{$voucher->date_voucher}}</td>
    </tr>
    <tr>
        <td class="label">Nomor Voucher</td><td class="value">{{$voucher->no_voucher}}</td>
        <td class="label">Tanggal Tagihan</td><td class="value">{{$voucher->bill_date}}</td>
    </tr>
    <tr>
        <td class="label">Nama Badan Usaha</td><td class="value">{{$voucher?->subkon?->name}}</td>
        <td class="label">Tanggal PO/SPK</td><td class="value">-</td>
    </tr>
    <tr>
        <td class="label">No. Tagihan</td><td class="value">{{$voucher->bill_number}}</td>
        <td class="label">PPn</td><td class="value">{{$voucher->tax_ppn}} %</td>
    </tr>
    <tr>
        <td class="label">Tanggal Terima Tagihan</td><td class="value">{{$voucher->date_receipt_bill}}</td>
        <td class="label">Total</td><td class="value">Rp.{{ $voucher->total }}</td>
    </tr>
    <tr>
        <td class="label">Deskripsi Pembayaran</td><td class="value">{{$voucher->payment_description}}</td>
        <td class="label">Potongan PPh 23</td><td class="value">Rp.{{$voucher->discount_pph_23}}</td>
    </tr>
    <tr>
        @php
            $no_po_spk = ($voucher->reference_type == 'App\Models\Spk') ? $voucher->reference->no_spk : $voucher->reference->po_number;
        @endphp
        <td class="label">No. PO/SPK</td><td class="value">{{$no_po_spk}}</td>
        <td class="label">Potongan PPh 4</td><td class="value">Rp.{{$voucher->discount_pph_4}}</td>
    </tr>
    <tr>
        <td class="label">Nilai Tagihan</td><td class="value">Rp.{{$voucher->bill_value}}</td>
        <td class="label">Potongan PPh 21</td><td class="value">Rp.{{$voucher->discount_pph_21}}</td>
    </tr>
    <tr>
        <td class="label">PPh 23</td><td class="value">{{$voucher->pph_21}}%</td>
        <td class="label">Status Faktur</td><td class="value">{{$voucher->factur_status}}</td>
    </tr>
    <tr>
        <td class="label">PPh 4</td><td class="value">{{$voucher->pph_4}}%</td>
        <td class="label">Tanggal Faktur</td><td class="value">{{$voucher->date_factur}}</td>
    </tr>
    <tr>
        <td class="label">PPh 21</td><td class="value">{{$voucher->pph_21}}%</td>
        <td class="label">No. Rekening</td><td class="value">{{$voucher->no_account}}</td>
    </tr>
    <tr>
        <td class="label">Pembayaran (Nilai Transfer)</td><td class="value">Rp.{{$voucher->payment_transfer}}</td>
        <td class="label">Status Bayar</td><td class="value">{{$voucher->payment_status}}</td>
    </tr>
    <tr>
        <td class="label">Jatuh Tempo</td><td class="value">{{$voucher->due_date}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">No. Faktur</td><td class="value">{{$voucher->no_factur}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">Nama Bank</td><td class="value">{{$voucher->bank_name}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">Jenis Pembayaran</td><td class="value">{{$voucher->payment_type}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">Tanggal Bayar</td><td class="value">{{$voucher->payment_date}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">Prioritas</td><td class="value">{{$voucher->priority}}</td>
        <td></td><td></td>
    </tr>
    <tr>
        <td class="label">Keterangan</td><td class="value">{{$voucher->information}}</td>
        <td></td><td></td>
    </tr>
</table>

</body>
</html>
