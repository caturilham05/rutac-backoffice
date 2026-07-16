import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import Autocomplete from '@mui/material/Autocomplete';
import Stack from '@mui/material/Stack';
import TextField from '@mui/material/TextField';
import { Fragment } from 'react';

function PurchasesCreate() {
    const { invoice, products } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        vendor: '',
        products: [],
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('purchases.store'), {
            preserveScroll: false,
            onSuccess: () => reset(),
        });
    };

    const addProducts = () => {
        setData('products', [
            ...data.products,
            {
                product_id: '',
                price: '',
                qty: '',
            },
        ]);
    };

    const updateProducts = (index, field, value) => {
        const products = [...data.products];
        products[index] = {
            ...products[index],
            [field]: value,
        };
        setData('products', products);
    };

    const removeProduct = (index) => {
        const products = data.products.filter((_, i) => i !== index);
        console.log(products);
        setData('products', products);

        if (products.length === 0) {
            // reset default product
            setData('products', []);
            return;
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Purchases Create
                </h2>
            }
        >
            <Head title="Purchase Create" />

            <div className="py-6">
                <form className="space-y-4" onSubmit={submit}>
                    <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                        <div className="my-3 rounded-lg bg-white p-6 shadow">
                            <h2 className="mb-5 text-2xl font-semibold text-gray-800">
                                Informasi Pembelian
                            </h2>

                            <div className="py-5">
                                <div className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                                    {invoice}
                                </div>
                            </div>

                            <Fragment>
                                <InputLabel htmlFor="vendor" value="Vendor" />

                                <input
                                    type="text"
                                    onChange={(e) =>
                                        setData('vendor', e.target.value)
                                    }
                                    className="my-2 block w-full rounded-md border-gray-300"
                                />

                                <InputError message={errors.vendor} />
                            </Fragment>

                            <Fragment>
                                <InputLabel htmlFor="discount" value="Discount" />
                                <div className="flex overflow-hidden rounded-lg border">
                                    <div className="flex items-center border-r bg-gray-50 px-4">
                                        Rp
                                    </div>

                                    <input
                                        type="text"
                                        onChange={(e) =>
                                            setData('discount', e.target.value)
                                        }
                                        className="w-full border-0 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.discount} />
                            </Fragment>

                            <Fragment>
                                <InputLabel htmlFor="additional_fee" value="Additional Fee" />
                                <div className="flex overflow-hidden rounded-lg border">
                                    <div className="flex items-center border-r bg-gray-50 px-4">
                                        Rp
                                    </div>

                                    <input
                                        type="text"
                                        onChange={(e) =>
                                            setData('additional_fee', e.target.value)
                                        }
                                        className="w-full border-0 focus:ring-0"
                                    />
                                </div>
                                <InputError message={errors.additional_fee} />
                            </Fragment>
                        </div>

                        <div className="rounded-lg bg-white p-6 shadow">
                            <h2 className="mb-5 text-2xl font-semibold text-gray-800">
                                Informasi Produk
                            </h2>
                            {data.products?.length > 0 && (
                                <div className="mt-6">
                                    <div className="space-y-4">
                                        {data.products?.map((v, i) => (
                                            <div
                                                key={i}
                                                className="rounded-lg border p-6"
                                            >
                                                {/* Header */}
                                                <div className="mb-4 flex items-center justify-between">
                                                    <h3 className="text-xl font-medium">
                                                        Produk {i + 1}
                                                    </h3>

                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            removeProduct(i)
                                                        }
                                                        className="text-2xl text-gray-500 hover:text-red-500"
                                                    >
                                                        ×
                                                    </button>
                                                </div>

                                                <Fragment>
                                                    <div className="py-3">
                                                        <Stack
                                                            spacing={2}
                                                            sx={{
                                                                width: 'auto',
                                                            }}
                                                        >
                                                            <Autocomplete
                                                                id="free-solo-demo3"
                                                                freeSolo
                                                                resetHighlightOnMouseLeave
                                                                options={
                                                                    products
                                                                }
                                                                onChange={(
                                                                    e,
                                                                    newValue,
                                                                ) =>
                                                                    updateProducts(
                                                                        i,
                                                                        'product_id',
                                                                        newValue.id,
                                                                    )
                                                                }
                                                                renderInput={(
                                                                    params,
                                                                ) => (
                                                                    <TextField
                                                                        {...params}
                                                                        label="Pilih Produk"
                                                                    />
                                                                )}
                                                                getOptionLabel={(
                                                                    option,
                                                                ) =>
                                                                    typeof option ===
                                                                    'string'
                                                                        ? option
                                                                        : option.name
                                                                }
                                                                isOptionEqualToValue={(
                                                                    option,
                                                                    value,
                                                                ) => {
                                                                    if (
                                                                        typeof value ===
                                                                        'string'
                                                                    ) {
                                                                        return (
                                                                            option.name ===
                                                                            value
                                                                        );
                                                                    }
                                                                    return (
                                                                        option.name ===
                                                                        value.name
                                                                    );
                                                                }}
                                                            />
                                                        </Stack>
                                                    </div>
                                                    <InputError
                                                        message={
                                                            errors[
                                                                `products.${i}.product_id`
                                                            ]
                                                        }
                                                    />
                                                </Fragment>

                                                <Fragment>
                                                    <InputLabel value="Harga" />
                                                    <div className="flex overflow-hidden rounded-lg border">
                                                        <div className="flex items-center border-r bg-gray-50 px-4">
                                                            Rp
                                                        </div>

                                                        <input
                                                            type="text"
                                                            onChange={(e) =>
                                                                updateProducts(
                                                                    i,
                                                                    'price',
                                                                    e.target
                                                                        .value,
                                                                )
                                                            }
                                                            className="w-full border-0 focus:ring-0"
                                                        />
                                                    </div>
                                                    <InputError
                                                        message={
                                                            errors[
                                                                `products.${i}.price`
                                                            ]
                                                        }
                                                    />
                                                </Fragment>

                                                <Fragment>
                                                    <InputLabel value="Stok" />
                                                    <input
                                                        type="text"
                                                        onChange={(e) =>
                                                            updateProducts(
                                                                i,
                                                                'qty',
                                                                e.target.value,
                                                            )
                                                        }
                                                        className="w-full rounded-md border-gray-300"
                                                    />
                                                    <InputError
                                                        message={
                                                            errors[
                                                                `products.${i}.qty`
                                                            ]
                                                        }
                                                    />
                                                </Fragment>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            <InputError message={errors.products} />

                            <button
                                type="button"
                                onClick={addProducts}
                                className="mt-4 rounded-lg border border-dashed border-orange-300 px-4 py-2 text-orange-500 hover:bg-orange-50"
                            >
                                + Tambah Produk
                            </button>
                        </div>
                    </div>

                    <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600 disabled:cursor-not-allowed disabled:bg-gray-400"
                            >
                                Create
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default PurchasesCreate;
