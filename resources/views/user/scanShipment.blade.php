@extends('user.main')

@section('title', 'Quét QR & Barcode')

@section('content')
    <h4 class="mb-3 text-center">📷 Quét QR / Barcode</h4>

    <div class="text-center mb-3">
        <button id="btnStartScan" class="btn btn-primary">Bật camera</button>
        <button id="btnStopScan" class="btn btn-danger">Tắt camera</button>
    </div>

    <div id="qr-reader" style="width:100%; margin: auto;"></div>

    <div class="mt-3">
        <label>Mã quét được:</label>
        <input type="text" id="scanResult" class="form-control" readonly>
    </div>

    <div class="mt-3 text-center">
        <button id="btnSendApi" class="btn btn-success">Gửi API</button>
    </div>
@endsection

@section('custom_script')
    <script>
        $(document).ready(function() {

            screenLog("Trang scan đã load");

            let html5QrCode;
            let scannerRunning = false;

            $('#btnStartScan').click(function() {

                screenLog("Đã bấm nút bật camera");

                if (typeof Html5Qrcode === "undefined") {
                    screenLog("❌ Html5Qrcode chưa load");
                    return;
                }

                if (scannerRunning) {
                    screenLog("⚠ Camera đang chạy rồi");
                    return;
                }

                try {
                    html5QrCode = new Html5Qrcode("qr-reader");
                    screenLog("✅ Tạo Html5Qrcode thành công");
                } catch (error) {
                    screenLog("❌ Lỗi tạo Html5Qrcode: " + error.message);
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
                    function(decodedText) {
                        screenLog("✅ Quét được: " + decodedText);
                        $('#scanResult').val(decodedText);

                        // Dừng sau khi quét được
                        html5QrCode.stop();
                        scannerRunning = false;
                        screenLog("📴 Đã dừng camera");
                    },
                    function(error) {
                        // Có thể bỏ nếu log quá nhiều
                    }
                ).then(() => {
                    scannerRunning = true;
                    screenLog("📸 Camera đã bật");
                }).catch(err => {
                    screenLog("❌ Lỗi mở camera: " + err);
                });
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
                let shipment_id = $('#scanResult').val();

                if (!shipment_id) {
                    screenLog("⚠ Chưa có mã để gửi");
                    return;
                }

                screenLog("📡 Chuẩn bị gọi API với shipment_id: " + shipment_id);

                $.ajax({
                    url: "/user/shipment-add",
                    type: "POST",
                    data: {
                        shipment_id: shipment_id,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        screenLog("✅ API Success: " + JSON.stringify(res));
                    },
                    error: function(err) {
                        screenLog("❌ API Error: " + err.responseText);
                    }
                });
            });


        });
    </script>
@endsection
