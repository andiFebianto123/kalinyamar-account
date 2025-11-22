<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            padding: 0px;
        }

        .header-table, .detail-table, .section-title, .section-title-nominal {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }

        .voucher-title {
            background: #dce9f7;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            padding: 6px;
            border: 1px solid #000;
        }

        .info-table td {
            padding: 6px;
            /* border: 1px solid #000; */
        }

        .info-table{
            border-top: 1px solid #000;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .section-title {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .section-detail {
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            /* border-bottom: 1px solid #000; */
            padding: 7px;
        }

        .section-title td {
            padding: 8px 6px 8px 6px;
            /* border: 1px solid #000; */
            background: #dce9f7;
            font-weight: bold;
        }

        .section-title-nominal td {
            border: 1px solid #000;
            background: #dce9f7;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .float-left { float: left; }
        .float-right { float: right; }
        .clear { clear: both; }

        .row { width: 100%; overflow: hidden; margin-bottom: 5px; }
        .left { float: left; width: 52%; }
        .right { float: right; width: 45%; }
        .label { float: left; width: 140px; }
        .colon { float: left; width: 15px; }
        .value { float: left; }

        .title-section {
            font-weight: bold;
            margin: 10px 0 5px 0;
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 30%; text-align: center;">
                <img src="{{ public_path('kalinyamat-logo-export.jpeg') }}" alt="" style="height: 40px;">
            </td>
            <td style="width: 40%; text-align: center; font-size: 22px;">
                <b>Nama Rek.</b>
            </td>
            <td style="width: 30%; text-align: center;">
                <small>50102 - Biaya Sewa dan Jasa (tdk final)</small>
            </td>
        </tr>
    </table>

    <!-- TITLE -->
    <div class="voucher-title">Voucher Pembayaran</div>

    <!-- INFO SECTION -->
    <table width="100%" class="info-table" border="0" cellspacing="0" cellpadding="0" style="font-size:12px;">
        <tr>
            <td style="width: 20%;">Dibayarkan Kepada</td>
            <td style="width: 30%;">: CV. ANUGRAH CIPTA NADJWA</td>
            <td style="width: 20%;">No. Rekening</td>
            <td style="width: 30%;">: 11111111</td>
        </tr>

        <tr>
            <td>NPWP</td>
            <td>: 000000000000</td>
            <td>A/N</td>
            <td>: CV. ANUGRAH CIPTA NADJWA</td>
        </tr>

        <tr>
            <td>Alamat</td>
            <td>: Jl Raya Jepara</td>
            <td>Bank</td>
            <td>: Mandiri</td>
        </tr>
    </table>

    <table class="section-title">
        <tr>
            <td style="width: 20%;">KDP005</td>
            <td style="width: 30%;">: ASH YARD MANAGEMENT</td>
            <td style="width: 20%;"></td>
            <td style="width: 30%;"></td>
        </tr>
    </table>

    <div class="section-detail">
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size:12px;">
            <tr>
                <td style="width:440px;" valign="top">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="20%">No. Pengajuan Pembayaran</td>
                            <td width="5%">:</td>
                            <td>979</td>
                        </tr>
                        <tr>
                            <td>Deskripsi Pembayaran</td>
                            <td>:</td>
                            <td>RENTAL 3 TRUK TANGKI BESERTA DRIVER PERIODE NOVEMBER 2024 - PLN TJB PLN NPS</td>
                        </tr>
                        <tr>
                            <td>PO / SPK</td>
                            <td>:</td>
                            <td>004/SPK.KP/I/24</td>
                        </tr>
                        <tr>
                            <td>Tgl PO / SPK</td>
                            <td>:</td>
                            <td>2 Januari 2024</td>
                        </tr>
                    </table>
                </td>
                <td style="" valign="top">
                    <table border="0" cellspacing="0" cellpadding="0" style="width: 330px;">
                        <tr>
                            <td width="40%">Tgl Terima Tagihan</td>
                            <td width="5%">:</td>
                            <td wdidth="30%">8 Januari 2025</td>
                        </tr>
                        <tr>
                            <td>No. Voucher</td>
                            <td>:</td>
                            <td>115/VCH</td>
                        </tr>
                        <tr>
                            <td>Tgl Voucher</td>
                            <td>:</td>
                            <td>30 Januari 2025</td>
                        </tr>
                        <tr>
                            <td>Jatuh Tempo Tagihan</td>
                            <td>:</td>
                            <td>30 Januari 2025</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table style="padding-top:12px;" width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; font-size: 12px;">
            <tr>
                <td style="width: 340px;">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="width: 90px;"><span style="border-bottom: 1px solid black;"><strong>Tagihan</strong></span></td>
                            <td style="width: 15px;"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Nomor</td>
                            <td>:</td>
                            <td>28/I/ACN/2025</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>06 Januari 2025</td>
                        </tr>
                        <tr>
                            <td>Nilai Tagihan</td>
                            <td>:</td>
                            <td>Rp 29.081.634</td>
                        </tr>
                        <tr>
                            <td>PPn</td>
                            <td>:</td>
                            <td>Rp 12.000.500</td>
                        </tr>
                        <tr>
                            <td style="padding-top:20px;"></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Total Tagihan</td>
                            <td>:</td>
                            <td>23.000.000</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td style="width: 120px;"><span style="border-bottom: 1px solid black;"><strong>Potongan</strong></span></td>
                            <td style="width: 70px;"><span style="border-bottom: 1px solid black; text-align: center;"><strong>Tarif PPh</strong></span></td>
                            <td style="width: 15px;"></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>PPh Pasal 23</td>
                            <td><center>2%</center></td>
                            <td>:</td>
                            <td>Rp 230.000.000</td>
                        </tr>
                        <tr>
                            <td>PPh Pasal 4 (2)</td>
                            <td><center>10%</center></td>
                            <td>:</td>
                            <td>Rp 20.000.000</td>
                        </tr>
                        <tr>
                            <td>PPh Pasal 21</td>
                            <td><center></center></td>
                            <td>:</td>
                            <td>Rp 12.000.000</td>
                        </tr>
                        <tr>
                            <td>Status Faktur</td>
                            <td></td>
                            <td>:</td>
                            <td><center>ADA</center></td>
                        </tr>
                        <tr>
                            <td>No. Faktur</td>
                            <td></td>
                            <td>:</td>
                            <td>010.009-25.39269585</td>
                        </tr>
                        <tr>
                            <td>Tgl. Faktur</td>
                            <td></td>
                            <td>:</td>
                            <td>06 Januari 2025</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <table class="section-title-nominal">
        <tr>
            <td style="font-weight: bold; width: 75%;">
                <center>Total Pembayaran</center>
            </td>
            <td rowspan="2"><center><span>Rp</span> <strong>31.698.981,06</strong></center></td>
        </tr>
        <tr>
            <td><center>(Tiga Puluh Satu Juta Enam Ratus Sembilan Puluh Delapan Ribu Sembilan Ratus Delapan Puluh Satu Rupiah)</center></td>
        </tr>
    </table>

    <div class="section-footer">
        <table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; font-family: Arial; font-size: 13px;">
            <tr>
                <!-- Kolom Keterangan -->
                <td width="50%" valign="top" height="130">
                    <b>Keterangan :</b><br>
                    - Belum termasuk lembur
                </td>

                <!-- Kolom Tanda Tangan -->
                <td width="50%" valign="top" align="center">

                    <table width="80%" border="0" align="center" style="font-size: 12px;">
                        <tr>
                            <td align="center">
                                <div style="height: 110px; padding-top:20px;">
                                </div>
                                <div>
                                    <span style="border-bottom: 1px solid black;">Ida Noorma Z</span> <br>
                                    <span style="font-size: 11px;">Verifikator</span>
                                </div>
                            </td>
                            <td align="center">
                                <div style="height: 100px; padding-top:20px;">
                                    <div style="font-size: 12px;">
                                        Telah dibayarkan Pada <br> Tanggal
                                    </div>
                                    <div style="
                                        width: 180px;
                                        height: 25px;
                                        box-sizing: border-box;
                                        border: 1px solid #ccc;
                                        background: #e8eef9;
                                        margin: 10px auto;
                                        text-align: center;
                                        padding-top: 8px;
                                    ">
                                        06 Februari 2025
                                    </div>
                                </div>
                                <div>
                                    <span style="border-bottom: 1px solid black;">Ibnu Hajar</span> <br>
                                    <span style="font-size: 11px;">Mgr. Keuangan</span>
                                </div>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
        <table width="100%" style="font-family: Arial; font-size: 13px;">
            <tr>
                <td width="20%">
                    <span><i>Cetak Tanggal</i></span>
                </td>
                <td width="30%">
                    <i>sesuai tgl cetak voucher</i>
                </td>
                <td width="50%">
                    <span style="padding-left: 12px;">Status Bayar &nbsp;</span> : &nbsp; <span>BELUM BAYAR</span>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>