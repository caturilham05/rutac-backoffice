import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Plus, SquarePen, Trash } from 'lucide-react';
import { useMemo } from 'react';

function Products() {
    const { products, flash, filters, sort, direction, categories, options } =
        usePage().props;

    const filterConfig = useMemo(
        () => [
            {
                key: 'name',
                label: 'Product Name',
                type: 'autocomplete',
                placeholder: 'Search product...',
                options: options || [],
            },
            {
                key: 'category',
                label: 'Category',
                type: 'select',
                options: [
                    { value: '', label: 'All Categories' },
                    ...categories.map((c) => ({ value: c.id, label: c.name })),
                ],
            },
        ],
        [categories, options],
    );

    const sortableColumns = ['name', 'price', 'stock', 'cat_name'];

    const columns = [
        {
            key: 'detail',
            label: '',
        },
        {
            key: 'name',
            label: 'Product Name',
            render: (row) => `${row.name ?? ''}`,
            renderDetail: (item) => `${item.name ?? ''}`,
        },
        {
            key: 'sku',
            label: 'SKU',
            render: () => `-`,
            renderDetail: (item) => `${item.sku}`,
        },
        {
            key: 'price',
            label: 'Price',
            render: (row) => {
                if (!row.has_variant) {
                    const minPrice = Math.min(
                        ...row.items.map((item) => Number(item.price)),
                    );
                    return `Rp ${minPrice.toLocaleString('id-ID')}`;
                } else {
                    const minPrice = Math.min(
                        ...row.items.map((item) => Number(item.price)),
                    );
                    const maxPrice = Math.max(
                        ...row.items.map((item) => Number(item.price)),
                    );

                    return `Rp ${minPrice.toLocaleString('id-ID')} - Rp ${maxPrice.toLocaleString('id-ID')}`;
                }
            },
            renderDetail: (item) =>
                `Rp ${Number(item.price).toLocaleString('id-ID')}`,
        },
        {
            key: 'stock',
            label: 'Stock',
            render: (row) => {
                let stockTotal = 0;

                if (!row.has_variant) {
                    stockTotal = row.items[0].stock;
                } else {
                    stockTotal = row.items.reduce(
                        (total, item) => total + +item.stock,
                        0,
                    );
                }

                return stockTotal;
            },
            renderDetail: (item) => `${item.stock}`,
        },
        {
            key: 'category',
            label: 'Category',
            render: (row) => `${row.cat_name ?? ''}`,
            renderDetail: (item, row) => `${row.cat_name}`,
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('products.edit', row.id)}
                        className="rounded bg-yellow-500 px-3 py-1 text-white"
                    >
                        <SquarePen size={15} />
                    </Link>
                    <button
                        onClick={() => handleDelete(row.id)}
                        className="rounded bg-red-500 px-3 py-1 text-white"
                    >
                        <Trash size={15} />
                    </button>
                </div>
            ),
        },
    ];

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

        router.delete(route('products.delete', id), {
            preserveScroll: false,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Product List
                </h2>
            }
        >
            <Head title="Product List" />
            {flash.success && (
                <FlashMessage type="success" message={flash.success} />
            )}

            {flash.error && <FlashMessage type="error" message={flash.error} />}

            <PrimaryButton
                className="mx-4 mt-4 py-2"
                onClick={() => router.visit(route('products.create'))}
            >
                <Plus size={15} />
                Add Product
            </PrimaryButton>

            <div className="py-4">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <DataTable
                            columns={columns}
                            data={products.data}
                            pagination={products}
                            filterValues={filters || {}}
                            sortColumn={sort || null}
                            sortDirection={direction || 'asc'}
                            filterConfig={filterConfig}
                            sortableColumns={sortableColumns}
                            baseUrl={route('products')}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Products;
