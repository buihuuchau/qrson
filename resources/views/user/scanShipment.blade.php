@extends('user.main')

@section('title', 'Barcode Shipment No')

@section('content')
    <h5 class="mb-3 text-center">Barcode Shipment No</h5>
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

    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Shipment No</th>
                    <th>Thời gian quét</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shipments as $key => $shipment)
                    <tr>
                        <td>{{ $shipment->id }}</td>
                        <td>{{ $shipment->created_at }}</td>
                        <td>
                            @if ($shipment->status != 'done' && $shipment->document->count() == 0)
                                <button class="btn btn-danger clearShipment" title="Xóa"
                                    data-shipment-id="{{ $shipment->id }}"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $shipments->appends($_GET)->links('user.layouts.pagination_vi') }}
        </div>
    </div>
@endsection

@section('custom_script')
    <script>
        $(document).ready(function() {
            $('#btnStartScan').click(async function() {
                try {
                    let resultShipmentId = await scanQr();
                    $('#loadingOverlay').css('display', 'flex');
                    if (!resultShipmentId) {
                        screenLog("❌ Chưa có mã để gửi");
                        return;
                    }
                    screenLog("👉 Chuẩn bị gọi API với shipment_id: " + resultShipmentId);
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
                                $('#loadingOverlay').hide();
                                let title = 'Xác nhận tạo Shipment No: ' +
                                    resultShipmentId;
                                countDownFunction(title, 10, 'addShipment',
                                    resultShipmentId);
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

            window.addShipment = function(resultShipmentId) {
                $('#loadingOverlay').css('display', 'flex');
                $.ajax({
                    url: "/user/shipment-add",
                    type: "post",
                    data: {
                        shipment_id: resultShipmentId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status_code == 201) {
                            screenLog(
                                "✅ Chuyển trang đến nhập Số chứng từ cho Shipment No: " +
                                resultShipmentId);
                            window.location.href = "/user/scan-document?shipment_id=" +
                                resultShipmentId;
                            $('#loadingOverlay').hide();
                        } else {
                            let html = `
                                    <h5 class="text-warning mb-3">${response.message}</h5>
                                `;
                            $("#apiResult").html(html);
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
            }
        });
    </script>
@endsection
