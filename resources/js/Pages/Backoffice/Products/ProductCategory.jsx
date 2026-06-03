import React from 'react'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm, usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

function ProductCategory() {
    const {catList, flash} = usePage().props;
    const columns = [
        {
            key: 'cat_name',
            label: 'Category Name',
            render: (row) => `${row.name ?? ''}`
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('product_category.edit', row.id)}
                        className="px-3 py-1 bg-yellow-500 text-white rounded"
                    >
                        Edit
                    </Link>
                    <button
                        onClick={() => handleDelete(row.id)}
                        className="px-3 py-1 bg-red-500 text-white rounded"
                    >
                        Delete
                    </button>
                </div>
            )
        },
    ];

    const {data, setData, post, processing, errors, reset} = useForm({
        name: '',
    })

    const submit = (e) => {
        e.preventDefault()
        post(route('product_category.store'), {
            preserveScroll: false,
            onSuccess: () => reset()
        });
    }

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return
        }

        router.delete(route('product_category.delete', id), {
            preserveScroll: false,
        })
    }

  return (
    <AuthenticatedLayout
        header={
            <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Product Category
            </h2>
        }
    >
        <Head title="Product Category" />
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
                    <DataTable columns={columns} data={catList} />
                </div>
            </div>
        </div>

        <div className="px-4 py-2">
            <form onSubmit={submit} className="space-y-4">
                <InputLabel htmlFor="name" value="Category Name" />
                <input
                type="text"
                className="mt-1 block w-full border-gray-300 rounded-md"
                value={data.name}
                onChange={(e) => setData("name", e.target.value)}
                />
                <InputError message={errors.name} />


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

export default ProductCategory
