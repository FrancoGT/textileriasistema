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
    const html = fs.readFileSync(`legacy/Plantillas/${name}.tpl`, 'utf8');
    const start = html.indexOf('{if $scBehavior');
    const branch = html.slice(html.indexOf('}', start) + 1, html.indexOf('{elseif', start));
    return (html.slice(0, start) + branch + html.slice(html.lastIndexOf('{/if}') + 5))
        .replace(/<script\b[^>]*>[\s\S]*?<\/script>/g, '')
        .replace(/onload=f_Desactivar\(\)/, '')
        .replace(/\{foreach[^}]*\}([\s\S]*?)\{\/foreach\}/g, '$1')
        .replace(/\{\$[^}]+\}/g, name === 'ERP1060'
            ? 'PROVEEDOR_CON_RAZON_SOCIAL_EXTENSA_PARA_COMPROBAR_EL_DESPLAZAMIENTO_LOCAL'
            : 'Personal con un nombre muy largo para comprobar el ancho del selector');
}

test('shared mobile workspace contains controls and wide supplier tables', { timeout: 60000 }, async () => {
    const server = http.createServer((req, res) => {
        let content, type = 'text/html; charset=utf-8';
        if (/^\/ERP(1000|1060)\.php/.test(req.url)) content = moduleFixture(req.url.slice(1, 8));
        else if (req.url === '/Mnu0000.php') content = fs.readFileSync('legacy/Plantillas/Mnu0000.tpl');
        else if (req.url === '/CSS/Sixavi.css') { content = fs.readFileSync('legacy/CSS/Sixavi.css'); type = 'text/css'; }
        else if (req.url === '/css/menu.css') { content = fs.readFileSync('public/css/menu.css'); type = 'text/css'; }
        else if (req.url === '/js/menu.js') { content = fs.readFileSync('public/js/menu.js'); type = 'text/javascript'; }
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
            for (const module of ['ERP1000', 'ERP1060']) {
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
                try {
                    if (width <= 760) {
                        assert.equal(metrics.page[0], metrics.page[1], 'outer page width');
                        assert.equal(metrics.module[0], metrics.module[1], 'module page width');
                        for (const b of metrics.buttons) { assert.ok(b.left >= 0 && b.right <= metrics.module[1], `${b.label} bounds`); assert.ok(b.height >= 44, 'touch height'); }
                        if (module === 'ERP1000') { assert.deepEqual(metrics.buttons.map(b => b.label), ['Aplicar', 'Nuevo', 'Salir']); assert.ok(metrics.selector <= metrics.module[1]); }
                        if (module === 'ERP1060') assert.ok(metrics.scroll.some(s => s.content > s.width && s.overflow === 'auto'), 'local table scrolling');
                        const menu = await execute(() => {
                            const button = document.getElementById('menu-toggle'), title = document.querySelector('#MH h1'), logout = document.querySelector('#MH a');
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
                        assert.equal(metrics.buttons[0].height, 39, 'desktop button height');
                        assert.deepEqual(metrics.buttons.map(b => b.right - b.left), [140, 140, 140], 'desktop button widths');
                        assert.equal(await execute(() => getComputedStyle(document.getElementById('menu-toggle')).display), 'none', 'desktop hamburger hidden');
                    }
                } catch (error) { failures.push(`${width} ${module}: ${error.message}`); }
            }
        }
        assert.deepEqual(failures, []);
    } finally {
        if (session) await request(`/session/${session}`, undefined, 'DELETE').catch(() => {});
        driver.kill(); server.close();
    }
});
