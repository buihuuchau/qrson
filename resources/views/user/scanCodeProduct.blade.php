@extends('user.main')

@section('title', 'Qr Mã sản phẩm')

@section('content')
    <h4 class="mb-3 text-center">📷 Qr/Nhập Mã sản phẩm</h4>
    <div style="text-align: end">
        <a href="{{ route('web.logout') }}">Đăng xuất</a>
    </div>
    <div class="text-center mb-3">
        <button id="btnStartScan" class="btn btn-primary">Bật camera</button>
        <button id="btnStopScan" class="btn btn-danger">Tắt camera</button>
    </div>
    <div id="qr-reader" style="width:100%; margin: auto;"></div>

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

    <form id="formAdd" action="{{ route('user.code-product.add') }}" method="post">
        @csrf
        <input type="hidden" name="scan" value="no">
        <div class="mb-3">
            <label for="input_shipment_id" class="form-label">Shipment No</label>
            <input type="text" class="form-control" id="input_shipment_id" name="shipment_id" value="{{ $shipment->id }}"
                readonly required>
        </div>
        <div class="mb-3">
            <label for="input_document_id" class="form-label">Số chứng từ</label>
            <input type="text" class="form-control" id="input_document_id" name="document_id" value="{{ $document->id }}"
                required required>
        </div>
        <div class="mb-3">
            <label for="input_code_product_id" class="form-label">Mã sản phẩm nhập</label>
            <input type="text" class="form-control" id="input_code_product_id" name="code_product_id"
                value="{{ old('code_product_id') }}" required>
        </div>
        <button id="btnAddSubmit" type="submit" class="btn btn-primary mb-3">Tạo Mã sản phẩm</button>
    </form>

    <p>Số lượng đã quét: <span id="document_total_current">{{ $document->total_current }}</span></p>
    <p>Số lượng tổng: <span id="document_">{{ $document->total }}</span></p>
    @php
        if ($document->total_current != $document->total) {
            $btnConfirmClass = 'd-none';
        } else {
            $btnConfirmClass = '';
        }
    @endphp

    <form id="formConfirm" action="{{ route('user.shipment.confirm') }}" method="post">
        @csrf
        <input type="hidden" name="shipment_id" value="{{ $shipment->id }}">
        <input type="hidden" name="document_id" value="{{ $document->id }}">
        <button id="btnConfirmSubmit" type="submit" class="btn btn-primary mb-3 {{ $btnConfirmClass }}">Xác nhận quét đủ
            mã sản phẩm</button>
    </form>


    <h5>Danh sách các Mã sản phẩm mà bạn đã quét.</h5>
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
                    <th>Thao tác</th>
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
                        <td>{{ $codeProduct->created_by }}</td>
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
            <tfoot>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Shipment ID</th>
                    <th>Số chứng từ</th>
                    <th>Mã sản phẩm</th>
                    <th>Thời gian quét</th>
                    <th>Người thực hiện</th>
                    <th>Thực hiện manual</th>
                    <th>Thao tác</th>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-end">
            {{ $codeProducts->appends($_GET)->links('web.layouts.pagination_vi') }}
        </div>
    </div>

    <h5 class="text-center" id="apiResult"></h5>

    <div class="mb-3">
        <label>Mã sản phẩm quét:</label>
        <input type="text" id="result_code_product_id" class="form-control">
    </div>

    <div class="mt-3 text-center">
        <button id="btnSendApi" class="btn btn-success">Gửi API</button>
    </div>
@endsection

