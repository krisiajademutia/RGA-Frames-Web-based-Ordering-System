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

// NOTE: Registration form validation has been removed from JS. 
// We are now letting the PHP backend handle validation so that 
// ALL error messages (empty fields, duplicates, etc.) show up 
// perfectly under the inputs at the same time!