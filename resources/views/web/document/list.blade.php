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
                                            <th>Thời gian tạo</th>
                                            <th>Thời gian chỉnh sửa</th>
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
                                                <td>{{ $document->updated_at }}</td>
                                                <td>
                                                    <a
                                                        href="{{ route('web.code-product.list', ['shipment_id' => $document->shipment_id, 'document_id' => $document->id]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
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
                                            <th>Thời gian tạo</th>
                                            <th>Thời gian chỉnh sửa</th>
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
    {{-- custom-script --}}
@endsection
