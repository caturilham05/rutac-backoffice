import { Head, Link } from '@inertiajs/react';
import {
    motion,
    useReducedMotion,
    useScroll,
    useSpring,
    useTransform,
} from 'motion/react';
import {
    ArrowDown,
    ArrowUpRight,
    BarChart3,
    Boxes,
    Calculator,
    Megaphone,
    Package,
    ReceiptText,
    ShoppingBag,
    Store,
    Tags,
} from 'lucide-react';

const features = [
    {
        title: 'Produk & SKU',
        description:
            'Kelola kategori, produk, varian, SKU, harga, dan stok yang tercatat.',
        route: 'products',
        icon: Package,
    },
    {
        title: 'Order / Pesanan Shopee',
        description:
            'Telusuri pesanan, status, pembeli, dan detail item hasil sinkronisasi.',
        route: 'order',
        icon: ShoppingBag,
    },
    {
        title: 'Purchase / Pembelian',
        description:
            'Catat invoice, item pembelian, diskon, dan biaya tambahan dari vendor.',
        route: 'purchases.list',
        icon: ReceiptText,
    },
    {
        title: 'Promosi Shopee',
        description: 'Pantau program diskon dan perbarui harga promosi item.',
        route: 'product_discounts',
        icon: Tags,
    },
    {
        title: 'Ads / Iklan Shopee',
        description:
            'Kelola kampanye dan tinjau performa iklan harian yang telah disinkronkan.',
        route: 'shopee.ads.index',
        icon: Megaphone,
    },
    {
        title: 'Kalkulator Profit',
        description:
            'Simulasikan biaya, margin, ROAS impas, dan estimasi laba satu SKU per order.',
        route: 'shopee.calculator.index',
        icon: Calculator,
    },
];

const steps = [
    [
        'Siapkan data usaha',
        'Kelola toko marketplace, kategori, produk/SKU, dan konfigurasi biaya.',
    ],
    [
        'Catat dan sinkronkan',
        'Catat pembelian vendor serta sinkronkan pesanan dan data Shopee melalui modul terkait.',
    ],
    [
        'Kelola promosi dan iklan',
        'Sesuaikan harga promosi, pengaturan kampanye, serta tinjau performa harian.',
    ],
    [
        'Evaluasi hasil',
        'Buka dashboard untuk ringkasan operasional dan kalkulator untuk estimasi profit.',
    ],
];

const primaryStyle =
    'inline-flex items-center justify-center gap-2 rounded-full bg-[#222222] px-6 py-3 text-sm font-semibold text-white transition hover:bg-stone-700 dark:bg-amber-300 dark:text-stone-950 dark:hover:bg-amber-200';

