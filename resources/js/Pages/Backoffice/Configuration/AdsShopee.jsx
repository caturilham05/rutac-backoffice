import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';

function AdsShopee() {
    const { ads } = usePage().props;

    const columns = [
        { key: 'name', label: 'Campaign Name' },
        { key: 'type', label: 'Type' },
        { key: 'status', label: 'Status' },
        { key: 'bidding_method', label: 'Bidding Method' },
        { key: 'campaign_budget', label: 'Budget' },
        { key: 'start_time', label: 'Start Time' },
        { key: 'end_time', label: 'End Time' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Shopee Ads Configuration
                </h2>
            }
        >
            <Head title="Shopee Ads" />

            <div className="p-6">
                <DataTable
                    columns={columns}
                    data={ads.data}
                    pagination={ads}
                    baseUrl={route('shopee.ads.index')}
                />
            </div>
        </AuthenticatedLayout>
    );
}

export default AdsShopee;
