import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const formatDate = (date) => date?.replace('T', ' ').slice(0, 16) || '-';
const value = (data) => data ?? '-';

export default function ProductDiscountDetail({ discount, items }) {
    const columns = [
        { key: 'product_origin_id', label: 'Item ID' },
        {
            key: 'item_name',
            label: 'Item',
            render: (item) => value(item.item_name),
        },
        { key: 'product_model_id', label: 'Model ID' },
        {
            key: 'model_name',
            label: 'Model',
            render: (item) => value(item.model_name),
        },
        {
            key: 'original_price',
            label: 'Harga Normal',
            render: (item) =>
                value(item.model_original_price ?? item.item_original_price),
        },
        {
            key: 'promotion_price',
            label: 'Harga Promo',
            render: (item) =>
                value(item.model_promotion_price ?? item.item_promotion_price),
        },
        {
            key: 'promotion_stock',
            label: 'Stok Promo',
            render: (item) =>
                value(item.model_promotion_stock ?? item.item_promotion_stock),
        },
        {
            key: 'purchase_limit',
            label: 'Batas Beli',
            render: (item) => value(item.purchase_limit),
        },
    ];

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

                    <div className="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <DataTable
                            columns={columns}
                            data={items.data}
                            pagination={items}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
