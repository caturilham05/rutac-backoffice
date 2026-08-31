import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const formatDate = (date) => date?.replace('T', ' ').slice(0, 16) || '-';
const value = (data) => data ?? '-';

export default function ProductDiscountDetail({ discount }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Product Discount Detail
                </h2>
            }
        >
            <Head title={`Discount ${discount.discount_id}`} />

            <div className="py-12">
                <div className="mx-auto flex max-w-7xl flex-col gap-6 sm:px-6 lg:px-8">
                    <Link
                        href={route('product_discounts')}
                        className="w-fit text-sm text-blue-600 hover:underline dark:text-blue-400"
                    >
                        ← Kembali ke Product Discount
                    </Link>

                    <div className="grid gap-4 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <span className="text-sm text-gray-500">
                                Discount ID
                            </span>
                            <p className="dark:text-gray-100">
                                {discount.discount_id}
                            </p>
                        </div>
                        <div>
                            <span className="text-sm text-gray-500">Nama</span>
                            <p className="dark:text-gray-100">
                                {discount.discount_name}
                            </p>
                        </div>
                        <div>
                            <span className="text-sm text-gray-500">
                                Status
                            </span>
                            <p className="dark:text-gray-100">
                                {value(discount.status)}
                            </p>
                        </div>
                        <div>
                            <span className="text-sm text-gray-500">Mulai</span>
                            <p className="dark:text-gray-100">
                                {formatDate(discount.start_date)}
                            </p>
                        </div>
                        <div>
                            <span className="text-sm text-gray-500">
                                Selesai
                            </span>
                            <p className="dark:text-gray-100">
                                {formatDate(discount.end_date)}
                            </p>
                        </div>
                    </div>

                    <div className="overflow-x-auto rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <table className="w-full text-left text-sm dark:text-gray-100">
                            <thead className="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <tr>
                                    <th className="px-4 py-3">Item ID</th>
                                    <th className="px-4 py-3">Item</th>
                                    <th className="px-4 py-3">Model ID</th>
                                    <th className="px-4 py-3">Model</th>
                                    <th className="px-4 py-3">Harga Normal</th>
                                    <th className="px-4 py-3">Harga Promo</th>
                                    <th className="px-4 py-3">Stok Promo</th>
                                    <th className="px-4 py-3">Batas Beli</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {discount.items.length ? (
                                    discount.items.map((item) => (
                                        <tr key={item.id}>
                                            <td className="px-4 py-3">
                                                {item.product_origin_id}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(item.item_name)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {item.product_model_id}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(item.model_name)}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(
                                                    item.model_original_price ??
                                                        item.item_original_price,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(
                                                    item.model_promotion_price ??
                                                        item.item_promotion_price,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(
                                                    item.model_promotion_stock ??
                                                        item.item_promotion_stock,
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                {value(item.purchase_limit)}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="8"
                                            className="px-4 py-6 text-center text-gray-400"
                                        >
                                            Tidak ada item
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
