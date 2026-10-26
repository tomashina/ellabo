#!/usr/bin/env node
'use strict';

// Exercise the shipped pipeline, without a browser or production database.
const assert = require('assert').strict;
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const root = path.resolve(__dirname, '../..');
const bundle = fs.readFileSync(path.join(root, 'admin/view/javascript/pqe/catalog.min.js'), 'utf8');
const pipelineStart = bundle.indexOf('$.fn.dataTable.pipeline=function');
const pipelineEnd = bundle.indexOf('$.fn.dataTable.Api.register(', pipelineStart);
assert.ok(pipelineStart >= 0 && pipelineEnd > pipelineStart, 'The production pipeline must exist');
const pipelineSource = bundle.slice(pipelineStart, pipelineEnd).replace(/,$/, ';');

function copy(value) {
    return JSON.parse(JSON.stringify(value));
}

function harness() {
    const requests = [];
    const alerts = [];
    const cacheWrites = [];
    const view = { processing: false, rows: [], draws: 0 };
    const settings = {
        iDraw: 0,
        bDrawing: false,
        oApi: {
            _fnProcessingDisplay(currentSettings, processing) {
                assert.equal(currentSettings, settings);
                view.processing = processing;
            }
        }
    };
    const $ = selector => ({ selector });
    $.fn = { dataTable: {} };
    $.extend = function (...args) {
        const deep = args[0] === true;
        if (deep) args.shift();
        const target = args.shift();
        for (const source of args) Object.assign(target, deep ? copy(source) : source);
        return target;
    };
    $.isArray = Array.isArray;
    $.isFunction = value => typeof value === 'function';
    $.isPlainObject = value => value !== null && typeof value === 'object' && !Array.isArray(value);
    $.merge = (first, second) => { first.push(...second); return first; };
    $.ajax = options => {
        const done = [];
        const fail = [];
        const request = {
            options,
            done(callback) { done.push(callback); return request; },
            fail(callback) { fail.push(callback); return request; },
            resolve(json) { for (const callback of done) callback(copy(json)); },
            reject(status = 'error') {
                for (const callback of fail) callback({ status: 503 }, status, 'Database unavailable');
            }
        };
        requests.push(request);
        return request;
    };
    const bull5i = {
        user_token: 'test',
        texts: { error_ajax_request: 'AJAX error' },
        display_alert(...args) { alerts.push(args); }
    };
    const quietConsole = { log() {} };
    vm.runInNewContext(pipelineSource, {
        $, bull5i, console: quietConsole, window: { console: quietConsole },
        location: { pathname: '/admin/index.php' },
        localStorageUsable: () => false,
        storeLocally: (key, cache) => cacheWrites.push(copy(cache))
    });
    const pipeline = $.fn.dataTable.pipeline({ url: '/grid', pages: 2 });

    function draw(number, start = 0, search = '') {
        settings.iDraw = number;
        settings.bDrawing = true;
        view.processing = true;
        pipeline({ draw: number, start, length: 2, order: [], columns: [], search: { value: search } }, json => {
            // DataTables rejects a cached response from an earlier draw. The old
            // failure handler used that response, leaving processing enabled.
            if (json.draw < settings.iDraw) return;
            assert.equal(json.draw, settings.iDraw);
            view.rows = copy(json.data);
            view.draws++;
            view.processing = false;
            settings.bDrawing = false;
        }, settings);
    }

    function response(index, ids, total = 4) {
        requests[index].resolve({
            draw: requests[index].options.data.draw,
            recordsTotal: total,
            recordsFiltered: total,
            data: ids.map(id => ({ id }))
        });
    }

    return { requests, alerts, cacheWrites, view, settings, draw, response };
}

function test(name, run) {
    run();
    process.stdout.write(`PASS ${name}\n`);
}

test('successful requests retain normal cached paging', () => {
    const h = harness();
    h.draw(1);
    h.response(0, [1, 2, 3, 4]);
    assert.deepEqual(h.view.rows, [{ id: 1 }, { id: 2 }]);
    h.draw(2, 2);
    assert.equal(h.requests.length, 1);
    assert.deepEqual(h.view.rows, [{ id: 3 }, { id: 4 }]);
    assert.equal(h.view.processing, false);
});

test('initial connection failure releases the spinner and can retry', () => {
    const h = harness();
    h.draw(1);
    h.requests[0].reject();
    assert.equal(h.view.processing, false);
    assert.equal(h.settings.bDrawing, false);
    assert.equal(h.view.draws, 0);
    assert.equal(h.alerts.length, 1);
    h.draw(2);
    assert.equal(h.requests.length, 2);
    h.response(1, [1, 2, 3, 4]);
    assert.deepEqual(h.view.rows, [{ id: 1 }, { id: 2 }]);
    assert.equal(h.view.processing, false);
});

