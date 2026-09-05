import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import { formatCurrency } from '@/Utils/format';
import { LineChart } from '@mui/x-charts/LineChart';
import { createTheme, ThemeProvider } from '@mui/material/styles';
import { useTheme } from '@/Hooks/useTheme';
import { useMemo } from 'react';

export default function Dashboard({ filters, stats, daily_chart, top_order_products, top_purchase_products, top_buyers, top_vendors }) {
    const { errors } = usePage().props;
    const { theme } = useTheme();
    const chartTheme = useMemo(() => createTheme({ palette: { mode: theme } }), [theme]);
    const [dateRange, setDateRange] = useState({
        start_date: filters.start_date,
        end_date  : filters.end_date,
    });

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('dashboard'), dateRange, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="p-6">
                <form onSubmit={handleFilter} className="mb-8 flex flex-wrap items-end gap-4 rounded bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                        <input type="date" value={dateRange.start_date} onChange={(e) => setDateRange({...dateRange, start_date: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                        <input type="date" value={dateRange.end_date} onChange={(e) => setDateRange({...dateRange, end_date: e.target.value})} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <button type="submit" className="rounded bg-indigo-600 px-6 py-2 text-white hover:bg-indigo-700 transition-colors">Filter</button>
                    {(errors.start_date || errors.end_date) && (
                        <p role="alert" className="w-full text-sm text-red-600 dark:text-red-400">{errors.start_date || errors.end_date}</p>
                    )}
                </form>

                <section aria-labelledby="daily-chart-title" className="mb-8 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-gray-800">
                    <h3 id="daily-chart-title" className="text-lg font-bold text-gray-800 dark:text-white">Daily Performance</h3>
                    <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">Revenue order completed, total purchase, dan biaya iklan seluruh marketplace (Rp). Order berdasarkan tanggal order; tanggal tanpa data iklan ditampilkan sebagai celah.</p>
                    <div className="mt-4 min-w-0">
                        <ThemeProvider theme={chartTheme}>
                            <LineChart
                                dataset={daily_chart}
                                height={360}
                                grid={{ horizontal: true }}
                                xAxis={[{ scaleType: 'point', dataKey: 'date', valueFormatter: (value) => new Date(`${value}T00:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }]}
                                yAxis={[{ width: 80, valueFormatter: (value) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value) }]}
                                series={[
                                    { dataKey: 'order_revenue', label: 'Order Completed', color: '#3b82f6' },
                                    { dataKey: 'purchase_total', label: 'Purchase', color: '#10b981' },
                                    { dataKey: 'ad_expense', label: 'Biaya Iklan', color: '#f97316' },
                                ].map((series) => ({ ...series, curve: 'linear', valueFormatter: (value) => value === null ? 'Belum ada data' : formatCurrency(value) }))}
                            />
                        </ThemeProvider>
                    </div>
                </section>

                {/* Order Statistic */}
                <div className="mb-4">
                    <h3 className="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-wider">Order Statistics</h3>
                </div>
                <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {[
                        { label: 'Total Orders', value: stats.order.total_orders },
                        { label: 'Total Revenue', value: formatCurrency(stats.order.total_revenue) },
                        { label: 'Total Income', value: formatCurrency(stats.order.total_income) },
                        { label: 'Total Qty', value: stats.order.total_qty },
                        { label: 'Total Discount', value: formatCurrency(stats.order.total_discount) },
                    ].map((item, idx) => (
                        <div key={idx} className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{item.label}</p>
                            <p className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{item.value}</p>
                        </div>
                    ))}
                </div>
                <div className="mb-10 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Buyer</h3>
                        </div>
                        <table className="w-full text-left">
                            <thead>
                                <tr>
                                    <th className="border-b p-2">Buyer Name</th>
                                    <th className="border-b p-2">Orders</th>
                                    <th className="border-b p-2">Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                {top_buyers.map((b, idx) => (
                                    <tr key={idx}>
                                        <td className="border-b p-2">{b.buyer_username}</td>
                                        <td className="border-b p-2">{b.total_orders}</td>
                                        <td className="border-b p-2">{formatCurrency(b.total_spent)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Order Product</h3>
                        </div>
                        <table className="w-full text-left">
                            <thead>
                                <tr>
                                    <th className="border-b p-2">Product Name</th>
                                    <th className="border-b p-2">Total Qty</th>
                                    <th className="border-b p-2">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                {top_order_products.map((p, idx) => (
                                    <tr key={idx}>
                                        <td className="border-b p-2">{p.product_name}</td>
                                        <td className="border-b p-2">{p.total_qty}</td>
                                        <td className="border-b p-2">{formatCurrency(p.total_price)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Purchase Statistic */}
                <div className="mb-4">
                    <h3 className="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-wider">Purchase Statistics</h3>
                </div>
                <div className="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {[
                        { label: 'Total Invoice', value: stats.purchase.total_invoice },
                        { label: 'Total Price', value: formatCurrency(stats.purchase.total_price) },
                        { label: 'Total Qty', value: stats.purchase.total_qty },
                        { label: 'Total Discount', value: formatCurrency(stats.purchase.total_discount) },
                        { label: 'Total Add. Fee', value: formatCurrency(stats.purchase.total_additional_fee) },
                    ].map((item, idx) => (
                        <div key={idx} className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{item.label}</p>
                            <p className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{item.value}</p>
                        </div>
                    ))}
                </div>
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Vendor</h3>
                        </div>
                        <table className="w-full text-left">
                            <thead>
                                <tr>
                                    <th className="border-b p-2">Vendor Name</th>
                                    <th className="border-b p-2">Total Invoice</th>
                                    <th className="border-b p-2">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {top_vendors.map((v, idx) => (
                                    <tr key={idx}>
                                        <td className="border-b p-2">{v.vendor}</td>
                                        <td className="border-b p-2">{v.total_invoice}</td>
                                        <td className="border-b p-2">{formatCurrency(v.total_amount)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Purchase Product</h3>
                        </div>
                        <table className="w-full text-left">
                            <thead>
                                <tr>
                                    <th className="border-b p-2">Product Name</th>
                                    <th className="border-b p-2">Total Qty</th>
                                    <th className="border-b p-2">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                {top_purchase_products.map((p, idx) => (
                                    <tr key={idx}>
                                        <td className="border-b p-2">{p.product_name}</td>
                                        <td className="border-b p-2">{p.total_qty}</td>
                                        <td className="border-b p-2">{formatCurrency(p.total_price)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
