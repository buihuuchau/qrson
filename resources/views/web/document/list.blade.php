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
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        @if (!empty($shipment_id))
                            <h3 class="m-0">
                                Shipment ID: {{ $shipment_id }}
                            </h3>
                        @endif
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('web.shipment.list') }}">Shipment</a></li>
                            <li class="breadcrumb-item active">Số chứng từ</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
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
                                        <label for="shipment_id" class="form-label">Shipment ID</label>
                                        <input id="shipment_id" type="text" class="form-control" name="shipment_id"
                                            value="{{ request()->query('shipment_id') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="document_id" class="form-label">Số chứng từ</label>
                                        <input id="document_id" type="text" class="form-control" name="document_id"
                                            value="{{ request()->query('document_id') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">Thời gian quét từ</label>
                                        <input id="from" type="datetime-local" class="form-control" name="from"
                                            value="{{ request()->query('from') }}"><br>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label">Thời gian quét đến</label>
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
                                                Chưa hoàn thành
                                            </option>
                                            <option value="done"
                                                {{ request()->query('status') == 'done' ? 'selected' : '' }}>
                                                Đã hoàn thành
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                    </div>
                                </form>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Shipment ID</th>
                                            <th>Số chứng từ</th>
                                            <th>Số mã đã quét</th>
                                            <th>Số mã tất cả</th>
                                            <th>Người nhập</th>
                                            <th>Thời gian nhập</th>
                                            <th>Trạng thái</th>
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
                                                <td>
                                                    @if ($document->status == 'pending')
                                                        @php
                                                            $draft = 1;
                                                        @endphp
                                                        Chưa hoàn thành
                                                    @else
                                                        @php
                                                            $draft = 0;
                                                        @endphp
                                                        Đã hoàn thành
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-primary" title="Chi tiết"
                                                        href="{{ route('web.code-product.list', ['shipment_id' => $document->shipment_id, 'document_id' => $document->id, 'draft' => $draft]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($document->status != 'done')
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
                                            <th>Số thứ tự</th>
                                            <th>Shipment ID</th>
                                            <th>Số chứng từ</th>
                                            <th>Số mã đã quét</th>
                                            <th>Số mã tất cả</th>
                                            <th>Người nhập</th>
                                            <th>Thời gian nhập</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-end">
                                    {{ $documents->appends($_GET)->links('web.layouts.pagination_vi') }}
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
@endsection
@section('custom_script')
    <script>
        $('.clearDocument').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let document_id = button.data('document-id');
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Số chứng từ:  " + document_id +
                    " và các mã sản phẩm đi kèm sẽ bị xóa và không thể khôi phục!",
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
                            let message = response && response.message ? response.message :
                                'Xóa Số chứng từ thành công';
                            Swal.fire({
                                icon: "success",
                                title: "Thành công",
                                text: message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            button.closest('tr').remove();
                            $("#example1 tbody tr").each(function(index) {
                                $(this).find("td:first").text(index + 1);
                            });
                            $('#loadingOverlay').hide();
                        },
                        error: function(xhr, status, error) {
                            let message = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Đã có lỗi xảy ra';
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