test('failed refresh after a save preserves rows and forces a fresh retry', () => {
    const h = harness();
    h.draw(1);
    h.response(0, [1, 2, 3, 4]);
    h.view.rows[0].name = 'Saved name';
    const visibleRows = copy(h.view.rows);
    h.settings.clearCache = true;
    h.draw(2);
    h.requests[1].reject();
    assert.deepEqual(h.view.rows, visibleRows);
    assert.equal(h.view.draws, 1, 'Do not redraw stale cached product data');
    assert.equal(h.view.processing, false);
    assert.equal(h.settings.clearCache, true);
    h.draw(3);
    assert.equal(h.requests.length, 3, 'Retry must bypass the existing cache');
    h.response(2, [5, 6, 7, 8]);
    assert.deepEqual(h.view.rows, [{ id: 5 }, { id: 6 }]);
});

test('an older failure cannot stop the newer request spinner or show a stale error', () => {
    const h = harness();
    h.draw(1);
    h.draw(2, 0, 'new search');
    h.requests[0].reject();
    assert.equal(h.view.processing, true);
    assert.equal(h.alerts.length, 0);
    h.response(1, [5, 6, 7, 8]);
    assert.deepEqual(h.view.rows, [{ id: 5 }, { id: 6 }]);
    assert.equal(h.view.processing, false);
});

test('a late failure cannot invalidate a newer successful cache', () => {
    const h = harness();
    h.draw(1);
    h.draw(2, 0, 'new search');
    h.response(1, [5, 6, 7, 8]);
    h.requests[0].reject();
    h.draw(3, 0, 'new search');
    assert.equal(h.requests.length, 2);
    assert.equal(h.alerts.length, 0);
    assert.deepEqual(h.view.rows, [{ id: 5 }, { id: 6 }]);
});

test('a late success cannot overwrite the newer cache', () => {
    const h = harness();
    h.draw(1);
    h.draw(2, 0, 'new search');
    h.response(1, [5, 6, 7, 8]);
    h.response(0, [1, 2, 3, 4]);
    h.draw(3, 0, 'new search');
    assert.equal(h.requests.length, 2);
    assert.deepEqual(h.view.rows, [{ id: 5 }, { id: 6 }]);
    assert.equal(h.cacheWrites.length, 1);
});

test('invalid JSON responses release the spinner and can retry', () => {
    const h = harness();
    h.draw(1);
    h.requests[0].reject('parsererror');
    assert.equal(h.view.processing, false);
    assert.equal(h.alerts.length, 1);
    h.draw(2);
    assert.equal(h.requests.length, 2);
});

test('malformed JSON payloads cannot corrupt the cache or leave a spinner', () => {
    for (const json of [
        null,
        { draw: 1, error: 'Database unavailable' },
        { draw: 1, recordsTotal: 0, recordsFiltered: 0, data: {} },
        { draw: 1, recordsFiltered: 0, data: [] },
        { draw: 1, recordsTotal: -1, recordsFiltered: 0, data: [] },
        { draw: 0, recordsTotal: 0, recordsFiltered: 0, data: [] }
    ]) {
        const h = harness();
        h.draw(1);
        h.requests[0].resolve(json);
        assert.equal(h.view.processing, false);
        assert.equal(h.view.draws, 0);
        assert.equal(h.alerts.length, 1);
        assert.equal(h.cacheWrites.length, 0);
        h.draw(2);
        assert.equal(h.requests.length, 2);
    }
});

test('an empty successful result remains valid', () => {
    const h = harness();
    h.draw(1);
    h.response(0, [], 0);
    assert.deepEqual(h.view.rows, []);
    assert.equal(h.view.processing, false);
    assert.equal(h.alerts.length, 0);
});

test('the table API is available before the first successful response', () => {
    const template = fs.readFileSync(path.join(root, 'admin/view/template/extension/module/catalog/product_list.twig'), 'utf8');
    const setup = template.match(/\$\(function\(\)\{[^\n;]*?\.DataTable\(/);
    assert.ok(setup, 'The product table constructor must exist');
    const bull5i = {};
    const api = { search() {} };
    const $ = value => {
        if (typeof value === 'function') return value();
        return { DataTable() { return api; } };
    };
    // Return the API from construction, without firing the success-only init
    // event. Filters must still have an API they can use to retry the request.
    vm.runInNewContext(setup[0] + '{});});', { $, bull5i });
    assert.equal(bull5i.dtApi, api);
});
