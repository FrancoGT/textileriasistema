const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

function setup(fetch) {
    const attributes = {};
    let handler;
    let destination;
    const form = {
        action: 'http://localhost/Mnu0000.php?Id=0000',
        reportValidity: () => true,
        addEventListener: (_, callback) => { handler = callback; },
        setAttribute: (key, value) => { attributes[key] = value; },
        removeAttribute: (key) => { delete attributes[key]; }
    };
    const submit = {};
    const message = { dataset: {} };
    const elements = { 'login-form': form, 'login-submit': submit, 'login-message': message };
    vm.runInNewContext(readFileSync('public/js/login.js', 'utf8'), {
        document: { getElementById: (id) => elements[id] },
        window: { fetch, location: { origin: 'http://localhost', assign: (url) => { destination = url; } } },
        fetch, FormData: class {}, URL
    });
    return { send: () => handler({ preventDefault() {} }), submit, message, attributes, destination: () => destination };
}

test('invalid credentials stay on the login and allow retry', async () => {
    const app = setup(async () => ({ ok: false, json: async () => ({ ok: false, message: 'Credenciales inválidas' }) }));
    await app.send();
    assert.equal(app.message.textContent, 'Credenciales inválidas');
    assert.equal(app.destination(), undefined);
    assert.equal(app.submit.disabled, false);
    assert.ok('error' in app.message.dataset);
});

test('successful login uses the server destination', async () => {
    const app = setup(async (_, options) => {
        assert.equal(options.method, 'POST');
        assert.equal(options.headers['X-Requested-With'], 'XMLHttpRequest');
        return { ok: true, json: async () => ({ ok: true, redirect: 'Mnu0000.php?Id=0000' }) };
    });
    await app.send();
    assert.equal(app.destination(), 'http://localhost/Mnu0000.php?Id=0000');
});

test('pending submission prevents duplicate requests', async () => {
    let finish;
    let calls = 0;
    const app = setup(() => { calls++; return new Promise((resolve) => { finish = resolve; }); });
    const request = app.send();
    await app.send();
    assert.equal(calls, 1);
    assert.equal(app.submit.disabled, true);
    finish({ ok: false, json: async () => ({ ok: false }) });
    await request;
    assert.equal(app.submit.disabled, false);
});

test('network and malformed responses allow retry', async () => {
    for (const fetch of [async () => { throw new Error('offline'); }, async () => ({ json: async () => { throw new Error('HTML'); } })]) {
        const app = setup(fetch);
        await app.send();
        assert.ok('error' in app.message.dataset);
        assert.equal(app.submit.disabled, false);
        assert.equal(app.attributes['aria-busy'], undefined);
    }
});
