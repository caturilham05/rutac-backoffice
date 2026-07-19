import DataTable from '@/Components/DataTable';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { SquarePen, Trash, KeyRound, Eye, EyeOff } from 'lucide-react';
import { useMemo, useState } from 'react';

function Marketplace() {
    const { marketplaces, flash, filters, sort, direction, options } =
        usePage().props;

    const [confirmingAction, setConfirmingAction] = useState(null);   // 'edit' or 'delete'
    const [password, setPassword]                 = useState('');
    const [showPassword, setShowPassword]         = useState(false);
    const [selectedId, setSelectedId]             = useState(null);
    const [showAllData, setShowAllData]           = useState({});

    const closeModal = () => {
        setConfirmingAction(null);
        setPassword('');
        setSelectedId(null);
    };

    const handleConfirmAction = (e) => {
        e.preventDefault();
        if (confirmingAction === 'edit') {
            router.post(
                route('marketplace.edit', selectedId),
                { password },
                {
                    preserveScroll: false,
                    onFinish: () => closeModal(),
                },
            );
        } else if (confirmingAction === 'delete') {
            router.delete(route('marketplace.delete', selectedId), {
                data: { password },
                preserveScroll: false,
                onFinish: () => closeModal(),
            });
        }
    };

    const filterConfig = useMemo(
        () => [
            {
                key        : 'marketplace',
                label      : 'Marketplace',
                type       : 'autocomplete',
                options    : options?.marketplace || [],
                placeholder: 'Search marketplace ...',
            },
            {
                key        : 'store',
                label      : 'Store',
                type       : 'autocomplete',
                options    : options?.store || [],
                placeholder: 'Search store ...',
            },
        ],
        [options],
    );

    const toggleShow = (id) => {
        setShowAllData(prev => ({
            ...prev,
            [id]: !prev[id],
        }));
    };

    const displayValue = (id, value, limit = 10) => {
        if (!showAllData[id]) {
            return '*****';
        }

        if (value === null) {
            return '-';
        }

        return String(value) && value.length > limit ? `${value.slice(0, limit)}...` : value;
    }

    const sortableColumns = ['marketplace', 'store'];

    const columns = [
        {
            key   : 'marketplace_id',
            label : 'Marketplace Id Origin',
            // render: (row) => {showAllData[row.id] ? `${row.marketplace_id}` : '*********'},
            render: (row) => displayValue(row.id, row.marketplace_id),
        },
        {
            key   : 'shop_id',
            label : 'Shop Id',
            render: (row) => displayValue(row.id, row.shop_id),
        },
        {
            key   : 'marketplace',
            label : 'Marketplace',
            render: (row) => `${row.marketplace ?? ''}`,
        },
        {
            key   : 'store',
            label : 'Store',
            render: (row) => `${row.store ?? ''}`,
        },
        {
            key   : 'access_token',
            label : 'Access Token',
            render: (row) => displayValue(row.id, row.access_token),
        },
        {
            key   : 'refresh_token',
            label : 'Refresh Token',
            render: (row) => displayValue(row.id, row.refresh_token),
        },
        {
            key   : 'chiper',
            label : 'Chiper',
            render: (row) => `${row.chiper ?? ''}`,
        },
        {
            key   : 'refresh_token_expires_at',
            label : 'Refresh Token Expired At',
            render: (row) => `${row.refresh_token_expires_at ?? ''}`,
        },
        {
            key   : 'app_key',
            label : 'App Key',
            render: (row) => displayValue(row.id, row.app_key),
        },
        {
            key   : 'app_secret',
            label : 'App Secret',
            render: (row) => `${row.app_secret ?? ''}`,
        },
        {
            key   : 'edit',
            label : 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <div onClick={() => toggleShow(row.id)} className="rounded bg-pink-500 px-3 py-1 text-white" style={{cursor: 'pointer'}}>
                        {showAllData[row.id] ? <EyeOff size={15} /> : <Eye size={15} />}
                    </div>
                    <Link
                        href={route('shopee.auth',  {id: row.id})}
                        className="rounded bg-green-500 px-3 py-1 text-white"
                    >
                        <KeyRound size={15} />
                    </Link>
                    <button
                        onClick={() => {
                            setSelectedId(row.id);
                            setConfirmingAction('edit');
                        }}
                        className="rounded bg-yellow-500 px-3 py-1 text-white"
                    >
                        <SquarePen size={15} />
                    </button>
                    <button
                        onClick={() => {
                            setSelectedId(row.id);
                            setConfirmingAction('delete');
                        }}
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

            <Modal show={!!confirmingAction} onClose={closeModal}>
                <form onSubmit={handleConfirmAction} className="p-6">
                    <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Konfirmasi Password
                    </h2>

                    <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Silahkan masukkan password anda untuk {confirmingAction} data
                        marketplace ini.
                    </p>

                    <div className="mt-6">
                        <InputLabel
                            htmlFor="password"
                            value="Password"
                            className="sr-only"
                        />

                        <div className="relative mt-1 block w-3/4">
                            <TextInput
                                id="password"
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                value={password}
                                onChange={(e) => setPassword(e.target.value)}
                                className="block w-full"
                                isFocused
                                placeholder="Password"
                                required
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                            </button>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end">
                        <SecondaryButton onClick={closeModal}>
                            Batal
                        </SecondaryButton>

                        <button
                            type="submit"
                            className="ms-3 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                        >
                            Konfirmasi
                        </button>
                    </div>
                </form>
            </Modal>

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
