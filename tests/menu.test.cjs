const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');

test('all menu categories are closed in the initial HTML', () => {
    const html = readFileSync('legacy/Plantillas/Mnu0000.tpl', 'utf8');
    const groups = [...html.matchAll(/<details\b([^>]*)>\s*<summary([^>]*)>/g)];
    assert.equal(groups.length, 9);
    for (const [, details, summary] of groups) {
        assert.doesNotMatch(details, /\bopen\b/);
        assert.match(summary, /aria-expanded="false"/);
    }
});

test('menu stylesheets resolve outside the front controller path', () => {
    const html = readFileSync('legacy/Plantillas/Mnu0000.tpl', 'utf8');
    const stylesheets = [...html.matchAll(/<link href="([^"]+)"/g)].map(match => match[1]);
    for (const page of ['/Mnu0000.php', '/index.php/Mnu0000.php']) {
        const paths = stylesheets.map(href => new URL(href, `http://localhost:8080${page}`).pathname);
        assert.deepEqual(paths, ['/CSS/Sixavi.css', '/css/menu.css']);
    }
});

test('module navigation keeps the menu in the parent page', () => {
    const html = readFileSync('legacy/Plantillas/Mnu0000.tpl', 'utf8');
    const links = [...html.matchAll(/<a href="(\/ERP\d+\.php)" target="([^"]+)"/g)];
    assert.equal(links.length, 36);
    assert.ok(links.every(link => link[2] === 'erp-workspace'));
    assert.match(html, /<aside id="menu-sidebar"/);
    assert.match(html, /<iframe id="erp-workspace" name="erp-workspace"/);
    assert.match(html, /aria-controls="menu-sidebar"/);
    assert.match(html, /src="\/js\/menu.js"/);
});

test('menu labels contain valid Spanish accents', () => {
    const html = readFileSync('legacy/Plantillas/Mnu0000.tpl', 'utf8');
    for (const label of ['Abrir men\u00fa', 'Cerrar sesi\u00f3n', 'M\u00f3dulos del sistema', 'opci\u00f3n del men\u00fa', '\u00c1rea de trabajo', 'Planificaci\u00f3n', 'Producci\u00f3n', 'Log\u00edstica', 'Te\u00f1ido']) {
        assert.ok(html.includes(label), `Missing label: ${label}`);
    }
    assert.doesNotMatch(html, /\uFFFD|\u00c3[\u0080-\u00bf]|\?/);
});
