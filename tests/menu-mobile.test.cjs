const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

test('menu button has an accessible icon and follows every existing close action', () => {
    const html = readFileSync('legacy/Plantillas/Mnu0000.tpl', 'utf8');
    assert.match(html, /id="menu-toggle"[^>]*aria-label="Abrir menú"/);
    assert.match(html, /<svg[^>]*width="24"[^>]*height="24"[^>]*aria-hidden="true"/);
    const handlers = {};
    const attributes = {};
    const classes = new Set();
    const element = (id) => ({
        addEventListener: (name, handler) => { handlers[`${id}:${name}`] = handler; },
        setAttribute: (name, value) => { attributes[`${id}:${name}`] = value; },
        removeAttribute() {}, textContent: 'Module'
    });
    const elements = Object.fromEntries(['erp-workspace', 'menu-toggle', 'workspace-title', 'workspace-status', 'workspace-welcome'].map(id => [id, element(id)]));
    const link = element('link');
    vm.runInNewContext(readFileSync('public/js/menu.js', 'utf8'), {
        window: {},
        document: {
            getElementById: id => elements[id], querySelectorAll: selector => selector === '.menu-group a' ? [link] : [],
            addEventListener: (name, handler) => { handlers[name] = handler; },
            body: { classList: {
                remove: name => classes.delete(name),
                toggle: name => { if (classes.delete(name)) return false; classes.add(name); return true; }
            } }
        }
    });
    const check = open => {
        assert.equal(classes.has('menu-open'), open);
        assert.equal(attributes['menu-toggle:aria-expanded'], String(open));
        assert.equal(attributes['menu-toggle:aria-label'], open ? 'Cerrar menú' : 'Abrir menú');
    };
    handlers['menu-toggle:click'](); check(true);
    handlers['menu-toggle:click'](); check(false);
    handlers['menu-toggle:click'](); handlers.keydown({ key: 'Escape' }); check(false);
    handlers['menu-toggle:click'](); handlers['link:click']({}); check(false);
});
