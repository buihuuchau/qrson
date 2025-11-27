@extends('user.main')

@section('title', 'Qr Mã sản phẩm')

@section('content')
    <h5 class="mb-3 text-center">Qr/Nhập Mã sản phẩm</h5>
    <div class="mb-3 d-flex justify-content-between">
        <a href="{{ route('user.scan.shipment') }}">Quét Shipment No</a>
        <a href="{{ route('web.logout') }}">Đăng xuất</a>
    </div>
    <div class="text-center mb-3">
        <button id="btnStartScan" class="btn btn-primary">Bật camera</button>
        <button id="btnStopScan" class="btn btn-danger">Tắt camera</button>
    </div>
    <div id="qr-reader" style="width:100%; margin: auto;"></div>
    <div id="apiResult" class="text-center" style="border: 2px solid red"></div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        if ($document->total_current != $document->total) {
            $formAddClass = '';
            $btnConfirmClass = 'd-none';
        } else {
            $formAddClass = 'd-none';
            $btnConfirmClass = '';
        }
    @endphp

    <form class="{{ $formAddClass }}" id="formAdd" action="{{ route('user.code-product.add') }}" method="post">
        @csrf
        <input type="hidden" name="scan" value="no">
        <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
        <input type="hidden" name="document_id" value="{{ $document->id }}">
        <div class="mb-3">
            <label for="input_code_product_id" class="form-label">
                Nhập Mã sản phẩm cho<br>
                Shipment No: {{ $shipment->id }}<br>
                Số chứng từ: {{ $document->id }}
            </label>
            <input type="text" class="form-control" id="input_code_product_id" name="code_product_id"
                value="{{ old('code_product_id') }}" required>
        </div>
        <button id="btnAddSubmit" type="submit" class="btn btn-primary mb-3">Tạo Mã sản phẩm</button>
    </form>

    <div class="d-flex">
        <p>Số lượng đã quét: <span id="document_total_current">{{ $document->total_current }}</span></p>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <p>Số lượng tổng: {{ $document->total }}</p>
    </div>

    <form id="formConfirm" action="{{ route('user.shipment.confirm') }}" method="post">
        @csrf
        <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
        <input type="hidden" name="document_id" value="{{ $document->id }}">
        <button id="btnConfirmSubmit" type="submit" class="btn btn-primary mb-3 {{ $btnConfirmClass }}">Xác nhận quét đủ
            mã sản phẩm</button>
    </form>

    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Mã sản phẩm</th>
                    <th>Manual</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codeProducts as $key => $codeProduct)
                    <tr>
                        <td><b>{{ $codeProduct->id }}</b><br>{{ $codeProduct->created_at }}</td>
                        <td>
                            @if ($codeProduct->scan == 'no')
                                X
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-danger clearCodeProduct" title="Xóa"
                                data-code-product-id="{{ $codeProduct->id }}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $codeProducts->appends($_GET)->links('user.layouts.pagination_vi') }}
        </div>
    </div>
@endsection

