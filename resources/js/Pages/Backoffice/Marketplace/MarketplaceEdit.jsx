import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';

function MarketplaceEdit() {
    const { marketplace } = usePage().props;
    const headTitle = `Marketplace Edit ${marketplace.marketplace} - ${marketplace.store}`;

    const { data, setData, put, processing, errors } = useForm({
        id: marketplace.id,
        marketplace: marketplace.marketplace ?? '',
        store: marketplace.store ?? '',
    });

    const submit = (e) => {
        e.preventDefault();

        put(route('marketplace.put', marketplace.id));
    };
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
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <form className="space-y-4" onSubmit={submit}>
                            <div>
                                <InputLabel
                                    htmlFor="marketplace"
                                    value="Marketplace"
                                />
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.marketplace}
                                    onChange={(e) =>
                                        setData('marketplace', e.target.value)
                                    }
                                >
                                    <option value="">Pilih Marketplace</option>
                                    <option value="Shopee">Shopee</option>
                                    <option value="Tiktok">TikTok</option>
                                </select>
                                <InputError message={errors.marketplace} />
                            </div>

                            <div>
                                <InputLabel htmlFor="store" value="Store" />

                                <input
                                    type="text"
                                    value={data.store}
                                    onChange={(e) =>
                                        setData('store', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                />

                                <InputError message={errors.store} />
                            </div>

                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:bg-gray-400"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default MarketplaceEdit;
