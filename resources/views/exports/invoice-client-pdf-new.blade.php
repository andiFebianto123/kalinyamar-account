<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .times {
            font-family: "Times New Roman", Times, serif !important;
            font-size: 20px;
            font-weight: 500;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .mb-0 { margin:0px !important; }
        .mb-1 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 20px; }

        .float-start { float: left; width: 50%; }
        .float-end { float: right; width: 50%; }
        .clearfix::after { content: ""; display: table; clear: both; }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            font-family: Arial, Helvetica, sans-serif !important;
            border: 1px solid #000;
            padding: 2px;
            vertical-align: top;
        }

        table td {
            padding:2px !important;
        }

        .no-border td {
            border: none;
            padding: 2px 0;
        }

        .signature {
            margin-top: 60px;
        }

        .underline {
            text-decoration: underline;
        }
        .font-default {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 13px;
        }
        .font-bold {
            font-weight: 600;
        }
        .table-header tr td {
            font-family: Arial, Helvetica, sans-serif !important;
            font-size: 11px !important;
            padding-left: 12px;
        }
        .remove-border {
            border: 0px solid #000 !important;
        }
        .border-item {
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }
        .border-top {
            border-top: 1px solid #000 !important;
        }
        .border-bottom {
            border-bottom: 1px solid #000 !important;
        }
    </style>
</head>
<body>

    <h3 class="times text-right fw-bold">INVOICE</h3>

    <div class="clearfix mb-4 font-default">
        <div class="float-start" style="width:47%">
            <p class="mb-0"><strong>To :</strong></p>
            <p class="mb-0 font-bold">{{$header->client_po->client->name}}</p>
            <p class="mb-0 font-bold">{{$header->client_po->client->address}}</p>
            {{-- <p class="mb-0">Jawa Tengah 59457</p> --}}
        </div>
        <div class="float-end">
            <table class="table-header no-border" style="width: 100%;">
                <tr><td style="width: 60%;" class="text-right">INVOICE NUMBER :</td><td class="text-right">{{$header->invoice_number}}</td></tr>
                <tr><td class="text-right">INVOICE DATE :</td><td class="text-right"> {{Carbon\Carbon::parse($header->invoice_date)->translatedFormat('F j, Y')}}</td></tr>
                <tr><td class="text-right">PURCHASE ORDER/SPK NO. :</td><td class="text-right"> {{$header->client_po->po_number}}</td></tr>
                <tr><td class="text-right">PURCHASE ORDER DATE :</td><td class="text-right"> {{Carbon\Carbon::parse($header->client_po->date_invoice)->translatedFormat('F j, Y')}}</td></tr>
            </table>
        </div>
    </div>

    <table class="mb-4">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 70%;">DESCRIPTION</th>
                <th style="width: 25%;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_item = $details->count();
                $total_price = 0;
            @endphp
            @foreach ($details as $key => $item)
                @php
                    $iteration = $key + 1;
                    $total_price += (int) $item->price;
                @endphp
                <tr>
                    <td class="text-center remove-border border-item">{{$iteration}}</td>
                    <td class="remove-border border-item">{{$item->name}}</td>
                    <td class="remove-border border-item">
                        <div class="clearfix">
                            <div class="float-start">Rp.</div>
                            <div class="float-end text-right">{{\App\Http\Helpers\CustomHelper::formatRupiah($item->price)}}</div>
                        </div>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2" class="text-right remove-border border-top border-item">Sub Total</td>
                <td class="remove-border border-top border-item">
                    <div class="clearfix">
                        <div class="float-start">Rp.</div>
                        <div class="float-end text-right">{{\App\Http\Helpers\CustomHelper::formatRupiah($details->sum('price'))}}</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="text-right remove-border border-item">DPP Nilai Lain</td>
                <td class="remove-border border-item">
                    <div class="clearfix">
                        <div class="float-start">Rp.</div>
                        <div class="float-end text-right">{{\App\Http\Helpers\CustomHelper::formatRupiah($header->price_dpp)}}</div>
                    </div>
                </td>
            </tr>
            <tr>
                @php
                    $price_ppn = (int) $header->price_total_include_ppn - $header->price_total_exclude_ppn;
                @endphp
                <td colspan="2" class="text-right remove-border border-item">PPn</td>
                <td class="remove-border border-item">
                    <div class="clearfix">
                        <div class="float-start">Rp.</div>
                        <div class="float-end text-right">{{\App\Http\Helpers\CustomHelper::formatRupiah($price_ppn)}}</div>
                    </div>
                </td>
            </tr>
            <tr>
                @php
                    $total_price = (int) $total_price + $header->price_dpp + $price_ppn;
                @endphp
                <th colspan="2" class="text-right remove-border border-item border-bottom">Total</th>
                <th class="remove-border border-item border-bottom">
                    <div class="clearfix">
                        <div class="float-start text-left">Rp.</div>
                        <div class="float-end text-right">{{\App\Http\Helpers\CustomHelper::formatRupiah($total_price)}}</div>
                    </div>
                </th>
            </tr>
        </tbody>
    </table>

    <p class="mb-1"><strong>Payment Method :</strong></p>
    <p class="mb-0">Bank Transfer to BNI 46 Cabang Jepara</p>
    <p class="mb-0">Account Number 00000000 a/n PT. Kalinyamat Perkasa</p>

    <div class="clearfix">
        <div class="float-start" style="width: 50%;"></div>
        <div class="float-end text-center" style="width: 50%;">
            <p class="mb-1">Semarang, {{\Carbon\Carbon::parse(now())->translatedFormat('F j, Y')}}</p>
            <p class="mb-0">PT. Kalinyamat Perkasa</p>
            <div class="signature"></div>
            <p class="mb-0 underline">Pra Julinuddin Kotto</p>
            <p class="mb-0">President Director</p>
        </div>
    </div>

</body>
</html>
