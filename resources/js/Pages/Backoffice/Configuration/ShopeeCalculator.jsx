import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/Utils/format';
import {
    Combobox,
    ComboboxInput,
    ComboboxOption,
    ComboboxOptions,
} from '@headlessui/react';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useReducer, useState } from 'react';
import {
    calculateShopee,
    calculatorReducer,
    calculatorState,
    feeFields,
    searchCalculatorProducts,
} from './shopeeCalculator';

const currency = (value) =>
    value == null || !Number.isFinite(value) ? '-' : formatCurrency(value);
const decimal = (value) =>
    value == null || !Number.isFinite(value)
        ? '-'
        : new Intl.NumberFormat('id-ID', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
          }).format(value);
const percent = (value) => (value == null ? '-' : `${decimal(value)}%`);
const labelSku = (sku) =>
    sku
        ? [sku.product_name, sku.variant_name, sku.sku]
              .filter(Boolean)
              .join(' · ')
        : '';
const selectClass =
    'w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300';

function Summary({ title, rows }) {
    return (
        <section className="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6">
            <h2 className="mb-4 text-lg font-semibold">{title}</h2>
            <dl className="grid gap-3">
                {rows.map(([label, value]) => (
                    <div
                        key={label}
                        className="flex flex-wrap justify-between gap-2 border-b border-gray-100 pb-2 dark:border-gray-700"
                    >
                        <dt>{label}</dt>
                        <dd className="font-semibold tabular-nums">{value}</dd>
                    </div>
                ))}
            </dl>
        </section>
    );
}

