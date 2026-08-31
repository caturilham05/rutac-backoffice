import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

export default function ProductDiscount({
    discounts,
    filters,
    sortColumn,
    sortDirection,
}) {
    const { flash } = usePage().props;
    const { post, processing } = useForm({});
    const formatDate = (date) => date?.replace('T', ' ').slice(0, 16) || '-';

    const syncDiscounts = () => {
        if (confirm('Sinkronkan seluruh product discount dari Shopee?')) {
            post(route('product_discounts.sync'), { preserveScroll: true });
        }
    };

    const columns = [
        {
            key: 'discount_id',
            label: 'Discount ID',
            render: (row) => (
                <Link
                    href={route('product_discounts.show', row.discount_id)}
                    className="text-blue-600 hover:underline dark:text-blue-400"
                >
                    {row.discount_id}
                </Link>
            ),
        },
        { key: 'discount_name', label: 'Discount Name' },
        { key: 'status', label: 'Status', render: (row) => row.status || '-' },
        {
            key: 'start_date',
            label: 'Start Date',
            render: (row) => formatDate(row.start_date),
        },
        {
            key: 'end_date',
            label: 'End Date',
            render: (row) => formatDate(row.end_date),
        },
    ];

    const filterConfig = [
        { key: 'discount_name', label: 'Discount Name', type: 'text' },
        { key: 'status', label: 'Status', type: 'text' },
        { key: 'start_date', label: 'Start Date', type: 'date' },
        { key: 'end_date', label: 'End Date', type: 'date' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Product Discount
                </h2>
            }
        >
            <Head title="Product Discount" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <FlashMessage message={flash.success} />
                    <FlashMessage message={flash.error} type="error" />

                    <div className="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="mb-6 flex justify-end">
                                <PrimaryButton
                                    type="button"
                                    disabled={processing}
                                    onClick={syncDiscounts}
                                >
                                    {processing
                                        ? 'Menyinkronkan...'
                                        : 'Sinkron Product Discount'}
                                </PrimaryButton>
                            </div>

                            <DataTable
                                columns={columns}
                                data={discounts.data}
                                pagination={discounts}
                                filterConfig={filterConfig}
                                filterValues={filters}
                                sortColumn={sortColumn}
                                sortDirection={sortDirection}
                                sortableColumns={[
                                    'discount_name',
                                    'status',
                                    'start_date',
                                ]}
                                baseUrl={route('product_discounts')}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
