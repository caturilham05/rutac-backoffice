import {React, useState} from 'react'
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import Checkbox from '@/Components/Checkbox';


function ProductsAction() {
    const {products, categories} = usePage().props;
    const {data, setData, put, post, processing, errors, reset} = useForm({
        id         : products.id ?? '',
        cat_id     : products.cat_id ?? '',
        cat_name   : products.cat_name ?? '',
        name       : products.name ?? '',
        description: products.description ?? '',
        has_variant: products.has_variant ?? false,
        sku_id     : products.sku_id ?? '',
        sku        : products.sku ?? '',
        stock      : products.stock ?? '',
        price      : products.price ?? '',
        variants   : products.items ?? [],
    });

    // console.log(data)

    let headerText = '';
    let routeCond  = '';
    let isEdit     = 0;
    if (Object.keys(products).length === 0) {
        headerText = 'Product Create';
        routeCond  = 'products.store';
    } else {
        headerText = 'Product Edit';
        routeCond  = 'products.put';
        isEdit     = 1;
    }

    const submit = (e) => {
        e.preventDefault()

        if (Object.keys(products).length === 0) {
            post(route(routeCond), {
                preserveScroll: false,
                onSuccess: () => reset(),
            });
        } else {
            put(route(routeCond, products.id), {
                preserveScroll: false,
                onSuccess: () => reset()
            });
        }
    }

    const addVariant = () => {
        setData("variants", [
            ...data.variants,
            {
                name : "",
                price: '',
                stock: '',
                sku  : ""
            }
        ]);
    };

    const removeVariant = (index) => {
        const variants = data.variants.filter((_, i) => i !== index)
        setData("variants", variants);

        if (variants.length === 0) {
            setData("has_variant", false);

            // reset default variasi
            setData("variants", [
                {
                    id    : null,
                    sku_id: null,
                    name  : "",
                    price : '',
                    stock : '',
                    sku   : ""
                },
            ]);

            return;
        }
    };

    const updateVariant = (index, field, value) => {
        const variants = [...data.variants];
        variants[index] = {
            ...variants[index],
            [field]: value
        }
        setData("variants", variants);
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {headerText}
                </h2>
            }
        >
            <Head title={headerText} />

            <div className="py-6">
                <form className="space-y-4" onSubmit={submit}>
                    <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200 mb-5">Informasi Produk</h2>

                            <div>
                                <InputLabel
                                    htmlFor="name"
                                    value="Name"
                                />

                                <input
                                    type="text"
                                    value={data.name || ''}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1 block w-full border-gray-300 rounded-md"
                                />

                                <InputError message={errors.name} />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="cat_id"
                                    value="Category"
                                />
                                <select
                                    className="mt-1 block w-full border-gray-300 rounded-md"
                                    value={data.cat_id || ''}
                                    onChange={(e) => setData("cat_id", e.target.value)}
                                >
                                    <option value="">Select Category</option>
                                    {
                                        categories?.map((v) => (
                                            <option value={v.id} key={v.id}>{v.name}</option>
                                        ))
                                    }
                                </select>
                                <InputError message={errors.cat_id} />
                            </div>

                            <div>
                                <InputLabel htmlFor="description" value="Description" />

                                <textarea
                                    id="description"
                                    className="mt-1 block w-full border-gray-300 rounded-md"
                                    value={data.description || ''}
                                    onChange={(e) => setData("description", e.target.value)}
                                    rows={5}
                                />
                            </div>
                        </div>
                    </div>

                    {/* Infomrasi Penjualan */}
                    <div className="max-w-4xl mx-auto sm:px-6 lg:px-8 mt-6">
                        <div className="bg-white shadow rounded-lg p-8">

                            <h2 className="text-2xl font-semibold text-gray-800 mb-5">
                                Informasi Variant
                            </h2>

                            {/* Variasi */}
                            {
                                (
                                    !isEdit || (isEdit && products.has_variant === 1)
                                ) && (
                                    <div className="mb-8">
                                        <label className="flex items-center gap-2 text-lg font-medium text-gray-700 mb-4">
                                            <span className="w-3 h-3 bg-orange-500 rounded-full"></span>
                                            Variasi
                                        </label>

                                        <button
                                            type="button"
                                            onClick={() => setData("has_variant", !data.has_variant)}
                                            className="
                                                border border-dashed border-gray-300
                                                rounded-lg px-6 py-4
                                                text-orange-500 font-medium
                                                hover:bg-orange-50
                                                transition
                                            "
                                        >
                                            + Aktifkan Variasi
                                        </button>
                                    </div>
                                )
                            }

                            {/* Pakai Variant */}
                            {Boolean(data.has_variant) && (
                                <div className="mt-6">
                                    <div className="space-y-4">
                                        {data.variants.map((variant, variantIndex) => (
                                            <div
                                                key={variantIndex}
                                                className="bg-gray-50 border rounded-lg p-6"
                                            >
                                                {/* Header */}
                                                <div className="flex justify-between items-center mb-4">
                                                    <h3 className="text-xl font-medium">
                                                        Variasi {variantIndex + 1}
                                                    </h3>

                                                    <button
                                                        type="button"
                                                        onClick={() => removeVariant(variantIndex)}
                                                        className="text-gray-500 hover:text-red-500 text-2xl"
                                                    >
                                                        ×
                                                    </button>
                                                </div>
                                                <div className="grid gap-4">
                                                    {/* Nama Variasi */}
                                                    <div>
                                                        <InputLabel value="Nama Variasi" />
                                                        <input
                                                            type="text"
                                                            value={variant.name || ''}
                                                            onChange={(e) =>
                                                                updateVariant(
                                                                    variantIndex,
                                                                    "name",
                                                                    e.target.value
                                                                )
                                                            }
                                                            className="w-full rounded-md border-gray-300"
                                                            placeholder="Contoh: Merah, XL, Large"
                                                        />
                                                        <InputError message={errors[`variants.${variantIndex}.name`]} />
                                                    </div>

                                                    {/* Harga */}
                                                    <div>
                                                        <InputLabel value="Harga" />
                                                        <div className="flex border rounded-lg overflow-hidden">
                                                            <div className="px-4 flex items-center bg-gray-50 border-r">
                                                                Rp
                                                            </div>

                                                            <input
                                                                type="number"
                                                                value={variant.price || ''}
                                                                onChange={(e) =>
                                                                    updateVariant(
                                                                        variantIndex,
                                                                        "price",
                                                                        e.target.value
                                                                    )
                                                                }
                                                                className="w-full border-0 focus:ring-0"
                                                            />
                                                        </div>
                                                        <InputError message={errors[`variants.${variantIndex}.price`]} />
                                                    </div>

                                                    {/* Stok */}
                                                    <div>
                                                        <InputLabel value="Stok" />
                                                        <input
                                                            type="number"
                                                            value={variant.stock || ''}
                                                            onChange={(e) =>
                                                                updateVariant(
                                                                    variantIndex,
                                                                    "stock",
                                                                    e.target.value
                                                                )
                                                            }
                                                            className="w-full rounded-md border-gray-300"
                                                        />
                                                        <InputError message={errors[`variants.${variantIndex}.stock`]} />
                                                    </div>

                                                    {/* SKU */}
                                                    <div>
                                                        <InputLabel value="SKU" />
                                                        <input
                                                            type="text"
                                                            value={variant.sku || ''}
                                                            onChange={(e) =>
                                                                updateVariant(
                                                                    variantIndex,
                                                                    "sku",
                                                                    e.target.value
                                                                )
                                                            }
                                                            className="w-full rounded-md border-gray-300"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    <button
                                        type="button"
                                        onClick={addVariant}
                                        className="
                                            mt-4
                                            px-4 py-2
                                            border border-dashed border-orange-300
                                            rounded-lg
                                            text-orange-500
                                            hover:bg-orange-50
                                        "
                                    >
                                        + Tambah Variasi
                                    </button>
                                </div>
                            )}

                            {/* Tanpa Variant */}
                            {!data.has_variant && (
                                <>
                                    {/* Harga */}
                                    <div className="mb-8">
                                        <label className="block font-medium text-gray-700 mb-2">
                                            <span className="text-red-500">*</span> Harga
                                        </label>

                                        <div className="flex border rounded-lg overflow-hidden">
                                            <div className="px-4 flex items-center bg-gray-50 border-r text-gray-500">
                                                Rp
                                            </div>

                                            <input
                                                type="number"
                                                value={data.price || ''}
                                                onChange={(e) => setData("price", e.target.value)}
                                                className="w-full border-0 focus:ring-0"
                                                placeholder="Input"
                                            />
                                        </div>
                                        <InputError message={errors.price} />
                                    </div>

                                    {/* Stok */}
                                    <div className="mb-8">
                                        <label className="block font-medium text-gray-700 mb-2">
                                            <span className="text-red-500">*</span> Stok
                                        </label>

                                        <input
                                            type="number"
                                            value={data.stock || ''}
                                            onChange={(e) => setData("stock", e.target.value)}
                                            className="w-full rounded-lg border-gray-300"
                                        />
                                        <InputError message={errors.stock} />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="sku"
                                            value="SKU Induk"
                                        />

                                        <input
                                            type="text"
                                            value={data.sku || ''}
                                            onChange={(e) => setData('sku', e.target.value)}
                                            className="mt-1 block w-full border-gray-300 rounded-md"
                                        />

                                        <InputError message={errors.sku} />
                                    </div>
                                </>
                            )}
                        </div>
                    </div>

                    <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
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
                                {headerText}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </AuthenticatedLayout>

    )
}

export default ProductsAction
