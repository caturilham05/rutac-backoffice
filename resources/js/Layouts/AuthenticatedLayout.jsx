import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import ThemeSwitcher from '@/Components/ThemeSwitcher';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
    const user = usePage().props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    const isProductsActive =
        route().current('products') || route().current('product_category');
    const isOrdersActive = route().current('order');
    const isPurchaseActive = route().current('purchases.list');
    const isConfigurationActive = route().current('ShopeeFee');

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav className="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href={route('dashboard')}>
                                    <ApplicationLogo className="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    href={route('dashboard')}
                                    active={route().current('dashboard')}
                                >
                                    Dashboard
                                </NavLink>

                                <div className="flex items-center">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button
                                                type="button"
                                                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700"
                                            >
                                                Products
                                                <svg
                                                    className="ms-1 h-4 w-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            </button>
                                        </Dropdown.Trigger>

                                        <Dropdown.Content align="left">
                                            <Dropdown.Link
                                                href={route('products')}
                                            >
                                                Product List
                                            </Dropdown.Link>
                                            <Dropdown.Link
                                                href={route('product_category')}
                                            >
                                                Product Category
                                            </Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>

                                <div className="flex items-center">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button
                                                type="button"
                                                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700"
                                            >
                                                Orders
                                                <svg
                                                    className="ms-1 h-4 w-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            </button>
                                        </Dropdown.Trigger>

                                        <Dropdown.Content align="left">
                                            <Dropdown.Link
                                                href={route('order')}
                                            >
                                                Order List
                                            </Dropdown.Link>
                                            <Dropdown.Link
                                                href={route('order')}
                                            >
                                                Order Produts
                                            </Dropdown.Link>
                                            <Dropdown.Link
                                                href={route('order')}
                                            >
                                                Order Return
                                            </Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>

                                <div className="flex items-center">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button
                                                type="button"
                                                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700"
                                            >
                                                Purchase
                                                <svg
                                                    className="ms-1 h-4 w-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            </button>
                                        </Dropdown.Trigger>

                                        <Dropdown.Content align="left">
                                            <Dropdown.Link
                                                href={route('purchases.list')}
                                            >
                                                Purchase List
                                            </Dropdown.Link>
                                            <Dropdown.Link
                                                href={route('order')}
                                            >
                                                Purchase Products
                                            </Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>

                                <NavLink
                                    href={route('marketplace')}
                                    active={route().current('marketplace')}
                                >
                                    Marketplace
                                </NavLink>

                                <div className="flex items-center">
                                    <Dropdown>
                                        <Dropdown.Trigger>
                                            <button
                                                type="button"
                                                className="inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-gray-500 hover:text-gray-700"
                                            >
                                                Configuration
                                                <svg
                                                    className="ms-1 h-4 w-4"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                                </svg>
                                            </button>
                                        </Dropdown.Trigger>

                                        <Dropdown.Content align="left">
                                            <Dropdown.Link
                                                href={route('ShopeeFee')}
                                            >
                                                Configuration Marketplace
                                            </Dropdown.Link>
                                        </Dropdown.Content>
                                    </Dropdown>
                                </div>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <ThemeSwitcher />
                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {user.name}

                                                <svg
                                                    className="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link
                                            href={route('profile.edit')}
                                        >
                                            Profile
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <ThemeSwitcher />
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' sm:hidden'
                    }
                >
                    <div className="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                        >
                            Dashboard
                        </ResponsiveNavLink>

                        <details className="group" open={isProductsActive}>
                            <summary
                                className={`flex cursor-pointer items-center justify-between border-l-4 py-2 pe-4 ps-3 text-base font-medium transition duration-150 ease-in-out focus:border-gray-300 focus:bg-gray-50 focus:outline-none dark:focus:border-gray-600 dark:focus:bg-gray-700 ${
                                    isProductsActive
                                        ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300'
                                        : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200'
                                }`}
                            >
                                Products
                                <svg
                                    className="h-4 w-4 transition group-open:rotate-180"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </summary>
                            <div className="space-y-1 ps-4">
                                <ResponsiveNavLink
                                    href={route('products')}
                                    active={route().current('products')}
                                >
                                    Product List
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('product_category')}
                                    active={route().current('product_category')}
                                >
                                    Product Category
                                </ResponsiveNavLink>
                            </div>
                        </details>

                        <details className="group" open={isOrdersActive}>
                            <summary
                                className={`flex cursor-pointer items-center justify-between border-l-4 py-2 pe-4 ps-3 text-base font-medium transition duration-150 ease-in-out focus:border-gray-300 focus:bg-gray-50 focus:outline-none dark:focus:border-gray-600 dark:focus:bg-gray-700 ${
                                    isOrdersActive
                                        ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300'
                                        : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200'
                                }`}
                            >
                                Orders
                                <svg
                                    className="h-4 w-4 transition group-open:rotate-180"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </summary>
                            <div className="space-y-1 ps-4">
                                <ResponsiveNavLink
                                    href={route('order')}
                                    active={route().current('order')}
                                >
                                    Order List
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('order')}
                                    active={route().current('order')}
                                >
                                    Order Produts
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('order')}
                                    active={route().current('order')}
                                >
                                    Order Return
                                </ResponsiveNavLink>
                            </div>
                        </details>

                        <details className="group" open={isPurchaseActive}>
                            <summary
                                className={`flex cursor-pointer items-center justify-between border-l-4 py-2 pe-4 ps-3 text-base font-medium transition duration-150 ease-in-out focus:border-gray-300 focus:bg-gray-50 focus:outline-none dark:focus:border-gray-600 dark:focus:bg-gray-700 ${
                                    isPurchaseActive
                                        ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300'
                                        : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200'
                                }`}
                            >
                                Purchase
                                <svg
                                    className="h-4 w-4 transition group-open:rotate-180"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </summary>
                            <div className="space-y-1 ps-4">
                                <ResponsiveNavLink
                                    href={route('purchases.list')}
                                    active={route().current('purchases.list')}
                                >
                                    Purchase List
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    href={route('order')}
                                    active={route().current('order')}
                                >
                                    Purchase Products
                                </ResponsiveNavLink>
                            </div>
                        </details>

                        <ResponsiveNavLink
                            href={route('marketplace')}
                            active={route().current('marketplace')}
                        >
                            Marketplace
                        </ResponsiveNavLink>

                        <details
                            className="group"
                            open={isConfigurationActive}
                        >
                            <summary
                                className={`flex cursor-pointer items-center justify-between border-l-4 py-2 pe-4 ps-3 text-base font-medium transition duration-150 ease-in-out focus:border-gray-300 focus:bg-gray-50 focus:outline-none dark:focus:border-gray-600 dark:focus:bg-gray-700 ${
                                    isConfigurationActive
                                        ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300'
                                        : 'border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200'
                                }`}
                            >
                                Configuration
                                <svg
                                    className="h-4 w-4 transition group-open:rotate-180"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </summary>
                            <div className="space-y-1 ps-4">
                                <ResponsiveNavLink
                                    href={route('ShopeeFee')}
                                    active={route().current('ShopeeFee')}
                                >
                                    Configuration Marketplace
                                </ResponsiveNavLink>
                            </div>
                        </details>
                    </div>

                    <div className="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
                        <div className="px-4">
                            <div className="text-base font-medium text-gray-800 dark:text-gray-200">
                                {user.name}
                            </div>
                            <div className="text-sm font-medium text-gray-500">
                                {user.email}
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow dark:bg-gray-800">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
