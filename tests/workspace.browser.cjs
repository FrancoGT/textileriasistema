const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const http = require('node:http');
const { spawn } = require('node:child_process');

function cachedBinary(name, executable) {
    const root = path.join(os.homedir(), '.cache', 'selenium', name, 'win64');
    return path.join(root, fs.readdirSync(root).sort().at(-1), executable);
}

function moduleFixture(name) {
    const html = fs.readFileSync(`legacy/Plantillas/${name}.tpl`, 'utf8')
        .replace(/\{if isset\(\$maintenance(?:Token|Message)\)[\s\S]*?\{\/if\}/g, '');
    const start = html.indexOf('{if $scBehavior');
    const branch = html.slice(html.indexOf('}', start) + 1, html.indexOf('{elseif', start));
    return (html.slice(0, start) + branch + html.slice(html.lastIndexOf('{/if}') + 5))
        .replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '')
        .replace(/onload=f_Desactivar\(\)/, '')
        .replace(/\{foreach[^}]*\}([\s\S]*?)\{\/foreach\}/g, (_, row) => name === 'ERP1060' ? row.repeat(25) : row)
        .replace(/\{\$i\['([0-5])'\]\}/g, (value, column) => name === 'ERP1050'
            ? ['01001', 'Hilo de algodon', 'Materia prima', 'Kilogramo', '20', '12.50'][column] : value)
        .replace(/\{\$[^}]+\}/g, name === 'ERP1060'
            ? 'PROVEEDOR_CON_RAZON_SOCIAL_EXTENSA_PARA_COMPROBAR_EL_DESPLAZAMIENTO_LOCAL'
            : 'Personal con un nombre muy largo para comprobar el ancho del selector');
}

