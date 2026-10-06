import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../public/js/workspace-navigation.js', import.meta.url), 'utf8');

// DOM doubles test state and event handling; visual layout still needs a browser.
function setup({ desktop = true, storageDisabled = false, pageSearch = false } = {}) {
    let document;
    function element(text = '') {
        const attributes = new Map();
        const classes = new Set();
        const events = {};
        return {
            attributes, events, textContent: text, inert: false,
            classList: {
                contains: name => classes.has(name),
                toggle: (name, enabled) => enabled ? classes.add(name) : classes.delete(name),
            },
            setAttribute: (name, value) => attributes.set(name, value),
            removeAttribute: name => attributes.delete(name),
            toggleAttribute: (name, enabled) => enabled ? attributes.set(name, '') : attributes.delete(name),
            addEventListener: (name, callback) => { events[name] = callback; },
            focus() { document.activeElement = this; },
            getClientRects: () => [1],
        };
    }
    const sidebar = element();
    const toggle = element();
    const mobile = element();
    const backdrop = element();
    const link = element('Riwayat');
    link.classList.toggle('active', true);
    link.href = '/courier/history';
    sidebar.querySelectorAll = selector => selector === '.nav-link' ? [link] : [toggle, link];
    sidebar.contains = node => [toggle, link].includes(node);
    const background = [element(), element()];
    const nodes = { 'workspace-sidebar': sidebar, 'sidebar-toggle': toggle, 'mobile-sidebar-toggle': mobile, 'sidebar-backdrop': backdrop };
    const search = element();
    const input = element();
    const options = [];
    input.value = '';
    input.setCustomValidity = message => { input.error = message; };
    input.reportValidity = () => {};
    search.querySelector = () => input;
    if (pageSearch) {
        nodes['workspace-page-search'] = search;
        nodes['workspace-page-options'] = { append: option => options.push(option) };
    }
    document = {
        body: element(), activeElement: null, events: {},
        getElementById: id => nodes[id] ?? null,
        querySelectorAll: () => background,
        createElement: () => element(),
        addEventListener(name, callback) { this.events[name] = callback; },
    };
    const media = { matches: desktop, addEventListener(name, callback) { this.change = callback; } };
    const stored = new Map();
    const window = {
        location: { assign: url => { window.destination = url; } },
        matchMedia: () => media,
        localStorage: {
            getItem(key) { if (storageDisabled) throw Error('Disabled'); return stored.get(key); },
            setItem(key, value) { if (storageDisabled) throw Error('Disabled'); stored.set(key, value); },
        },
    };
    runInNewContext(source, { document, window });
    return { document, sidebar, toggle, mobile, backdrop, background, media, stored, link, search, input, options, window };
}

test('desktop collapse persists and marks the active page', () => {
    const ui = setup();
    ui.toggle.events.click();
    assert.equal(ui.document.body.classList.contains('sidebar-collapsed'), true);
    assert.equal(ui.stored.get('laundry-wash-sidebar-collapsed'), 'true');
    assert.equal(ui.link.attributes.get('aria-current'), 'page');
    ui.toggle.events.click();
    assert.equal(ui.document.body.classList.contains('sidebar-collapsed'), false);
});

test('mobile drawer opens, disables background, and closes with Escape', () => {
    const ui = setup({ desktop: false });
    assert.equal(ui.sidebar.inert, true);
    ui.mobile.events.click();
    assert.equal(ui.sidebar.inert, false);
    assert.equal(ui.background.every(item => item.inert), true);
    assert.equal(ui.document.activeElement, ui.toggle);
    ui.document.events.keydown({ key: 'Escape', preventDefault() {} });
    assert.equal(ui.sidebar.inert, true);
    assert.equal(ui.background.every(item => !item.inert), true);
    assert.equal(ui.document.activeElement, ui.mobile);
});

test('backdrop and close button dismiss mobile navigation', () => {
    const ui = setup({ desktop: false });
    for (const close of [ui.backdrop, ui.toggle]) {
        ui.mobile.events.click();
        close.events.click();
        assert.equal(ui.mobile.attributes.get('aria-expanded'), 'false');
    }
});

test('keyboard focus stays in the open drawer and resize clears modal state', () => {
    const ui = setup({ desktop: false });
    ui.mobile.events.click();
    ui.link.focus();
    ui.document.events.keydown({ key: 'Tab', shiftKey: false, preventDefault() {} });
    assert.equal(ui.document.activeElement, ui.toggle);
    ui.media.matches = true;
    ui.media.change();
    assert.equal(ui.sidebar.inert, false);
    assert.equal(ui.sidebar.attributes.has('aria-modal'), false);
    assert.equal(ui.background.every(item => !item.inert), true);
});

test('blocked localStorage does not break navigation', () => {
    const ui = setup({ storageDisabled: true });
    assert.doesNotThrow(() => ui.toggle.events.click());
    assert.equal(ui.document.body.classList.contains('sidebar-collapsed'), true);
});

test('page search only navigates to links available in the role sidebar', () => {
    const ui = setup({ pageSearch: true });
    assert.equal(ui.options.length, 1);
    ui.input.value = '  riwayat  ';
    ui.search.events.submit({ preventDefault() {} });
    assert.equal(ui.window.destination, '/courier/history');
});

test('unknown page search does not navigate and explains the available choices', () => {
    const ui = setup({ pageSearch: true });
    ui.input.value = '/admin/settings';
    ui.search.events.submit({ preventDefault() {} });
    assert.equal(ui.window.destination, undefined);
    assert.ok(ui.input.error);
    ui.input.events.input();
    assert.equal(ui.input.error, '');
});
