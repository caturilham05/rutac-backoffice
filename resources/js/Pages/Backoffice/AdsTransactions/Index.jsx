import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FlashMessage from '@/Components/FlashMessage';
import DataTable from '@/Components/DataTable';
import { Head, Link, usePage } from '@inertiajs/react';

const formatCurrency = (value) =>
    `Rp${Number(value ?? 0).toLocaleString('id-ID')}`;

export default function Index({ transactions, filters, sortColumn, sortDirection }) {
    const { flash } = usePage().props;
    const columns = [
        {
            key: 'name',
            label: 'Produk',
            render: (transaction) => (
                    <Link href={route('ads-transactions.show', transaction.id)} className="flex min-w-64 items-center gap-4 hover:opacity-80">
                        <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-orange-500 text-lg font-bold text-white">
                            AD
                        </div>
                        <div>
                            <p className="font-semibold text-gray-900 dark:text-white">{transaction.name}</p>
                            <p className="mt-1 text-gray-500 dark:text-gray-400">No. Pesanan: {transaction.invoice}</p>
                        </div>
                    </Link>
            ),
        },
        {
            key: 'actual_price',
            label: 'Total Pembayaran',
            render: (transaction) => formatCurrency(transaction.actual_price),
        },
        {
            key: 'payment_method',
            label: 'Metode Pembayaran',
            render: (transaction) => (
                <>
                    <div>{transaction.payment_method}</div>
                    {transaction.payment_method_channel && (
                        <div className="mt-1 text-gray-500 dark:text-gray-400">{transaction.payment_method_channel}</div>
                    )}
                </>
            ),
        },
        { key: 'status', label: 'Status' },
        { key: 'payment_type', label: 'Tipe Pembayaran', render: () => 'Pembayaran manual' },
        {
            key: 'order_time',
            label: 'Order Time',
            render: (transaction) => transaction.order_time_formatted,
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Ads Transaction
                </h2>
            }
        >
            <Head title="Ads Transaction" />

            <div className="py-8 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div className="px-4 pt-4">
                            <FlashMessage message={flash.success} role="status" />
                            <FlashMessage message={flash.error} type="error" role="alert" />
                        </div>
                        <div className="flex justify-end p-4">
                            <Link href={route('ads-transactions.sync')} className="rounded-md bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                                Sync Transaction Ads
                            </Link>
                        </div>
                        <DataTable
                            columns={columns}
                            data={transactions.data}
                            pagination={transactions}
                            filterConfig={[{ key: 'order_time', label: 'Order Time', type: 'date-range' }]}
                            filterValues={filters}
                            sortColumn={sortColumn}
                            sortDirection={sortDirection}
                            sortableColumns={['order_time']}
                            baseUrl={route('ads-transactions.index')}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
