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
                                <h3 class="card-title">Danh sách các Số chứng từ</h3>
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
                                                <td>{{ $document->created_at }}</td>
                                                <td>
                                                    @if ($document->status == 'pending')
                                                        Chưa hoàn thành
                                                    @else
                                                        Đã hoàn thành
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-primary" title="Chi tiết"
                                                        href="{{ route('web.code-product.list', ['shipment_id' => $document->shipment_id, 'document_id' => $document->id]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if ($document->status == 'pending')
                                                        <button class="btn btn-danger clearDocument" title="Xóa"
                                                            value="{{ $document->id }}"><i
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
                                            <th>Thời gian nhập</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
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
        $('.clearDocument').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            let id = $(this).val();
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Số chứng từ:  " + id +
                    " và các mã sản phẩm đi kèm sẽ bị xóa và không thể khôi phục!",
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
                        url: "{{ route('web.document.delete') }}",
                        data: {
                            id: id,
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