@section('custom_script')
    <script>
        $(document).ready(function() {
            let html5QrCode = null;
            let scannerRunning = false;

            function scanQr() {
                return new Promise((resolve, reject) => {

                    screenLog("📷 Bắt đầu scan QR...");

                    if (typeof Html5Qrcode === "undefined") {
                        screenLog("❌ Html5Qrcode chưa load");
                        reject("Html5Qrcode chưa load");
                        return;
                    }

                    if (scannerRunning) {
                        screenLog("⚠ Camera đang chạy rồi");
                        reject("Camera đang chạy");
                        return;
                    }

                    try {
                        html5QrCode = new Html5Qrcode("qr-reader");
                    } catch (error) {
                        screenLog("❌ Lỗi tạo Html5Qrcode: " + error.message);
                        reject(error);
                        return;
                    }

                    html5QrCode.start({
                            facingMode: "environment"
                        }, {
                            fps: 10,
                            qrbox: 250,
                            formatsToSupport: [
                                Html5QrcodeSupportedFormats.QR_CODE,
                                Html5QrcodeSupportedFormats.CODE_128,
                                Html5QrcodeSupportedFormats.EAN_13,
                                Html5QrcodeSupportedFormats.EAN_8
                            ]
                        },
                        (decodedText) => {
                            screenLog("✅ Quét được: " + decodedText);

                            html5QrCode.stop().then(() => {
                                screenLog("📴 Đã dừng camera");
                                scannerRunning = false;
                                resolve(decodedText);
                            });
                        },
                        (error) => {
                            // bỏ log nếu spam
                        }
                    ).then(() => {
                        scannerRunning = true;
                        screenLog("📸 Camera đã bật");
                    }).catch(err => {
                        reject(err);
                    });
                });
            }

            $('#btnStartScan').click(async function() { // 👉 thêm async
                try {
                    const codeProductId = await scanQr();
                    $("#result_code_product_id").val(codeProductId);
                    screenLog("✅ Gán code_product_id thành công: " + codeProductId);
                } catch (err) {
                    screenLog("❌ Scan lỗi: " + err);
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

            $('#btnSendApi').click(function() {
                let shipment_id = $('#input_shipment_id').val();
                let document_id = $('#input_document_id').val();
                let code_product_id = $('#result_code_product_id').val();
                if (!code_product_id) {
                    screenLog("⚠ Chưa có mã để gửi");
                    return;
                }
                screenLog("📡 Chuẩn bị gọi API với code_product_id: " + code_product_id);
                $.ajax({
                    url: "/user/code-product-add",
                    type: "post",
                    data: {
                        shipment_id: shipment_id,
                        document_id: document_id,
                        code_product_id: code_product_id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status_code == 201) {
                            screenLog("✅ Tạo thành công Mã sản phẩm: " +
                                code_product_id);
                            let html = `
                                <h5 class="text-success mb-3">${response.message}</h5>
                            `;
                            $("#apiResult").html(html);

                            let document_total_current = response.data.document['total_current'];
                            let document_total = response.data.document['total'];
                            $("#document_total_current").text(document_total_current);
                            if (document_total_current == document_total) {
                                $("#btnConfirmSubmit").removeClass('d-none');
                            }

                            let valShipmentId = response.data.codeProductTemp['shipment_id'];
                            let valDocumentId = response.data.codeProductTemp['document_id'];
                            let valId = response.data.codeProductTemp['id'];
                            let valCreatedAt = response.data.codeProductTemp['created_at'];
                            let valCreatedBy = response.data.codeProductTemp['created_by'];
                            let valTimeCreatedAt = response.data.codeProductTemp[
                                'time_created_at'];
                            let rowHtml = `
                                <tr>
                                    <td>1</td>
                                    <td>${valShipmentId}</td>
                                    <td>${valDocumentId}</td>
                                    <td>${valId}</td>
                                    <td>${valTimeCreatedAt}</td>
                                    <td>${valCreatedBy}</td>
                                    <td></td>
                                    <td>
                                        <button class="btn btn-danger clearCodeProduct" title="Xóa"
                                            data-code-product-id="${valId}"><i
                                                class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            `;
                            $('#example1 tbody').prepend(rowHtml);
                            $("#example1 tbody tr").each(function(index) {
                                $(this).find("td:first").text(index + 1);
                            });
                        }
                        if (response.status_code != 201) {
                            screenLog(
                                "✅ Tạo Mã sản phẩm không thành công, nên nhập Mã sản phẩm thủ công"
                            );
                            let html = `
                                <h5 class="text-warning mb-3">${response.message}</h5>
                            `;
                            $("#apiResult").html(html);
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
                    }
                });
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
                            url: "{{ route('user.code-product.delete') }}",
                            data: {
                                code_product_id: code_product_id,
                                _token: "{{ csrf_token() }}"
                            },
                            dataType: "json",
                            success: function(response) {
                                let message = response && response.message ? response
                                    .message :
                                    'Xóa Mã sản phẩm thành công';
                                Swal.fire({
                                    icon: "success",
                                    title: "Thành công",
                                    text: message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $("#document_total_current").text(
                                    document_total_current - 1);
                                $("#btnConfirmSubmit").addClass('d-none');
                                button.closest('tr').remove();
                                $("#example1 tbody tr").each(function(index) {
                                    $(this).find("td:first").text(index + 1);
                                });
                                $('#loadingOverlay').hide();
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
