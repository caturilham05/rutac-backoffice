import React from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';
import PrimaryButton from '@/Components/PrimaryButton';
import {Trash, SquarePen, Plus} from 'lucide-react';
import FlashMessage from '@/Components/FlashMessage';

function PurchasesList() {
    const {purchases, flash} = usePage().props;
    const columns = [
        {
            key: 'invoice',
            label: 'Invoice',
            render: (row) => `${row.invoice ?? ''}`
        },
        {
            key: 'vendor',
            label: 'Vendor',
            render: (row) => `${row.vendor ?? ''}`
        },
        {
            key: 'price',
            label: 'Price',
            render: (row) => {
                let price = Number(row.price);
                return `Rp ${price.toLocaleString('id-ID')}`
            }
        },
        {
            key: 'purchase_date',
            label: 'Purchase Date',
            render: (row) => `${row.created_at_formatted ?? ''}`
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
                        <DataTable columns={columns} data={purchases} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default PurchasesList
