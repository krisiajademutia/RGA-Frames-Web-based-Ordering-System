/**
 * OTP Input Handler for RGA Frames
 * Manages auto-focus and joining 6 digits into a single hidden field.
 */
document.addEventListener('DOMContentLoaded', () => {
    const digits = document.querySelectorAll('.votp-digit');
    const fullOtpStorage = document.getElementById('full_otp');

    if (!digits.length || !fullOtpStorage) return;

    digits.forEach((el, index) => {
        // Move to next input when a number is entered
        el.addEventListener('input', (e) => {
            if (e.target.value.length === 1 && index < digits.length - 1) {
                digits[index + 1].focus();
            }
            combineDigits();
        });

        // Move to previous input on backspace
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !el.value && index > 0) {
                digits[index - 1].focus();
            }
        });
    });

    function combineDigits() {
        let combined = "";
        digits.forEach(d => combined += d.value);
        fullOtpStorage.value = combined;
    }
});