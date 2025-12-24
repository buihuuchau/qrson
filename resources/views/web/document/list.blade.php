@extends('web.layouts.main')
@section('title')
    <title>
        Danh sách Số chứng từ</title>
@endsection
@section('custom_css')
    {{-- custom-style --}}
@endsection
@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        @if (!empty($shipment_id))
                            <h3 class="m-0">
                                Shipment ID: {{ $shipment_id }}
                            </h3>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('web.shipment.list') }}">Shipment No</a></li>
                            <li class="breadcrumb-item active">Số chứng từ</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-3">Danh sách các Số chứng từ</h3>
                                <form class="col-md-12 col-sm-12 d-flex row" action="{{ route('web.document.list') }}"
                                    method="get">
                                    <div class="col-md-3 col-sm-6">
                                        <label for="shipment_id" class="form-label">Shipment No</label>
                                        <input id="shipment_id" type="text" class="form-control" name="shipment_id"
                                            value="{{ request()->query('shipment_id') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="document_id" class="form-label">Số chứng từ</label>
                                        <input id="document_id" type="text" class="form-control" name="document_id"
                                            value="{{ request()->query('document_id') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">Thời gian nhập từ</label>
                                        <input id="from" type="datetime-local" class="form-control" name="from"
                                            value="{{ request()->query('from') }}"><br>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">Thời gian nhập đến</label>
                                        <input id="to" type="datetime-local" class="form-control" name="to"
                                            value="{{ request()->query('to') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="created_by" class="form-label">Người nhập</label>
                                        <input id="created_by" type="text" class="form-control" name="created_by"
                                            value="{{ request()->query('created_by') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="status" class="form-label">Trạng thái</label>
                                        <select class="form-control" name="status">
                                            <option value="" disabled selected>-- Chọn --</option>
                                            <option value="pending"
                                                {{ request()->query('status') == 'pending' ? 'selected' : '' }}>
                                                Chưa xong
                                            </option>
                                            <option value="done"
                                                {{ request()->query('status') == 'done' ? 'selected' : '' }}>
                                                Xong
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                    </div>
                                </form>
                            </div>

                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Shipment No</th>
                                            <th>Số chứng từ</th>
                                            <th>Số mã đã nhập</th>
                                            <th>Số mã tất cả</th>
                                            <th>Người nhập</th>
                                            <th>Thời gian nhập</th>
                                            <th>Trạng thái</th>
                                            <th>Ghi chú</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($documents as $key => $document)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $document->shipment_id }}</td>
                                                <td>{{ $document->id }}</td>
                                                <td>{{ $document->total_current }}</td>
                                                <td>{{ $document->total }}</td>
                                                <td>{{ $document->created_by }}</td>
                                                <td>{{ $document->created_at }}</td>
                                                <td class="tbl_status">
                                                    @if ($document->status == 'pending')
                                                        @php
                                                            $draft = 1;
                                                        @endphp
                                                        Chưa xong
                                                    @else
                                                        @php
                                                            $draft = 0;
                                                        @endphp
                                                        Đã xong
                                                    @endif
                                                </td>
                                                <td class="tbl_note">{{ $document->note }}</td>
                                                <td>
                                                    <a class="btn btn-primary detailDocument" title="Chi tiết"
                                                        href="{{ route('web.code-product.list', ['shipment_id' => $document->shipment_id, 'document_id' => $document->id, 'draft' => $draft]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($document->status != 'done')
                                                        @if ($document->total_current >= $document->total * config('app.percent_done'))
                                                            <button class="btn btn-success confirmDocument"
                                                                title="Phê duyệt" data-document-id="{{ $document->id }}"><i
                                                                    class="fas fa-check"></i></button>
                                                        @endif
                                                        <button class="btn btn-danger clearDocument" title="Xóa"
                                                            data-document-id="{{ $document->id }}"><i
                                                                class="fas fa-trash"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>STT</th>
                                            <th>Shipment No</th>
                                            <th>Số chứng từ</th>
                                            <th>Số mã đã nhập</th>
                                            <th>Số mã tất cả</th>
                                            <th>Người nhập</th>
                                            <th>Thời gian nhập</th>
                                            <th>Trạng thái</th>
                                            <th>Ghi chú</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-end">
                                    {{ $documents->appends($_GET)->links('web.layouts.pagination_vi') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('custom_script')
    <script>
        $('.confirmDocument').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let document_id = button.data('document-id');
            let note_value = button.closest('tr').find('.tbl_note').text();
            Swal.fire({
                title: "Xác nhận phê duyệt?",
                text: "Số chứng từ:  " + document_id,
                icon: "info",
                input: "textarea",
                inputLabel: "Ghi chú phê duyệt",
                inputValue: note_value + " - Admin đã xem và duyệt.",
                inputAttributes: {
                    maxlength: 255
                },
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return "Vui lòng nhập ghi chú!";
                    }
                },
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Phê duyệt",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingOverlay').css('display', 'flex');
                    let note_input = result.value;
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.document.confirm') }}",
                        data: {
                            document_id: document_id,
                            note: note_input,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.status_code == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Thành công",
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                button.closest('tr').find('.tbl_status').text('Đã xong');
                                button.closest('tr').find('.tbl_note').text(note_input);
                                let detailDocumentLink = button.closest('tr').find('.detailDocument');
                                let href = detailDocumentLink.attr('href');
                                href = href.replace(/draft=\d+/g, 'draft=0');
                                detailDocumentLink.attr('href', href);
                                button.closest('td').find('.clearDocument').remove();
                                button.remove();
                                $('#loadingOverlay').hide();
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Thất bại",
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#loadingOverlay').hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Đã có lỗi xảy ra.';
                            Swal.fire({
                                icon: "error",
                                title: "Lỗi",
                                text: message,
                            });
                            $('#loadingOverlay').hide();
                        }
                    });
                }
            });
        });
        $('.clearDocument').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let document_id = button.data('document-id');
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Số chứng từ:  " + document_id +
                    " và các Mã sản phẩm đi kèm sẽ bị xóa và không thể khôi phục!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingOverlay').css('display', 'flex');
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.document.delete') }}",
                        data: {
                            document_id: document_id,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            if (response.status_code == 200) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Thành công",
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                button.closest('tr').remove();
                                $("#example1 tbody tr").each(function(index) {
                                    $(this).find("td:first").text(index + 1);
                                });
                                $('#loadingOverlay').hide();
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Thất bại",
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#loadingOverlay').hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Đã có lỗi xảy ra.';
                            Swal.fire({
                                icon: "error",
                                title: "Lỗi",
                                text: message,
                            });
                            $('#loadingOverlay').hide();
                        }
                    });
                }
            });
        });
    </script>
@endsection
