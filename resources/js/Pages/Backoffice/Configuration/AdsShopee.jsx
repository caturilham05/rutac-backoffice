import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/Utils/format';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { LineChart } from '@mui/x-charts/LineChart';
import { Pause, Play } from 'lucide-react';
import { useState } from 'react';

const metricGroups = {
    traffic: {
        label: 'Traffic',
        series: [
            ['impressions', 'Impressions'],
            ['clicks', 'Clicks'],
        ],
        format: (value) => Number(value).toLocaleString('id-ID'),
    },
    orders: {
        label: 'Orders',
        series: [
            ['direct_orders', 'Direct Orders'],
            ['broad_orders', 'Broad Orders'],
        ],
        format: (value) => Number(value).toLocaleString('id-ID'),
    },
    items: {
        label: 'Items Sold',
        series: [
            ['direct_items_sold', 'Direct Items'],
            ['broad_items_sold', 'Broad Items'],
        ],
        format: (value) => Number(value).toLocaleString('id-ID'),
    },
    value: {
        label: 'GMV and Expense',
        series: [
            ['direct_gmv', 'Direct GMV'],
            ['broad_gmv', 'Broad GMV'],
            ['expense', 'Expense'],
        ],
        format: formatCurrency,
    },
    conversion: {
        label: 'Conversion Rate',
        series: [
            ['direct_conversion_rate', 'Direct Rate'],
            ['broad_conversion_rate', 'Broad Rate'],
        ],
        format: (value) =>
            `${(Number(value) * 100).toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`,
    },
    roas: {
        label: 'ROAS',
        series: [
            ['direct_roas', 'Direct ROAS'],
            ['broad_roas', 'Broad ROAS'],
        ],
        format: (value) =>
            `${Number(value).toLocaleString('id-ID', { maximumFractionDigits: 2 })}x`,
    },
    ctr: {
        label: 'CTR',
        series: [['ctr', 'CTR']],
        format: (value) =>
            `${(Number(value) * 100).toLocaleString('id-ID', { maximumFractionDigits: 2 })}%`,
    },
    cost: {
        label: 'Cost per Conversion',
        series: [['cost_per_conversion', 'Cost per Conversion']],
        format: formatCurrency,
    },
};

