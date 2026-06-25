import React from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';
import PrimaryButton from '@/Components/PrimaryButton';
import FlashMessage from '@/Components/FlashMessage';
import {Trash, SquarePen, Plus} from 'lucide-react';

function Products() {
    const {products, flash} = usePage().props;
    const columns = [
        {
            key: 'detail',
            label: '',
        },
        {
            key: 'name',
            label: 'Product Name',
            render: (row) => `${row.name ?? ''}`
        },
        {
            key: 'price',
            label: 'Price',
            render: (row) => {
                if (!row.has_variant) {
                    const minPrice = Math.min(...row.items.map(item => Number(item.price)));
                    return `Rp ${minPrice.toLocaleString('id-ID')}`;
                } else {
                    const minPrice = Math.min(...row.items.map(item => Number(item.price)));
                    const maxPrice = Math.max(...row.items.map(item => Number(item.price)));

                    return `Rp ${minPrice.toLocaleString('id-ID')} - Rp ${maxPrice.toLocaleString('id-ID')}`;
                }
            }
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
                        0
                    );
                }

                return stockTotal
            }
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('products.edit', row.id)}
                        className="px-3 py-1 bg-yellow-500 text-white rounded"
                    >
                        <SquarePen size={15} />
                    </Link>
                    <button
                        onClick={() => handleDelete(row.id)}
                        className="px-3 py-1 bg-red-500 text-white rounded"
                    >
                        <Trash size={15}/>
                    </button>
                </div>
            )
        },
    ];

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return
        }

        router.delete(route('products.delete', id), {
            preserveScroll: false,
        })
    }

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

            {flash.error && (
                <FlashMessage type="error" message={flash.error} />
            )}

            <PrimaryButton className="py-2 mx-4 mt-4" onClick={() => router.visit(route('products.create'))}><Plus size={15} />Add Product</PrimaryButton>

            <div className="py-4">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <DataTable columns={columns} data={products} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default Products
