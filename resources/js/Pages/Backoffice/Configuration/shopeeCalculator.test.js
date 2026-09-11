import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
    calculateShopee,
    calculatorReducer,
    calculatorState,
    searchCalculatorProducts,
    simulationDefaults,
} from './shopeeCalculator.js';

const sku = { sku_id: 1, original_price: 45896, discount_price: 80999 };
const configuration = {
    id: 1,
    admin_fee: 8.25,
    free_shipping: 4,
    extra_promo: 4.5,
    processing_fee: 1250,
    affiliate: 0,
    live: 0,
    premi_fee: 0.5,
    operational: 0,
};
const inputs = simulationDefaults(configuration, sku);

test('preserves configured percentage rates and rounds fee amounts up', () => {
    const { result: r, errors } = calculateShopee(sku, inputs);
    assert.deepEqual(errors, {});
    assert.equal(inputs.admin_fee, 8.25);
    assert.equal(
        simulationDefaults({ ...configuration, admin_fee: '8.25' }).admin_fee,
        '8.25',
    );
    assert.deepEqual(r.fees, {
        admin_fee: 6683,
        free_shipping: 3240,
        extra_promo: 3645,
        processing_fee: 1250,
        affiliate: 0,
        live: 0,
        premi_fee: 405,
        operational: 0,
    });
    assert.deepEqual(
        [
            r.initialMargin,
            r.totalFees,
            r.revenue,
            r.nonAdCost,
            r.profit,
            r.target.budget,
            r.target.profit,
            r.actual.budget,
            r.actual.profit,
        ],
        [35103, 15223, 65776, 61119, 19880, 8946, 10934, 16200, 3680],
    );
    assert.deepEqual(
        [
            r.initialMarginPercent,
            r.revenueMargin,
            r.salesMargin,
            r.target.acos,
            r.breakEvenRoas,
            r.target.margin,
            r.actual.margin,
        ].map((n) => n.toFixed(2)),
        ['43.34', '30.22', '24.54', '11.04', '4.07', '13.50', '4.54'],
    );
});

test('applies discount before percentage fees and rounds every component separately', () => {
    const { result } = calculateShopee(sku, {
        ...inputs,
        discount: 999,
        processing_fee: 1250.1,
    });
    assert.equal(result.sales, 80000);
    assert.equal(result.fees.admin_fee, 6600);
    assert.equal(result.fees.processing_fee, 1251);
    assert.equal(result.actual.budget, 16000);
    const tiny = calculateShopee(
        { original_price: 1, discount_price: 10 },
        { ...inputs, processing_fee: 0, sellingPrice: 10 },
    ).result;
    assert.equal(tiny.totalFees, 4);
    assert.equal(
        calculateShopee(
            { original_price: 1, discount_price: 10000 },
            { ...inputs, admin_fee: 0.07, sellingPrice: 10000 },
        ).result.fees.admin_fee,
        7,
    );
});

test('rejects invalid numbers and preserves independent results for invalid ROAS', () => {
    for (const value of ['', ' ', null, undefined, 'no', Infinity, NaN, -1]) {
        for (const key of Object.keys(inputs)) {
            const output = calculateShopee(sku, { ...inputs, [key]: value });
            assert.ok(output.errors[key], `${key}: ${value}`);
            if (!['acosAllocation', 'actualRoas'].includes(key))
                assert.equal(output.result, null);
        }
    }
    for (const discount of [80999, 81000])
        assert.equal(
            calculateShopee(sku, { ...inputs, discount }).result,
            null,
        );
    assert.ok(
        calculateShopee(sku, { ...inputs, admin_fee: 101 }).errors.admin_fee,
    );
    for (const key of ['acosAllocation', 'actualRoas'])
        assert.ok(calculateShopee(sku, { ...inputs, [key]: 0 }).errors[key]);
    const r = calculateShopee(sku, { ...inputs, acosAllocation: 0 }).result;
    assert.equal(r.target, null);
    assert.equal(r.actual.profit, 3680);
    for (const value of [null, 0, -1, Infinity])
        assert.equal(
            calculateShopee({ ...sku, original_price: value }, inputs).result,
            null,
        );
});

