<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            font-weight: bold;
            background-color: #eee;
        }
    </style>
</head>
<body>
    <h3 style="text-align:center;">{{trans('backpack::crud.po.export.pdf.title_header')}}</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>{{trans('backpack::crud.subkon.column.name')}}</th>
                <th>{{trans('backpack::crud.po.column.po_number')}}</th>
                <th>{{trans('backpack::crud.po.column.date_po')}}</th>
                <th>{{trans('backpack::crud.po.column.job_name')}}</th>
                <th>{{trans('backpack::crud.po.column.job_description')}}</th>
                <th>{{trans('backpack::crud.po.column.job_value')}}</th>
                <th>{{trans('backpack::crud.po.column.tax_ppn')}}</th>
                <th>{{trans('backpack::crud.po.column.total_value_with_tax')}}</th>
                <th>{{trans('backpack::crud.po.column.due_date')}}</th>
                <th>{{trans('backpack::crud.po.column.status')}}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->subkon->name ?? '-' }}</td>
                <td>{{ $item->po_number ?? '-' }}</td>
                <td>{{ $item->date_po ? \Carbon\Carbon::parse($item->date_po)->translatedFormat('d F Y') : '-' }}</td>
                <td>{{ $item->job_name ?? '-' }}</td>
                <td>{{ $item->job_description ?? '-' }}</td>
                <td>Rp. {{ number_format($item->job_value ?? 0) }}</td>
                <td>{{ $item->tax_ppn ?? 0 }} %</td>
                <td>Rp. {{ number_format($item->total_value_with_tax ?? 0) }}</td>
                <td>{{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->translatedFormat('d F Y') : '-' }}</td>
                <td>{{ strtoupper($item->status ?? '-') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
