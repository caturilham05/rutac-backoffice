import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Home } from 'lucide-react';

const errors = {
    403: {
        title: 'Akses ditolak',
        description: 'Anda tidak memiliki izin untuk membuka halaman ini.',
    },
    404: {
        title: 'Halaman tidak ditemukan',
        description: 'Halaman yang Anda cari tidak tersedia atau telah dipindahkan.',
    },
    500: {
        title: 'Terjadi kesalahan',
        description: 'Server mengalami kendala. Silakan coba kembali beberapa saat lagi.',
    },
    503: {
        title: 'Layanan sementara tidak tersedia',
        description: 'Kami sedang melakukan pemeliharaan. Silakan kembali sebentar lagi.',
    },
};

export default function ErrorPage({ status }) {
    const error = errors[status];

    return (
        <>
            <Head title={`${status} - ${error.title}`} />

            <main className="relative flex min-h-screen items-center justify-center overflow-hidden bg-stone-50 px-6 py-16 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
                <div className="absolute -left-24 top-1/4 h-72 w-72 rounded-full bg-amber-200/40 blur-3xl dark:bg-amber-900/20" />
                <div className="absolute -right-24 bottom-1/4 h-72 w-72 rounded-full bg-rose-200/40 blur-3xl dark:bg-rose-900/20" />

                <section className="relative w-full max-w-xl text-center">
                    <p className="text-8xl font-semibold tracking-tighter text-amber-700/20 sm:text-9xl dark:text-amber-300/20">
                        {status}
                    </p>
                    <div className="-mt-6 rounded-3xl border border-stone-200/80 bg-white/80 p-8 shadow-xl shadow-stone-200/40 backdrop-blur sm:p-12 dark:border-stone-800 dark:bg-stone-900/80 dark:shadow-black/20">
                        <p className="text-sm font-semibold uppercase tracking-[0.3em] text-amber-700 dark:text-amber-400">
                            Rutac Perfume
                        </p>
                        <h1 className="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            {error.title}
                        </h1>
                        <p className="mt-4 leading-7 text-stone-600 dark:text-stone-400">
                            {error.description}
                        </p>
                        <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                            <button
                                type="button"
                                onClick={() => window.history.back()}
                                className="inline-flex items-center justify-center gap-2 rounded-xl border border-stone-300 px-5 py-3 text-sm font-semibold transition hover:bg-stone-100 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 dark:border-stone-700 dark:hover:bg-stone-800 dark:focus:ring-offset-stone-900"
                            >
                                <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                                Kembali
                            </button>
                            <Link
                                href="/"
                                className="inline-flex items-center justify-center gap-2 rounded-xl bg-stone-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-600 focus:ring-offset-2 dark:bg-amber-600 dark:text-stone-950 dark:hover:bg-amber-500 dark:focus:ring-offset-stone-900"
                            >
                                <Home className="h-4 w-4" aria-hidden="true" />
                                Ke Beranda
                            </Link>
                        </div>
                    </div>
                </section>
            </main>
        </>
    );
}
