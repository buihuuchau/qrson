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
                                                <td>{{ $codeProduct->user->name }} - {{ $codeProduct->user->phone }}</td>
                                                <td>
                                                    @if ($codeProduct->scan == 'no')
                                                        X
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
                                            <th>Mã sản phẩm</th>
                                            <th>Thời gian quét</th>
                                            <th>Người thực hiện</th>
                                            <th>Thực hiện manual</th>
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
