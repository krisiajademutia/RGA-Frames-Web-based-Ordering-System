document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.post-calc-trigger');
    const sizeBtns = document.querySelectorAll('.post-size-trigger');
    const priceDisplay = document.getElementById('post_total_display');
    const hiddenSize = document.getElementById('post_hidden_size_id');

    let currentSizePrice = 0;

    function runCalculation() {
        let total = 0;
        selects.forEach(s => {
            const price = parseFloat(s.options[s.selectedIndex]?.getAttribute('data-price')) || 0;
            total += price;
        });
        total += currentSizePrice;
        priceDisplay.value = total.toFixed(2);
    }

    selects.forEach(s => s.addEventListener('change', runCalculation));

    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            hiddenSize.value = btn.getAttribute('data-id');
            currentSizePrice = parseFloat(btn.getAttribute('data-price')) || 0;
            runCalculation();
        });
    });
});

function handlePostFileChange(input) {
    const textElement = document.getElementById('post_img_text');
    if (input.files && input.files[0]) {
        textElement.innerHTML = `<span style="color: #0a332c; font-weight: 600;">Selected: ${input.files[0].name}</span>`;
    } else {
        textElement.innerText = "Click to upload product photo";
    }
}