import React from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import {Trash, SquarePen} from 'lucide-react';

function Marketplace() {
    const {marketplaces, flash} = usePage().props;

    const columns = [
        {
            key: 'marketplace_id',
            label: 'Marketplace Id Origin',
            render: (row) => `${row.marketplace_id ?? 0}`
        },
        {
            key: 'shop_id',
            label: 'Shop Id',
            render: (row) => `${row.shop_id ?? 0}`
        },
        {
            key: 'marketplace',
            label: 'Marketplace',
            render: (row) => `${row.marketplace ?? ''}`
        },
        {
            key: 'store',
            label: 'Store',
            render: (row) => `${row.store ?? ''}`
        },
        {
            key: 'access_token',
            label: 'Access Token',
            render: (row) => `${row.access_token ?? ''}`
        },
        {
            key: 'refresh_token',
            label: 'Refresh Token',
            render: (row) => `${row.refresh_token ?? ''}`
        },
        {
            key: 'chiper',
            label: 'Chiper',
            render: (row) => `${row.chiper ?? ''}`
        },
        {
            key: 'refresh_token_expires_at',
            label: 'Refresh Token Expired At',
            render: (row) => `${row.refresh_token_expires_at ?? ''}`
        },
        {
            key: 'app_key',
            label: 'App Key',
            render: (row) => `${row.app_key ?? ''}`
        },
        {
            key: 'app_secret',
            label: 'App Secret',
            render: (row) => `${row.app_secret ?? ''}`
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('marketplace.edit', row.id)}
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

    const {data, setData, post, processing, errors, reset} = useForm({
        marketplace: '',
        store: '',
    })

    const submit = (e) => {
        e.preventDefault();
        post(route('marketplace.store'), {
            preserveScroll: false,
            onSuccess: () => reset()
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return
        }

        router.delete(route('marketplace.delete', id), {
            preserveScroll: false,
        })
    }

  return (
    <AuthenticatedLayout
        header={
            <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Marketplace
            </h2>
        }
    >
        <Head title="Marketplace" />
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

        <div className="py-4">
            <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <DataTable columns={columns} data={marketplaces} />
                </div>
            </div>
        </div>

        <div className="px-4 py-2">
            <form onSubmit={submit} className="space-y-4">
                <InputLabel htmlFor="marketplace" value="Marketplace Name" />
                <select
                    className="mt-1 block w-full border-gray-300 rounded-md"
                    value={data.marketplace}
                    onChange={(e) => setData("marketplace", e.target.value)}
                >
                    <option value="">Pilih Marketplace</option>
                    <option value="Shopee">Shopee</option>
                    <option value="Tiktok">TikTok</option>
                </select>
                <InputError message={errors.marketplace} />

                <InputLabel htmlFor="store" value="Store Name" />
                <input
                type="text"
                className="mt-1 block w-full border-gray-300 rounded-md"
                value={data.store}
                onChange={(e) => setData("store", e.target.value)}
                />
                <InputError message={errors.store} />


                <button
                    type="submit"
                    disabled={processing}
                    className={`
                        px-4 py-2 rounded text-white transition
                        ${processing
                            ? 'bg-gray-400 cursor-not-allowed'
                            : 'bg-blue-500 hover:bg-blue-600'
                        }
                    `}
                >
                    Simpan
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
  )
}

export default Marketplace
