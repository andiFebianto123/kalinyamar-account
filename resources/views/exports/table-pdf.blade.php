<!DOCTYPE html>
<html>
<head>
    <title>{{$title}}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px;}
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
    <h3 style="text-align:center;">{{$title}}</h3>
    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th>{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    @foreach($columns as $column)
                        <td>{{ $item->{$column['name']} }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
