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
// ==========================================
// TOAST NOTIFICATIONS
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    var toastElement = document.getElementById('successToast');
    
    // Only try to show the toast if it actually exists on the page
    if (toastElement) {
        var toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
});