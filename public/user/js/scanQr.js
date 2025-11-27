// Start quét mã QR
let html5QrCode = null;
let scannerRunning = false;

function scanQr() {
    return new Promise((resolve, reject) => {
        screenLog("👉 Bắt đầu scan QR...");
        if (typeof Html5Qrcode === "undefined") {
            screenLog("❌ Html5Qrcode chưa load");
            reject("Html5Qrcode chưa load");
            return;
        }
        if (scannerRunning) {
            screenLog("✅ Camera đang chạy rồi");
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
        html5QrCode
            .start(
                {
                    facingMode: "environment",
                },
                {
                    fps: 10,
                    qrbox: 250,
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.QR_CODE,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                    ],
                },
                (decodedText) => {
                    screenLog("✅ Quét được: " + decodedText);
                    html5QrCode.stop().then(() => {
                        screenLog("👉 Đã dừng camera");
                        scannerRunning = false;
                        resolve(decodedText);
                    });
                }
            )
            .then(() => {
                scannerRunning = true;
                screenLog("👉 Camera đã bật");
            })
            .catch((err) => {
                reject(err);
            });
    });
}
// End quét mã QR
