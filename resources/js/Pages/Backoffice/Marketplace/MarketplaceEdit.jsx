import React from 'react'
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

function MarketplaceEdit() {
    const {marketplace} = usePage().props;
    const headTitle     = `Marketplace Edit ${marketplace.marketplace} - ${marketplace.store}`;

    const {data, setData, put, processing, errors} = useForm({
        id         : marketplace.id,
        marketplace: marketplace.marketplace ?? '',
        store      : marketplace.store ?? ''
    });

    const submit = (e) => {
        e.preventDefault()

        put(route('marketplace.put', marketplace.id));
    }
    return (
    <AuthenticatedLayout
        header={
            <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {headTitle}
            </h2>
        }
    >
    <Head title={headTitle} />

    <div className="py-6">
        <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div className="bg-white shadow rounded-lg p-6">
                <form className="space-y-4" onSubmit={submit}>
                    <div>
                        <InputLabel
                            htmlFor="marketplace"
                            value="Marketplace"
                        />
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
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="store"
                            value="Store"
                        />

                        <input
                            type="text"
                            value={data.store}
                            onChange={(e) => setData('store', e.target.value)}
                            className="mt-1 block w-full border-gray-300 rounded-md"
                        />

                        <InputError message={errors.store} />
                    </div>

                    <div className="flex justify-end">
                        <button
                            type="submit"
                            disabled={processing}
                            className="
                                px-4 py-2 rounded text-white transition
                                bg-blue-500 hover:bg-blue-600
                                disabled:bg-gray-400
                                disabled:cursor-not-allowed
                            "
                        >
                            Update
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>

    </AuthenticatedLayout>
  )
}

export default MarketplaceEdit
