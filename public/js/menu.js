(() => {
    const frame = document.getElementById('erp-workspace');
    if (!frame) return;
    const toggle = document.getElementById('menu-toggle');
    const title = document.getElementById('workspace-title');
    const status = document.getElementById('workspace-status');
    const welcome = document.getElementById('workspace-welcome');
    const links = [...document.querySelectorAll('.menu-group a')];
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
        const open = document.body.classList.toggle('menu-open');
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
    });
    links.forEach(link => link.addEventListener('click', (event) => {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
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
        const page = frame.contentDocument;
        if (!page || frame.contentWindow.location.href === 'about:blank') return;
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
        status.textContent = '';
        frame.removeAttribute('aria-busy');
    });
})();
