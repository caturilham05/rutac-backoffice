import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Masuk ke ERP" />

            <div className="mb-6">
                <p className="text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                    SMALL ERP
                </p>
                <h1 className="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Masuk ke ERP
                </h1>
                <p className="mt-1.5 text-sm text-gray-600 dark:text-gray-400">
                    Lanjutkan pengelolaan produk, pembelian, pesanan, dan iklan dalam satu tempat.
                </p>
            </div>

            {status && (
                <div className="mb-4 rounded-md bg-green-50 p-3 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
                        autoComplete="username"
                        isFocused={true}
                        placeholder="nama@usaha.com"
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Kata sandi" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span className="ms-2 text-sm text-gray-600 dark:text-gray-400">
                            Ingat saya
                        </span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            Lupa kata sandi?
                        </Link>
                    )}
                </div>
                <p className="text-xs text-gray-500 dark:text-gray-500">
                    Gunakan hanya pada perangkat pribadi.
                </p>

                <div>
                    <PrimaryButton
                        className="w-full justify-center rounded-full bg-gray-900 py-3 text-sm font-semibold text-white hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white dark:active:bg-gray-200"
                        disabled={processing}
                    >
                        {processing ? 'Memproses…' : 'Masuk ke ERP'}
                    </PrimaryButton>
                </div>

                <div className="mt-6 space-y-3 text-center text-sm text-gray-600 dark:text-gray-400">
                    <div>
                        Belum memiliki akun?{' '}
                        <Link
                            href={route('register')}
                            className="font-medium text-gray-900 underline hover:text-gray-700 dark:text-gray-200 dark:hover:text-white"
                        >
                            Daftar Akun
                        </Link>
                    </div>
                    <div>
                        <Link
                            href="/"
                            className="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            Kembali ke Beranda
                        </Link>
                    </div>
                    <p className="pt-2 text-xs text-gray-400 dark:text-gray-500">
                        Small ERP untuk operasional bisnis terintegrasi Shopee Seller.
                    </p>
                </div>
            </form>
        </GuestLayout>
    );
}
