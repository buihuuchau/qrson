@extends('web.layouts.main')
@section('title')
    <title>Danh sách Shipment</title>
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
                        <h1 class="m-0">Shipment</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Shipment</a></li>
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
                                <h3 class="card-title">Danh sách các Shipment ID</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Shipment ID</th>
                                            <th>Người quét</th>
                                            <th>Thời gian quét</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shipments as $key => $shipment)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $shipment->id }}</td>
                                                <td>{{ $shipment->created_by }}</td>
                                                <td>{{ $shipment->created_at }}</td>
                                                <td class="d-flex">
                                                    <a class="btn btn-primary mr-5" title="Chi tiết"
                                                        href="{{ route('web.document.list', ['shipment_id' => $shipment->id]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form class="mr-5" action="{{ route('web.shipment.export') }}"
                                                        method="post">
                                                        @csrf
                                                        <input type="hidden" name="shipment_id"
                                                            value="{{ $shipment->id }}">
                                                        <button class="btn btn-success" type="submit" title="Xuất file">
                                                            <i class="fas fa-file-export"></i>
                                                        </button>
                                                    </form>
                                                    @if ($shipment->status == 'pending' && $shipment->document->count() == 0)
                                                        <button class="btn btn-danger clearShipment" title="Xóa"
                                                            value="{{ $shipment->id }}"><i
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
                                            <th>Người quét</th>
                                            <th>Thời gian quét</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-end">
                                    {{ $shipments->appends($_GET)->links('web.layouts.pagination_vi') }}
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
        $('.clearShipment').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            btn.prop('disabled', true);
            let shipment_id = $(this).val();
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Shipment ID:  " + shipment_id + " sẽ bị xóa và không thể khôi phục!",
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
                        url: "{{ route('web.shipment.delete') }}",
                        data: {
                            shipment_id: shipment_id,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            let message = response && response.message ? response.message :
                                'Xóa Shipment ID thành công';
                            Swal.fire({
                                icon: "success",
                                title: "Thành công",
                                text: message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            btn.closest('tr').remove();
                            $("#example1 tbody tr").each(function(index) {
                                $(this).find("td:first").text(index + 1);
                            });
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
                            btn.prop('disabled', false);
                        }
                    });
                } else {
                    btn.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
