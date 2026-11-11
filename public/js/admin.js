(() => {
    document.querySelectorAll('[data-import-file]').forEach((input) => {
        const label = input.closest('.import-drop');
        const name = label ? label.querySelector('[data-selected-name]') : null;
        if (!name) return;

        const defaultName = name.textContent;
        input.addEventListener('change', () => {
            const files = Array.from(input.files || []);
            name.textContent = files.length > 1
                ? `${files.length} ملفات محددة`
                : files.length === 1
                    ? files[0].name
                    : defaultName;
        });
    });

    document.querySelectorAll('form[method="post"], form[method="POST"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            });
        });
    });
})();