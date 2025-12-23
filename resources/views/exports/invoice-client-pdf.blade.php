<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <style>
        body {
            padding: 20px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }

        .bold { font-weight: bold; }
        .semibold { font-weight: 600; }
        .normal { font-weight: normal; }

        .mb-0 { margin-bottom: 0; }
        .mb-1 { margin-bottom: 5px; }
        .mb-4 { margin-bottom: 20px; }
        .mb-5 { margin-bottom: 30px; }

        .mt-3 { margin-top: 15px; }

        .float-start { float: left; width: 50%; }
        .float-end { float: right; width: 50%; }
        .clearfix::after { content: ""; display: table; clear: both; }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: top;
        }

        .table-no-border td, .table-no-border th {
            border: none;
            padding: 0;
        }

        .border-left {
            border-left: 1px solid #000 !important;
        }

        .border-right {
            border-right: 1px solid #000 !important;
        }

        .remove-border-bottom {
            border-bottom: none !important;
        }

        .table-title {
            font-size: 14px;
        }

        .font-weight-total {
            font-weight: 500;
        }

        .underline {
            text-decoration: underline;
        }

        .signature-space {
            height: 60px;
        }

        .w-80 {
            width: 80%;
        }

        .w-20 {
            width: 20%;
        }
    </style>
    <style>
        .times {
            font-family: 'Times New Roman', Times, serif;
        }
        .float-start { width: 50%; }
        .float-end { width: 50%; }
        .clearfix::after { content: ""; display: table; clear: both; }
        table td, table th { vertical-align: top; }
        .bordered-table td, .bordered-table th { border: 1px solid #000; padding: 3px; }
        .no-border td, .no-border th { border: none; padding: 0px 0px; }
        .title-head-detail,
        .title-head-detail + div,
        .title-client,
        .table-item tbody tr td,
        .font-standart
         {
            font-size: 13px;
            /* font-weight: 900; */
        }
        .title-client {
            font-weight: 600;
        }
        .table-item thead tr th {
            font-size: 14px;
        }
        .remove-border-bottom {
            border: 0px solid #000 !important;

        }
        .border-left {
            border-left: 1px solid #000 !important;
        }
        .border-right {
            border-right: 1px solid #000 !important;
        }
        .font-weight-total {
            font-weight: 500;
        }
        .underline {
            text-decoration: underline;
        }
    </style>
</head>
<body class="p-4">

    <h3 class="times fw-bold text-end mb-5">INVOICE</h3>

    <div class="clearfix mb-3">
        <div class="title-client float-start">
            <p class="mb-0" style="font-size: 12px;"><strong>To :</strong></p>
            <p class="mb-0">PT. PLN (Persero) Unit Induk Pembangkit</p>
            <p class="mb-0">Desa Tubanan Kecamatan Kembang Kabupaten Jepara Jawa Tengah 59457</p>
        </div>
        <div class="float-end">
            <div>
                <div class="title-head-detail float-start">
                    <div><strong>INVOICE NUMBER</strong> <span style="margin-left:70px;">:</span> </div>
                </div>
                <div class="float-end text-end">189/INV.KP/VI/2025</div>
            </div>
            <div>
                <div class="title-head-detail float-start"><strong>INVOICE DATE</strong> <span style="margin-left:94px;">:</span></div>
                <div class="float-end text-end">June 13, 2025</div>
            </div>
            <div>
                <div class="title-head-detail float-start"><strong>PURCHASE ORDER/SPK NO.</strong><span style="margin-left:15px;">:</span></div>
                <div class="float-end text-end">PO ABCDE</div>
            </div>
            <div>
                <div class="title-head-detail float-start"><strong>PURCHASE ORDER DATE</strong><span style="margin-left:36px;">:</span></div>
                <div class="float-end text-end">May 13, 2025</div>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>

    <table class="table-item table bordered-table w-100 mb-4">
        <thead>
            <tr>
                <th style="width: 5%;"><center>No</center></th>
                <th style="width: 70%;"><center>DESCRIPTION</center></th>
                <th style="width: 25%;"><center>AMOUNT</center></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center remove-border-bottom border-left">1</td>
                <td class="remove-border-bottom border-left">Renovasi Kantor Periode April 2025</td>
                <td class="remove-border-bottom border-left border-right">
                    <div class="float-start">Rp.</div>
                    <div class="float-end text-end">10.000</div>
                </td>
            </tr>
            <tr>
                <td class="text-center remove-border-bottom border-left">2</td>
                <td class="remove-border-bottom border-left">Renovasi Kantor Periode April 2025</td>
                <td class="remove-border-bottom border-left border-right">
                    <div class="float-start">Rp.</div>
                    <div class="float-end text-end">10.000</div>
                </td>
            </tr>
            <tr>
                <td class="text-center remove-border-bottom border-left">3</td>
                <td class="remove-border-bottom border-left">Renovasi Kantor Periode April 2025</td>
                <td class="remove-border-bottom border-left border-right">
                    <div class="float-start">Rp.</div>
                    <div class="float-end text-end">10.000</div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="float-start" style="width: 80%">
                    </div>
                    <div class="float-end" style="width: 20%;">
                        <div class="font-weight-total">Sub Total</div>
                        <div class="font-weight-total">DPP Nilai Lain</div>
                        <div class="font-weight-total">PPn</div>
                        <div class="font-weight-total">Total</div>
                    </div>
                </td>
                <td>
                    <div>
                        <div class="float-start font-weight-total">Rp.</div>
                        <div class="float-end text-end font-weight-total">10.000</div>
                    </div>
                    <div>
                        <div class="float-start font-weight-total">Rp.</div>
                        <div class="float-end text-end font-weight-total">9.167</div>
                    </div>
                    <div>
                        <div class="float-start font-weight-total">Rp.</div>
                        <div class="float-end text-end font-weight-total">1.100</div>
                    </div>
                    <div>
                        <div class="float-start font-weight-total">Rp.</div>
                        <div class="float-end text-end font-weight-total">11.100</div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="mb-1 font-standart"><strong>Payment Method :</strong></p>
    <p class="mb-1 font-standart font-weight-total">Bank Transfer to BNI 46 Cabang Jepara</p>
    <p class="mb-4 font-standart font-weight-total">Account Number 00000000 a/n PT. Kalinyamat Perkasa</p>

    <div class="text-end">
        <div class="float-start"></div>
        <div class="float-end text-center">
            <p class="mb-0 font-standart font-weight-total">Semarang, June 13, 2025</p>
            <p class="mb-0 font-standart font-weight-total">PT. Kalinyamat Perkasa</p>
            <div style="height: 60px;"></div>
            <p class="mb-0 font-standart font-weight-total underline">Pra Julinuddin Kotto</p>
            <p class="mb-0 font-standart font-weight-total">President Director</p>
        </div>
    </div>

</body>
</html>
