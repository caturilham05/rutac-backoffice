import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Daftar Akun" />

            <div className="mb-6">
                <p className="text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                    SMALL ERP
                </p>
                <h1 className="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Daftar Akun
                </h1>
                <p className="mt-1.5 text-sm text-gray-600 dark:text-gray-400">
                    Mulai kelola operasional bisnis dan iklan Anda dengan Small ERP.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="name" value="Nama Lengkap" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
                        autoComplete="name"
                        isFocused={true}
                        placeholder="Nama Anda"
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
                        autoComplete="username"
                        placeholder="nama@usaha.com"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />

                    <p className="mt-1 text-xs text-gray-500">
                        Gunakan huruf kecil untuk alamat email.
                    </p>

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
                        autoComplete="new-password"
                        placeholder="Minimal 8 karakter"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Konfirmasi Kata sandi"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-gray-100 dark:focus:ring-gray-100"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="pt-2">
                    <PrimaryButton
                        className="w-full justify-center rounded-full bg-gray-900 py-3 text-sm font-semibold text-white hover:bg-gray-800 focus:bg-gray-800 active:bg-gray-900 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white dark:active:bg-gray-200"
                        disabled={processing}
                    >
                        {processing ? 'Membuat akun…' : 'Daftar Akun'}
                    </PrimaryButton>
                </div>

                <div className="mt-6 space-y-3 text-center text-sm text-gray-600 dark:text-gray-400">
                    <div>
                        Sudah memiliki akun?{' '}
                        <Link
                            href={route('login')}
                            className="font-medium text-gray-900 underline hover:text-gray-700 dark:text-gray-200 dark:hover:text-white"
                        >
                            Masuk ke ERP
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
                </div>
            </form>
        </GuestLayout>
    );
}
