import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';
import { formatCurrency } from '@/Utils/format';

export default function Dashboard({ filters, stats, top_products, top_vendors }) {
    const { flash } = usePage().props;
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
                </form>

                <div className="mb-4">
                    <h3 className="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-wider">Purchase Statistics</h3>
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {[
                        { label: 'Total Invoice', value: stats.total_invoice },
                        { label: 'Total Price', value: formatCurrency(stats.total_price) },
                        { label: 'Total Qty', value: stats.total_qty },
                        { label: 'Total Discount', value: formatCurrency(stats.total_discount) },
                        { label: 'Total Add. Fee', value: formatCurrency(stats.total_additional_fee) },
                    ].map((item, idx) => (
                        <div key={idx} className="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                            <p className="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{item.label}</p>
                            <p className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{item.value}</p>
                        </div>
                    ))}
                </div>

                <div className="mt-10 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Products</h3>
                            <p className="text-sm text-gray-500">Based on quantity purchased</p>
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
                                {top_products.map((p, idx) => (
                                    <tr key={idx}>
                                        <td className="border-b p-2">{p.product_name}</td>
                                        <td className="border-b p-2">{p.total_qty}</td>
                                        <td className="border-b p-2">{formatCurrency(p.total_price)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="rounded-xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div className="mb-6">
                            <h3 className="text-lg font-bold text-gray-800 dark:text-white">Top 5 Vendors</h3>
                            <p className="text-sm text-gray-500">Based on total purchase amount</p>
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
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
