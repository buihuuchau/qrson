@extends('user.main')

@section('title', 'Quét QR & Barcode')

@section('content')
    <h4 class="mb-3 text-center">📷 Quét QR / Barcode</h4>
    <div class="text-center mb-3">
        <button id="btnStartScan" class="btn btn-primary">Bật camera</button>
        <button id="btnStopScan" class="btn btn-danger">Tắt camera</button>
    </div>
    <div id="qr-reader" style="width:100%; margin: auto;"></div>
    <div class="mb-3">
        <label>Shipment No:</label>
        <input type="text" id="shipment_id" class="form-control">
    </div>
    <h5 class="text-center" id="apiResult"></h5>
    <form id="formAddShipment" class="d-none" action="{{ route('user.shipment.add') }}" method="post">
        @csrf
        <input id="shipment_id" type="hidden" name="shipment_id">
        <button type="submit">Tạo Shipment No</button>
    </form>
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
                    const shipmentId = await scanQr();
                    $("#shipment_id").val(shipmentId);
                    screenLog("✅ Gán shipment_id thành công: " + shipmentId);
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
                let shipment_id = $('#shipment_id').val();
                if (!shipment_id) {
                    screenLog("⚠ Chưa có mã để gửi");
                    return;
                }
                screenLog("📡 Chuẩn bị gọi API với shipment_id: " + shipment_id);
                $.ajax({
                    url: "/user/shipment-check",
                    type: "get",
                    data: {
                        shipment_id: shipment_id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status_code == 200) {
                            screenLog("✅ Chuyển trang đến nhập Số chứng từ cho Shipment No: " +
                                shipment_id);
                            window.location.href = "/user/scan-document?shipment_id=" +
                                shipment_id;
                        }
                    },
                    error: function(err) {
                        let error = err.responseJSON;
                        let shipment_id = $("#shipment_id").val();
                        screenLog("❌ API Error status_code: " + error.status_code);

                        let html = `
                            <h5 class="text-danger mb-3">${error.message}</h5>
                        `;
                        $("#apiResult").html(html);

                        $("#shipment_id").val(shipment_id);

                        $("#formAddShipment").removeClass("d-none");
                    }
                });
            });
        });
    </script>
@endsection
