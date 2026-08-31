import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function ProductDiscount({
    discounts,
    filters,
    sortColumn,
    sortDirection,
}) {
    const formatDate = (date) => date?.replace('T', ' ').slice(0, 16) || '-';

    const columns = [
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
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
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
