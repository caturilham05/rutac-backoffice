import React, { useMemo } from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';
import PrimaryButton from '@/Components/PrimaryButton';
import {Trash, SquarePen, Plus} from 'lucide-react';
import FlashMessage from '@/Components/FlashMessage';

function PurchasesList() {
    const {purchases, flash, filters, sort, direction} = usePage().props;

    const filterConfig = useMemo(() => [
        {
            key        : 'invoice',
            label      : 'Invoice',
            type       : 'text',
            placeholder: 'Seach Invoice ...'
        },
        {
            key        : 'vendor',
            label      : 'Vendor',
            type       : 'text',
            placeholder: 'Search vendor ...'
        },
        {
            key  : 'created_at',
            label: 'Purchase Date',
            type : 'date'
        },
    ])

    const sortableColumns = ['invoice', 'created_at', 'qty', 'price'];

    const columns = [
        {
            key  : 'detail',
            label: 'detail'
        },
        {
            key   : 'invoice',
            label : 'Invoice',
            render: (row) => `${row.invoice ?? ''}`
        },
        {
            key   : 'vendor',
            label : 'Vendor',
            render: (row) => `${row.vendor ?? ''}`
        },
        {
            key   : 'created_at',
            label : 'Purchase Date',
            render: (row) => `${row.created_at_formatted ?? ''}`
        },
        {
            key         : 'product_name',
            label       : 'Product Name',
            renderDetail: (item) => `${item.product_name}`,
            render      : (item) => `-`
        },
        {
            key   : 'qty',
            label : 'Quantity',
            render: (row) => {
                let qtyTotal = 0;

                qtyTotal = row.items.reduce(
                    (total, item) => total + +item.qty,
                    0
                );

                return qtyTotal
            },
            renderDetail: (item) => `${item.qty}`
        },
        {
            key   : 'price',
            label : 'Price Total',
            render: (row) => {
                let price = Number(row.price);
                return `Rp ${price.toLocaleString('id-ID')}`
            },
            renderDetail: (row) => {
                let price = Number(row.price);
                return `Rp ${price.toLocaleString('id-ID')}`
            }
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <></>
            )
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Purchases List
                </h2>
            }
        >
            <Head title="Purchases List" />
            {flash.success && (
                <FlashMessage type="success" message={flash.success} />
            )}

            {flash.error && (
                <FlashMessage type="error" message={flash.error} />
            )}

            <PrimaryButton className="py-2 mx-4 mt-4" onClick={() => router.visit(route('purchases.create'))}><Plus size={15} />Add Purchases</PrimaryButton>

            <div className="py-4">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <DataTable
                            columns={columns}
                            data={purchases.data}
                            pagination={purchases}
                            filterValues={filters || {}}
                            sortColumn={sort || null}
                            sortDirection={direction || 'asc'}
                            filterConfig={filterConfig}
                            sortableColumns={sortableColumns}
                            baseUrl={route('purchases.list')}

                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default PurchasesList
