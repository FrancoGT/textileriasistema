const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');

function setup() {
    class Element {
        constructor() { this.dataset = {}; this.children = []; this.handlers = {}; this.attributes = {}; this.value = ''; }
        setAttribute(key, value) { this.attributes[key] = value; }
        getAttribute(key) { return this.attributes[key]; }
        removeAttribute(key) { delete this.attributes[key]; }
        addEventListener(key, fn) { this.handlers[key] = fn; }
        append(...children) { this.children.push(...children); }
        before(wrapper) { this.wrapper = wrapper; }
        replaceChildren() { this.children = []; }
        dispatchEvent(event) { this.events ??= []; this.events.push(event.type); this.handlers[event.type]?.(event); }
        click() { this.handlers.click(); }
        scrollIntoView() {}
    }
    const select = new Element();
    select.name = 'products';
    select.dataset.searchKey = 'session-key';
    select.options = [{ value: '01', textContent: 'Algodón' }, { value: '04', textContent: 'Lana' }];
    select.selectedIndex = 0;
    let timer;
    const requests = [];
    vm.runInNewContext(fs.readFileSync('public/js/select-search.js', 'utf8'), {
        document: { createElement: () => new Element(), querySelectorAll: () => [select], body: {} },
        MutationObserver: class { observe() {} }, URL, AbortController,
        location: { origin: 'http://localhost' },
        Event: class { constructor(type) { this.type = type; } },
        setTimeout: fn => { timer = fn; return 1; }, clearTimeout: () => { timer = null; },
        fetch: (url, options) => new Promise(resolve => requests.push({ url, options, resolve }))
    });
    const [input, list, status] = select.wrapper.children;
    return { select, input, list, status, requests, search: value => { input.value = value; input.handlers.input(); return timer(); } };
}

test('search uses AJAX and selecting a suggestion keeps native field events', async () => {
    const ui = setup();
    assert.equal(ui.select.hidden, true);
    assert.equal(ui.input.value, 'Algodón');
    const pending = ui.search('lana');
    assert.equal(ui.requests[0].url.searchParams.get('q'), 'lana');
    assert.equal(ui.requests[0].url.searchParams.get('key'), 'session-key');
    ui.requests[0].resolve({ ok: true, json: async () => ({ results: [{ index: 1, value: '04', label: 'Lana' }] }) });
    await pending;
    assert.equal(ui.list.children[0].textContent, 'Lana');
    ui.input.handlers.keydown({ key: 'ArrowDown', preventDefault() {} });
    ui.input.handlers.keydown({ key: 'Enter', preventDefault() {} });
    assert.equal(ui.select.selectedIndex, 1);
    assert.equal(ui.input.value, 'Lana');
    assert.deepEqual(ui.select.events, ['input', 'change']);
    assert.equal(ui.select.options.length, 2);
});

test('opening the combined control requests all choices and blur restores the selection', async () => {
    const ui = setup();
    const pending = ui.input.handlers.focus();
    assert.equal(ui.requests[0].url.searchParams.get('q'), '');
    ui.requests[0].resolve({ ok: true, json: async () => ({ results: [{ index: 1, value: '04', label: 'Lana' }] }) });
    await pending;
    assert.equal(ui.list.hidden, false);
    ui.input.value = 'unselected text';
    ui.input.handlers.blur();
    assert.equal(ui.input.value, 'Algodón');
    assert.equal(ui.select.selectedIndex, 0);
    assert.equal(ui.list.hidden, true);
});

test('late responses cannot replace current results and failures preserve native selection', async () => {
    const ui = setup();
    const old = ui.search('old');
    const current = ui.search('current');
    assert.equal(ui.requests[0].options.signal.aborted, true);
    ui.requests[1].resolve({ ok: true, json: async () => ({ results: [] }) });
    await current;
    ui.requests[0].resolve({ ok: true, json: async () => ({ results: [{ index: 0, value: '01', label: 'Old' }] }) });
    await old;
    assert.equal(ui.list.children.length, 0);
    assert.equal(ui.status.textContent, 'Sin resultados.');
    const failed = ui.search('error');
    ui.requests[2].resolve({ ok: false });
    await failed;
    assert.match(ui.status.textContent, /No se pudo cargar la lista/);
    assert.equal(ui.select.selectedIndex, 0);
});

test('native changes and Escape keep the displayed selection synchronized', async () => {
    const ui = setup();
    ui.select.selectedIndex = 1;
    ui.select.dispatchEvent({ type: 'change' });
    assert.equal(ui.input.value, 'Lana');
    const pending = ui.search('unselected');
    ui.input.handlers.keydown({ key: 'Escape' });
    assert.equal(ui.input.value, 'Lana');
    assert.equal(ui.requests[0].options.signal.aborted, true);
    ui.requests[0].resolve({ ok: true, json: async () => ({ results: [] }) });
    await pending;
    assert.equal(ui.list.hidden, true);
    assert.equal(ui.input.getAttribute('aria-expanded'), 'false');
});
