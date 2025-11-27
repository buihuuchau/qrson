@extends('user.main')

@section('title', 'Barcode Shipment No')

@section('content')
    <h4 class="mb-3 text-center">Barcode Shipment No</h4>
    <div style="text-align: end">
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
    <form id="formAdd" class="d-none text-center" action="{{ route('user.shipment.add') }}" method="post">
        @csrf
        <input id="input_shipment_id" type="hidden" name="shipment_id">
        <button id="btnAddSubmit" type="submit" class="btn btn-primary">Tạo Shipment No mới với mã vừa quét
            được</button>
    </form>

    <h5>Danh sách các Shipment No mà bạn đã tạo.</h5>
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Shipment No</th>
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
                        <td class="d-flex">
                            <a class="btn btn-primary mr-5" title="Chi tiết"
                                href="{{ route('user.scan.document', ['shipment_id' => $shipment->id]) }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if ($shipment->status != 'done' && $shipment->document->count() == 0)
                                <button class="btn btn-danger clearShipment" title="Xóa"
                                    data-shipment-id="{{ $shipment->id }}"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Shipment No</th>
                    <th>Thời gian quét</th>
                    <th>Thao tác</th>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-end">
            {{ $shipments->appends($_GET)->links('web.layouts.pagination_vi') }}
        </div>
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
                    let resultShipmentId = await scanQr();
                    $('#loadingOverlay').css('display', 'flex');
                    if (!resultShipmentId) {
                        screenLog("⚠ Chưa có mã để gửi");
                        return;
                    }
                    screenLog("📡 Chuẩn bị gọi API với shipment_id: " + resultShipmentId);
                    $.ajax({
                        url: "/user/shipment-check",
                        type: "get",
                        data: {
                            shipment_id: resultShipmentId,
                        },
                        success: function(response) {
                            if (response.status_code == 200) {
                                screenLog(
                                    "✅ Chuyển trang đến nhập Số chứng từ cho Shipment No: " +
                                    resultShipmentId);
                                window.location.href = "/user/scan-document?shipment_id=" +
                                    resultShipmentId;
                                $('#loadingOverlay').hide();
                            }
                            if (response.status_code == 404) {
                                screenLog(
                                    "✅ Shipment No chưa được tạo, hiển thị form tạo Shipment No"
                                );
                                let html = `
                                    <h5 class="text-warning mb-3">${response.message}</h5>
                                `;
                                $("#apiResult").html(html);
                                $("#input_shipment_id").val(resultShipmentId);
                                $("#formAdd").removeClass("d-none");
                                $('#loadingOverlay').hide();
                            }
                        },
                        error: function(err) {
                            let error = err.responseJSON;
                            screenLog("❌ API Error status_code: " + error.message);
                            screenLog("❌ API Error message: " + error.message);
                            $('#loadingOverlay').hide();
                        }
                    });
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

            $('#btnAddSubmit').click(function(e) {
                e.preventDefault();
                let formAdd = $('#formAdd')[0];
                Swal.fire({
                    title: "Thêm mới",
                    text: "Xác nhận tạo mới Shipment No?",
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

            $('.clearShipment').click(function(e) {
                e.preventDefault();
                let button = $(this);
                let shipment_id = button.data('shipment-id');
                Swal.fire({
                    title: "Xác nhận xóa?",
                    text: "Shipment No:  " + shipment_id + " sẽ bị xóa và không thể khôi phục!",
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
                            url: "{{ route('user.shipment.delete') }}",
                            data: {
                                shipment_id: shipment_id,
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
                                    button.closest('tr').remove();
                                    $("#example1 tbody tr").each(function(index) {
                                        $(this).find("td:first").text(index +
                                            1);
                                    });
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
                                    'Đã có lỗi xảy ra.';
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
        });
    </script>
@endsection
