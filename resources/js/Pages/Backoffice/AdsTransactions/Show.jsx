import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/Utils/format';
import { Head, Link } from '@inertiajs/react';

const value = (value) => value ?? '-';

export default function Show({ transaction, storeName }) {
    const details = [
        ['Subtotal', formatCurrency(transaction.original_price ?? 0)],
        ['PPN', formatCurrency(transaction.tax_amount ?? 0)],
        ['Biaya Penanganan', formatCurrency(transaction.transaction_fee ?? 0)],
        ['Total Pembayaran', formatCurrency(transaction.actual_price ?? 0)],
        [
            'Metode Pembayaran',
            [transaction.payment_method, transaction.payment_method_channel]
                .filter(Boolean)
                .join(' - ') || '-',
        ],
        ['Tipe Pembayaran Pesanan', 'Pembayaran manual'],
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Ads Transaction Detail
                </h2>
            }
        >
            <Head title="Ads Transaction Detail" />

            <div className="py-8 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 sm:p-8">
                        <Link
                            href={route('ads-transactions.index')}
                            className="text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            ← Kembali ke Ads Transaction
                        </Link>

                        <h1 className="mt-6 border-b border-dashed border-gray-300 pb-6 text-3xl font-semibold text-gray-900 dark:border-gray-600 dark:text-white">
                            {transaction.status === 'completed' ? 'Selesai' : transaction.status}
                        </h1>

                        <p className="py-6 text-lg text-gray-600 dark:text-gray-300">
                            <span className="font-semibold text-gray-900 dark:text-white">Iklan Shopee:</span>{' '}
                            Pengisian saldo berhasil dilakukan pada {value(transaction.order_time_formatted)}.
                        </p>

                        <section className="overflow-hidden border border-gray-100 shadow-sm dark:border-gray-700">
                            <div className="bg-gray-50 px-6 py-5 text-lg text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                Nama Toko: {value(storeName)}
                            </div>

                            <div className="flex flex-col justify-between gap-6 px-6 py-8 sm:flex-row sm:items-center sm:px-8 sm:py-10">
                                <div className="flex items-center gap-5">
                                    <div aria-hidden="true" className="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-orange-500 text-2xl font-bold text-white">
                                        AD
                                    </div>
                                    <div>
                                        <h2 className="text-xl font-semibold text-gray-900 dark:text-white">
                                            {transaction.name}
                                        </h2>
                                        <p className="mt-1 text-gray-500 dark:text-gray-400">
                                            No. Pesanan: {transaction.invoice}
                                        </p>
                                    </div>
                                </div>
                                <p className="shrink-0 text-lg text-gray-600 dark:text-gray-300">
                                    Total Pembayaran:{' '}
                                    <span className="text-3xl font-semibold text-orange-600 dark:text-orange-400">
                                        {formatCurrency(transaction.actual_price ?? 0)}
                                    </span>
                                </p>
                            </div>

                            <div className="border-t border-dashed border-gray-300 px-6 py-6 dark:border-gray-600 sm:px-10">
                                <dl className="ml-auto grid max-w-2xl grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-[1fr_auto]">
                                    {details.map(([label, detail]) => (
                                        <div key={label} className="contents">
                                            <dt className="text-gray-600 dark:text-gray-300">{label}</dt>
                                            <dd className="font-medium text-gray-900 dark:text-white sm:text-right">{detail}</dd>
                                        </div>
                                    ))}
                                </dl>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
