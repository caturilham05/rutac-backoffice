import React from 'react'
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';


function ProductCategoryEdit() {
    const {category} = usePage().props;
    const {data, setData, put, processing, errors} = useForm({
        id   : category.id,
        name : category.name ?? '',
    });

    const submit = (e) => {
        e.preventDefault()

        put(route('product_category.put', category.id));
    }

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Product Category Edit
                </h2>
            }
        >
            <Head title="Product Category Edit" />

            <div className="py-6">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow rounded-lg p-6">
                        <form className="space-y-4" onSubmit={submit}>
                            <div>
                                <InputLabel
                                    htmlFor="name"
                                    value="Name"
                                />

                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="mt-1 block w-full border-gray-300 rounded-md"
                                />

                                <InputError message={errors.name} />
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

export default ProductCategoryEdit
