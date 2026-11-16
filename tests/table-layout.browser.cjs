const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const os = require('node:os');
const http = require('node:http');
const { spawn } = require('node:child_process');

function binary(name, executable) {
    const root = path.join(os.homedir(), '.cache', 'selenium', name, 'win64');
    return path.join(root, fs.readdirSync(root).sort().at(-1), executable);
}

(async () => {
    const fixtures = JSON.parse(fs.readFileSync('var/table-layout-fixtures.json', 'utf8').replace(/^\uFEFF/, ''));
    const server = http.createServer((req, res) => {
        const name = new URL(req.url, 'http://localhost').pathname;
        if (name === '/Mnu0000.php') {
            res.setHeader('Content-Type', 'text/html; charset=utf-8');
            return res.end(fs.readFileSync('templates/erp/Mnu0000.html.twig', 'utf8').replace(/\{%[\s\S]*?%\}|\{\{[\s\S]*?\}\}/g, ''));
        }
        if (fixtures[name.slice(1)]) {
            res.setHeader('Content-Type', 'text/html; charset=utf-8');
            return res.end(fixtures[name.slice(1)]);
        }
        const file = path.join('public', name);
        if (!name.includes('..') && fs.existsSync(file) && fs.statSync(file).isFile()) {
            res.setHeader('Content-Type', name.endsWith('.css') ? 'text/css' : name.endsWith('.js') ? 'text/javascript' : 'image/png');
            return res.end(fs.readFileSync(file));
        }
        res.writeHead(404); res.end();
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const port = 19519;
    const driver = spawn(process.env.CHROMEDRIVER || binary('chromedriver', 'chromedriver.exe'), [`--port=${port}`], { stdio: 'ignore', windowsHide: true });
    let session;
    const request = async (route, body, method = body ? 'POST' : 'GET') => {
        const response = await fetch(`http://127.0.0.1:${port}${route}`, { method, body: body ? JSON.stringify(body) : undefined, headers: { 'Content-Type': 'application/json' }, signal: AbortSignal.timeout(15000) });
        const json = await response.json();
        if (json.value?.error) throw Error(JSON.stringify(json.value));
        return json.value;
    };
    try {
        for (let i = 0; i < 30; i++) {
            try { await request('/status'); break; } catch { await new Promise(resolve => setTimeout(resolve, 100)); }
        }
        session = (await request('/session', { capabilities: { alwaysMatch: { browserName: 'chrome', 'goog:chromeOptions': { binary: process.env.CHROME_BINARY || binary('chrome', 'chrome.exe'), args: ['--headless', '--disable-gpu', '--no-first-run', '--no-sandbox'] } } } })).sessionId;
        const command = (route, body, method) => request(`/session/${session}${route}`, body, method);
        const execute = (fn, ...args) => command('/execute/sync', { script: `return (${fn}).apply(null, arguments)`, args });
        const base = `http://127.0.0.1:${server.address().port}`;
        await command('/url', { url: `${base}/Mnu0000.php` });
        const cases = process.argv.includes('--diagnose') ? ['ERP5010-2'] : process.argv.includes('--invoice-only') ? ['ERP5010-0', 'ERP5010-2'] : Object.keys(fixtures);
        const measurements = [];
        const failures = [];
        for (const width of process.argv.includes('--diagnose') ? [1440] : [1440, 390]) {
            await command('/window/rect', { width, height: 1000 });
            await command('/goog/cdp/execute', { cmd: 'Emulation.setDeviceMetricsOverride', params: { width, height: 1000, deviceScaleFactor: 1, mobile: false } });
            for (const name of cases) {
                await execute(url => { const frame = document.getElementById('erp-workspace'); document.getElementById('workspace-welcome').hidden = true; frame.hidden = false; frame.src = url; }, `${base}/${name}`);
                let ready = false;
                for (let i = 0; i < 100; i++) {
                    ready = await execute(name => {
                        const frame = document.getElementById('erp-workspace');
                        return frame.contentWindow.location.pathname === `/${name}` && !!frame.contentDocument.getElementById('workspace-styles');
                    }, name);
                    if (ready) break;
                    await new Promise(resolve => setTimeout(resolve, 30));
                }
                assert.ok(ready, name);
                const result = await execute(async () => {
                    const page = document.getElementById('erp-workspace').contentDocument;
                    const tables = [...page.querySelectorAll('.erp-data-table')].map(table => {
                        const scroll = table.closest('.erp-table-scroll');
                        const fieldset = table.closest('fieldset');
                        const last = [...(table.rows[0]?.cells || [])].filter(cell => page.defaultView.getComputedStyle(cell).display !== 'none').at(-1);
                        scroll.scrollLeft = scroll.scrollWidth;
                        const reachable = !last || last.getBoundingClientRect().right <= scroll.getBoundingClientRect().right + 2;
                        scroll.scrollLeft = 0;
                        return { table: table.offsetWidth, scroll: scroll.clientWidth, scrollWidth: scroll.scrollWidth, fieldset: fieldset?.offsetWidth, fieldsetStyle: fieldset?.getAttribute('style'), available: page.getElementById('navcontainer').clientWidth, reachable };
                    });
                    const overflow = [...page.querySelectorAll('body *')].filter(node => !node.closest('.erp-table-scroll') && node.getBoundingClientRect().right > page.documentElement.clientWidth + 2).slice(0, 8).map(node => ({ tag: node.tagName, id: node.id, className: node.className, style: node.getAttribute('style'), width: node.offsetWidth }));
                    const overlappingControls = [...page.querySelectorAll('.workspace-records-toolbar, .workspace-records-footer')].some(bar => {
                        const boxes = [...bar.children].map(node => node.getBoundingClientRect());
                        return boxes.some((box, index) => boxes.slice(index + 1).some(other => Math.min(box.right, other.right) - Math.max(box.left, other.left) > 1 && Math.min(box.bottom, other.bottom) - Math.max(box.top, other.top) > 1));
                    });
                    const fieldsPreserved = JSON.stringify(page.defaultView.layoutFormNames) === JSON.stringify([...page.querySelectorAll('form [name]')].map(field => field.name).sort());
                    const records = page.querySelector('.workspace-records');
                    let controlsWork = true;
                    const stages = [];
                    if (records && !records.querySelector('.workspace-records-pagination button:last-child').disabled) {
                        const table = records.querySelector('.workspace-records-table');
                        const rows = [...table.rows].filter(row => row.querySelector('td') && !row.querySelector('th'));
                        const next = records.querySelector('.workspace-records-pagination button:last-child');
                        next.click();
                        controlsWork = rows[0].hidden && !rows[10].hidden;
                        stages.push({ stage: 'next', passed: controlsWork, rows: rows.length });
                        const size = records.querySelector('.workspace-records-footer select');
                        size.value = '25';
                        size.dispatchEvent(new page.defaultView.Event('change'));
                        controlsWork &&= rows.filter(row => !row.hidden).length === Math.min(25, rows.length);
                        stages.push({ stage: 'size', visible: rows.filter(row => !row.hidden).length, value: size.value });
                        const search = records.querySelector('.workspace-records-search input');
                        search.value = 'no_matching_record_987654';
                        search.dispatchEvent(new page.defaultView.Event('input'));
                        await new Promise(resolve => setTimeout(resolve, 320));
                        controlsWork &&= rows.every(row => row.hidden);
                        stages.push({ stage: 'filter', visible: rows.filter(row => !row.hidden).length });
                        search.value = '';
                        search.dispatchEvent(new page.defaultView.Event('input'));
                        await new Promise(resolve => setTimeout(resolve, 320));
                        controlsWork &&= rows.filter(row => !row.hidden).length === Math.min(25, rows.length);
                        stages.push({ stage: 'restore', visible: rows.filter(row => !row.hidden).length });
                        size.value = '10';
                        size.dispatchEvent(new page.defaultView.Event('change'));
                    }
                    return { viewport: page.documentElement.clientWidth, pageWidth: page.documentElement.scrollWidth, tables, overflow, overlappingControls, controlsWork, fieldsPreserved, stages };
                });
                measurements.push({ name, width, ...result });
                if (!process.argv.includes('--diagnose')) {
                    if (result.pageWidth > result.viewport + 2) failures.push(`${name} at ${width}: page overflow ${JSON.stringify(result.overflow)}`);
                    assert.equal(result.overlappingControls, false, `${name}: overlapping controls`);
                    assert.equal(result.fieldsPreserved, true, `${name}: form fields changed`);
                    assert.equal(result.controlsWork, true, `${name}: search or pagination failed ${JSON.stringify(result.stages)}`);
                    result.tables.forEach(table => {
                        assert.ok(table.reachable, `${name}: last column unreachable`);
                        if (table.fieldset) assert.ok(table.fieldset >= table.available - 2, `${name}: constrained fieldset ${JSON.stringify(table)}`);
                    });
                    if (name === 'ERP5010-2' && width === 1440) {
                        assert.ok(result.tables[0].scrollWidth <= result.tables[0].scroll + 1, 'invoice detail should fit desktop');
                    }
                }
                if (name === 'ERP5010-2') {
                    fs.mkdirSync('var/table-layout', { recursive: true });
                    fs.writeFileSync(`var/table-layout/invoice-${width}${process.argv.includes('--diagnose') ? '-before' : '-after'}.png`, Buffer.from(await command('/screenshot'), 'base64'));
                    if (width === 390) {
                        await execute(() => {
                            const page = document.getElementById('erp-workspace').contentDocument;
                            const scroll = page.querySelector('.erp-table-scroll');
                            scroll.scrollLeft = scroll.scrollWidth;
                            page.querySelector('.workspace-records-footer').scrollIntoView({ block: 'end' });
                        });
                        fs.writeFileSync('var/table-layout/invoice-390-last-column.png', Buffer.from(await command('/screenshot'), 'base64'));
                    }
                }
            }
        }
        fs.mkdirSync('var/table-layout', { recursive: true });
        fs.writeFileSync(`var/table-layout/${process.argv.includes('--diagnose') ? 'before' : process.argv.includes('--invoice-only') ? 'invoice' : 'after'}.json`, JSON.stringify(measurements, null, 2));
        assert.deepEqual(failures, []);
        console.log(process.argv.includes('--diagnose') ? JSON.stringify(measurements, null, 2) : `${measurements.length} rendered screen/viewport checks passed.`);
    } finally {
        if (session) await request(`/session/${session}`, undefined, 'DELETE').catch(() => {});
        driver.kill(); server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
