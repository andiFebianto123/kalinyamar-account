<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Laba Rugi</title>
    <style>
        /* Gaya dasar untuk keseluruhan halaman */
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        /* Kontainer utama untuk mengatur lebar dan posisi */
        /* .container {
            width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #000;
        } */

        /* Gaya untuk judul laporan */
        .report-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .report-header h1 {
            margin: 0;
            font-size: 18px;
        }

        .report-header h2 {
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }

        /* Gaya untuk tabel utama */
        .report-table {
            width: 100%;
            border-collapse: collapse; /* Menyatukan border sel */
            /* border: 1px solid #000; */
            font-size: 16px;
        }

        .report-table th,
        .report-table td {
            /* border: 1px solid #000; */
            padding: 8px 12px;
            vertical-align: top;
        }

        /* Tabel bersarang untuk rincian biaya */
        .nested-table {
            width: 100%;
            border-collapse: collapse;
        }

        .nested-table td {
            border: none; /* Hilangkan semua border di tabel dalam */
            padding: 4px 0;
        }

        /* Menambahkan garis bawah pada setiap item biaya */
        .cost-item td {
            /* border-bottom: 1px solid #eee; */
        }

        /* Baris Total Biaya dengan garis atas */
        .total-row td {
            /* border-top: 1px solid #000; */
            padding-top: 12px;
            font-weight: bold;
        }

        /* Kelas utilitas untuk styling */
        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        /* Menghilangkan padding dari sel yang berisi tabel lain */
        .no-padding {
            padding: 0;
        }

        /* Memberi indentasi pada rincian biaya */
        .indent {
            /* padding-left: 30px !important; */
        }

    </style>
</head>
<body>

    <div class="container">
        <div class="report-header">
            <h1><b>LAPORAN LABA RUGI</b></h1>
            <h2><b>({{$profit_lost->clientPo->job_name}})</b></h2>
            <h2>({{$profit_lost->clientPo->category}})</h2>
        </div>

        <table class="report-table">
            <tbody>
                <tr>
                    <td width="60%"><span class="bold">A. Pendapatan PO (exc PPn)</span></td>
                    <td class="text-right bold">{{$report['price_po_excl_ppn']}}</td>
                </tr>

                <tr>
                    <td colspan="2">
                        <table class="nested-table">
                            <tr>
                                <td colspan="2" class="bold">B. Biaya Pekerjaan</td>
                            </tr>
                            <tr>
                                <td><center>Jenis Biaya</center></td>
                                <td><center>Nilai (Rp)</center></td>
                            </tr>
                            <tr>
                                <td class="indent">Material</td>
                                <td class="text-right">{{$report['price_material']}}</td>
                            </tr>
                            <tr>
                                <td class="indent">Biaya Subkont</td>
                                <td class="text-right">{{$report['price_subkon']}}</td>
                            </tr>
                            <tr>
                                <td class="indent">Biaya Tenaga Kerja Langsung (BTKL)</td>
                                <td class="text-right">{{$report['price_btkl']}}</td>
                            </tr>
                             <tr>
                                <td class="indent">Biaya lainnya</td>
                                <td class="text-right">{{$report['price_other']}}</td>
                            </tr>
                             <tr>
                                <td class="indent">Biaya lewat Tahun</td>
                                <td class="text-right">{{$report['price_profit_lost_project']}}</td>
                            </tr>
                            <tr class="total-row">
                                <td class="bold">Total Biaya</td>
                                <td class="text-right bold">{{$report['price_total']}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="total-row">
                    <td><span class="bold">C. Laba Rugi PO</span></td>
                    <td class="text-right bold">{{$report['price_profit_lost_po']}}</td>
                </tr>

                <tr class="total-row">
                    <td><span class="bold">D. Beban Umum</span></td>
                    <td class="text-right bold">{{$report['price_general']}}</td>
                </tr>

                <tr class="total-row">
                    <td><span class="bold">E. Laba Akhir</span></td>
                    <td class="text-right bold">{{$report['price_profit_final']}}</td>
                </tr>
            </tbody>
        </table>

    </div>

</body>
</html>
