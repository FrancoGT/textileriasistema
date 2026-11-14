(() => {
    if (window.ErpFeedback) return;

    function attach(page) {
        if (page.__erpFeedbackController) return page.__erpFeedbackController;
        const view = page.defaultView;
        const styles = page.createElement('link');
        styles.rel = 'stylesheet';
        styles.href = '/css/ui-feedback.css';
        page.head.appendChild(styles);
        const notice = page.createElement('div');
        notice.className = 'erp-feedback-notice';
        notice.hidden = true;
        const spinner = page.createElement('span');
        spinner.className = 'erp-feedback-spinner';
        spinner.setAttribute('aria-hidden', 'true');
        const message = page.createElement('span');
        message.setAttribute('role', 'status');
        message.setAttribute('aria-live', 'polite');
        message.setAttribute('aria-atomic', 'true');
        const dismiss = page.createElement('button');
        dismiss.type = 'button';
        dismiss.className = 'erp-feedback-dismiss';
        dismiss.setAttribute('aria-label', 'Ocultar aviso de carga');
        dismiss.textContent = '×';
        notice.append(spinner, message, dismiss);
        page.body.appendChild(notice);
        const active = new Map();
        let timer;
        const restore = (element, values) => {
            for (const [name, value] of Object.entries(values)) {
                if (value === null) element.removeAttribute(name);
                else element.setAttribute(name, value);
            }
            element.classList.remove('erp-feedback-busy');
        };
        const reset = () => {
            active.forEach((values, element) => restore(element, values));
            active.clear();
            view.clearTimeout(timer);
            page.documentElement.classList.remove('erp-feedback-pending');
            message.textContent = '';
            notice.hidden = true;
        };
        const start = (element, text = 'Procesando. Espere un momento…') => {
            if (active.has(element)) return () => {};
            const values = { 'aria-busy': element.getAttribute('aria-busy'), 'aria-disabled': element.getAttribute('aria-disabled') };
            active.set(element, values);
            element.setAttribute('aria-busy', 'true');
            element.setAttribute('aria-disabled', 'true');
            element.classList.add('erp-feedback-busy');
            page.documentElement.classList.add('erp-feedback-pending');
            notice.hidden = false;
            message.textContent = text;
            view.clearTimeout(timer);
            timer = view.setTimeout(() => {
                message.textContent = 'La operación está tardando más de lo habitual. Puede seguir en curso; espere antes de repetirla.';
            }, 15000);
            return () => {
                const stored = active.get(element);
                if (!stored) return;
                restore(element, stored);
                active.delete(element);
                if (!active.size) reset();
            };
        };
        dismiss.addEventListener('click', () => { notice.hidden = true; });
        page.addEventListener('submit', event => {
            if (active.has(event.target)) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
        page.addEventListener('submit', event => {
            const form = event.target;
            if (event.defaultPrevented || form.id === 'login-form' || form.dataset.loading === 'false') return;
            const target = event.submitter?.formTarget || form.target;
            if (target && !['_self', '_top', '_parent'].includes(target)) return;
            // Native submit controls must remain successful controls in the POST payload.
            const finishForm = start(form);
            const finishButton = event.submitter ? start(event.submitter) : () => {};
            view.queueMicrotask(() => {
                if (event.defaultPrevented) { finishButton(); finishForm(); }
            });
        });
        page.addEventListener('click', event => {
            if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            const link = event.target.closest('a[href]');
            if (!link || link.dataset.loading === 'false' || link.hasAttribute('download')) return;
            if (link.getAttribute('href').trim().startsWith('#')) return;
            if (link.target && !['_self', '_top', '_parent'].includes(link.target)) return;
            const url = new URL(link.href, page.baseURI);
            if (url.origin !== view.location.origin || !/^https?:$/.test(url.protocol)) return;
            if (url.pathname === view.location.pathname && url.search === view.location.search && url.hash) return;
            if (/\.(pdf|xlsx?|csv|zip|docx?|rtf)$/i.test(url.pathname)) return;
            if (active.has(link)) { event.preventDefault(); return; }
            if (event.defaultPrevented) return;
            const finish = start(link, 'Abriendo página. Espere un momento…');
            view.queueMicrotask(() => { if (event.defaultPrevented) finish(); });
        });
        view.addEventListener('pageshow', reset);
        view.addEventListener('online', () => {
            if (active.size) message.textContent = 'Conexión recuperada. La operación puede seguir en curso.';
        });
        view.addEventListener('offline', () => {
            if (active.size) message.textContent = 'Se perdió la conexión. Espere a recuperarla antes de repetir la operación.';
        });
        const api = { start, reset };
        Object.defineProperty(page, '__erpFeedbackController', { value: api });
        if (!view.ErpFeedback) view.ErpFeedback = window.ErpFeedback;
        return api;
    }
    window.ErpFeedback = { attach, start: (element, text) => attach(element.ownerDocument).start(element, text) };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => attach(document), { once: true });
    else attach(document);
})();
