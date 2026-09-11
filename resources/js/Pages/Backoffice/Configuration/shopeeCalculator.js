export const feeFields = {
    admin_fee: 'Biaya admin',
    free_shipping: 'Gratis ongkir',
    extra_promo: 'Promo ekstra',
    processing_fee: 'Biaya pemrosesan',
    affiliate: 'Afiliasi',
    live: 'Live',
    premi_fee: 'Premi',
    operational: 'Operasional',
};

export function simulationDefaults(configuration, sku = null) {
    return {
        ...Object.fromEntries(
            Object.keys(feeFields).map((key) => [
                key,
                configuration?.[key] ?? '',
            ]),
        ),
        sellingPrice: sku?.discount_price ?? '',
        discount: 0,
        acosAllocation: 45,
        actualRoas: 5,
    };
}

export function calculateShopee(sku, inputs) {
    const errors = {};
    const number = (key, min, max = Infinity, exclusiveMin = false) => {
        const raw = inputs[key];
        const value = Number(raw);
        if (
            (typeof raw !== 'number' && typeof raw !== 'string') ||
            String(raw).trim() === '' ||
            !Number.isFinite(value) ||
            (exclusiveMin ? value <= min : value < min) ||
            value > max
        ) {
            errors[key] =
                key === 'discount'
                    ? 'Diskon harus angka, minimal 0 dan kurang dari harga jual.'
                    : `Masukkan angka ${exclusiveMin ? 'lebih dari' : 'minimal'} ${min}${Number.isFinite(max) ? ` dan maksimal ${max}` : ''}.`;
        }
        return value;
    };
    const sellingPrice = number('sellingPrice', 0, Infinity, true);
    const cost = Number(sku?.original_price);
    const discount = number('discount', 0);
    if (discount >= sellingPrice)
        errors.discount =
            'Diskon harus angka, minimal 0 dan kurang dari harga jual.';
    const rates = Object.fromEntries(
        Object.keys(feeFields).map((key) => [
            key,
            number(key, 0, key === 'processing_fee' ? Infinity : 100),
        ]),
    );
    const acosAllocation = number('acosAllocation', 0, 100, true);
    const actualRoas = number('actualRoas', 0, Infinity, true);
    if (
        !Number.isFinite(cost) ||
        cost <= 0 ||
        !Number.isFinite(sellingPrice) ||
        sellingPrice <= 0
    )
        return { errors, result: null };
    if (
        Object.keys(errors).some(
            (key) => !['acosAllocation', 'actualRoas'].includes(key),
        )
    )
        return { errors, result: null };

    // Remove binary floating-point noise at exact rupiah boundaries before rounding up.
    const ceilRupiah = (value) =>
        Math.ceil(value - Math.abs(value) * Number.EPSILON);
    const sales = sellingPrice - discount;
    const fees = Object.fromEntries(
        Object.entries(rates).map(([key, rate]) => [
            key,
            key === 'processing_fee'
                ? Math.ceil(rate)
                : ceilRupiah((sales * rate) / 100),
        ]),
    );
    const totalFees = Object.values(fees).reduce((sum, fee) => sum + fee, 0);
    const revenue = sales - totalFees;
    const profit = revenue - cost;
    const advertising = (roas, key) => {
        if (errors[key]) return null;
        if (!Number.isFinite(sales / roas)) {
            errors[key] = 'ROAS terlalu kecil untuk dihitung.';
            return null;
        }
        const budget = ceilRupiah(sales / roas);
        return {
            budget,
            acos: (budget / sales) * 100,
            profit: profit - budget,
            margin: ((profit - budget) / sales) * 100,
        };
    };
    if (!Number.isFinite(totalFees + cost))
        return {
            errors: {
                ...errors,
                processing_fee: 'Nominal terlalu besar untuk dihitung.',
            },
            result: null,
        };
    let target = null;
    if (!errors.acosAllocation && profit > 0) {
        const allocatedBudget = (profit * acosAllocation) / 100;
        const acos = (profit / sales) * acosAllocation;
        const budget = ceilRupiah(allocatedBudget);
        target = {
            budget,
            acos,
            roas: acos > 0 ? 100 / acos : null,
            profit: profit - budget,
            margin: ((profit - budget) / sales) * 100,
        };
    }
    return {
        errors,
        result: {
            sales,
            fees,
            totalFees,
            revenue,
            nonAdCost: cost + totalFees,
            profit,
            initialMargin: sales - cost,
            initialMarginPercent: ((sales - cost) / sales) * 100,
            revenueMargin: revenue > 0 ? (profit / revenue) * 100 : null,
            salesMargin: (profit / sales) * 100,
            breakEvenRoas: profit > 0 ? sales / profit : null,
            target,
            actual: advertising(actualRoas, 'actualRoas'),
        },
    };
}

export function calculatorState(marketplace) {
    const configuration =
        marketplace?.config_fees?.length === 1
            ? marketplace.config_fees[0]
            : null;
    return {
        marketplace,
        configuration,
        sku: null,
        query: '',
        inputs: simulationDefaults(configuration),
    };
}

export function calculatorReducer(state, action) {
    switch (action.type) {
        case 'store':
            return calculatorState(action.marketplace);
        case 'configuration':
            return {
                ...state,
                configuration: action.configuration,
                inputs: {
                    ...simulationDefaults(action.configuration),
                    sellingPrice: state.inputs.sellingPrice,
                },
            };
        case 'sku':
            return {
                ...state,
                sku: action.sku,
                query: '',
                inputs: {
                    ...state.inputs,
                    sellingPrice: action.sku?.discount_price ?? '',
                },
            };
        case 'query':
            return {
                ...state,
                sku: null,
                query: action.query,
                inputs: { ...state.inputs, sellingPrice: '' },
            };
        case 'input':
            return {
                ...state,
                inputs: { ...state.inputs, [action.key]: action.value },
            };
        case 'reset':
            return {
                ...state,
                inputs: simulationDefaults(state.configuration, state.sku),
            };
        default:
            return state;
    }
}

export function searchCalculatorProducts(url, onChange) {
    const controller = new AbortController();
    let active = true;
    onChange({ status: 'typing', items: [] });
    const timer = setTimeout(async () => {
        onChange({ status: 'loading', items: [] });
        try {
            const response = await fetch(url, {
                signal: controller.signal,
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error('Pencarian gagal');
            const items = await response.json();
            if (!Array.isArray(items)) throw new Error('Respons tidak valid');
            if (active) onChange({ status: 'ready', items });
        } catch {
            if (active) onChange({ status: 'error', items: [] });
        }
    }, 500);
    return () => {
        active = false;
        clearTimeout(timer);
        controller.abort();
    };
}
