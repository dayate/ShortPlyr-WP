document.addEventListener('DOMContentLoaded', () => {
    const apiKeyInput = document.getElementById('shortplyr_melolo_api_key');
    const toggleButton = document.getElementById('toggle-api-key');

    if (!apiKeyInput || !toggleButton) {
        return;
    }

    const eyeIcon = toggleButton.querySelector('i');
    if (!eyeIcon) return;

    // A flag to check if we have already revealed the real key.
    let isKeyRevealed = false;

    toggleButton.addEventListener('click', () => {
        if (apiKeyInput.type === 'password') {
            // If this is the first time showing the key, get it from the data attribute.
            if (!isKeyRevealed) {
                const realApiKey = apiKeyInput.dataset.apikey;
                if (realApiKey) {
                    apiKeyInput.value = realApiKey;
                    isKeyRevealed = true;
                }
            }
            apiKeyInput.type = 'text';
            eyeIcon.classList.remove('ri-eye-line');
            eyeIcon.classList.add('ri-eye-off-line');
        } else {
            apiKeyInput.type = 'password';
            eyeIcon.classList.remove('ri-eye-off-line');
            eyeIcon.classList.add('ri-eye-line');
        }
    });
});