export default function Home({ auth, canLogin = false, canRegister = false }) {
    const reduceMotion = useReducedMotion();
    const sectionReveal = {
        initial: { opacity: 0, y: 48 },
        whileInView: { opacity: 1, y: 0 },
        viewport: { once: false, amount: 0.15, margin: '0px 0px -80px 0px' },
        transition: { duration: 0.8, ease: 'easeOut' },
    };
    const { scrollY } = useScroll();
    const illustrationOffset = useTransform(scrollY, [0, 600], [0, 100]);
    const illustrationY = useSpring(illustrationOffset, { stiffness: 100, damping: 25 });
    const storeY = useTransform(scrollY, [0, 360], [0, -12]);
    const workspaceY = useTransform(scrollY, [0, 360], [0, 24]);
    const isAuthenticated = Boolean(auth?.user);
    const primaryRoute = isAuthenticated
        ? 'dashboard'
        : canLogin
          ? 'login'
          : canRegister
            ? 'register'
            : null;
    const primaryLabel = isAuthenticated
        ? 'Buka Dashboard'
        : canLogin
          ? 'Masuk ke ERP'
          : canRegister
            ? 'Daftar Akun'
            : 'Lihat fitur';
    const primaryAction = primaryRoute ? (
        <Link href={route(primaryRoute)} className={primaryStyle}>
            {primaryLabel}
            <ArrowUpRight aria-hidden="true" className="h-4 w-4" />
        </Link>
    ) : (
        <a href="#fitur" className={primaryStyle}>
            {primaryLabel}
            <ArrowDown aria-hidden="true" className="h-4 w-4" />
        </a>
    );

    return (
        <div
            id="home-page"
            lang="id"
            className="min-h-screen bg-[#faf8f0] font-sans text-[#222222] dark:bg-stone-950 dark:text-stone-100 [&_a]:focus-visible:outline [&_a]:focus-visible:outline-2 [&_a]:focus-visible:outline-offset-4 [&_a]:focus-visible:outline-amber-700 dark:[&_a]:focus-visible:outline-amber-300"
        >
            <Head title="Rutac Small ERP" />
            <style>{`
                @media (prefers-reduced-motion: no-preference) {
                    html:has(#home-page) {
                        scroll-behavior: smooth;
                    }
                }
                @media (max-width: 1023px), (prefers-reduced-motion: reduce) {
                    #home-page [data-parallax-layer] {
                        transform: none !important;
                    }
                }
                @media (prefers-reduced-motion: reduce) {
                    #home-page [data-section-reveal] {
                        opacity: 1 !important;
                        transform: none !important;
                    }
                }
                #home-page [data-section-reveal]:focus-within {
                    opacity: 1 !important;
                    transform: none !important;
                }
            `}</style>
            <a
                href="#konten"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-10 focus:rounded-full focus:bg-white focus:p-4 focus:text-black"
            >
                Lewati ke konten
            </a>
            <header className="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-6 px-6 py-6">
                <Link href="/" className="flex items-center gap-3">
                    <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f7d046] text-stone-950">
                        <Boxes aria-hidden="true" className="h-6 w-6" />
                    </span>
                    <span className="flex flex-col">
                        <span className="text-lg font-bold tracking-tight">
                            Rutac Small ERP
                        </span>
                        <span className="text-xs tracking-widest text-stone-600 dark:text-stone-400">
                            SMALL ERP
                        </span>
                    </span>
                </Link>
                <nav
                    aria-label="Navigasi utama"
                    className="flex flex-wrap items-center gap-x-6 gap-y-4 text-sm font-medium"
                >
                    <a href="#fitur" className="hover:underline">
                        Fitur
                    </a>
                    <a href="#alur-kerja" className="hover:underline">
                        Alur Kerja
                    </a>
                    {!isAuthenticated && canLogin && canRegister && (
                        <Link
                            href={route('register')}
                            className="hover:underline"
                        >
                            Daftar Akun
                        </Link>
                    )}
                    {primaryAction}
                </nav>
            </header>

            <main id="konten" className="mx-auto max-w-[1200px] px-6">
                <motion.section
                    {...(reduceMotion ? {} : sectionReveal)}
                    data-section-reveal
                    aria-labelledby="hero-title"
                    className="grid items-center gap-12 py-12 lg:grid-cols-[1.15fr_1fr] lg:gap-16 lg:py-20"
                >
                    <div>
                        <p className="mb-6 text-xs font-bold tracking-[0.16em] text-stone-600 dark:text-stone-400">
                            SMALL ERP · INTEGRASI SHOPEE SELLER
                        </p>
                        <h1
                            id="hero-title"
                            className="text-[2.5rem] font-semibold leading-[1.08] tracking-tight text-black dark:text-white sm:text-5xl lg:text-[4rem]"
                        >
                            Kelola order, purchase, dan ads{' '}
                            <span className="relative isolate whitespace-nowrap">
                                <span
                                    aria-hidden="true"
                                    className="absolute inset-x-0 bottom-1 -z-10 h-3 bg-[#f7d046] dark:bg-amber-700"
                                />
                                dalam satu
                            </span>{' '}
                            tempat.
                        </h1>
                        <p className="mt-6 max-w-xl text-lg leading-8 text-stone-600 dark:text-stone-300">
                            Small ERP untuk mengelola produk dan pembelian,
                            memantau pesanan, serta mengevaluasi iklan dan
                            promosi melalui integrasi Shopee Seller.
                        </p>
                        <div className="mt-8 flex flex-wrap items-center gap-4">
                            {primaryAction}
                            {primaryRoute && (
                                <a
                                    href="#fitur"
                                    className="inline-flex items-center gap-2 rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold hover:bg-white dark:border-stone-700 dark:hover:bg-stone-900"
                                >
                                    Lihat fitur
                                    <ArrowDown
                                        aria-hidden="true"
                                        className="h-4 w-4"
                                    />
                                </a>
                            )}
                        </div>
                        <p className="mt-4 text-sm text-stone-600 dark:text-stone-400">
                            Akses operasional memerlukan akun.
                        </p>
                    </div>
                    <motion.figure
                        data-parallax-layer="illustration"
                        style={{ y: reduceMotion ? 0 : illustrationY }}
                        className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm dark:border-stone-800 dark:bg-stone-900"
                    >
                        <figcaption className="mb-6 flex items-center justify-between gap-3 text-xs font-semibold uppercase tracking-widest text-stone-500 dark:text-stone-400">
                            Ilustrasi alur kerja
                            <Boxes aria-hidden="true" className="h-5 w-5" />
                        </figcaption>
                        <motion.div
                            data-parallax-layer="store"
                            style={{
                                y: reduceMotion ? 0 : storeY,
                            }}
                            className="flex items-center gap-4 rounded-2xl bg-[#faf8f0] p-5 dark:bg-stone-800"
                        >
                            <Store
                                aria-hidden="true"
                                className="h-8 w-8 shrink-0 text-[#d8573f] dark:text-orange-300"
                            />
                            <div>
                                <p className="font-semibold">Toko Shopee</p>
                                <p className="mt-1 text-sm text-stone-600 dark:text-stone-400">
                                    Sumber data pesanan & iklan
                                </p>
                            </div>
                        </motion.div>
                        <div className="flex items-center justify-center gap-2 py-4 text-xs text-stone-600 dark:text-stone-400">
                            <ArrowDown aria-hidden="true" className="h-4 w-4" />
                            Sinkronisasi melalui modul terkait
                        </div>
                        <motion.div
                            data-parallax-layer="workspace"
                            style={{
                                y: reduceMotion ? 0 : workspaceY,
                            }}
                            className="rounded-2xl border border-stone-200 p-4 dark:border-stone-700"
                        >
                            <p className="mb-4 text-sm font-semibold">
                                Ruang kerja Small ERP
                            </p>
                            <div className="grid grid-cols-2 gap-3">
                                {[
                                    [ShoppingBag, 'Order', 'Dari Shopee'],
                                    [Megaphone, 'Ads', 'Dari Shopee'],
                                    [
                                        ReceiptText,
                                        'Purchase',
                                        'Dicatat internal',
                                    ],
                                    [
                                        Package,
                                        'Produk / SKU',
                                        'Master internal',
                                    ],
                                ].map(([Icon, title, source]) => (
                                    <div
                                        key={title}
                                        className="rounded-xl bg-stone-50 p-3 dark:bg-stone-800"
                                    >
                                        <Icon
                                            aria-hidden="true"
                                            className="mb-3 h-5 w-5 text-stone-600 dark:text-stone-300"
                                        />
                                        <p className="text-sm font-semibold">
                                            {title}
                                        </p>
                                        <p className="mt-1 text-xs text-stone-600 dark:text-stone-400">
                                            {source}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        </motion.div>
                        <div className="mt-10 flex items-center gap-3 rounded-2xl bg-[#f7d046]/20 p-4 text-sm">
                            <BarChart3
                                aria-hidden="true"
                                className="h-5 w-5 shrink-0"
                            />
                            <span>Ringkasan operasional & simulasi profit</span>
                        </div>
                    </motion.figure>
                </motion.section>

                <motion.section
                    {...(reduceMotion ? {} : sectionReveal)}
                    data-section-reveal
                    id="fitur"
                    aria-labelledby="features-title"
                    className="scroll-mt-6 py-12 lg:py-20"
                >
                    <div className="mb-10 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p className="mb-3 text-xs font-semibold uppercase tracking-widest text-stone-600 dark:text-stone-400">
                                Modul operasional
                            </p>
                            <h2
                                id="features-title"
                                className="text-3xl font-semibold tracking-tight sm:text-4xl"
                            >
                                Satu ruang untuk pekerjaan harian.
                            </h2>
                        </div>
                        <p className="max-w-sm text-stone-600 dark:text-stone-400">
                            Dari data produk hingga evaluasi usaha, buka modul
                            sesuai kebutuhan Anda.
                        </p>
                    </div>
                    <div className="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        {features.map(
                            (
                                {
                                    title,
                                    description,
                                    route: routeName,
                                    icon: Icon,
                                },
                                index,
                            ) => (
                                <article
                                    key={routeName}
                                    className="flex flex-col items-start rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-stone-800 dark:bg-stone-900"
                                >
                                    <div className="mb-6 flex w-full items-center justify-between">
                                        <span className="rounded-2xl bg-[#faf8f0] p-3 dark:bg-stone-800">
                                            <Icon
                                                aria-hidden="true"
                                                className="h-6 w-6"
                                            />
                                        </span>
                                        <span
                                            aria-hidden="true"
                                            className="text-xs text-stone-500"
                                        >
                                            0{index + 1}
                                        </span>
                                    </div>
                                    <h3 className="text-lg font-semibold">
                                        {title}
                                    </h3>
                                    <p className="mt-3 flex-1 leading-7 text-stone-600 dark:text-stone-400">
                                        {description}
                                    </p>
                                    {isAuthenticated && (
                                        <Link
                                            href={route(routeName)}
                                            className="mt-6 inline-flex items-center gap-2 text-sm font-semibold underline decoration-stone-300 underline-offset-4"
                                        >
                                            Buka {title}
                                            <ArrowUpRight
                                                aria-hidden="true"
                                                className="h-4 w-4"
                                            />
                                        </Link>
                                    )}
                                </article>
                            ),
                        )}
                    </div>
                </motion.section>

                <motion.section
                    {...(reduceMotion ? {} : sectionReveal)}
                    data-section-reveal
                    id="alur-kerja"
                    aria-labelledby="workflow-title"
                    className="scroll-mt-6 border-t border-stone-200 py-12 dark:border-stone-800 lg:py-20"
                >
                    <p className="mb-3 text-xs font-semibold uppercase tracking-widest text-stone-600 dark:text-stone-400">
                        Alur kerja
                    </p>
                    <h2
                        id="workflow-title"
                        className="text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Dari pencatatan ke evaluasi.
                    </h2>
                    <p className="mt-4 text-stone-600 dark:text-stone-400">
                        Panduan aktivitas usaha. Setiap modul dapat digunakan
                        sesuai pekerjaan Anda.
                    </p>
                    <ol className="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                        {steps.map(([title, description], index) => (
                            <li key={title}>
                                <span className="mb-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#f7d046] text-sm font-bold text-stone-950">
                                    0{index + 1}
                                </span>
                                <h3 className="text-lg font-semibold">
                                    {title}
                                </h3>
                                <p className="mt-3 leading-7 text-stone-600 dark:text-stone-400">
                                    {description}
                                </p>
                            </li>
                        ))}
                    </ol>
                </motion.section>

                <motion.section
                    {...(reduceMotion ? {} : sectionReveal)}
                    data-section-reveal
                    aria-labelledby="start-title"
                    className="mb-12 flex flex-col items-center gap-6 rounded-3xl border border-stone-200 bg-white px-6 py-12 text-center dark:border-stone-800 dark:bg-stone-900 lg:mb-20"
                >
                    <span
                        aria-hidden="true"
                        className="rounded-2xl bg-[#f7d046] p-3 text-stone-950"
                    >
                        <Boxes className="h-7 w-7" />
                    </span>
                    <h2
                        id="start-title"
                        className="max-w-2xl text-3xl font-semibold leading-tight tracking-tight sm:text-4xl"
                    >
                        Mulai kelola operasional melalui Rutac Small ERP.
                    </h2>
                    {primaryAction}
                </motion.section>
            </main>
            <footer className="mx-auto flex max-w-[1200px] flex-col justify-between gap-3 border-t border-stone-200 px-6 py-8 text-sm dark:border-stone-800 sm:flex-row">
                <p className="font-semibold">Rutac Small ERP</p>
                <p className="text-stone-600 dark:text-stone-400">
                    Small ERP untuk operasional bisnis terintegrasi Shopee
                    Seller.
                </p>
            </footer>
        </div>
    );
}
