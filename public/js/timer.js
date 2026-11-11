(() => {
    const app = document.getElementById('practice-app');
    const timer = document.getElementById('practice-timer');
    if (!app || !timer || !app.dataset.attemptId) return;

    const storageKey = `step-practice-started-${app.dataset.attemptId}`;
    let startedAt = Number(window.localStorage.getItem(storageKey));
    if (!Number.isFinite(startedAt) || startedAt <= 0) {
        startedAt = Date.now();
        window.localStorage.setItem(storageKey, String(startedAt));
    }

    const render = () => {
        const seconds = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const remainingSeconds = String(seconds % 60).padStart(2, '0');
        timer.textContent = `${minutes}:${remainingSeconds}`;
    };

    render();
    const interval = window.setInterval(render, 1000);
    document.addEventListener('step:practice-finished', () => {
        window.clearInterval(interval);
        window.localStorage.removeItem(storageKey);
    }, { once: true });
})();