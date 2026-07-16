import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useMemo } from 'react';


function PurchaseProducts() {
    const {purchase_products, filters, sort, direction, categories} = usePage().props;

    const filterConfig = useMemo(() => [
        {
            key        : 'invoice',
            label      : 'Invoice',
            type       : 'text',
            placeholder: 'Seach Invoice ...',
        },
        {
            key        : 'product_name',
            label      : 'Product Name',
            type       : 'text',
            placeholder: 'Search product name ...',
        },
        {
            key    : 'cat_name',
            label  : 'Category Name',
            type   : 'select',
            options: [
                { value: '', label: 'All Categories' },
                ...categories.map((c) => ({ value: c.name, label: c.name })),
            ],
        },
    ]);

    const sortableColumns = ['invoice', 'product_name', 'cat_name'];

    const columns = [
        {
            key   : 'invoice',
            label : 'Invoice',
            render: (row) => `${row.purchase?.invoice ?? ''}`,
        },
        {
            key   : 'product_name',
            label : 'Product Name',
            render: (row) => `${row.product_name ?? ''}`,
        },
        {
            key   : 'cat_name',
            label : 'Category Name',
            render: (row) => `${row.cat_name ?? ''}`,
        },
        {
            key: 'price',
            label: 'Price Product',
            render: (row) => {
                let price = Number(row.price);
                return `Rp ${price.toLocaleString('id-ID')}`;
            },
        },
        {
            key: 'qty',
            label: 'Quantity',
            render: (row) => `${row.qty ?? ''}`,
        },
    ];


    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Purchase Products
                </h2>
            }
        >
            <Head title="Purchase Products" />

            <div className="py-4">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <DataTable
                            columns={columns}
                            data={purchase_products.data}
                            pagination={purchase_products}
                            filterValues={filters || {}}
                            sortColumn={sort || null}
                            sortDirection={direction || 'asc'}
                            filterConfig={filterConfig}
                            sortableColumns={sortableColumns}
                            baseUrl={route('purchases.products')}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>

    )
}

export default PurchaseProducts