test('keeps losses and handles zero or negative revenue and break-even profit', () => {
    const zeroFees = simulationDefaults(
        Object.fromEntries(Object.keys(configuration).map((key) => [key, 0])),
    );
    const even = calculateShopee(
        { original_price: 100, discount_price: 100 },
        { ...zeroFees, sellingPrice: 100 },
    ).result;
    assert.equal(even.profit, 0);
    assert.equal(even.breakEvenRoas, null);
    for (const processing_fee of [100, 200]) {
        const r = calculateShopee(
            { original_price: 50, discount_price: 100 },
            { ...zeroFees, processing_fee, sellingPrice: 100 },
        ).result;
        assert.equal(r.revenueMargin, null);
        assert.equal(r.breakEvenRoas, null);
        assert.ok(r.profit < 0);
        assert.ok(r.actual.profit < 0);
    }
    const r = calculateShopee(
        { original_price: 80, discount_price: 100 },
        { ...zeroFees, actualRoas: 5, sellingPrice: 100 },
    ).result;
    assert.equal(r.actual.profit, 0);
});

test('resets simulation without changing SKU or persisted configuration and clears state on store change', () => {
    const store = { id: 1, config_fees: [configuration] };
    let state = calculatorReducer(calculatorState(store), { type: 'sku', sku });
    state = calculatorReducer(state, {
        type: 'input',
        key: 'admin_fee',
        value: 20,
    });
    assert.equal(state.inputs.admin_fee, 20);
    assert.equal(configuration.admin_fee, 8.25);
    state = calculatorReducer(state, { type: 'reset' });
    assert.equal(state.sku, sku);
    assert.deepEqual(state.inputs, inputs);
    state = calculatorReducer(state, {
        type: 'store',
        marketplace: {
            id: 2,
            config_fees: [configuration, { ...configuration, id: 2 }],
        },
    });
    assert.equal(state.sku, null);
    assert.equal(state.query, '');
    assert.equal(state.configuration, null);
    state = calculatorReducer(state, { type: 'configuration', configuration });
    assert.deepEqual(state.inputs, simulationDefaults(configuration));
    assert.equal(
        calculatorState({ id: 3, config_fees: [] }).configuration,
        null,
    );
});

test('debounces searches and ignores late responses even if fetch ignores abort', async (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const pending = [];
    t.mock.method(
        globalThis,
        'fetch',
        () => new Promise((resolve) => pending.push(resolve)),
    );
    const updates = [];
    const cancel = searchCalculatorProducts('/first-store', (value) =>
        updates.push(value),
    );
    t.mock.timers.tick(499);
    assert.equal(pending.length, 0);
    t.mock.timers.tick(1);
    cancel();
    searchCalculatorProducts('/second-store', (value) => updates.push(value));
    t.mock.timers.tick(500);
    pending[1]({ ok: true, json: async () => [{ sku_id: 2 }] });
    await new Promise((resolve) => setImmediate(resolve));
    pending[0]({ ok: true, json: async () => [{ sku_id: 1 }] });
    await new Promise((resolve) => setImmediate(resolve));
    assert.deepEqual(updates.at(-1), {
        status: 'ready',
        items: [{ sku_id: 2 }],
    });
    assert.equal(updates.filter((value) => value.status === 'ready').length, 1);
});

test('reports failed searches and supports a successful retry with empty results', async (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const fetchMock = t.mock.method(globalThis, 'fetch', async () => ({
        ok: false,
    }));
    const updates = [];
    searchCalculatorProducts('/products', (value) => updates.push(value));
    t.mock.timers.tick(500);
    await new Promise((resolve) => setImmediate(resolve));
    assert.equal(updates.at(-1).status, 'error');
    fetchMock.mock.mockImplementation(async () => ({
        ok: true,
        json: async () => [],
    }));
    searchCalculatorProducts('/products', (value) => updates.push(value));
    t.mock.timers.tick(500);
    await new Promise((resolve) => setImmediate(resolve));
    assert.deepEqual(updates.at(-1), { status: 'ready', items: [] });
});

test('recalculates from an editable selling price and restores the SKU price on reset', () => {
    let state = calculatorReducer(
        calculatorState({ id: 1, config_fees: [configuration] }),
        { type: 'sku', sku },
    );
    assert.equal(state.inputs.sellingPrice, 80999);
    state = calculatorReducer(state, {
        type: 'input',
        key: 'sellingPrice',
        value: '100000',
    });
    const { result } = calculateShopee(state.sku, state.inputs);
    assert.equal(result.sales, 100000);
    assert.equal(result.fees.admin_fee, 8250);
    assert.equal(result.profit, 35604);
    assert.equal(result.actual.budget, 20000);
    assert.equal(sku.discount_price, 80999);
    state = calculatorReducer(state, { type: 'configuration', configuration });
    assert.equal(state.inputs.sellingPrice, '100000');
    state = calculatorReducer(state, { type: 'reset' });
    assert.equal(state.inputs.sellingPrice, 80999);
    state = calculatorReducer(state, {
        type: 'sku',
        sku: { ...sku, sku_id: 2, discount_price: 50000 },
    });
    assert.equal(state.inputs.sellingPrice, 50000);
    state = calculatorReducer(state, { type: 'query', query: 'new' });
    assert.equal(state.inputs.sellingPrice, '');
    state = calculatorReducer(state, { type: 'store', marketplace: null });
    assert.equal(state.inputs.sellingPrice, '');
});

