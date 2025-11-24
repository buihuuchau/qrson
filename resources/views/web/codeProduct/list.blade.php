@extends('web.layouts.main')
@section('title')
    <title>
        Danh sách Mã sản phẩm</title>
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
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('web.shipment.list') }}">Shipment</a></li>
                            @if (!empty(request()->query('document_id')))
                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ route('web.document.list', ['document_id' => request()->query('document_id')]) }}">
                                        Số chứng từ
                                    </a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active">Mã sản phẩm</li>
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
                                <h3 class="card-title mb-3">Danh sách các Mã sản phẩm</h3>
                                <form class="col-md-12 col-sm-12 d-flex row" action="{{ route('web.code-product.list') }}"
                                    method="get">
                                    <input type="hidden" name="draft" value="true">
                                    <div class="col-md-2 col-sm-6">
                                        <label for="shipment_id" class="form-label">Shipment ID</label>
                                        <input id="shipment_id" type="text" class="form-control" name="shipment_id"
                                            value="{{ request()->query('shipment_id') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label for="document_id" class="form-label">Số chứng từ</label>
                                        <input id="document_id" type="text" class="form-control" name="document_id"
                                            value="{{ request()->query('document_id') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label for="codeProduct" class="form-label">Mã sản phẩm</label>
                                        <input id="codeProduct" type="text" class="form-control" name="code_product_id"
                                            value="{{ request()->query('code_product_id') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label for="created_by" class="form-label">Người nhập</label>
                                        <input id="created_by" type="text" class="form-control" name="created_by"
                                            value="{{ request()->query('created_by') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label class="form-label">Thời gian quét từ</label>
                                        <input id="from" type="datetime-local" class="form-control" name="from"
                                            value="{{ request()->query('from') }}"><br>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label class="form-label">Thời gian quét đến</label>
                                        <input id="to" type="datetime-local" class="form-control" name="to"
                                            value="{{ request()->query('to') }}">
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label for="draft" class="form-label">Trạng thái</label>
                                        <select class="form-control" name="draft">
                                            <option value="0"
                                                {{ (request()->query('draft') ?? 0) == 0 ? 'selected' : '' }}>
                                                Dữ liệu thật
                                            </option>
                                            <option value="1"
                                                {{ (request()->query('draft') ?? 0) == 1 ? 'selected' : '' }}>
                                                Dữ liệu tạm
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <label for="scan" class="form-label">Manual</label>
                                        <select class="form-control" name="scan">
                                            <option value="">-- Chọn --</option>
                                            <option value="yes"
                                                {{ request()->query('scan') == 'yes' ? 'selected' : '' }}>
                                                Quét Qr
                                            </option>
                                            <option value="no"
                                                {{ request()->query('scan') == 'no' ? 'selected' : '' }}>
                                                Nhập thủ công
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
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
                                            <th>Mã sản phẩm</th>
                                            <th>Thời gian quét</th>
                                            <th>Người thực hiện</th>
                                            <th>Thực hiện manual</th>
                                            @if (!empty(request()->query('draft')) && request()->query('draft') == 1)
                                                <th>Thao tác</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($codeProducts as $key => $codeProduct)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $codeProduct->shipment_id }}</td>
                                                <td>{{ $codeProduct->document_id }}</td>
                                                <td>{{ $codeProduct->id }}</td>
                                                <td>{{ $codeProduct->created_by }}</td>
                                                <td>{{ $codeProduct->created_at }}</td>
                                                <td>
                                                    @if ($codeProduct->scan == 'no')
                                                        X
                                                    @endif
                                                </td>
                                                @if (!empty(request()->query('draft')) && request()->query('draft') == 1)
                                                    <td>
                                                        <button class="btn btn-danger clearCodeProduct" title="Xóa"
                                                            data-code-product-id="{{ $codeProduct->id }}"><i
                                                                class="fas fa-trash"></i></button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Shipment ID</th>
                                            <th>Số chứng từ</th>
                                            <th>Mã sản phẩm</th>
                                            <th>Thời gian quét</th>
                                            <th>Người thực hiện</th>
                                            <th>Thực hiện manual</th>
                                            @if (!empty(request()->query('draft')) && request()->query('draft') == 1)
                                                <th>Thao tác</th>
                                            @endif
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-end">
                                    {{ $codeProducts->appends($_GET)->links('web.layouts.pagination_vi') }}
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
        $('.clearCodeProduct').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let code_product_id = button.data('code-product-id');
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Mã sản phẩm " + code_product_id + " sẽ bị xóa và không thể khôi phục!",
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
                        url: "{{ route('web.code-product.delete') }}",
                        data: {
                            code_product_id: code_product_id,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            let message = response && response.message ? response.message :
                                'Xóa Mã sản phẩm thành công';
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
