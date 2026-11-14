(() => {
    const form = document.getElementById('login-form');
    if (!form || !window.fetch) return;

    const submit = document.getElementById('login-submit');
    const message = document.getElementById('login-message');
    let pending = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pending || !form.reportValidity()) return;

        pending = true;
        submit.disabled = true;
        submit.value = 'Validando…';
        form.setAttribute('aria-busy', 'true');
        delete message.dataset.error;
        message.textContent = 'Validando sus datos. Espere un momento.';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.ok) {
                message.dataset.error = '';
                message.textContent = result.message || 'No se pudo iniciar sesión. Intente nuevamente.';
                return;
            }
            const destination = new URL(result.redirect, form.action);
            if (destination.origin !== window.location.origin) throw new Error('Invalid redirect');
            message.textContent = 'Sesión iniciada. Abriendo el sistema…';
            window.location.assign(destination.href);
        } catch {
            message.dataset.error = '';
            message.textContent = 'No se pudo completar la solicitud. Revise su conexión e intente nuevamente.';
        } finally {
            pending = false;
            submit.disabled = false;
            submit.value = 'Iniciar sesión';
            form.removeAttribute('aria-busy');
        }
    });
})();
