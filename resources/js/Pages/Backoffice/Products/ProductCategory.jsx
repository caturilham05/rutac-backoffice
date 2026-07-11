import DataTable from '@/Components/DataTable';
import FlashMessage from '@/Components/FlashMessage';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { SquarePen, Trash } from 'lucide-react';

function ProductCategory() {
    const { catList, flash } = usePage().props;
    const columns = [
        {
            key: 'cat_name',
            label: 'Category Name',
            render: (row) => `${row.name ?? ''}`,
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('product_category.edit', row.id)}
                        className="rounded bg-yellow-500 px-3 py-1 text-white"
                    >
                        <SquarePen size={15} />
                    </Link>
                    <button
                        onClick={() => handleDelete(row.id)}
                        className="rounded bg-red-500 px-3 py-1 text-white"
                    >
                        <Trash size={15} />
                    </button>
                </div>
            ),
        },
    ];

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('product_category.store'), {
            preserveScroll: false,
            onSuccess: () => reset(),
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

        router.delete(route('product_category.delete', id), {
            preserveScroll: false,
        });
    };

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
                <FlashMessage type="success" message={flash.success} />
            )}

            {flash.error && <FlashMessage type="error" message={flash.error} />}

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
                        className="mt-1 block w-full rounded-md border-gray-300"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <InputError message={errors.name} />

                    <button
                        type="submit"
                        disabled={processing}
                        className={`rounded px-4 py-2 text-white transition ${
                            processing
                                ? 'cursor-not-allowed bg-gray-400'
                                : 'bg-blue-500 hover:bg-blue-600'
                        } `}
                    >
                        Simpan
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}

export default ProductCategory;
