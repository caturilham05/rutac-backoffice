import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import FlashMessage from '@/Components/FlashMessage';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

const exampleCurl = `curl 'https://seller.shopee.co.id/api/valueadded/v1/search_order_list/?SPC_CDS=...&SPC_CDS_VER=2&page_size=24&page_number=1&biz_id=1&status=completed' \\
  --compressed \\
  -H 'User-Agent: Mozilla/5.0' \\
  -H 'Accept: application/json' \\
  -H 'Cookie: tempel-cookie-sesi-shopee-di-sini'`;

export default function Sync() {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({ curl: exampleCurl });

    function submit(event) {
        event.preventDefault();
        post(route('ads-transactions.sync.store'));
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Sync Transaction Ads</h2>}>
            <Head title="Sync Transaction Ads" />
            <div className="py-8 sm:py-12">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800 sm:p-8">
                        <Link href={route('ads-transactions.index')} className="text-sm text-blue-600 hover:underline dark:text-blue-400">← Kembali</Link>
                        <FlashMessage message={flash.success} role="status" />
                        <FlashMessage message={flash.error} type="error" role="alert" />
                        <p className="mt-5 text-sm text-gray-600 dark:text-gray-300">Tempel perintah cURL dari halaman Seller Centre Shopee. Cookie sesi hanya dipakai untuk permintaan ini.</p>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">Pastikan untuk memproses data iklan harus dalam 1 browser yang sama.</p>
                        <form onSubmit={submit} className="mt-5 space-y-4">
                            <div>
                                <label htmlFor="curl" className="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">cURL transaksi</label>
                                <textarea id="curl" rows="14" value={data.curl} onChange={(event) => setData('curl', event.target.value)} className="w-full rounded-md border-gray-300 font-mono text-xs text-gray-900 shadow-sm focus:border-orange-500 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100" required />
                                <InputError message={errors.curl} className="mt-2" />
                            </div>
                            <PrimaryButton disabled={processing}>{processing ? 'Menyinkronkan...' : 'Mulai Sync'}</PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
