document.addEventListener('DOMContentLoaded', () => {
    // Elements Meta & Live Preview
    const inputTitle = document.getElementById('meta_title');
    const inputDesc = document.getElementById('meta_description');
    const inputImg = document.getElementById('meta_image');

    const previewTitle = document.getElementById('preview-title');
    const previewDesc = document.getElementById('preview-desc');
    const previewImg = document.getElementById('preview-img');

    // Live Preview Listeners
    if (inputTitle) inputTitle.addEventListener('input', (e) => previewTitle.textContent = e.target.value.trim() || 'Untitled Link');
    if (inputDesc) inputDesc.addEventListener('input', (e) => previewDesc.textContent = e.target.value.trim() || 'Provide a description to see it here...');
    if (inputImg) {
        inputImg.addEventListener('input', (e) => {
            previewImg.src = e.target.value.trim() || 'https://via.placeholder.com/600x314?text=IMAGE+PREVIEW';
        });
    }

    // Form Submit via AJAX
    const form = document.getElementById('generator-form');
    const resultWrapper = document.getElementById('result-wrapper');
    const resultText = document.getElementById('result-text');
    const btnCopy = document.getElementById('btn-copy');
    const btnClear = document.getElementById('btn-clear');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        try {
            const response = await fetch('api/generate.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                resultText.textContent = data.links.join('\n');
                resultWrapper.classList.remove('hidden');
            } else {
                alert('Gagal: ' + data.message);
            }
        } catch (err) {
            alert('Terjadi kesalahan koneksi server');
        }
    });

    // Copy All
    btnCopy.addEventListener('click', () => {
        if (resultText.textContent) {
            navigator.clipboard.writeText(resultText.textContent);
            alert('Semua link berhasil disalin!');
        }
    });

    // Clear
    btnClear.addEventListener('click', () => {
        resultText.textContent = '';
        resultWrapper.classList.add('hidden');
    });
});