test('requires a positive selling price and validates discount against the edited price', () => {
    for (const sellingPrice of [
        '',
        ' ',
        0,
        -1,
        'invalid',
        null,
        undefined,
        Infinity,
    ]) {
        const output = calculateShopee(sku, { ...inputs, sellingPrice });
        assert.ok(output.errors.sellingPrice);
        assert.equal(output.result, null);
    }
    const output = calculateShopee(sku, {
        ...inputs,
        sellingPrice: 1000,
        discount: 1000,
    });
    assert.ok(output.errors.discount);
    assert.equal(output.result, null);
    assert.equal(
        calculateShopee(sku, { ...inputs, sellingPrice: 1000, discount: 999 })
            .result.sales,
        1,
    );
});

test('derives ACOS and target ROAS from the allocated profit without rounding ROAS into the budget', () => {
    const rates = Object.fromEntries(
        Object.keys(configuration).map((key) => [key, 0]),
    );
    const exampleSku = { original_price: 22672, discount_price: 51000 };
    const exampleInputs = {
        ...simulationDefaults(rates, exampleSku),
        discount: 1000,
    };
    const { result } = calculateShopee(exampleSku, exampleInputs);
    assert.equal(exampleInputs.acosAllocation, 45);
    assert.equal(result.profit, 27328);
    assert.equal(result.target.budget, 12298);
    assert.ok(Math.abs(result.target.acos - 24.5952) < 1e-10);
    assert.ok(Math.abs(result.target.roas - 4.065833983865144) < 1e-10);
    assert.equal(result.target.profit, 15030);
    const updated = calculateShopee(exampleSku, {
        ...exampleInputs,
        acosAllocation: 50,
    }).result;
    assert.equal(updated.target.budget, 13664);
    const allProfit = calculateShopee(exampleSku, {
        ...exampleInputs,
        acosAllocation: 100,
    }).result;
    assert.equal(allProfit.target.profit, 0);
    const invalid = calculateShopee(exampleSku, {
        ...exampleInputs,
        acosAllocation: 101,
    });
    assert.ok(invalid.errors.acosAllocation);
    assert.equal(invalid.result.target, null);
    const loss = calculateShopee(exampleSku, {
        ...exampleInputs,
        sellingPrice: 20000,
    }).result;
    assert.equal(loss.target, null);
    assert.ok(loss.actual.profit < 0);
    let state = calculatorReducer(
        calculatorState({ id: 1, config_fees: [rates] }),
        { type: 'sku', sku: exampleSku },
    );
    state = calculatorReducer(state, {
        type: 'input',
        key: 'acosAllocation',
        value: 60,
    });
    state = calculatorReducer(state, { type: 'reset' });
    assert.equal(state.inputs.acosAllocation, 45);
});

test('hides results while typing and restarts the idle delay on every new query', async (t) => {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const fetchMock = t.mock.method(globalThis, 'fetch', async () => ({
        ok: true,
        json: async () => [{ sku_id: 3 }],
    }));
    let search = { status: 'ready', items: [{ sku_id: 1 }] };
    let cancel = searchCalculatorProducts('/products?q=pa', (value) => {
        search = value;
    });
    for (const query of ['par', 'parf', 'parfum']) {
        assert.deepEqual(search, { status: 'typing', items: [] });
        t.mock.timers.tick(400);
        assert.equal(fetchMock.mock.callCount(), 0);
        cancel();
        cancel = searchCalculatorProducts(`/products?q=${query}`, (value) => {
            search = value;
        });
    }
    t.mock.timers.tick(499);
    assert.deepEqual(search, { status: 'typing', items: [] });
    t.mock.timers.tick(1);
    assert.deepEqual(search, { status: 'loading', items: [] });
    await new Promise((resolve) => setImmediate(resolve));
    assert.equal(fetchMock.mock.callCount(), 1);
    assert.equal(fetchMock.mock.calls[0].arguments[0], '/products?q=parfum');
    assert.deepEqual(search, { status: 'ready', items: [{ sku_id: 3 }] });
    cancel();
});
