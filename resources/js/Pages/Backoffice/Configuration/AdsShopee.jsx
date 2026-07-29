import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, Link } from '@inertiajs/react';
import { Pause, Play } from 'lucide-react';

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
        { key: 'roas_target', label: 'Roas Target' },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => (
                <div className="flex gap-2">
                    <button
                        onClick={() => handleAds(row.id)}
                        className={`rounded px-3 py-1 text-white ${row.status === 'ongoing' ? 'bg-red-500' : 'bg-green-500'}`}
                    >
                        {statusIcon[row.status] ?? null}
                    </button>
                </div>
            )
        }
    ];

    const statusIcon = {
        ongoing: <Pause size = {15} />,
        paused : <Play size = {15} />
    }

    const handleAds = () => {
        console.log('first')
    }

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
