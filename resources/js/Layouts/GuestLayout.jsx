import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-[#faf8f0] px-4 py-8 sm:justify-center dark:bg-gray-900">
            <div>
                <Link href="/" className="flex flex-col items-center gap-2">
                    <ApplicationLogo className="h-16 w-16 fill-current text-gray-800 dark:text-gray-200" />
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-8 shadow-sm rounded-[24px] sm:max-w-[480px] border border-gray-200/80 dark:bg-gray-800 dark:border-gray-700">
                {children}
            </div>
        </div>
    );
}