export default function ShopeeCalculator({ marketplaces }) {
    const [state, dispatch] = useReducer(
        calculatorReducer,
        marketplaces.length === 1 ? marketplaces[0] : null,
        calculatorState,
    );
    const { marketplace, configuration, sku, query, inputs } = state;
    const [search, setSearch] = useState({ status: 'idle', items: [] });
    const [retry, setRetry] = useState(0);
    const searchKey = `${marketplace?.id ?? ''}:${query}`;
    const currentSearch =
        !sku && query.trim().length >= 2 && search.key === searchKey
            ? search
            : { status: 'idle', items: [] };
    useEffect(() => {
        if (
            !marketplace ||
            query.trim().length < 2 ||
            query.length > 100 ||
            sku
        )
            return;
        return searchCalculatorProducts(
            route('shopee.calculator.products', {
                marketplace_id: marketplace.id,
                q: query.trim(),
            }),
            (value) => setSearch({ ...value, key: searchKey }),
        );
    }, [marketplace, query, sku, retry, searchKey]);
    const { errors, result } = calculateShopee(sku, inputs);
    const visibleResult = configuration && sku ? result : null;
    const field = (key, label, max) => (
        <div key={key}>
            <InputLabel htmlFor={key} value={label} />
            <TextInput
                id={key}
                type="number"
                step="any"
                min="0"
                max={max}
                value={inputs[key]}
                disabled={!configuration || (key === 'sellingPrice' && !sku)}
                className="mt-1 w-full"
                aria-invalid={Boolean(errors[key])}
                aria-describedby={errors[key] ? `${key}-error` : undefined}
                onChange={(event) =>
                    dispatch({ type: 'input', key, value: event.target.value })
                }
            />
            {configuration && (
                <InputError id={`${key}-error`} message={errors[key]} />
            )}
        </div>
    );
    const adRows = (ad) => [
        ['Budget iklan/order', currency(ad?.budget)],
        ['ACOS', percent(ad?.acos)],
        ['Laba/order', currency(ad?.profit)],
        ['Laba terhadap penjualan', percent(ad?.margin)],
        [
            'Status',
            ad
                ? ad.profit > 0
                    ? 'Untung'
                    : ad.profit < 0
                      ? 'Rugi'
                      : 'Impas'
                : '-',
        ],
    ];
    return (
        <AuthenticatedLayout
            header={
                <h1 className="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    Kalkulator Shopee
                </h1>
            }
        >
            <Head title="Kalkulator Shopee" />
            <div className="mx-auto grid max-w-7xl gap-6 p-4 text-gray-800 dark:text-gray-200 sm:p-6">
                <p>
                    Simulasi satu unit SKU per order. Harga jual awal mengikuti
                    SKU; perubahan harga dan tarif hanya berlaku di kalkulator.
                </p>
                <section className="grid gap-4 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:grid-cols-2 sm:p-6">
                    <div>
                        <InputLabel
                            htmlFor="calculator-store"
                            value="Toko Shopee"
                        />
                        <select
                            id="calculator-store"
                            className={selectClass}
                            value={marketplace?.id ?? ''}
                            onChange={(event) =>
                                dispatch({
                                    type: 'store',
                                    marketplace:
                                        marketplaces.find(
                                            (item) =>
                                                String(item.id) ===
                                                event.target.value,
                                        ) ?? null,
                                })
                            }
                        >
                            <option value="">Pilih toko</option>
                            {marketplaces.map((item) => (
                                <option key={item.id} value={item.id}>
                                    {item.store || `Toko #${item.id}`}
                                </option>
                            ))}
                        </select>
                        {!marketplaces.length && <p>Belum ada toko Shopee.</p>}
                    </div>
                    <div>
                        <InputLabel
                            htmlFor="calculator-configuration"
                            value="Konfigurasi biaya"
                        />
                        <select
                            id="calculator-configuration"
                            className={selectClass}
                            disabled={!marketplace?.config_fees.length}
                            value={configuration?.id ?? ''}
                            onChange={(event) =>
                                dispatch({
                                    type: 'configuration',
                                    configuration:
                                        marketplace.config_fees.find(
                                            (item) =>
                                                String(item.id) ===
                                                event.target.value,
                                        ) ?? null,
                                })
                            }
                        >
                            <option value="">Pilih konfigurasi</option>
                            {marketplace?.config_fees.map((item) => (
                                <option key={item.id} value={item.id}>
                                    Konfigurasi #{item.id} ·{' '}
                                    {Object.entries(feeFields)
                                        .map(
                                            ([key, label]) =>
                                                `${label} ${key === 'processing_fee' ? currency(Number(item[key])) : `${decimal(Number(item[key]))}%`}`,
                                        )
                                        .join(' · ')}
                                </option>
                            ))}
                        </select>
                    </div>
                    {marketplace && !marketplace.config_fees.length && (
                        <p className="sm:col-span-2">
                            Konfigurasi biaya belum tersedia.{' '}
                            <Link
                                className="text-indigo-600 underline dark:text-indigo-400"
                                href={route('ShopeeFee')}
                            >
                                Buka pengaturan biaya
                            </Link>
                        </p>
                    )}
                    {configuration && (
                        <p className="text-sm sm:col-span-2">
                            Tarif konfigurasi #{configuration.id}:{' '}
                            {Object.entries(feeFields)
                                .map(
                                    ([key, label]) =>
                                        `${label} ${key === 'processing_fee' ? currency(Number(configuration[key])) : `${decimal(Number(configuration[key]))}%`}`,
                                )
                                .join(' · ')}
                        </p>
                    )}
                    <div className="relative sm:col-span-2">
                        <InputLabel
                            htmlFor="calculator-sku"
                            value="Cari produk, varian, atau kode SKU"
                        />
                        <Combobox
                            key={marketplace?.id ?? 'no-store'}
                            value={sku}
                            by="sku_id"
                            disabled={!marketplace}
                            onChange={(value) =>
                                dispatch({ type: 'sku', sku: value })
                            }
                        >
                            <ComboboxInput
                                id="calculator-sku"
                                className={selectClass}
                                displayValue={labelSku}
                                maxLength={100}
                                onChange={(event) => {
                                    setSearch({ status: 'typing', items: [] });
                                    dispatch({
                                        type: 'query',
                                        query: event.target.value,
                                    });
                                }}
                                placeholder="Ketik minimal 2 karakter"
                                autoComplete="off"
                            />
                            {currentSearch.status === 'ready' &&
                                currentSearch.items.length > 0 && (
                                    <ComboboxOptions className="absolute z-10 mt-1 max-h-64 w-full overflow-auto rounded-md border border-gray-300 bg-white shadow-lg empty:invisible dark:border-gray-700 dark:bg-gray-900">
                                        {currentSearch.items.map((item) => (
                                            <ComboboxOption
                                                key={item.sku_id}
                                                value={item}
                                                className="cursor-pointer px-4 py-3 data-[focus]:bg-indigo-100 data-[selected]:font-semibold dark:data-[focus]:bg-indigo-900"
                                            >
                                                {labelSku(item)}
                                            </ComboboxOption>
                                        ))}
                                    </ComboboxOptions>
                                )}
                        </Combobox>
                        <div role="status" className="mt-2 text-sm">
                            {currentSearch.status === 'loading' &&
                                'Sedang mencari…'}
                            {currentSearch.status === 'ready' &&
                                !currentSearch.items.length &&
                                'Tidak ada SKU dengan HPP dan harga jual valid.'}
                            {currentSearch.status === 'error' && (
                                <span>
                                    Pencarian gagal.{' '}
                                    <SecondaryButton
                                        onClick={() =>
                                            setRetry((value) => value + 1)
                                        }
                                    >
                                        Coba lagi
                                    </SecondaryButton>
                                </span>
                            )}
                        </div>
                    </div>
                    <dl>
                        <dt className="text-sm font-medium text-gray-700 dark:text-gray-300">
                            HPP
                        </dt>
                        <dd className="mt-1 text-lg font-semibold tabular-nums">
                            {currency(sku ? Number(sku.original_price) : null)}
                        </dd>
                    </dl>
                    {field('sellingPrice', 'Harga jual simulasi (Rp)')}
                    {field('discount', 'Diskon tambahan (Rp)')}
                    <div className="flex items-end">
                        <SecondaryButton
                            disabled={!configuration}
                            onClick={() => dispatch({ type: 'reset' })}
                        >
                            Reset simulasi
                        </SecondaryButton>
                    </div>
                </section>
                <section className="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800 sm:p-6">
                    <h2 className="mb-4 text-lg font-semibold">
                        Rincian biaya
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {Object.entries(feeFields).map(([key, label]) => (
                            <div key={key}>
                                {field(
                                    key,
                                    `${label} (${key === 'processing_fee' ? 'Rp/order' : '%'})`,
                                    key === 'processing_fee' ? undefined : 100,
                                )}
                                <p className="mt-1 text-sm">
                                    Nominal:{' '}
                                    {currency(visibleResult?.fees[key])}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>
                {!sku && (
                    <p role="status">
                        Pilih satu SKU untuk melihat hasil perhitungan.
                    </p>
                )}
                {sku && !configuration && (
                    <p role="status">
                        Pilih konfigurasi biaya untuk memulai perhitungan.
                    </p>
                )}
                {sku && configuration && !result && (
                    <p role="status">
                        Perbaiki input yang tidak valid untuk melihat hasil.
                    </p>
                )}
                <div className="grid gap-6 lg:grid-cols-2">
                    <Summary
                        title="Ringkasan sebelum iklan"
                        rows={[
                            [
                                'Harga setelah diskon',
                                currency(visibleResult?.sales),
                            ],
                            [
                                'Margin awal',
                                currency(visibleResult?.initialMargin),
                            ],
                            [
                                'Margin awal terhadap penjualan',
                                percent(visibleResult?.initialMarginPercent),
                            ],
                            ['Total biaya', currency(visibleResult?.totalFees)],
                            [
                                'Pendapatan setelah biaya',
                                currency(visibleResult?.revenue),
                            ],
                            [
                                'Total biaya noniklan (HPP + biaya)',
                                currency(visibleResult?.nonAdCost),
                            ],
                            ['Laba noniklan', currency(visibleResult?.profit)],
                            [
                                'Margin terhadap pendapatan setelah biaya',
                                percent(visibleResult?.revenueMargin),
                            ],
                            [
                                'Margin noniklan terhadap penjualan',
                                percent(visibleResult?.salesMargin),
                            ],
                        ]}
                    />
                    <div className="grid gap-4">
                        <div>
                            {field(
                                'acosAllocation',
                                'Variabel ACOS (% laba noniklan)',
                                100,
                            )}
                        </div>
                        <p className="text-sm">
                            ACOS = margin noniklan terhadap penjualan × variabel
                            ACOS. Budget iklan = laba noniklan × variabel ACOS.
                        </p>
                        <Summary
                            title="Simulasi iklan"
                            rows={[
                                ...adRows(visibleResult?.target),
                                [
                                    'Target ROAS (otomatis)',
                                    decimal(visibleResult?.target?.roas),
                                ],
                                [
                                    'ROAS BEP',
                                    decimal(visibleResult?.breakEvenRoas),
                                ],
                            ]}
                        />
                        {visibleResult && visibleResult.profit <= 0 && (
                            <p
                                role="status"
                                className="text-red-600 dark:text-red-400"
                            >
                                Tidak dapat impas melalui ROAS: laba sebelum
                                iklan tidak positif.
                            </p>
                        )}
                    </div>
                    <div className="grid gap-4">
                        {field('actualRoas', 'ROAS aktual')}
                        <Summary
                            title="ROAS aktual"
                            rows={adRows(visibleResult?.actual)}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