function AdsShopee() {
    const { ads, daily, flash, filters, sort, campaigns, marketplaces } =
        usePage().props;
    const [selectedMarketplace, setSelectedMarketplace] = useState(
        daily.marketplace_id ?? '',
    );
    const [metricGroup, setMetricGroup] = useState('traffic');
    const { data, setData, post, processing, errors } = useForm({
        start_date: daily.start_date,
        end_date: daily.end_date,
    });
    const selectedStore = marketplaces.find(
        (marketplace) => marketplace.id === Number(selectedMarketplace),
    );
    const chart = metricGroups[metricGroup];
    const latestSyncedAt = daily.metrics.reduce(
        (latest, metric) =>
            metric.synced_at > latest ? metric.synced_at : latest,
        '',
    );

    const reloadDaily = (values) =>
        router.get(
            route('shopee.ads.index'),
            {
                ...Object.fromEntries(
                    new URLSearchParams(window.location.search),
                ),
                marketplace_id: selectedMarketplace,
                ...values,
            },
            { preserveScroll: true, replace: true },
        );

    const syncDaily = (event) => {
        event.preventDefault();
        post(route('shopee.ads.daily-metrics.sync', selectedMarketplace), {
            preserveScroll: true,
        });
    };

    const statusConfig = {
        ongoing: {
            label: 'Ongoing',
            icon: <Pause size={15} />,
            nextAction: 'pause',
            color: 'bg-red-500',
        },
        scheduled: { label: 'Scheduled', color: 'bg-blue-500' },
        ended: { label: 'Ended', color: 'bg-gray-500' },
        paused: {
            label: 'Paused',
            icon: <Play size={15} />,
            nextAction: 'resume',
            color: 'bg-green-500',
        },
        deleted: { label: 'Deleted', color: 'bg-red-800' },
        closed: { label: 'Closed', color: 'bg-gray-800' },
    };

    const columns = [
        { key: 'campaign_id', label: 'Campaign ID' },
        { key: 'name', label: 'Campaign Name' },
        { key: 'type', label: 'Type' },
        {
            key: 'status',
            label: 'Status',
            render: (row) => statusConfig[row.status]?.label || row.status,
        },
        { key: 'bidding_method', label: 'Bidding Method' },
        { key: 'campaign_budget', label: 'Budget' },
        { key: 'start_time', label: 'Start Time' },
        { key: 'end_time', label: 'End Time' },
        { key: 'roas_target', label: 'Roas Target' },
        {
            key: 'edit',
            label: 'Action',
            render: (row) =>
                statusConfig[row.status] &&
                statusConfig[row.status].nextAction && (
                    <div className="flex gap-2">
                        <button
                            onClick={() =>
                                handleAds(
                                    row.marketplace_id,
                                    row.campaign_id,
                                    row.status,
                                )
                            }
                            className={`rounded px-3 py-1 text-white ${statusConfig[row.status].color}`}
                        >
                            {statusConfig[row.status].icon}
                        </button>
                    </div>
                ),
        },
    ];

    const handleAds = (marketplaceId, campaignId, status) => {
        const action = statusConfig[status]?.nextAction;
        if (!action) return;

        router.post(route('shopee.ads.edit', marketplaceId), {
            campaign_id: campaignId,
            edit_action: action,
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Shopee Ads Configuration
                </h2>
            }
        >
            <Head title="Shopee Ads" />

            {flash.success && (
                <div className="mb-4 mt-4 rounded bg-green-300 p-4 text-green-700">
                    {flash.success}
                </div>
            )}

            {flash.error && (
                <div className="mb-4 mt-4 rounded bg-red-300 p-4 text-red-700">
                    {flash.error}
                </div>
            )}

            <div className="flex items-center gap-4 p-6 pb-0">
                <select
                    value={selectedMarketplace}
                    aria-label="Pilih toko Shopee"
                    disabled={marketplaces.length === 0}
                    onChange={(e) => {
                        setSelectedMarketplace(e.target.value);
                        router.get(
                            route('shopee.ads.index'),
                            {
                                ...Object.fromEntries(
                                    new URLSearchParams(window.location.search),
                                ),
                                marketplace_id: e.target.value,
                            },
                            { preserveScroll: true, replace: true },
                        );
                    }}
                    className="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    {marketplaces.length === 0 && (
                        <option value="">Tidak ada toko Shopee</option>
                    )}
                    {marketplaces.map((m) => (
                        <option key={m.id} value={m.id}>
                            {m.store}
                        </option>
                    ))}
                </select>
                <button
                    onClick={() => {
                        if (
                            confirm(
                                'Apakah Anda yakin ingin menyinkronkan data iklan?',
                            )
                        ) {
                            router.get(
                                route('shopee.ads', selectedMarketplace),
                            );
                        }
                    }}
                    disabled={!selectedMarketplace}
                    className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Sync Ads
                </button>
            </div>

            <div className="flex flex-col gap-6 p-6">
                <section className="rounded-xl border border-gray-200 bg-white p-4 text-gray-800 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 sm:p-6">
                    <div className="flex flex-col gap-4">
                        <div>
                            <h3 className="text-lg font-semibold">
                                Ads Daily Performance
                            </h3>
                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                {selectedStore
                                    ? `${selectedStore.store} · ${data.start_date}—${data.end_date}`
                                    : 'Hubungkan toko Shopee untuk mulai sinkronisasi.'}
                            </p>
                            {latestSyncedAt && (
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Terakhir sinkron:{' '}
                                    {new Date(latestSyncedAt).toLocaleString(
                                        'id-ID',
                                    )}
                                </p>
                            )}
                        </div>

                        <form
                            onSubmit={syncDaily}
                            className="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end"
                        >
                            <div>
                                <label
                                    htmlFor="daily-start-date"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Tanggal Mulai
                                </label>
                                <input
                                    id="daily-start-date"
                                    type="date"
                                    value={data.start_date}
                                    onChange={(event) =>
                                        setData(
                                            'start_date',
                                            event.target.value,
                                        )
                                    }
                                    onBlur={() =>
                                        reloadDaily({
                                            start_date: data.start_date,
                                            end_date: data.end_date,
                                        })
                                    }
                                    className="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900"
                                />
                                {errors.start_date && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.start_date}
                                    </p>
                                )}
                            </div>
                            <div>
                                <label
                                    htmlFor="daily-end-date"
                                    className="mb-1 block text-sm font-medium"
                                >
                                    Tanggal Selesai
                                </label>
                                <input
                                    id="daily-end-date"
                                    type="date"
                                    value={data.end_date}
                                    min={data.start_date}
                                    onChange={(event) =>
                                        setData('end_date', event.target.value)
                                    }
                                    onBlur={() =>
                                        reloadDaily({
                                            start_date: data.start_date,
                                            end_date: data.end_date,
                                        })
                                    }
                                    className="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900"
                                />
                                {errors.end_date && (
                                    <p className="mt-1 text-sm text-red-600">
                                        {errors.end_date}
                                    </p>
                                )}
                            </div>
                            <button
                                type="submit"
                                disabled={!selectedMarketplace || processing}
                                className="rounded bg-orange-600 px-4 py-2 text-white hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing
                                    ? 'Menyinkronkan...'
                                    : 'Sync Daily Performance'}
                            </button>
                            {errors.marketplace && (
                                <p className="text-sm text-red-600 sm:col-span-2 lg:col-span-3">
                                    {errors.marketplace}
                                </p>
                            )}
                        </form>

                        {daily.metrics.length > 0 ? (
                            <div className="flex flex-col gap-3">
                                <label className="text-sm font-medium">
                                    Grup Metrik
                                    <select
                                        value={metricGroup}
                                        onChange={(event) =>
                                            setMetricGroup(event.target.value)
                                        }
                                        className="mt-1 block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 sm:w-auto"
                                    >
                                        {Object.entries(metricGroups).map(
                                            ([value, group]) => (
                                                <option
                                                    key={value}
                                                    value={value}
                                                >
                                                    {group.label}
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </label>
                                <div className="min-h-80 w-full overflow-hidden">
                                    <LineChart
                                        height={320}
                                        xAxis={[
                                            {
                                                scaleType: 'point',
                                                data: daily.metrics.map(
                                                    (metric) =>
                                                        new Date(
                                                            `${metric.metric_date}T00:00:00`,
                                                        ).toLocaleDateString(
                                                            'id-ID',
                                                            {
                                                                day: '2-digit',
                                                                month: 'short',
                                                            },
                                                        ),
                                                ),
                                            },
                                        ]}
                                        yAxis={[
                                            { valueFormatter: chart.format },
                                        ]}
                                        series={chart.series.map(
                                            ([key, label]) => ({
                                                data: daily.metrics.map(
                                                    (metric) =>
                                                        Number(metric[key]),
                                                ),
                                                label,
                                                valueFormatter: chart.format,
                                                showMark:
                                                    daily.metrics.length < 32,
                                            }),
                                        )}
                                        margin={{ left: 80, right: 20 }}
                                    />
                                </div>
                            </div>
                        ) : (
                            <div className="rounded-lg bg-gray-50 p-8 text-center text-sm text-gray-500 dark:bg-gray-900/50 dark:text-gray-400">
                                Belum ada data Ads Daily untuk toko dan rentang
                                tanggal ini.
                            </div>
                        )}
                    </div>
                </section>

                <DataTable
                    columns={columns}
                    data={ads.data}
                    pagination={ads}
                    baseUrl={route('shopee.ads.index')}
                    filterConfig={[
                        {
                            key: 'campaign_name',
                            label: 'Campaign Name',
                            type: 'autocomplete',
                            options: campaigns,
                        },
                        {
                            key: 'status',
                            label: 'Status',
                            type: 'select',
                            options: [
                                { value: 'ongoing', label: 'Ongoing' },
                                { value: 'scheduled', label: 'Scheduled' },
                                { value: 'ended', label: 'Ended' },
                                { value: 'paused', label: 'Paused' },
                                { value: 'deleted', label: 'Deleted' },
                                { value: 'closed', label: 'Closed' },
                            ],
                        },
                    ]}
                    filterValues={filters}
                    sortColumn={sort.sort}
                    sortDirection={sort.direction}
                    sortableColumns={[
                        'campaign_id',
                        'campaign_budget',
                        'roas_target',
                    ]}
                />
            </div>
        </AuthenticatedLayout>
    );
}

export default AdsShopee;
