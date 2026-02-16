// 1. UNIVERSAL PASSWORD TOGGLE
// Works for both 'loginPassword' and 'registerPassword' IDs
function togglePassword(inputId, btn) {
    const passwordInput = document.getElementById(inputId);
    const icon = btn.querySelector('i');

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        // Change icon to 'eye' (open)
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        passwordInput.type = "password";
        // Change icon back to 'eye-slash' (hidden)
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

// 2. REGISTRATION FORM VALIDATION
document.addEventListener('DOMContentLoaded', function() {
    const regForm = document.getElementById('regForm');

    if (regForm) {
        regForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const phone = document.getElementById('phone_number');
            let isValid = true;

            // Reset previous validation states
            email.classList.remove('is-invalid');
            phone.classList.remove('is-invalid');

            // Gmail Validation: Must end with @gmail.com
            const emailValue = email.value.trim().toLowerCase();
            if (!emailValue.endsWith('@gmail.com')) {
                email.classList.add('is-invalid');
                isValid = false;
            }

            // Phone Validation: Must be 11 digits and start with 09
            const phoneValue = phone.value.trim().replace(/\D/g, ''); // Remove non-digits
            if (phoneValue.length !== 11 || !phoneValue.startsWith('09')) {
                phone.classList.add('is-invalid');
                isValid = false;
            }

            // If any validation failed, stop the form from submitting to the PHP process
            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
});