test('shared mobile workspace contains controls and wide supplier tables', { timeout: 60000 }, async () => {
    const submissions = [];
    let releaseSave;
    const server = http.createServer((req, res) => {
        let content, type = 'text/html; charset=utf-8';
        if (/^\/ERP(1000|1050|1060)\.php/.test(req.url)) content = moduleFixture(req.url.slice(1, 8));
        else if (req.url === '/Mnu0000.php') content = fs.readFileSync('legacy/Plantillas/Mnu0000.tpl');
        else if (req.url === '/CSS/Sixavi.css') { content = fs.readFileSync('legacy/CSS/Sixavi.css'); type = 'text/css'; }
        else if (req.url === '/css/menu.css') { content = fs.readFileSync('public/css/menu.css'); type = 'text/css'; }
        else if (req.url === '/js/menu.js') { content = fs.readFileSync('public/js/menu.js'); type = 'text/javascript'; }
        else if (req.url === '/js/ui-feedback.js') { content = fs.readFileSync('public/js/ui-feedback.js'); type = 'text/javascript'; }
        else if (req.url === '/css/ui-feedback.css') { content = fs.readFileSync('public/css/ui-feedback.css'); type = 'text/css'; }
        else if (req.url === '/js/login.js') { content = fs.readFileSync('public/js/login.js'); type = 'text/javascript'; }
        else if (/^\/CSS\/(?:jquery-ui-1\.11\.0\/images\/)?[\w.-]+\.(png|gif|jpg)$/i.test(req.url) && fs.existsSync(`legacy${req.url}`)) {
            content = fs.readFileSync(`legacy${req.url}`);
            type = req.url.endsWith('.png') ? 'image/png' : req.url.endsWith('.gif') ? 'image/gif' : 'image/jpeg';
        }
        else if (req.url === '/feedback-save') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', () => {
                submissions.push(body);
                const timer = setTimeout(() => { if (!res.writableEnded) res.end('<!doctype html><body>Saved</body>'); }, 5000);
                releaseSave = () => { clearTimeout(timer); res.end('<!doctype html><body>Saved</body>'); };
            });
            return;
        }
        else if (req.url === '/feedback-login') {
            setTimeout(() => { res.writeHead(401, { 'Content-Type': 'application/json' }); res.end('{"ok":false,"message":"Credenciales inválidas"}'); }, 300);
            return;
        }
        else { res.writeHead(404); res.end(); return; }
        res.setHeader('Content-Type', type); res.end(content);
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const port = 19517;
    const driver = spawn(process.env.CHROMEDRIVER || cachedBinary('chromedriver', 'chromedriver.exe'), [`--port=${port}`], { stdio: 'ignore', windowsHide: true });
    const request = async (route, body, method = body ? 'POST' : 'GET') => {
        const response = await fetch(`http://127.0.0.1:${port}${route}`, { method, body: body ? JSON.stringify(body) : undefined, headers: { 'Content-Type': 'application/json' }, signal: AbortSignal.timeout(15000) });
        const json = await response.json();
        if (json.value?.error) throw Error(JSON.stringify(json.value));
        return json.value;
    };
    let session;
    try {
        for (let attempt = 0; attempt < 30; attempt++) {
            try { await request('/status'); break; } catch { await new Promise(resolve => setTimeout(resolve, 100)); }
        }
        session = (await request('/session', { capabilities: { alwaysMatch: { browserName: 'chrome', 'goog:chromeOptions': { binary: process.env.CHROME_BINARY || cachedBinary('chrome', 'chrome.exe'), args: ['--headless', '--disable-gpu', '--no-first-run', '--no-sandbox', '--disable-software-rasterizer'] } } } })).sessionId;
        const command = (route, body, method) => request(`/session/${session}${route}`, body, method);
        const execute = (fn, ...args) => command('/execute/sync', { script: `return (${fn}).apply(null, arguments)`, args });
        const failures = [];
        for (const width of [320, 375, 414, 1280]) {
            await command('/window/rect', { width: width + 100, height: 850 });
            await command('/url', { url: `http://127.0.0.1:${server.address().port}/Mnu0000.php` });
            await command('/goog/cdp/execute', { cmd: 'Emulation.setDeviceMetricsOverride', params: { width, height: 800, deviceScaleFactor: 1, mobile: false } });
            const underline = await execute(() => {
                const title = document.querySelector('.menu-header h1');
                const span = title.querySelector('span');
                if (!span) return null;
                const original = title.cloneNode(false);
                original.textContent = title.textContent;
                original.style.cssText = 'position:absolute;visibility:hidden;border-bottom:1px solid #EE6600';
                title.after(original);
                const range = document.createRange();
                range.selectNodeContents(span);
                const result = {
                    text: title.textContent,
                    underlinedText: span.textContent,
                    titleBorder: getComputedStyle(title).borderBottomWidth,
                    spanBorder: getComputedStyle(span).borderBottomWidth,
                    color: getComputedStyle(span).borderBottomColor,
                    width: span.getBoundingClientRect().width,
                    textWidth: range.getBoundingClientRect().width,
                    height: title.getBoundingClientRect().height,
                    originalHeight: original.getBoundingClientRect().height
                };
                original.remove();
                return result;
            });
            assert.ok(underline, 'system title has its own span');
            assert.equal(underline.text, 'SISTEMA ERP-X');
            assert.equal(underline.underlinedText, 'SISTEMA ERP-X');
            assert.equal(underline.titleBorder, width <= 760 ? '0px' : '1px');
            assert.equal(underline.spanBorder, width <= 760 ? '1px' : '0px');
            assert.equal(underline.height, underline.originalHeight, 'title height is unchanged');
            if (width <= 760) {
                assert.equal(underline.color, 'rgb(238, 102, 0)');
                assert.ok(Math.abs(underline.width - underline.textWidth) < 0.1, 'underline matches system title width');
            }
            const categories = () => execute(async () => {
                await new Promise(resolve => setTimeout(resolve, 30));
                return [...document.querySelectorAll('.menu-group')].map(group => {
                const summary = group.querySelector('summary');
                return {
                    open: group.open,
                    expanded: summary.getAttribute('aria-expanded'),
                    visible: group.querySelector('a').checkVisibility(),
                    indicator: getComputedStyle(summary, '::before').backgroundImage
                };
                });
            });
            const checkCategories = async () => {
                const groups = await categories();
                assert.equal(groups.length, 9);
                assert.ok(groups.every(group => !group.open && group.expanded === 'false' && !group.visible && group.indicator.includes('toggle-small-expand.png')), 'all categories initially closed');
            };
            await checkCategories();
            if (width <= 760) await execute(() => document.getElementById('menu-toggle').click());
            for (let index = 0; index < 9; index++) {
                const summary = await execute(index => document.querySelectorAll('.menu-group summary')[index], index);
                const elementId = summary['element-6066-11e4-a52e-4f735466cecf'];
                // Click the far edge of the row, outside the label and indicator.
                const point = await execute(index => {
                    const summary = document.querySelectorAll('.menu-group summary')[index];
                    summary.scrollIntoView({ block: 'center' });
                    const rect = summary.getBoundingClientRect();
                    return { x: Math.floor(rect.right - 8), y: Math.floor(rect.top + rect.height / 2) };
                }, index);
                await command('/actions', { actions: [{ type: 'pointer', id: 'mouse', parameters: { pointerType: 'mouse' }, actions: [
                    { type: 'pointerMove', duration: 0, origin: 'viewport', ...point },
                    { type: 'pointerDown', button: 0 }, { type: 'pointerUp', button: 0 }
                ] }] });
                let state = (await categories())[index];
                assert.ok(state.open && state.expanded === 'true' && state.visible && state.indicator.includes('/toggle-small.png'), `row click opens category ${width}/${index}: ${JSON.stringify(state)}`);
                await command(`/element/${elementId}/click`, {});
                state = (await categories())[index];
                assert.ok(!state.open && state.expanded === 'false' && !state.visible, `second click closes category ${width}/${index}: ${JSON.stringify(state)}`);
                await command(`/element/${elementId}/value`, { text: '\uE007', value: ['\uE007'] });
                state = (await categories())[index];
                assert.ok(state.open && state.expanded === 'true', 'Enter opens category');
                await command(`/element/${elementId}/value`, { text: ' ', value: [' '] });
                state = (await categories())[index];
                assert.ok(!state.open && state.expanded === 'false', 'Space closes category');
            }
            await execute(() => document.querySelector('.menu-group summary').click());
            await command('/refresh', {});
            await checkCategories();
            console.log(`${width}: all 9 categories pass load, row click, keyboard and reload checks`);
            const account = await execute(() => {
                const toggle = document.getElementById('user-toggle');
                const panel = document.getElementById('user-panel');
                const closedInitially = panel.hidden && toggle.getAttribute('aria-expanded') === 'false';
                toggle.click();
                const rect = panel.getBoundingClientRect();
                const open = !panel.hidden && toggle.getAttribute('aria-expanded') === 'true';
                const contained = rect.left >= 0 && rect.right <= innerWidth;
                document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
                const escaped = panel.hidden && document.activeElement === toggle;
                toggle.dispatchEvent(new KeyboardEvent('keydown', { key: 'ArrowDown', bubbles: true }));
                const keyboard = !panel.hidden && document.activeElement === panel.querySelector('a');
                document.getElementById('workspace-title').click();
                const outside = panel.hidden && toggle.getAttribute('aria-expanded') === 'false';
                const actions = [...panel.querySelectorAll('a')].map(link => [link.getAttribute('href'), link.getAttribute('target')]);
                return { closedInitially, open, contained, escaped, keyboard, outside, actions };
            });
            assert.deepEqual(account, {
                closedInitially: true, open: true, contained: true, escaped: true, keyboard: true, outside: true,
                actions: [['/Mnu1000.php', 'erp-workspace'], ['/index.php', null]]
            });
            if (width === 375 || width === 1280) {
                await execute(() => document.getElementById('user-toggle').click());
                fs.writeFileSync(path.join(os.tmpdir(), `erp-x-user-${width}.png`), Buffer.from(await command('/screenshot'), 'base64'));
                await execute(() => document.getElementById('user-toggle').click());
            }
            for (const module of ['ERP1000', 'ERP1050', 'ERP1060']) {
                await execute(module => { document.querySelector(`a[href="/${module}.php"]`).click(); }, module);
                let loaded = false;
                for (let attempt = 0; attempt < 40; attempt++) {
                    loaded = await execute(module => {
                        const frame = document.getElementById('erp-workspace');
                        return frame.contentWindow.location.pathname === `/${module}.php` && !!frame.contentDocument.getElementById('workspace-styles');
                    }, module);
                    if (loaded) break;
                    await new Promise(resolve => setTimeout(resolve, 50));
                }
                assert.ok(loaded, `${module} loaded`);
                const metrics = await execute(() => {
                    const frame = document.getElementById('erp-workspace'), d = frame.contentDocument, w = frame.contentWindow;
                    const buttons = [...d.querySelectorAll('input[type=submit]')];
                    return {
                        page: [document.documentElement.scrollWidth, document.documentElement.clientWidth],
                        module: [d.documentElement.scrollWidth, d.documentElement.clientWidth],
                        buttons: buttons.map(b => { const r = b.getBoundingClientRect(); return { label: b.value, left: r.left, right: r.right, height: r.height }; }),
                        selector: d.querySelector('select')?.getBoundingClientRect().right,
                        scroll: [...d.querySelectorAll('.workspace-table-scroll')].map(e => ({ width: e.clientWidth, content: e.scrollWidth, overflow: w.getComputedStyle(e).overflowX })),
                        offenders: [...d.querySelectorAll('body *')].filter(e => e.getBoundingClientRect().right > d.documentElement.clientWidth + 1).slice(0, 8).map(e => e.tagName + (e.id ? '#' + e.id : '') + ':' + Math.round(e.getBoundingClientRect().width))
                    };
                });
                console.log(width, module, JSON.stringify(metrics));
                if (module === 'ERP1050') {
                    const presentation = await execute(() => {
                        const page = document.getElementById('erp-workspace').contentDocument;
                        const numeric = [...page.querySelectorAll('.erp-data-table th.erp-number')];
                        return {
                            numbers: numeric.length,
                            aligned: numeric.every(cell => page.defaultView.getComputedStyle(cell).textAlign === 'right'),
                            primary: page.querySelector('input[value="Nuevo"]').classList.contains('erp-primary'),
                            destructive: page.querySelector('input[value="Eliminar"]').classList.contains('erp-danger'),
                            namedSelection: !!page.querySelector('input[type=radio][aria-label]'),
                            icons: page.querySelectorAll('.erp-action-icon[aria-hidden=true]').length,
                            selectionHeader: page.querySelector('.erp-data-table th:last-child img').getAttribute('alt')
                        };
                    });
                    assert.deepEqual(presentation, { numbers: 2, aligned: true, primary: true, destructive: true, namedSelection: true, icons: 3, selectionHeader: 'Seleccionar registro' });
                    if (width === 375 || width === 1280) {
                        fs.writeFileSync(path.join(os.tmpdir(), `erp-x-insumos-${width}.png`), Buffer.from(await command('/screenshot'), 'base64'));
                    }
                }
                if (module === 'ERP1060') {
                    const spacing = await execute(() => {
                        const page = document.getElementById('erp-workspace').contentDocument;
                        const size = page.querySelector('.workspace-records-footer select');
                        size.value = '25';
                        size.dispatchEvent(new Event('change'));
                        const scroll = page.querySelector('.workspace-records-scroll');
                        const footer = page.querySelector('.workspace-records-footer');
                        const wrapper = footer.closest('.wrapper');
                        const result = {
                            verticalOverflow: scroll.scrollHeight - scroll.clientHeight,
                            bottomSpace: wrapper.getBoundingClientRect().bottom - footer.getBoundingClientRect().bottom
                        };
                        size.value = '10';
                        size.dispatchEvent(new Event('change'));
                        return result;
                    });
                    assert.ok(spacing.verticalOverflow <= 1, 'paginated tables expand without an inner vertical scrollbar');
                    assert.ok(spacing.bottomSpace >= 16 && spacing.bottomSpace <= 32, 'space below pagination stays between 16 and 32 pixels');
                    const filtering = await execute(async () => {
                        const page = document.getElementById('erp-workspace').contentDocument;
                        const search = page.querySelector('.workspace-records-search input');
                        const table = page.querySelector('.workspace-records-table');
                        const row = [...table.rows].find(row => row.querySelector('td'));
                        const radio = row.querySelector('input[type=radio]');
                        radio.checked = true;
                        search.value = 'no-such-supplier';
                        search.dispatchEvent(new Event('input'));
                        const busy = table.getAttribute('aria-busy') === 'true';
                        await new Promise(resolve => setTimeout(resolve, 400));
                        const empty = row.hidden && !radio.checked && page.querySelector('.workspace-records-footer [role=status]').textContent.startsWith('0 registros');
                        search.value = 'proveedor';
                        search.dispatchEvent(new Event('input'));
                        await new Promise(resolve => setTimeout(resolve, 400));
                        const restored = !row.hidden;
                        const pagination = page.querySelector('.workspace-records-pagination');
                        pagination.querySelector('button:last-child').click();
                        const paginated = row.hidden && pagination.querySelector('span').textContent === '2 / 3';
                        pagination.querySelector('button:first-child').click();
                        return { busy, empty, restored, paginated, actions: !!page.querySelector('.workspace-records-toolbar .workspace-actions'), settled: !table.hasAttribute('aria-busy') };
                    });
                    assert.deepEqual(filtering, { busy: true, empty: true, restored: true, paginated: true, actions: true, settled: true });
                }
                try {
                    if (width <= 760) {
                        assert.equal(metrics.page[0], metrics.page[1], 'outer page width');
                        assert.equal(metrics.module[0], metrics.module[1], 'module page width');
                        for (const b of metrics.buttons) { assert.ok(b.left >= 0 && b.right <= metrics.module[1], `${b.label} bounds`); assert.ok(b.height >= 44, 'touch height'); }
                        if (module === 'ERP1000') { assert.deepEqual(metrics.buttons.map(b => b.label), ['Aplicar', 'Nuevo', 'Salir']); assert.ok(metrics.selector <= metrics.module[1]); }
                        if (module === 'ERP1060') assert.ok(metrics.scroll.some(s => s.content > s.width && s.overflow === 'auto'), 'local table scrolling');
                        const menu = await execute(() => {
                            const button = document.getElementById('menu-toggle'), title = document.querySelector('#MH h1'), logout = document.getElementById('user-toggle');
                            const rect = element => element.getBoundingClientRect();
                            const layout = rect(button).width === 44 && rect(button).height === 44
                                && rect(button).right <= rect(title).left && rect(title).right <= rect(logout).left
                                && rect(logout).right <= innerWidth;
                            button.focus();
                            const focus = getComputedStyle(button).outlineStyle !== 'none';
                            button.click();
                            const open = button.getAttribute('aria-expanded') === 'true' && button.getAttribute('aria-label') === 'Cerrar menú'
                                && getComputedStyle(document.getElementById('menu-sidebar')).display !== 'none'
                                && getComputedStyle(document.querySelector('.menu-icon-close')).display !== 'none';
                            button.click();
                            return layout && focus && open && button.getAttribute('aria-expanded') === 'false' && button.getAttribute('aria-label') === 'Abrir menú';
                        });
                        assert.ok(menu, 'hamburger toggle');
                        if (module === 'ERP1000') {
                            const wraps = await execute(() => {
                                const d = document.getElementById('erp-workspace').contentDocument;
                                const row = d.querySelector('.workspace-actions tr');
                                row.style.width = '160px';
                                const buttons = [...row.querySelectorAll('input')];
                                const wrapped = buttons.at(-1).getBoundingClientRect().top > buttons[0].getBoundingClientRect().top;
                                row.style.width = '';
                                return wrapped;
                            });
                            assert.ok(wraps, 'buttons wrap when space is insufficient');
                        } else {
                            const scrolls = await execute(() => {
                                const container = document.getElementById('erp-workspace').contentDocument.querySelector('.workspace-table-scroll');
                                container.scrollLeft = 100;
                                return container.scrollLeft > 0;
                            });
                            assert.ok(scrolls, 'supplier table can scroll');
                        }
                    } else {
                        assert.equal(metrics.buttons[0].height, 36, 'desktop button height');
                        assert.ok(metrics.buttons.every(b => b.right - b.left >= 88), 'desktop buttons fit their labels');
                        assert.equal(await execute(() => getComputedStyle(document.getElementById('menu-toggle')).display), 'none', 'desktop hamburger hidden');
                    }
                } catch (error) { failures.push(`${width} ${module}: ${error.message}`); }
            }
        }
        await command('/goog/cdp/execute', { cmd: 'Emulation.setDeviceMetricsOverride', params: { width: 320, height: 800, deviceScaleFactor: 1, mobile: false } });
        const invalid = await execute(() => {
            const page = document.getElementById('erp-workspace').contentDocument;
            const form = page.createElement('form');
            form.action = '/feedback-save'; form.method = 'POST';
            form.innerHTML = '<input name="pcNombre" required><input type="submit" name="Boton1" value="Aplicar">';
            page.body.replaceChildren(form);
            // Reinsert the existing notice after replacing the fixture content.
            page.__erpFeedbackController.reset();
            form.requestSubmit(form.querySelector('[type=submit]'));
            return form.hasAttribute('aria-busy');
        });
        assert.equal(invalid, false, 'invalid forms do not enter a loading state');
        // Reload a fixture so the feedback notice is connected to the document again.
        await execute(() => { document.getElementById('erp-workspace').src = '/ERP1000.php'; });
        for (let attempt = 0; attempt < 40; attempt++) {
            if (await execute(() => !!document.getElementById('erp-workspace').contentDocument.querySelector('.erp-feedback-notice'))) break;
            await new Promise(resolve => setTimeout(resolve, 50));
        }
        const pending = await execute(() => {
            const page = document.getElementById('erp-workspace').contentDocument;
            const form = page.querySelector('form');
            form.action = '/feedback-save'; form.method = 'POST';
            form.innerHTML = '<input name="pcNombre" value="Test"><input type="submit" name="Boton1" value="Aplicar">';
            const button = form.querySelector('[type=submit]');
            const before = button.getBoundingClientRect();
            form.requestSubmit(button);
            form.requestSubmit(button);
            const after = button.getBoundingClientRect();
            const api = window.ErpFeedback.attach(page);
            page.defaultView.ErpFeedback.attach(page);
            return {
                formBusy: form.getAttribute('aria-busy'), buttonBusy: button.getAttribute('aria-busy'), disabled: button.disabled,
                notice: !page.querySelector('.erp-feedback-notice').hidden,
                notices: page.querySelectorAll('.erp-feedback-notice').length,
                unchanged: before.width === after.width && before.height === after.height,
                payload: [...new page.defaultView.FormData(form, button).entries()],
                overflow: page.documentElement.scrollWidth > page.documentElement.clientWidth
            };
        });
        assert.equal(pending.formBusy, 'true'); assert.equal(pending.buttonBusy, 'true');
        assert.equal(pending.disabled, false, 'submitter remains in the native payload');
        assert.equal(pending.notice, true); assert.equal(pending.notices, 1, 'iframe attachment is idempotent');
        assert.equal(pending.unchanged, true); assert.equal(pending.overflow, false);
        assert.ok(pending.payload.some(([key, value]) => key === 'Boton1' && value === 'Aplicar'));
        for (let attempt = 0; attempt < 40 && !submissions.length; attempt++) await new Promise(resolve => setTimeout(resolve, 25));
        assert.equal(submissions.length, 1, 'duplicate native submission is blocked');
        assert.match(submissions[0], /Boton1=Aplicar/);
        releaseSave();
        for (let attempt = 0; attempt < 40; attempt++) {
            if (await execute(() => document.getElementById('erp-workspace').contentDocument.body.textContent.includes('Saved'))) break;
            await new Promise(resolve => setTimeout(resolve, 25));
        }
        const recovery = await execute(async () => {
            const page = document.getElementById('erp-workspace').contentDocument;
            const api = window.ErpFeedback.attach(page);
            const button = page.createElement('button'); button.textContent = 'Nuevo'; page.body.appendChild(button);
            api.start(button);
            page.defaultView.dispatchEvent(new page.defaultView.PageTransitionEvent('pageshow', { persisted: true }));
            const restored = !button.hasAttribute('aria-busy') && page.querySelector('.erp-feedback-notice').hidden;
            const form = page.createElement('form'); form.innerHTML = '<button type="submit">Apply</button>';
            page.body.appendChild(form); form.addEventListener('submit', event => event.preventDefault());
            form.requestSubmit();
            return restored && !form.hasAttribute('aria-busy');
        });
        assert.ok(recovery, 'back navigation and canceled submissions recover');
        const ignoredLinks = await execute(() => {
            const page = document.getElementById('erp-workspace').contentDocument;
            const results = [];
            for (const attributes of [{ href: '#' }, { href: '/download.pdf', download: '' }, { href: '/ERP1000.php', target: '_blank' }]) {
                const link = page.createElement('a');
                Object.entries(attributes).forEach(([name, value]) => link.setAttribute(name, value));
                page.body.appendChild(link);
                page.defaultView.addEventListener('click', event => event.preventDefault(), { once: true });
                link.click();
                results.push(!link.hasAttribute('aria-busy'));
                link.remove();
            }
            return results.every(Boolean);
        });
        assert.ok(ignoredLinks, 'anchors, downloads and new tabs do not get stuck loading');
        await command('/goog/cdp/execute', { cmd: 'Emulation.setEmulatedMedia', params: { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] } });
        const reduced = await execute(() => {
            const page = document.getElementById('erp-workspace').contentDocument;
            return page.defaultView.getComputedStyle(page.querySelector('.erp-feedback-spinner')).animationName;
        });
        assert.equal(reduced, 'none', 'reduced motion disables the spinner animation');
        const loginPending = await execute(async () => {
            const page = document.getElementById('erp-workspace').contentDocument;
            const form = page.createElement('form'); form.id = 'login-form'; form.action = '/feedback-login';
            form.innerHTML = '<input name="pcCodigo" value="test"><input name="pcClave" value="bad"><input type="submit" id="login-submit" value="Iniciar sesión"><p id="login-message"></p>';
            page.body.appendChild(form);
            await new Promise(resolve => { const script = page.createElement('script'); script.src = '/js/login.js'; script.onload = resolve; page.head.appendChild(script); });
            form.requestSubmit(form.querySelector('[type=submit]'));
            return !page.querySelector('.erp-feedback-notice').hidden && form.getAttribute('aria-busy') === 'true';
        });
        assert.ok(loginPending, 'login shows the shared loading feedback');
        let loginRecovered = false;
        for (let attempt = 0; attempt < 40; attempt++) {
            loginRecovered = await execute(() => {
                const page = document.getElementById('erp-workspace').contentDocument;
                return page.getElementById('login-message').textContent === 'Credenciales inválidas'
                    && !page.getElementById('login-submit').disabled && page.querySelector('.erp-feedback-notice').hidden;
            });
            if (loginRecovered) break;
            await new Promise(resolve => setTimeout(resolve, 25));
        }
        assert.ok(loginRecovered, 'login errors clear feedback and allow retry');
        console.log('Loading feedback: native payload, duplicate protection, validation, recovery, login errors and reduced motion pass');
        assert.deepEqual(failures, []);
    } finally {
        if (session) await request(`/session/${session}`, undefined, 'DELETE').catch(() => {});
        driver.kill(); server.close();
    }
});
