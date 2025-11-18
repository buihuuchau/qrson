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
                                            <th>Thời gian quét</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($shipments as $key => $shipment)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $shipment->id }}</td>
                                                <td>{{ $shipment->created_at }}</td>
                                                <td>
                                                    <a class="btn btn-primary" title="Chi tiết"
                                                        href="{{ route('web.document.list', ['shipment_id' => $shipment->id]) }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a class="btn btn-success" title="Xuất file"
                                                        href="{{ route('web.document.list', ['shipment_id' => $shipment->id]) }}">
                                                        <i class="fas fa-file-export"></i>
                                                    </a>
                                                    <a id="clearShipment" class="btn btn-danger" title="Xóa"
                                                        href="{{ route('web.document.list', ['shipment_id' => $shipment->id]) }}">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Shipment ID</th>
                                            <th>Thời gian quét</th>
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