@section('custom_script')
    <script>
        $(document).ready(function() {
            $('#btnStartScan').click(async function() {
                try {
                    let resultCodeProductId = await scanQr();
                    $('#loadingOverlay').css('display', 'flex');
                    if (!resultCodeProductId) {
                        screenLog("❌ Chưa có mã để gửi");
                        return;
                    }
                    screenLog("👉 Chuẩn bị gọi API với code_product_id: " + resultCodeProductId);
                    $.ajax({
                        url: "{{ route('user.code-product.add') }}",
                        type: "post",
                        data: {
                            shipment_id: {{ $shipment->id }},
                            document_id: {{ $document->id }},
                            code_product_id: resultCodeProductId,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status_code == 201) {
                                let html = `
                                    <h5 class="text-success mb-3">${response.message}</h5>
                                `;
                                $("#apiResult").html(html);

                                let document_total_current = response.data.document[
                                    'total_current'];
                                let document_total = response.data.document['total'];
                                $("#document_total_current").text(document_total_current);
                                if (document_total_current == document_total) {
                                    $("#formAdd").addClass('d-none');
                                    $("#btnConfirmSubmit").removeClass('d-none');
                                }

                                let codeProductId = response.data.codeProductTemp['id'];
                                let createdAtFormat = response.data.codeProductTemp[
                                    'created_at_format'];
                                let rowHtml = `
                                    <tr>
                                        <td><b>${codeProductId}</b></br>${createdAtFormat}</td>
                                        <td></td>
                                        <td>
                                            <button class="btn btn-danger clearCodeProduct" title="Xóa"
                                                data-code-product-id="${codeProductId}"><i
                                                    class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                `;
                                $('#example1 tbody').prepend(rowHtml);
                                $('#loadingOverlay').hide();
                                $('#btnStartScan').click();
                            } else {
                                screenLog(
                                    "✅ Tạo Mã sản phẩm không thành công, nên nhập Mã sản phẩm thủ công"
                                );
                                let html = `
                                    <h5 class="text-warning mb-3">${response.message}</h5>
                                `;
                                $("#apiResult").html(html);
                                $('#loadingOverlay').hide();
                            }
                        },
                        error: function(err) {
                            let error = err.responseJSON;
                            screenLog("❌ API Error status_code: " + error.status_code);
                            screenLog("❌ API Error message: " + error.message);
                            screenLog(
                                "✅ Tạo Mã sản phẩm không thành công, nên nhập Mã sản phẩm thủ công"
                            );
                            let html = `
                                <h5 class="text-warning mb-3">${error.message}</h5>
                            `;
                            $("#apiResult").html(html);
                            $('#loadingOverlay').hide();
                        }
                    });
                } catch (err) {
                    screenLog("❌ Scan lỗi: " + err);
                    $('#loadingOverlay').hide();
                }
            });

            $('#btnStopScan').click(function() {
                if (html5QrCode && scannerRunning) {
                    html5QrCode.stop().then(() => {
                        screenLog("📴 Camera đã tắt");
                        scannerRunning = false;
                    }).catch(err => {
                        screenLog("❌ Lỗi khi tắt camera: " + err);
                    });
                } else {
                    screenLog("⚠ Camera chưa được bật");
                }
            });

            $('#btnAddSubmit').click(function(e) {
                e.preventDefault();
                let formAdd = $('#formAdd')[0];
                Swal.fire({
                    title: "Thêm mới",
                    text: "Xác nhận tạo mới Mã sản phẩm?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Thêm",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#loadingOverlay').css('display', 'flex');
                        setTimeout(function() {
                            if (!formAdd.checkValidity()) {
                                $('#loadingOverlay').hide();
                                formAdd.reportValidity();
                                return;
                            }
                            formAdd.submit();
                        }, 300);
                    }
                });
            });

            $("#example1").on("click", ".clearCodeProduct", function(e) {
                e.preventDefault();
                let button = $(this);
                let code_product_id = button.data('code-product-id');
                let document_total_current = $("#document_total_current").text();
                Swal.fire({
                    title: "Xác nhận xóa?",
                    text: "Mã sản phẩm: " + code_product_id + " sẽ bị xóa và không thể khôi phục!",
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
                            url: "{{ route('user.code-product.delete') }}",
                            data: {
                                code_product_id: code_product_id,
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
                                    $("#document_total_current").text(
                                        document_total_current - 1);
                                    $("#formAdd").removeClass('d-none');
                                    $("#btnConfirmSubmit").addClass('d-none');
                                    button.closest('tr').remove();
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
                                let message = xhr.responseJSON && xhr.responseJSON
                                    .message ?
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

            $('#btnConfirmSubmit').click(function(e) {
                e.preventDefault();
                let formAdd = $('#formConfirm')[0];
                Swal.fire({
                    title: "Thêm mới",
                    text: "Xác nhận quét đủ Mã sản phẩm?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Thêm",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#loadingOverlay').css('display', 'flex');
                        setTimeout(function() {
                            if (!formAdd.checkValidity()) {
                                $('#loadingOverlay').hide();
                                formAdd.reportValidity();
                                return;
                            }
                            formAdd.submit();
                        }, 300);
                    }
                });
            });
        });
    </script>
@endsection
