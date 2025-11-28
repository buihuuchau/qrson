<table>
    <thead>
        <tr>
            <th>Shipment No</th>
            <th>Số chứng từ</th>
            <th>Product Code (QR trên thùng sơn, 27 kí tự)</th>
            <th>Thời gian quét</th>
            <th>Người thực hiện</th>
            <th>Thực hiện manual</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            <tr>
                <td>&nbsp;{{ $item->shipment_id }}</td>
                <td>&nbsp;{{ $item->document_id }}</td>
                <td>&nbsp;{{ $item->id }}</td>
                <td>{{ $item->created_at }}</td>
                <td>{{ $item->created_by }}</td>
                <td>
                    @if ($item->scan == 'no')
                        X
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
