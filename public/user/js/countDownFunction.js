let timerInterval;
function countDownFunction(
    title, // tiêu đề
    time, // thời gian đếm ngược
    autoLoadFunctionName, // tên hàm gọi sau khi đếm
    param1 = null,
    param2 = null,
    param3 = null,
    param4 = null,
    param5 = null
) {
    let timeLeft = time;
    Swal.fire({
        title: title,
        html: `
                Hệ thống sẽ tự động thực hiện sau
                <b><span id="countdown">${timeLeft}</span></b> giây.<br>
                Nhấn <b>Hủy</b> để dừng.
            `,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Chạy ngay",
        cancelButtonText: "Hủy",
        timer: timeLeft * 1000,
        timerProgressBar: true,
        didOpen: () => {
            let countdownEl = $("#countdown");
            timerInterval = setInterval(() => {
                timeLeft--;
                countdownEl.text(timeLeft);
            }, 1000);
            Swal.getPopup().timerInterval = timerInterval;
        },
        willClose: () => {
            clearInterval(Swal.getPopup().timerInterval);
        },
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            return;
        }
        if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
            if (
                autoLoadFunctionName &&
                typeof window[autoLoadFunctionName] === "function"
            ) {
                window[autoLoadFunctionName](
                    param1,
                    param2,
                    param3,
                    param4,
                    param5
                );
            } else {
                screenLog("❌ Không tìm thấy hàm: " + autoLoadFunctionName);
            }
        }
    });
}
