import DataTable from '@/Components/DataTable';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const formatDate = (date) => date?.replace('T', ' ').slice(0, 16) || '-';
const value = (data) => data ?? '-';

export default function ProductDiscountDetail({
    discount,
    items,
    editing = false,
}) {
    const { data, setData, put, processing, errors } = useForm({
        items: items.data.map((item) => ({
            id: item.id,
            promotion_price:
                item.model_promotion_price ?? item.item_promotion_price ?? '',
            purchase_limit: item.purchase_limit ?? 0,
        })),
    });

    const updateItem = (id, field, value, productOriginId = null) => {
        setData(
            'items',
            data.items.map((item, index) =>
                item.id === id ||
                (field === 'purchase_limit' &&
                    items.data[index].product_origin_id === productOriginId)
                    ? { ...item, [field]: value }
                    : item,
            ),
        );
    };

    const input = (item, field, productOriginId = null) => {
        const index = data.items.findIndex((row) => row.id === item.id);

        return (
            <div className="min-w-32">
                <input
                    type="number"
                    min={field === 'purchase_limit' ? 0 : 0.01}
                    step={field === 'purchase_limit' ? 1 : 0.01}
                    value={data.items[index][field]}
                    onChange={(event) =>
                        updateItem(
                            item.id,
                            field,
                            event.target.value,
                            productOriginId,
                        )
                    }
                    className="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                />
                <InputError message={errors[`items.${index}.${field}`]} />
            </div>
        );
    };

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
                editing
                    ? input(item, 'promotion_price')
                    : value(
                          item.model_promotion_price ??
                              item.item_promotion_price,
                      ),
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
            render: (item) =>
                editing
                    ? input(item, 'purchase_limit', item.product_origin_id)
                    : value(item.purchase_limit),
        },
    ];

    const submit = (event) => {
        event.preventDefault();
        put(route('product_discounts.update', discount.discount_id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {editing
                        ? 'Edit Product Discount Item'
                        : 'Product Discount Detail'}
                </h2>
            }
        >
            <Head title={`Discount ${discount.discount_id}`} />

            <div className="py-12">
                <div className="mx-auto flex max-w-7xl flex-col gap-6 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between gap-4">
                        <Link
                            href={
                                editing
                                    ? route(
                                          'product_discounts.show',
                                          discount.discount_id,
                                      )
                                    : route('product_discounts')
                            }
                            className="w-fit text-sm text-blue-600 hover:underline dark:text-blue-400"
                        >
                            ←{' '}
                            {editing
                                ? 'Batal Edit'
                                : 'Kembali ke Product Discount'}
                        </Link>

                        {!editing && (
                            <Link
                                href={route('product_discounts.edit', {
                                    productDiscount: discount.discount_id,
                                    page: items.current_page,
                                })}
                                className="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"
                            >
                                Edit Discount Item
                            </Link>
                        )}
                    </div>

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

                    <form
                        onSubmit={submit}
                        className="flex flex-col gap-4 rounded-lg bg-white shadow-sm dark:bg-gray-800"
                    >
                        <DataTable
                            columns={columns}
                            data={items.data}
                            pagination={items}
                        />

                        {editing && (
                            <div className="flex justify-end px-4 pb-4">
                                <PrimaryButton disabled={processing}>
                                    {processing
                                        ? 'Menyimpan...'
                                        : 'Simpan Perubahan'}
                                </PrimaryButton>
                            </div>
                        )}
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
