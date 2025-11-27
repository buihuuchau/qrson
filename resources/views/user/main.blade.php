<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>@yield('title', 'QR Scanner')</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('AdminLTE-3.2.0/plugins/fontawesome-free/css/all.min.css') }}">

    <!-- sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Spinner -->
    <link rel="stylesheet" href="{{ asset('web/css/spinner.css') }}">

    <!-- Custom CSS -->
    @yield('custom_css')

    <style>
        body {
            background: #f5f5f5;
        }

        #debugLog {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            max-height: 40%;
            overflow-y: auto;
            background: black;
            color: #0f0;
            font-size: 12px;
            padding: 10px;
            z-index: 99999;
        }

        #debugLog div {
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

    <div class="container py-3">
        @yield('content')
        <div id="loadingOverlay">
            <div class="spinner"></div>
        </div>
    </div>

    <!-- Debug Console -->
    {{-- <div id="debugLog"></div> --}}

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- HTML5 QR + Barcode Scanner -->
    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

    <!-- Thư viện quét QR / Barcode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- Js quét QR / Barcode -->
    <script src="{{ asset('user/js/scanQr.js') }}"></script>

     <!-- Js đếm ngược rồi tự chạy -->
    <script src="{{ asset('user/js/countDownFunction.js') }}"></script>

    <script>
        function screenLog(message) {
            // $('#debugLog').append('<div>👉 ' + message + '</div>');
            // $('#debugLog').scrollTop($('#debugLog')[0].scrollHeight);
        }
    </script>

    @yield('custom_script')

</body>

</html>
