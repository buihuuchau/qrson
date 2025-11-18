class SimpleLoginForm {
    constructor() {
        this.form = document.getElementById('loginForm');
        this.phoneInput = document.getElementById('phone');
        this.passwordInput = document.getElementById('password');
        this.passwordToggle = document.getElementById('passwordToggle');

        this.bindEvents();
    }

    bindEvents() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.phoneInput.addEventListener('blur', () => this.validatePhone());
        this.passwordInput.addEventListener('blur', () => this.validatePassword());
        this.phoneInput.addEventListener('input', () => this.clearError('phone'));
        this.passwordInput.addEventListener('input', () => this.clearError('password'));

        // Toggle hiển thị password
        this.passwordToggle.addEventListener('click', () => {
            const type = this.passwordInput.type === 'password' ? 'text' : 'password';
            this.passwordInput.type = type;
            this.passwordToggle.classList.toggle('show-password', type === 'text');
        });
    }

    validatePhone() {
        const phone = this.phoneInput.value.trim();
        const phoneRegex = /^\d{10}$/; // chỉ 10 số

        if (!phone) {
            this.showError('phone', 'Số điện thoại là bắt buộc');
            return false;
        }
        if (!phoneRegex.test(phone)) {
            this.showError('phone', 'Số điện thoại phải có 10 số');
            return false;
        }

        this.clearError('phone');
        return true;
    }

    validatePassword() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showError('password', 'Mật khẩu là bắt buộc');
            return false;
        }
        if (password.length < 6) {
            this.showError('password', 'Mật khẩu phải có ít nhất 6 ký tự');
            return false;
        }

        this.clearError('password');
        return true;
    }

    showError(field, message) {
        const errorElement = document.getElementById(`${field}Error`);
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }

    clearError(field) {
        const errorElement = document.getElementById(`${field}Error`);
        errorElement.textContent = '';
        errorElement.classList.remove('show');
    }

    handleSubmit(e) {
        const isPhoneValid = this.validatePhone();
        const isPasswordValid = this.validatePassword();

        if (!isPhoneValid || !isPasswordValid) {
            e.preventDefault(); // chặn submit nếu không hợp lệ
        }
        // Nếu hợp lệ → submit form bình thường
    }
}

// Khởi tạo khi DOM sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    new SimpleLoginForm();
});
