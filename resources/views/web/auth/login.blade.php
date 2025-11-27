<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="{{ asset('web/css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="material-logo">
                    <div class="logo-layers">
                        <div class="layer layer-1"></div>
                        <div class="layer layer-2"></div>
                        <div class="layer layer-3"></div>
                    </div>
                </div>
                <h2>Đăng nhập</h2>
            </div>

            <form action="{{ route('web.post-login') }}" class="login-form" id="loginForm" method="POST">
                @csrf
                <div class="form-group">
                    <div class="input-wrapper">
                        <input type="text" id="phone" name="phone" required autocomplete="phone">
                        <label for="phone">Số điện thoại</label>
                        <div class="input-line"></div>
                        <div class="ripple-container"></div>
                    </div>
                    <span class="error-message" id="phoneError"></span>
                </div>

                <div class="form-group">
                    <div class="input-wrapper password-wrapper">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <label for="password">Mật khẩu</label>
                        <div class="input-line"></div>
                        <button type="button" class="password-toggle" id="passwordToggle"
                            aria-label="Toggle password visibility">
                            <span class="toggle-icon"></span>
                        </button>
                        <div class="ripple-container"></div>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>

                @error('login')
                    <span style="color: red;">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <button type="submit" class="login-btn material-btn">
                    <div class="btn-ripple"></div>
                    <span class="btn-text">Đăng nhập</span>
                    <div class="btn-loader">
                        <svg class="loader-circle" viewBox="0 0 50 50">
                            <circle class="loader-path" cx="25" cy="25" r="12" fill="none"
                                stroke="currentColor" stroke-width="3" />
                        </svg>
                    </div>
                </button>
            </form>
        </div>
    </div>
</body>

<script src="{{ asset('web/js/login.js') }}"></script>

</html>
