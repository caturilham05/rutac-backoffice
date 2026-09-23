import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { formatCurrency } from '@/Utils/format';
import { DialogTitle } from '@headlessui/react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, ImageOff, Pencil } from 'lucide-react';
import { useState } from 'react';

const statusLabels = {
    ongoing: 'Berjalan',
    paused: 'Dijeda',
    scheduled: 'Terjadwal',
    ended: 'Selesai',
    deleted: 'Dihapus',
    closed: 'Ditutup',
};
const panel =
    'rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 sm:p-6';
const button =
    'rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:hover:bg-gray-700';
const input =
    'w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900';
const cpcLabel = (value) =>
    typeof value === 'boolean'
        ? value
            ? 'Aktif'
            : 'Nonaktif'
        : 'Belum diketahui';

export default function AdsShopeeEdit() {
    const {
        ad,
        marketplace,
        settings,
        settingsError,
        listQuery,
        flash,
    } = usePage().props;
    const [editor, setEditor] = useState(null);
    const [busy, setBusy] = useState(false);
    const [unlimitedBudget, setUnlimitedBudget] = useState(false);
    const form = useForm({ value: '' });
    const current = settings ?? ad;
    const status = current.status;
    const parameters = { marketplace: marketplace.id, ad: ad.id, ...listQuery };
    const editors = {
        budget: {
            title: 'Modal',
            action: 'change_budget',
            value: settings?.campaign_budget,
        },
        roas_target: {
            title: 'ROAS Target',
            action: 'change_roas_target',
            value: settings?.roas_target,
        },
        enhanced_cpc: {
            title: 'Enhanced CPC',
            action: 'change_enhanced_cpc',
            value: settings?.enhanced_cpc,
        },
    };
    const activeEditor = editors[editor];
    const numericValue = Number(form.data.value);
    const isUnlimitedBudget = editor === 'budget' && unlimitedBudget;
    const chosenValue = isUnlimitedBudget
        ? 0
        : editor === 'enhanced_cpc'
          ? form.data.value === 'true'
          : numericValue;
    const unchanged =
        activeEditor?.value != null && chosenValue === activeEditor.value;
    const invalid =
        !isUnlimitedBudget &&
        (form.data.value === '' ||
            (editor !== 'enhanced_cpc' &&
                (!Number.isFinite(numericValue) || numericValue <= 0)));
    const processing = busy || form.processing;

    const openEditor = (field) => {
        form.clearErrors();
        setUnlimitedBudget(field === 'budget' && editors[field].value === 0);
        form.setData(
            'value',
            editors[field].value == null ||
                (field === 'budget' && editors[field].value === 0)
                ? ''
                : String(editors[field].value),
        );
        setEditor(field);
    };
    const reload = () => {
        router.get(
            route('shopee.ads.settings.edit', parameters),
            {},
            {
                preserveScroll: true,
                onStart: () => setBusy(true),
                onFinish: () => setBusy(false),
            },
        );
    };
    const submit = (event) => {
        event.preventDefault();
        form.transform(() => ({
            edit_action: activeEditor.action,
            [editor]: chosenValue,
        }));
        form.patch(route('shopee.ads.settings.update', parameters), {
            preserveScroll: true,
            onSuccess: () => setEditor(null),
        });
    };
    const changeStatus = () =>
        router.post(
            route('shopee.ads.edit', marketplace.id),
            {
                ...listQuery,
                campaign_id: ad.campaign_id,
                edit_action: status === 'ongoing' ? 'pause' : 'resume',
                return_to_detail: true,
            },
            {
                preserveScroll: true,
                onStart: () => setBusy(true),
                onFinish: () => setBusy(false),
            },
        );
    const editButton = (field, disabled = false) => (
        <button
            type="button"
            aria-label={`Edit ${editors[field].title}`}
            onClick={() => openEditor(field)}
            disabled={!settings || disabled || processing}
            className="rounded p-2 text-orange-600 hover:bg-orange-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-500 disabled:cursor-not-allowed disabled:opacity-40 dark:text-orange-400 dark:hover:bg-gray-700"
        >
            <Pencil size={16} aria-hidden="true" />
        </button>
    );

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold text-gray-800 dark:text-gray-200">
                    Detail Iklan Shopee
                </h2>
            }
        >
            <Head title={`${ad.name ?? 'Iklan'} · Shopee`} />
            <div
                className="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 text-gray-800 dark:text-gray-200 sm:px-6 lg:px-8"
                aria-busy={processing}
            >
                <Link
                    href={route('shopee.ads.index', listQuery)}
                    className="flex w-fit items-center gap-2 text-sm hover:underline"
                >
                    <ArrowLeft size={16} aria-hidden="true" /> Kembali
                </Link>
                <p className="text-sm text-gray-500 dark:text-gray-400">
                    Terakhir tersimpan. Perubahan langsung di Shopee akan terlihat setelah sinkronisasi.
                </p>
                {flash.success && (
                    <p
                        role="status"
                        className="rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-950 dark:text-green-200"
                    >
                        {flash.success}
                    </p>
                )}
                {flash.error && (
                    <p
                        role="alert"
                        className="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-950 dark:text-red-200"
                    >
                        {flash.error}
                    </p>
                )}
                {settingsError && (
                    <div
                        role="alert"
                        className="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-amber-50 p-4 text-amber-900 dark:bg-amber-950 dark:text-amber-200"
                    >
                        <p>{settingsError}</p>
                        <button
                            type="button"
                            className={button}
                            disabled={processing}
                            onClick={reload}
                        >
                            {busy ? 'Memuat...' : 'Coba lagi'}
                        </button>
                    </div>
                )}
                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                    <section
                        className={panel}
                        aria-label="Identitas dan pengaturan iklan"
                    >
                        <div className="flex items-start gap-4">
                            <div
                                className="flex h-20 w-20 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400 dark:bg-gray-700"
                                role="img"
                                aria-label="Thumbnail belum tersedia"
                            >
                                <ImageOff size={28} aria-hidden="true" />
                            </div>
                            <div className="flex min-w-0 flex-col gap-1">
                                <h1 className="break-words text-xl font-semibold">
                                    {ad.name ?? 'Nama iklan belum tersedia'}
                                </h1>
                                <p className="text-sm text-gray-500 dark:text-gray-400">
                                    {marketplace.store} · Campaign ID{' '}
                                    {ad.campaign_id}
                                </p>
                                <p className="text-sm">
                                    Periode:{' '}
                                    {ad.start_time ?? 'Belum diketahui'} -{' '}
                                    {ad.end_time ?? 'Belum diketahui'}
                                </p>
                            </div>
                        </div>
                        <div className="mt-6 grid gap-5 border-t border-gray-200 pt-5 dark:border-gray-700 md:grid-cols-3">
                            <div>
                                <div className="flex items-center justify-between gap-2">
                                    <h2 className="text-sm text-gray-500 dark:text-gray-400">
                                        Modal
                                    </h2>
                                    {editButton('budget')}
                                </div>
                                <p className="text-xl font-semibold">
                                    {current.campaign_budget == null
                                        ? 'Belum diketahui'
                                        : Number(current.campaign_budget) === 0
                                          ? 'Tidak Terbatas'
                                          : formatCurrency(
                                                current.campaign_budget,
                                            )}
                                </p>
                                {!settings && (
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Terakhir tersimpan
                                    </p>
                                )}
                            </div>
                            <div>
                                <div className="flex items-center justify-between gap-2">
                                    <h2 className="text-sm text-gray-500 dark:text-gray-400">
                                        Mode Bidding
                                    </h2>
                                    {editButton(
                                        'roas_target',
                                        settings?.bidding_method !== 'auto',
                                    )}
                                </div>
                                <p className="font-semibold">
                                    {current.bidding_method === 'auto'
                                        ? 'Otomatis'
                                        : current.bidding_method === 'manual'
                                          ? 'Manual'
                                          : current.bidding_method ||
                                            'Belum diketahui'}
                                </p>
                                <p className="text-sm">
                                    ROAS Target: {current.roas_target ?? '—'}
                                </p>
                                {current.bidding_method !== 'auto' && (
                                    <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Editor ROAS Target ditujukan untuk auto
                                        bidding.
                                    </p>
                                )}
                                {!settings && (
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Terakhir tersimpan
                                    </p>
                                )}
                            </div>
                            <div>
                                <div className="flex items-center justify-between gap-2">
                                    <h2 className="text-sm text-gray-500 dark:text-gray-400">
                                        Enhanced CPC
                                    </h2>
                                    {editButton('enhanced_cpc')}
                                </div>
                                <p className="font-semibold">
                                    {cpcLabel(settings?.enhanced_cpc)}
                                </p>
                                {settings?.enhanced_cpc == null && (
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        Terakhir tersimpan:{' '}
                                        {cpcLabel(ad.enhanced_cpc)}
                                    </p>
                                )}
                                <p className="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Optimasi bid per klik. Dukungan bergantung
                                    pada campaign dan keputusan Shopee.
                                </p>
                            </div>
                        </div>
                    </section>
                    <aside
                        className={`${panel} flex flex-col gap-4`}
                        aria-label="Status iklan"
                    >
                        <div>
                            <h2 className="text-sm text-gray-500 dark:text-gray-400">
                                Status Iklan
                            </h2>
                            <p
                                className={`mt-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold ${
                                    status === 'ongoing'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : status === 'paused'
                                          ? 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'
                                          : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'
                                }`}
                            >
                                {statusLabels[status] ??
                                    status ??
                                    'Belum diketahui'}
                            </p>
                            {!settings && (
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Terakhir tersimpan
                                </p>
                            )}
                        </div>
                        {['ongoing', 'paused'].includes(status) && (
                            <button
                                type="button"
                                className={button}
                                onClick={changeStatus}
                                disabled={processing || !settings}
                            >
                                {busy
                                    ? 'Memproses...'
                                    : status === 'ongoing'
                                      ? 'Jeda'
                                      : 'Lanjutkan'}
                            </button>
                        )}
                        <div className="flex gap-2">
                            <button disabled className={button}>
                                Berhenti
                            </button>
                            <button disabled className={button}>
                                Hapus
                            </button>
                        </div>
                        <button disabled className={`${button} text-left`}>
                            Riwayat Pengaturan Iklan
                        </button>
                        <p className="text-xs text-gray-500 dark:text-gray-400">
                            Berhenti, hapus, dan riwayat belum tersedia pada
                            versi ini.
                        </p>
                    </aside>
                </div>
            </div>
            <Modal
                show={editor !== null}
                onClose={() => setEditor(null)}
                closeable={!form.processing}
                maxWidth="md"
            >
                {activeEditor && (
                    <form
                        onSubmit={submit}
                        className="flex flex-col gap-5 p-6 text-gray-800 dark:text-gray-200"
                    >
                        <DialogTitle className="text-lg font-semibold">
                            Edit {activeEditor.title}
                        </DialogTitle>
                        {editor === 'budget' && (
                            <fieldset
                                className="flex flex-col gap-3"
                                disabled={form.processing}
                            >
                                <legend className="sr-only">Jenis modal</legend>
                                <label className="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="budget-mode"
                                        value="unlimited"
                                        checked={unlimitedBudget}
                                        onChange={() =>
                                            setUnlimitedBudget(true)
                                        }
                                        className="text-orange-600 focus:ring-orange-500"
                                    />
                                    Tidak Terbatas
                                </label>
                                <label className="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="budget-mode"
                                        value="limited"
                                        checked={!unlimitedBudget}
                                        onChange={() =>
                                            setUnlimitedBudget(false)
                                        }
                                        className="text-orange-600 focus:ring-orange-500"
                                    />
                                    Atur Modal
                                </label>
                            </fieldset>
                        )}
                        {!isUnlimitedBudget && (
                            <label
                                className="flex flex-col gap-2 text-sm"
                                htmlFor="setting-value"
                            >
                                {activeEditor.title}
                                {editor === 'enhanced_cpc' ? (
                                    <select
                                        autoFocus
                                        id="setting-value"
                                        className={input}
                                        value={form.data.value}
                                        onChange={(event) =>
                                            form.setData(
                                                'value',
                                                event.target.value,
                                            )
                                        }
                                        required
                                        disabled={form.processing}
                                    >
                                        <option value="" disabled>
                                            Pilih status
                                        </option>
                                        <option value="true">Aktif</option>
                                        <option value="false">Nonaktif</option>
                                    </select>
                                ) : (
                                    <input
                                        autoFocus
                                        id="setting-value"
                                        type="number"
                                        step="any"
                                        className={input}
                                        value={form.data.value}
                                        onChange={(event) =>
                                            form.setData(
                                                'value',
                                                event.target.value,
                                            )
                                        }
                                        required
                                        disabled={form.processing}
                                    />
                                )}
                            </label>
                        )}
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            {editor === 'enhanced_cpc'
                                ? 'Optimasi bid per klik. Dukungan bergantung pada campaign dan keputusan Shopee.'
                                : editor === 'roas_target'
                                  ? 'Masukkan rasio ROAS lebih dari nol, bukan persen.'
                                  : isUnlimitedBudget
                                    ? 'Modal tidak dibatasi.'
                                    : 'Masukkan budget rupiah lebih dari nol.'}
                        </p>
                        <div role="alert">
                            {Object.entries(form.errors).map(
                                ([key, message]) => (
                                    <InputError key={key} message={message} />
                                ),
                            )}
                        </div>
                        {(form.errors.uncertain || form.errors.settings) && (
                            <button
                                type="button"
                                className={button}
                                onClick={reload}
                                disabled={processing}
                            >
                                Muat ulang pengaturan
                            </button>
                        )}
                        <div className="flex justify-end gap-3">
                            <button
                                type="button"
                                className={button}
                                onClick={() => setEditor(null)}
                                disabled={form.processing}
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                disabled={
                                    processing ||
                                    !settings ||
                                    invalid ||
                                    unchanged ||
                                    !!form.errors.uncertain
                                }
                                className="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-orange-500 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {form.processing ? 'Menyimpan...' : 'Simpan'}
                            </button>
                        </div>
                    </form>
                )}
            </Modal>
        </AuthenticatedLayout>
    );
}
