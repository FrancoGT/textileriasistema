(() => {
    let sequence = 0;
    function attach(select) {
        if (select.dataset.searchAttached) return;
        select.dataset.searchAttached = '1';
        const wrapper = document.createElement('span');
        wrapper.className = 'erp-select-search';
        const input = document.createElement('input');
        input.type = 'search';
        input.placeholder = 'Buscar opción…';
        input.setAttribute('aria-label', `Buscar en ${select.getAttribute('aria-label') || select.name || 'lista'}`);
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        if (select.getAttribute('aria-labelledby')) input.setAttribute('aria-labelledby', select.getAttribute('aria-labelledby'));
        if (select.required) input.setAttribute('aria-required', 'true');
        input.autocomplete = 'off';
        const list = document.createElement('div');
        list.id = `select-results-${++sequence}`;
        list.setAttribute('role', 'listbox');
        input.setAttribute('aria-controls', list.id);
        const status = document.createElement('div');
        status.setAttribute('role', 'status');
        list.hidden = true;
        select.before(wrapper);
        wrapper.append(input, list, status);
        const selectionText = () => [...select.options]
            .filter((option, index) => select.multiple ? option.selected : index === select.selectedIndex)
            .map(option => option.textContent.trim()).join(', ');
        const restoreSelection = () => { input.value = selectionText(); };
        let timer, controller, generation = 0, active = -1;
        const close = () => {
            clearTimeout(timer);
            controller?.abort();
            generation++;
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
        };
        const choose = item => {
            const option = select.options[item.index];
            if (!option || option.disabled || option.value !== item.value || select.disabled) return;
            if (select.multiple) option.selected = !option.selected;
            else select.selectedIndex = item.index;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            restoreSelection();
            status.textContent = '';
            close();
        };
        async function search(version, query = input.value.trim()) {
            controller = new AbortController();
            status.textContent = 'Buscando…';
            try {
                const url = new URL('/api/select-search', location.origin);
                url.searchParams.set('key', select.dataset.searchKey);
                url.searchParams.set('q', query);
                const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin', cache: 'no-store' });
                if (!response.ok) throw new Error('search');
                const data = await response.json();
                if (version !== generation) return;
                list.replaceChildren();
                active = -1;
                data.results.forEach((item, index) => {
                    const row = document.createElement('div');
                    row.id = `${list.id}-${index}`;
                    row.setAttribute('role', 'option');
                    row.setAttribute('aria-selected', 'false');
                    row.textContent = item.label;
                    row.addEventListener('mousedown', event => event.preventDefault());
                    row.addEventListener('click', () => choose(item));
                    list.append(row);
                });
                list.hidden = data.results.length === 0;
                input.setAttribute('aria-expanded', String(!list.hidden));
                status.textContent = data.more ? 'Hay más resultados. Escriba más texto.' : data.results.length ? '' : 'Sin resultados.';
            } catch (error) {
                if (error.name !== 'AbortError' && version === generation) {
                    close();
                    status.textContent = 'No se pudo cargar la lista. Intente abrirla de nuevo o recargue el panel.';
                }
            }
        }
        input.addEventListener('input', () => {
            close();
            const version = generation;
            timer = setTimeout(() => search(version), 250);
        });
        const open = () => {
            if (select.disabled) return;
            close();
            input.setAttribute('aria-expanded', 'true');
            input.select?.();
            return search(generation, '');
        };
        input.addEventListener('focus', open);
        input.addEventListener('click', () => {
            if (input.getAttribute('aria-expanded') === 'false') return open();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') { close(); restoreSelection(); status.textContent = ''; return; }
            if (event.key === 'Enter') {
                event.preventDefault();
                if (!list.hidden && active >= 0) list.children[active].click();
            }
            if (['ArrowDown', 'ArrowUp'].includes(event.key) && list.hidden) {
                event.preventDefault();
                open();
                return;
            }
            if (['ArrowDown', 'ArrowUp'].includes(event.key) && !list.hidden) {
                event.preventDefault();
                active = (active + (event.key === 'ArrowDown' ? 1 : -1) + list.children.length) % list.children.length;
                [...list.children].forEach((row, index) => row.setAttribute('aria-selected', String(index === active)));
                input.setAttribute('aria-activedescendant', list.children[active].id);
                list.children[active].scrollIntoView({ block: 'nearest' });
            }
        });
        input.addEventListener('blur', () => { close(); restoreSelection(); });
        select.addEventListener('change', restoreSelection);
        select.addEventListener('invalid', event => {
            event.preventDefault();
            input.focus();
            input.setAttribute('aria-invalid', 'true');
            status.textContent = select.validationMessage;
        });
        select.addEventListener('input', () => input.removeAttribute('aria-invalid'));
        const sync = () => { input.disabled = select.disabled; if (select.disabled) close(); };
        new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['disabled'] });
        sync();
        select.form?.addEventListener('reset', () => {
            status.textContent = '';
            close();
            setTimeout(restoreSelection, 0);
        });
        restoreSelection();
        select.hidden = true;
    }
    const scan = () => document.querySelectorAll('select[data-search-key]').forEach(attach);
    scan();
    new MutationObserver(scan).observe(document.body, { childList: true, subtree: true });
})();
