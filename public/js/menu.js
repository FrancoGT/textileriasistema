(() => {
    function organizeRecords(page) {
        const view = page.defaultView;
        const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('es');
        page.querySelectorAll('.workspace-table-scroll > table').forEach((table, index) => {
            if (!table.querySelector('th') || table.querySelector('select, textarea, input:not([type=radio]):not([type=checkbox]):not([type=hidden])')) return;
            const rows = [...table.rows].filter(row => row.querySelector('td') && !row.querySelector('th'));
            const records = rows.map(row => ({ row, text: normalize(row.textContent) }));
            const scroll = table.parentElement;
            const section = page.createElement('section');
            section.className = 'workspace-records';
            section.setAttribute('aria-label', 'Listado de registros');
            scroll.before(section);
            section.append(scroll);
            scroll.classList.add('workspace-records-scroll');
            table.classList.add('workspace-records-table');
            const toolbar = page.createElement('div');
            toolbar.className = 'workspace-records-toolbar';
            if (section.nextElementSibling?.matches('.workspace-actions')) toolbar.append(section.nextElementSibling);
            const label = page.createElement('label');
            label.className = 'workspace-records-search';
            label.textContent = 'Buscar registros';
            const search = page.createElement('input');
            search.type = 'text';
            search.placeholder = 'Escriba para buscar…';
            search.autocomplete = 'off';
            search.setAttribute('aria-controls', table.id || (table.id = `workspace-records-${index}`));
            search.addEventListener('keydown', event => { if (event.key === 'Enter') event.preventDefault(); });
            label.append(search);
            toolbar.append(label);
            section.prepend(toolbar);
            const footer = page.createElement('div');
            footer.className = 'workspace-records-footer';
            const status = page.createElement('span');
            status.setAttribute('role', 'status');
            const sizeLabel = page.createElement('label');
            sizeLabel.textContent = 'Registros por página ';
            const size = page.createElement('select');
            [10, 25, 50, 100].forEach(value => size.add(new view.Option(String(value), String(value))));
            sizeLabel.append(size);
            const previous = page.createElement('button');
            previous.type = 'button';
            previous.textContent = 'Anterior';
            const next = page.createElement('button');
            next.type = 'button';
            next.textContent = 'Siguiente';
            const position = page.createElement('span');
            const navigation = page.createElement('div');
            navigation.className = 'workspace-records-pagination';
            navigation.append(previous, position, next);
            footer.append(status, sizeLabel, navigation);
            section.append(footer);
            const empty = page.createElement('p');
            empty.textContent = 'No se encontraron registros.';
            scroll.after(empty);
            let matches = records, current = 0, revision = 0, timer;
            function render() {
                const limit = Number(size.value);
                const pages = Math.max(1, Math.ceil(matches.length / limit));
                current = Math.min(current, pages - 1);
                const visible = new Set(matches.slice(current * limit, (current + 1) * limit).map(record => record.row));
                rows.forEach(row => {
                    row.hidden = !visible.has(row);
                    if (row.hidden) row.querySelectorAll('input:checked').forEach(control => { control.checked = false; });
                });
                empty.hidden = matches.length !== 0;
                status.textContent = matches.length ? `${current * limit + 1}–${Math.min((current + 1) * limit, matches.length)} de ${matches.length} registros` : '0 registros';
                if (search.value.trim()) status.textContent += ` (${records.length} en total)`;
                position.textContent = `${current + 1} / ${pages}`;
                previous.disabled = current === 0;
                next.disabled = current >= pages - 1;
                table.removeAttribute('aria-busy');
            }
            search.addEventListener('input', () => {
                view.clearTimeout(timer);
                const version = ++revision;
                table.setAttribute('aria-busy', 'true');
                status.textContent = 'Buscando…';
                timer = view.setTimeout(() => {
                    const words = normalize(search.value.trim()).split(/\s+/).filter(Boolean);
                    const result = [];
                    let offset = 0;
                    function batch() {
                        if (version !== revision) return;
                        const end = Math.min(offset + 500, records.length);
                        for (; offset < end; offset++) {
                            if (words.every(word => records[offset].text.includes(word))) result.push(records[offset]);
                        }
                        if (offset < records.length) view.setTimeout(batch, 0);
                        else { matches = result; current = 0; render(); }
                    }
                    batch();
                }, 250);
            });
            size.addEventListener('change', () => { current = 0; render(); });
            previous.addEventListener('click', () => { current--; render(); });
            next.addEventListener('click', () => { current++; render(); });
            render();
        });
    }
    const frame = document.getElementById('erp-workspace');
    if (!frame) return;
    const toggle = document.getElementById('menu-toggle');
    const title = document.getElementById('workspace-title');
    const status = document.getElementById('workspace-status');
    const welcome = document.getElementById('workspace-welcome');
    const links = [...document.querySelectorAll('.menu-group a'), ...document.querySelectorAll('.user-account-link')];
    const userToggle = document.getElementById('user-toggle');
    const userPanel = document.getElementById('user-panel');
    const userAccount = document.getElementById('user-account');
    const closeUser = (restoreFocus = false) => {
        if (!userToggle) return;
        const wasOpen = !userPanel.hidden;
        userPanel.hidden = true;
        userToggle.setAttribute('aria-expanded', 'false');
        userToggle.setAttribute('aria-label', 'Abrir opciones de usuario');
        if (restoreFocus && wasOpen) userToggle.focus();
    };
    if (userToggle) {
        userToggle.addEventListener('click', () => {
            const open = userPanel.hidden;
            userPanel.hidden = !open;
            userToggle.setAttribute('aria-expanded', String(open));
            userToggle.setAttribute('aria-label', open ? 'Cerrar opciones de usuario' : 'Abrir opciones de usuario');
            if (open) closeMenu();
        });
        userToggle.addEventListener('keydown', event => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (userPanel.hidden) userToggle.click();
                userPanel.querySelector('a').focus();
            }
        });
        document.addEventListener('click', event => { if (!userAccount.contains(event.target)) closeUser(); });
        document.addEventListener('focusin', event => { if (!userAccount.contains(event.target)) closeUser(); });
    }
    let finishNavigation = () => {};
    document.querySelectorAll('.menu-group').forEach(group => {
        const summary = group.querySelector('summary');
        const syncExpanded = () => summary.setAttribute('aria-expanded', String(group.open));
        group.addEventListener('toggle', syncExpanded);
        syncExpanded();
    });
    const closeMenu = () => {
        document.body.classList.remove('menu-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Abrir menú');
    };
    toggle.addEventListener('click', () => {
        closeUser();
        const open = document.body.classList.toggle('menu-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') { closeMenu(); closeUser(true); }
    });
    links.forEach(link => link.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        closeUser();
        finishNavigation();
        finishNavigation = window.ErpFeedback?.start(link, 'Cargando módulo. Espere un momento…') || (() => {});
        welcome.hidden = true;
        frame.hidden = false;
        frame.setAttribute('aria-busy', 'true');
        title.textContent = link.textContent.trim();
        status.textContent = 'Cargando…';
        links.forEach(item => item.removeAttribute('aria-current'));
        link.setAttribute('aria-current', 'page');
        closeMenu();
    }));
    frame.addEventListener('load', () => {
        finishNavigation();
        frame.removeAttribute('aria-busy');
        status.textContent = '';
        const page = frame.contentDocument;
        if (!page || frame.contentWindow.location.href === 'about:blank') return;
        window.ErpFeedback?.attach(page);
        page.addEventListener('pointerdown', () => closeUser());
        page.addEventListener('focusin', () => closeUser());
        const path = frame.contentWindow.location.pathname;
        if (/\/(?:index\.php)?$/.test(path)) {
            window.location.assign('/index.php');
            return;
        }
        if (/\/Mnu0000\.php$/.test(path)) {
            frame.hidden = true;
            welcome.hidden = false;
            title.textContent = 'Inicio';
            status.textContent = '';
            links.forEach(link => link.removeAttribute('aria-current'));
            return;
        }
        const heading = page.querySelector('#MH h1');
        if (heading) title.textContent = heading.textContent.trim();
        if (!page.getElementById('workspace-styles')) {
            const style = page.createElement('style');
            style.id = 'workspace-styles';
            style.textContent = `
                #MH { display: none !important; }
                body { margin: 0; }
                .wrapper { box-sizing: border-box; width: 100%; margin: 0; border: 0; }
                #navigation { float: none; width: 100%; text-align: left; }
                #content { margin-left: 0; min-height: 0; padding: 5px; }
                input, select, textarea { max-width: 100%; box-sizing: border-box; }
                .workspace-records { width: 100%; min-width: 0; box-sizing: border-box; padding: 16px; }
                .workspace-records-toolbar, .workspace-records-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
                .workspace-records-toolbar { margin-bottom: 16px; }
                .workspace-records-search { display: flex; flex-direction: column; gap: 6px; margin-left: auto; width: min(100%, 320px); }
                .workspace-records-search input { width: 100%; min-height: 36px; padding: 8px; }
                .workspace-records-scroll { width: 100% !important; height: auto !important; max-height: none; overflow-x: auto; }
                .workspace-records-table { width: 100%; }
                .workspace-records-table th, .workspace-records-table td { padding: 10px 12px; vertical-align: middle; }
                .workspace-records-table [hidden] { display: none !important; }
                .workspace-records-footer { margin-top: 16px; }
                .workspace-records-pagination { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
                .workspace-records button, .workspace-records select { min-height: 36px; }
                .workspace-records .workspace-actions { margin: 0; }
                @media (max-width: 760px) {
                    .wrapper, #navigation, #navcontainer, #content { min-width: 0; max-width: 100%; box-sizing: border-box; }
                    .workspace-actions, .workspace-actions > tbody { display: block; width: 100%; }
                    .workspace-actions tr { display: flex; flex-wrap: wrap; gap: 8px; padding: 5px; }
                    .workspace-actions td { display: block; padding: 0; min-width: 0; }
                    .workspace-actions input, .workspace-actions button { width: auto; max-width: 100%; min-height: 44px; height: auto; padding: 8px 16px; }
                    .workspace-form, .workspace-form > tbody { display: block; width: 100%; }
                    .workspace-form tr { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; padding: 5px; }
                    .workspace-form td { display: block; flex: 1 1 220px; min-width: 0; padding: 0; overflow-wrap: anywhere; }
                    .workspace-form td:first-child { flex: 0 1 140px; }
                    .workspace-form input, .workspace-form select, .workspace-form textarea { min-width: 0; max-width: 100%; }
                    .workspace-form select { width: 100%; min-height: 44px; }
                    .workspace-table-scroll { max-width: 100%; min-width: 0; box-sizing: border-box; overflow-x: auto; }
                }
            `;
            page.head.appendChild(style);
            page.querySelectorAll('#navigation table, #content table').forEach(table => {
                if (table.parentElement.closest('table')) return;
                const controls = [...table.querySelectorAll('input:not([type=hidden]), button, select, textarea')];
                const actions = controls.length && controls.every(control => ['submit', 'button', 'reset'].includes(control.type))
                    && [...table.querySelectorAll('td, th')].every(cell => !cell.textContent.trim());
                if (actions) {
                    table.classList.add('workspace-actions');
                } else if (!table.querySelector('th') && controls.some(control => control.matches('select, textarea, input:not([type=radio]):not([type=checkbox])'))) {
                    table.classList.add('workspace-form');
                } else {
                    const parent = table.parentElement;
                    if (['auto', 'scroll'].includes(page.defaultView.getComputedStyle(parent).overflowX)) {
                        parent.classList.add('workspace-table-scroll');
                    } else {
                        const container = page.createElement('div');
                        container.className = 'workspace-table-scroll';
                        table.before(container);
                        container.appendChild(table);
                    }
                }
            });
        }
        organizeRecords(page);
        status.textContent = '';
    });
})();
