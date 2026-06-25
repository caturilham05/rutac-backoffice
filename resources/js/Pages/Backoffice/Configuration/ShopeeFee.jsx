import React from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import DataTable from '@/Components/DataTable';
import {Trash, SquarePen} from 'lucide-react';

function ShopeeFee() {
    const { setting, flash, marketplaces } = usePage().props;

    const columns = [
        {
            key: 'marketplace',
            label: 'Marketplace',
            render: (row) => `${row.marketplace?.marketplace ?? ''}`,
        },
        {
            key: 'store',
            label: 'Store',
            render: (row) => `${row.marketplace?.store ?? ''}`,
        },
        {
            key: 'admin_fee',
            label: 'Admin Fee',
            render: (row) => `${row.admin_fee ?? 0}%`,
        },
        {
            key: 'free_shipping',
            label: 'Free Shipping',
            render: (row) => `${row.free_shipping ?? 0}%`,
        },
        {
            key: 'extra_promo',
            label: 'Extra Promo',
            render: (row) => `${row.extra_promo ?? 0}%`,
        },
        {
            key: 'processing_fee',
            label: 'Processing Fee',
            render: (row) =>
                `Rp ${new Intl.NumberFormat('id-ID').format(row.processing_fee ?? 0)}`,
        },
        {
            key: 'affiliate',
            label: 'Affiliate Fee',
            render: (row) => `${row.affiliate ?? 0}%`,
        },
        {
            key: 'live',
            label: 'Live Fee',
            render: (row) => `${row.live ?? 0}%`,
        },
        {
            key: 'premi_fee',
            label: 'Premi Fee',
            render: (row) => `${row.premi_fee ?? 0}%`,
        },
        {
            key: 'operational',
            label: 'Operational Fee',
            render: (row) => `${row.operational ?? 0}%`,
        },
        {
            key: 'action',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('shopeeFee.edit', row.id)}
                        className="px-3 py-1 bg-yellow-500 text-white rounded"
                    >
                        <SquarePen size={15} />
                    </Link>
                    <button
                        onClick={() => handleDelete(row.id)}
                        className="px-3 py-1 bg-red-500 text-white rounded"
                    >
                        <Trash size={15} />
                    </button>
                </div>
            )
        },
    ];

    const tableData = setting ? setting : [];

    const { data, setData, post, errors, processing, reset } = useForm({
        admin_fee     : setting?.admin_fee ?? '',
        free_shipping : setting?.free_shipping ?? '',
        extra_promo   : setting?.extra_promo ?? '',
        processing_fee: setting?.processing_fee ?? '',
        affiliate     : setting?.affiliate ?? '',
        live          : setting?.live ?? '',
        premi_fee     : setting?.premi_fee ?? '',
        operational   : setting?.operational ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('ShopeeFeePost'), {
            preserveScroll: false,
            onSuccess: () => reset(),
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return
        }

        console.log(id)

        router.delete(route('shopeeFee.delete', id), {
            preserveScroll: false,
        })
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Configuration Shopee Fee
                </h2>
            }
        >
            <Head title="ShopeeFee" />

            {flash.success && (
                <div className="mb-4 mt-4 p-4 bg-green-300 text-green-700 rounded">
                    {flash.success}
                </div>
            )}

            {flash.error && (
                <div className="mb-4 mt-4 p-4 bg-red-300 text-red-700 rounded">
                    {flash.error}
                </div>
            )}

            <div className="p-6 space-y-6">

                {/* 🔥 TABLE */}
                <DataTable columns={columns} data={tableData} />

                {/* 🔥 FORM */}
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <InputLabel
                            htmlFor="marketplace_id"
                            value="Marketplace Name"
                        />
                        <select
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            // value={data.marketplace}
                            onChange={(e) => setData("marketplace_id", e.target.value)}
                        >
                            <option value="">Pilih Marketplace</option>
                            {
                                marketplaces?.map((v) => (
                                    <option value={v.id} key={v.id}>{v.marketplace} - {v.store}</option>
                                ))
                            }
                        </select>
                        <InputError message={errors.marketplace_id} />
                    </div>

                    <div>
                        <InputLabel htmlFor="admin_fee" value="Admin Fee (%)" />
                        <input
                            id="admin_fee"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.admin_fee}
                            onChange={(e) => setData('admin_fee', e.target.value)}
                        />
                        <InputError message={errors.admin_fee} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="free_shipping" value="Free Shipping (%)" />
                        <input
                            id="free_shipping"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.free_shipping}
                            onChange={(e) => setData('free_shipping', e.target.value)}
                        />
                        <InputError message={errors.free_shipping} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="extra_promo" value="Extra Promo (%)" />
                        <input
                            id="extra_promo"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.extra_promo}
                            onChange={(e) => setData('extra_promo', e.target.value)}
                        />
                        <InputError message={errors.extra_promo} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="processing_fee" value="Processing Fee (Rp)" />
                        <input
                            id="processing_fee"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.processing_fee}
                            onChange={(e) => setData('processing_fee', e.target.value)}
                        />
                        <InputError message={errors.processing_fee} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="live" value="Live Fee (%)" />
                        <input
                            id="live"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.live}
                            onChange={(e) => setData('live', e.target.value)}
                        />
                        <InputError message={errors.live} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="affiliate" value="Affiliate Fee (%)" />
                        <input
                            id="affiliate"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.affiliate}
                            onChange={(e) => setData('affiliate', e.target.value)}
                        />
                        <InputError message={errors.affiliate} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="premi_fee" value="Premi Fee (%)" />
                        <input
                            id="premi_fee"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.premi_fee}
                            onChange={(e) => setData('premi_fee', e.target.value)}
                        />
                        <InputError message={errors.premi_fee} className="mt-2" />
                    </div>

                    <div>
                        <InputLabel htmlFor="operational" value="Operational Fee (%)" />
                        <input
                            id="operational"
                            className="mt-1 block w-full border-gray-300 rounded-md"
                            value={data.operational}
                            onChange={(e) => setData('operational', e.target.value)}
                        />
                        <InputError message={errors.operational} className="mt-2" />
                    </div>

                    <button className={`
                        px-4 py-2 rounded text-white transition
                        ${processing
                            ? 'bg-gray-400 cursor-not-allowed'
                            : 'bg-blue-500 hover:bg-blue-600'
                        }
                    `}
                    disabled={processing}>
                        Save
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default ShopeeFee
