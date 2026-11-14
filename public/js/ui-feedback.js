(() => {
    if (window.ErpFeedback) return;

    function attach(page) {
        if (page.__erpFeedbackController) return page.__erpFeedbackController;
        const view = page.defaultView;
        page.documentElement.classList.add('erp-ui');
        const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toLowerCase();
        const buttonSelector = 'input[type=submit], input[type=button], button';
        const decorateButton = control => {
            if (control.closest('.menu-header')) return;
            const action = normalize(control.value || control.textContent);
            if (/^(nuevo|nueva|grabar|guardar|registrar|confirmar|aceptar|aplicar|generar|iniciar sesion)$/.test(action)) control.classList.add('erp-primary');
            if (/^(eliminar|borrar|anular)$/.test(action)) control.classList.add('erp-danger');
            const icons = { nuevo: '-16px -128px', nueva: '-16px -128px', editar: '-64px -112px', eliminar: '-176px -96px', buscar: '-160px -112px', grabar: '-96px -112px', guardar: '-96px -112px', descargar: '-224px -48px' };
            if (icons[action] && !control.closest('.erp-action-with-icon')) {
                const wrapper = page.createElement('span');
                wrapper.className = 'erp-action-with-icon';
                const icon = page.createElement('span');
                icon.className = 'erp-action-icon';
                icon.style.backgroundPosition = icons[action];
                icon.setAttribute('aria-hidden', 'true');
                control.before(wrapper);
                wrapper.append(icon, control);
            }
            if (!action && !control.hasAttribute('aria-label') && control.title) control.setAttribute('aria-label', control.title);
        };
        page.querySelectorAll(buttonSelector).forEach(decorateButton);
        const buttonsObserver = new view.MutationObserver(records => {
            records.forEach(record => record.addedNodes.forEach(node => {
                if (node.nodeType !== 1) return;
                if (node.matches(buttonSelector)) decorateButton(node);
                node.querySelectorAll(buttonSelector).forEach(decorateButton);
            }));
        });
        buttonsObserver.observe(page.body, { childList: true, subtree: true });
        page.querySelectorAll('#navigation table, #content table, .ui-dialog table').forEach(table => {
            if (table.parentElement.closest('table')) return;
            const controls = [...table.querySelectorAll('input:not([type=hidden]), button, select, textarea')];
            if (table.querySelector('th')) {
                table.classList.add('erp-data-table');
                const headings = [...table.querySelectorAll('tr:first-child th, tr:first-child td')];
                headings.forEach((heading, column) => {
                    if (heading.tagName === 'TH' && !heading.hasAttribute('scope')) heading.setAttribute('scope', 'col');
                    const selectionImage = heading.querySelector('img[src$="Check.png"], img[src$="Check.jpg"]');
                    if (selectionImage) {
                        selectionImage.src = '/CSS/Check.jpg';
                        selectionImage.alt = 'Seleccionar registro';
                    }
                    if (/^(cantidad|importe|precio|total|saldo|monto|costo|stock|granulado|debe|haber)(\s|$)/.test(normalize(heading.textContent))) {
                        [...table.rows].forEach(row => row.cells[column]?.classList.add('erp-number'));
                    }
                });
                const parent = table.parentElement;
                if (['auto', 'scroll'].includes(view.getComputedStyle(parent).overflowX)) parent.classList.add('erp-table-scroll');
                else {
                    const scroll = page.createElement('div');
                    scroll.className = 'erp-table-scroll';
                    table.before(scroll);
                    scroll.append(table);
                }
            } else if (controls.length && controls.every(control => ['submit', 'button', 'reset'].includes(control.type))) {
                table.classList.add('erp-actions');
            } else if (controls.length) {
                table.classList.add('erp-form');
                [...table.rows].forEach((row, rowIndex) => {
                    [...row.cells].forEach((cell, cellIndex) => {
                        const field = cell.querySelector('input:not([type=hidden]), select, textarea');
                        const caption = row.cells[cellIndex - 1];
                        if (!field || !caption || caption.querySelector('input, select, textarea') || !caption.textContent.trim() || field.labels?.length || field.hasAttribute('aria-label')) return;
                        if (!caption.id) caption.id = `erp-label-${page.querySelectorAll('.erp-form').length}-${rowIndex}-${cellIndex}`;
                        field.setAttribute('aria-labelledby', caption.id);
                    });
                });
            }
        });
        page.querySelectorAll('.erp-data-table input[type=radio], .erp-data-table input[type=checkbox]').forEach(control => {
            if (!control.labels?.length && !control.hasAttribute('aria-label')) {
                const row = control.closest('tr');
                control.setAttribute('aria-label', `Seleccionar ${row?.cells[0]?.textContent.trim() || 'registro'}`);
            }
        });
        page.querySelectorAll('.erp-table-scroll').forEach(scroll => {
            scroll.tabIndex = 0;
            scroll.setAttribute('role', 'region');
            scroll.setAttribute('aria-label', 'Tabla de registros, desplazamiento horizontal disponible');
        });
        page.querySelectorAll('a[onclick]:not([href])').forEach(link => {
            link.setAttribute('role', 'button');
            link.tabIndex = 0;
            link.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); link.click(); }
            });
        });
        const styles = page.createElement('link');
        styles.rel = 'stylesheet';
        styles.href = '/css/ui-feedback.css';
        if (!page.querySelector('link[href="/css/ui-feedback.css"]')) page.head.appendChild(styles);
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
