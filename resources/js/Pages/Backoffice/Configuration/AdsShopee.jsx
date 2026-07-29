import DataTable from '@/Components/DataTable';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, Link, router } from '@inertiajs/react';
import { Pause, Play } from 'lucide-react';

function AdsShopee() {
    const { ads, flash } = usePage().props;

    const statusConfig = {
        ongoing: { label: 'Ongoing', icon: <Pause size={15} />, nextAction: 'pause', color: 'bg-red-500' },
        paused: { label: 'Paused', icon: <Play size={15} />, nextAction: 'resume', color: 'bg-green-500' }
    };

    const columns = [
        { key: 'name', label: 'Campaign Name' },
        { key: 'type', label: 'Type' },
        { key: 'status', label: 'Status', render: (row) => statusConfig[row.status]?.label || row.status },
        { key: 'bidding_method', label: 'Bidding Method' },
        { key: 'campaign_budget', label: 'Budget' },
        { key: 'start_time', label: 'Start Time' },
        { key: 'end_time', label: 'End Time' },
        { key: 'roas_target', label: 'Roas Target' },
        {
            key: 'edit',
            label: 'Action',
            render: (row) => statusConfig[row.status] && (
                <div className="flex gap-2">
                    <button
                        onClick={() => handleAds(row.marketplace_id, row.campaign_id, row.status)}
                        className={`rounded px-3 py-1 text-white ${statusConfig[row.status].color}`}
                    >
                        {statusConfig[row.status].icon}
                    </button>
                </div>
            )
        }
    ];

    const handleAds = (marketplaceId, campaignId, status) => {
        const action = statusConfig[status]?.nextAction;
        if (!action) return;

        router.post(route('shopee.ads.edit', marketplaceId), {
            campaign_id: campaignId,
            edit_action: action,
            preserveScroll: true
        });
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

            {flash.success && (
                <div className="mb-4 mt-4 rounded bg-green-300 p-4 text-green-700">
                    {flash.success}
                </div>
            )}

            {flash.error && (
                <div className="mb-4 mt-4 rounded bg-red-300 p-4 text-red-700">
                    {flash.error}
                </div>
            )}

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
