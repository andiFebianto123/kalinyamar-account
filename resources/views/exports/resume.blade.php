<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>{{$title}}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      /* margin: 20px; */
      color: #212529;
    }

    .mt-2 { margin-top: 12px; }
    .mt-3 { margin-top: 18px; }
    .fs-6 { font-size: 14px; font-weight: bold; }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }

    th, td {
      padding: 6px 8px;
      text-align: left;
    }

    table.table-borderless th,
    table.table-borderless td {
      border: none;
    }

    /* Warna custom */
    .bg-navy {
      background-color: #003e6b;
      color: white;
    }

    .bg-light-actual {
      background-color: #ededed;
      color: black;
    }

    .bg-gray-actual {
      background-color: #bebebe;
      color: black;
    }

    .bg-danger {
      background-color: #dc3545;
      color: white;
    }

    .bg-status-red {
      background-color: #dc3545;
      color: white;
    }

    .bg-status-blue {
      background-color: #0d6efd;
      color: white;
    }

    .due-date {
      color: red;
      font-weight: bold;
      font-size: 11px;
    }

    .section-title {
      font-weight: bold;
      margin-top: 16px;
      margin-bottom: 8px;
      font-size: 13px;
    }

    /* Aturan khusus DOMPDF */
    thead {
      display: table-header-group;
    }
    tfoot {
      display: table-row-group;
    }
    tr {
      page-break-inside: avoid;
    }
  </style>
</head>
<body>

  <center><h3>{{$title}}</h3></center>
  <div class="mt-2">

    <div class="fs-6"><strong>KATEGORI TAGIHAN</strong></div>

    <!-- 1 -->
    <div><strong>1. TAGIHAN YANG BELUM DIBAYAR</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items['list']['invoice_1'] as $key => $item_1)
            <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
          @endforeach
          <tr class="bg-light-actual">
            <td></td>
            <td></td>
            <td><strong>{{ $items['list']['invoice_1_total_str'] }}</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 2 -->
    <div class="mt-2"><strong>2. TAGIHAN TERTUNDA (RUTIN)</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($items['list']['invoice_2'] as $key => $item_1)
                <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
            @endforeach
            <tr class="bg-light-actual">
                <td></td>
                <td></td>
                <td><strong>{{ $items['list']['invoice_2_total_str'] }}</strong></td>
            </tr>
        </tbody>
      </table>
    </div>

    <!-- 3 -->
    <div class="mt-2"><strong>3. TAGIHAN TERTUNDA (PROJECT)</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($items['list']['invoice_3'] as $key => $item_1)
                <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
            @endforeach
            <tr class="bg-light-actual">
                <td></td>
                <td></td>
                <td><strong>{{ $items['list']['invoice_3_total_str'] }}</strong></td>
            </tr>
        </tbody>
      </table>
    </div>

    <!-- 4 -->
    <div class="mt-2"><strong>4. TAGIHAN RETENSI</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($items['list']['invoice_4'] as $key => $item_1)
                <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
            @endforeach
            <tr class="bg-light-actual">
                <td></td>
                <td></td>
                <td><strong>{{ $items['list']['invoice_4_total_str'] }}</strong></td>
            </tr>
        </tbody>
      </table>
    </div>

    <!-- 5 -->
    <div class="mt-2"><strong>5. PEKERJAAN YANG BELUM SELESAI (RUTIN)</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($items['list']['invoice_5'] as $key => $item_1)
                <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
            @endforeach
            <tr class="bg-light-actual">
                <td></td>
                <td></td>
                <td><strong>{{ $items['list']['invoice_5_total_str'] }}</strong></td>
            </tr>
        </tbody>
      </table>
    </div>

    <!-- 6 -->
    <div class="mt-2"><strong>6. PEKERJAAN YANG BELUM SELESAI (PROJECT)</strong></div>
    <div class="table-responsive mt-2">
      <table class="table-borderless">
        <thead class="bg-light-actual">
          <tr>
            <th style="width:4%;">No.</th>
            <th style="width:26%;">Nama Perusahaan</th>
            <th style="width:70%;">Nominal Include PPn</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($items['list']['invoice_6'] as $key => $item_1)
                <tr><td>{{ $key + 1 }}</td><td>{{ $item_1->name }}</td><td>{{ $item_1->price_total_include_ppn_str }}</td></tr>
            @endforeach
            <tr class="bg-light-actual">
                <td></td>
                <td></td>
                <td><strong>{{ $items['list']['invoice_6_total_str'] }}</strong></td>
            </tr>
        </tbody>
      </table>
    </div>

    <!-- Grand Total -->
    <div class="table-responsive mt-3">
      <table class="table-borderless">
        <thead class="bg-navy">
          <tr>
            <th colspan="2" style="width:30%;">GRAND TOTAL</th>
            <th style="width:70%;">{{ $items['grand_total'] }}</th>
          </tr>
        </thead>
      </table>
    </div>

  </div>

</body>
</html>
