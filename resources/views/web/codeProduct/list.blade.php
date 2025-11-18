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
                        @if (!empty($shipment_id))
                            <h3 class="m-0">
                                Shipment ID: {{ $shipment_id }}
                            </h3>
                        @endif
                        @if (!empty($document))
                            <h3 class="m-0">
                                Số chứng từ: {{ $document->id }}<br>
                                Số lượng hiện tại: {{ $document->total_current }}<br>
                                Số lượng tổng: {{ $document->total }}
                            </h3>
                        @endif
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('web.shipment.list') }}">Shipment</a></li>
                            @if (!empty($document))
                                <li class="breadcrumb-item">
                                    <a href="{{ route('web.document.list', ['document_id' => $document->id]) }}">
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
                                <h3 class="card-title">Danh sách các Mã sản phẩm</h3>
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
                                            @if ((!empty($shipment_id) || !empty($document)) && $document->status == 'pending')
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
                                                <td>{{ $codeProduct->created_at }}</td>
                                                <td>
                                                    @if ((!empty($shipment_id) || !empty($document)) && $document->status == 'pending')
                                                        {{ $codeProduct->user->name }} - {{ $codeProduct->user->phone }}
                                                    @else
                                                        {{ $codeProduct->created_by }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($codeProduct->scan == 'no')
                                                        X
                                                    @endif
                                                </td>
                                                @if ((!empty($shipment_id) || !empty($document)) && $document->status == 'pending')
                                                    <td>
                                                        <button class="btn btn-danger clearCodeProduct"
                                                            title="Xóa" value="{{ $codeProduct->id }}"><i
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
                                            @if ((!empty($shipment_id) || !empty($document)) && $document->status == 'pending')
                                                <th>Thao tác</th>
                                            @endif
                                        </tr>
                                    </tfoot>
                                </table>
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
            let btn = $(this);
            let id = $(this).val();
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Mã sản phẩm " + id + " sẽ bị xóa và không thể khôi phục!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.code-product.delete') }}",
                        data: {
                            id: id,
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
                            btn.closest('tr').remove();
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
                        }
                    });
                }
            });
        });
    </script>
@endsection
