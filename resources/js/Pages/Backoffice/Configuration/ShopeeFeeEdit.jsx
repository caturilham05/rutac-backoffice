import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';

function shopeeFeeEdit() {
    const { config_fee, marketplaces } = usePage().props;
    const { data, setData, put, processing, errors } = useForm({
        id: config_fee.id,
        admin_fee: config_fee.admin_fee ?? 0,
        free_shipping: config_fee.free_shipping ?? 0,
        extra_promo: config_fee.extra_promo ?? 0,
        processing_fee: config_fee.processing_fee ?? 0,
        affiliate: config_fee.affiliate ?? 0,
        live: config_fee.live ?? 0,
        premi_fee: config_fee.premi_fee ?? 0,
        operational: config_fee.operational ?? 0,
        marketplace_id: config_fee.marketplace_id ?? 0,
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('shopeeFee.put', config_fee.id));
    };
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Shopee Fee Edit
                </h2>
            }
        >
            <Head title="Shopee Fee Edit" />
            <div className="py-6">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow">
                        <form className="space-y-4" onSubmit={submit}>
                            <div>
                                <InputLabel
                                    htmlFor="marketplace_id"
                                    value="Marketplace Name"
                                />
                                <select
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.marketplace_id}
                                    onChange={(e) =>
                                        setData(
                                            'marketplace_id',
                                            e.target.value,
                                        )
                                    }
                                >
                                    <option value="">Pilih Marketplace</option>
                                    {marketplaces?.map((v) => (
                                        <option value={v.id} key={v.id}>
                                            {v.marketplace} - {v.store}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.marketplace_id} />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="admin_fee"
                                    value="Admin Fee"
                                />

                                <input
                                    type="text"
                                    value={data.admin_fee}
                                    onChange={(e) =>
                                        setData('admin_fee', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                />

                                <InputError message={errors.admin_fee} />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="free_shipping"
                                    value="Free Shipping (%)"
                                />
                                <input
                                    id="free_shipping"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.free_shipping}
                                    onChange={(e) =>
                                        setData('free_shipping', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.free_shipping}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="extra_promo"
                                    value="Extra Promo (%)"
                                />
                                <input
                                    id="extra_promo"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.extra_promo}
                                    onChange={(e) =>
                                        setData('extra_promo', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.extra_promo}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="processing_fee"
                                    value="Processing Fee (Rp)"
                                />
                                <input
                                    id="processing_fee"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.processing_fee}
                                    onChange={(e) =>
                                        setData(
                                            'processing_fee',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={errors.processing_fee}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="live"
                                    value="Live Fee (%)"
                                />
                                <input
                                    id="live"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.live}
                                    onChange={(e) =>
                                        setData('live', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.live}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="affiliate"
                                    value="Affiliate Fee (%)"
                                />
                                <input
                                    id="affiliate"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.affiliate}
                                    onChange={(e) =>
                                        setData('affiliate', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.affiliate}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="premi_fee"
                                    value="Premi Fee (%)"
                                />
                                <input
                                    id="premi_fee"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.premi_fee}
                                    onChange={(e) =>
                                        setData('premi_fee', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.premi_fee}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="operational"
                                    value="Operational Fee (%)"
                                />
                                <input
                                    id="operational"
                                    className="mt-1 block w-full rounded-md border-gray-300"
                                    value={data.operational}
                                    onChange={(e) =>
                                        setData('operational', e.target.value)
                                    }
                                />
                                <InputError
                                    message={errors.operational}
                                    className="mt-2"
                                />
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

export default shopeeFeeEdit;
