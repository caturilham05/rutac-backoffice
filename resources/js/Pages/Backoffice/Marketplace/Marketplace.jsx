import DataTable from '@/Components/DataTable';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { SquarePen, Trash, KeyRound } from 'lucide-react';
import { useMemo } from 'react';

function Marketplace() {
    const { marketplaces, flash, filters, sort, direction, options } =
        usePage().props;

    const filterConfig = useMemo(
        () => [
            {
                key: 'marketplace',
                label: 'Marketplace',
                type: 'autocomplete',
                options: options?.marketplace || [],
                placeholder: 'Search marketplace ...',
            },
            {
                key: 'store',
                label: 'Store',
                type: 'autocomplete',
                options: options?.store || [],
                placeholder: 'Search store ...',
            },
        ],
        [options],
    );

    const sortableColumns = ['marketplace', 'store'];

    const columns = [
        {
            key: 'marketplace_id',
            label: 'Marketplace Id Origin',
            render: (row) => `${row.marketplace_id ?? 0}`,
        },
        {
            key: 'shop_id',
            label: 'Shop Id',
            render: (row) => `${row.shop_id ?? 0}`,
        },
        {
            key: 'marketplace',
            label: 'Marketplace',
            render: (row) => `${row.marketplace ?? ''}`,
        },
        {
            key: 'store',
            label: 'Store',
            render: (row) => `${row.store ?? ''}`,
        },
        {
            key: 'access_token',
            label: 'Access Token',
            render: (row) => `${row.access_token ?? ''}`,
        },
        {
            key: 'refresh_token',
            label: 'Refresh Token',
            render: (row) => `${row.refresh_token ?? ''}`,
        },
        {
            key: 'chiper',
            label: 'Chiper',
            render: (row) => `${row.chiper ?? ''}`,
        },
        {
            key: 'refresh_token_expires_at',
            label: 'Refresh Token Expired At',
            render: (row) => `${row.refresh_token_expires_at ?? ''}`,
        },
        {
            key: 'app_key',
            label: 'App Key',
            render: (row) => `${row.app_key ?? ''}`,
        },
        {
            key: 'app_secret',
            label: 'App Secret',
            render: (row) => `${row.app_secret ?? ''}`,
        },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <Link
                        href={route('shopee.auth',  {id: row.id})}
                        className="rounded bg-green-500 px-3 py-1 text-white"
                    >
                        <KeyRound size={15} />
                    </Link>
                    <Link
                        href={route('marketplace.edit', row.id)}
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
        marketplace: '',
        store: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('marketplace.store'), {
            preserveScroll: false,
            onSuccess: () => reset(),
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

        router.delete(route('marketplace.delete', id), {
            preserveScroll: false,
        });
    };

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
                <div className="mb-4 mt-4 rounded bg-green-300 p-4 text-green-700">
                    {flash.success}
                </div>
            )}

            {flash.error && (
                <div className="mb-4 mt-4 rounded bg-red-300 p-4 text-red-700">
                    {flash.error}
                </div>
            )}

            <div className="py-4">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-4">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <DataTable
                            columns={columns}
                            data={marketplaces.data}
                            pagination={marketplaces}
                            filterValues={filters || {}}
                            sortColumn={sort || null}
                            sortDirection={direction || 'asc'}
                            filterConfig={filterConfig}
                            sortableColumns={sortableColumns}
                            baseUrl={route('marketplace')}
                        />
                    </div>
                </div>
            </div>

            <div className="px-4 py-2">
                <form onSubmit={submit} className="space-y-4">
                    <InputLabel
                        htmlFor="marketplace"
                        value="Marketplace Name"
                    />
                    <select
                        className="mt-1 block w-full rounded-md border-gray-300"
                        value={data.marketplace}
                        onChange={(e) => setData('marketplace', e.target.value)}
                    >
                        <option value="">Pilih Marketplace</option>
                        <option value="Shopee">Shopee</option>
                        <option value="Tiktok">TikTok</option>
                    </select>
                    <InputError message={errors.marketplace} />

                    <InputLabel htmlFor="store" value="Store Name" />
                    <input
                        type="text"
                        className="mt-1 block w-full rounded-md border-gray-300"
                        value={data.store}
                        onChange={(e) => setData('store', e.target.value)}
                    />
                    <InputError message={errors.store} />

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

export default Marketplace;
