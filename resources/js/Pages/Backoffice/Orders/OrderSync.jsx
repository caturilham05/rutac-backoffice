import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, router } from '@inertiajs/react';
import { useState } from 'react';

function OrderSync() {
    const { flash }                         = usePage().props;
    const [loading, setLoading]             = useState(false);
    const [marketplaceId, setMarketplaceId] = useState(1);
    const [timeFrom, setTimeFrom]           = useState('');
    const [timeTo, setTimeTo]               = useState('');
    const [orders, setOrders]               = useState([]);

    const fetchOrders = () => {
        if (!timeFrom || !timeTo) {
            alert('Please select a time range');
            return;
        }

        setLoading(true);
        router.post(route('shopee.order.get', marketplaceId), {
            time_from: timeFrom,
            time_to  : timeTo
        }, {
            preserveState: true,
            onSuccess: (page) => {
                setOrders(page.props.data || []);
                setLoading(false);
            },
            onError: () => setLoading(false)
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Order Sync
                </h2>
            }
        >
            <Head title="Order Sync" />

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

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <div className="mb-4">
                                <label className="mr-2">
                                    From:
                                    <input type="date" value={timeFrom} onChange={e => setTimeFrom(e.target.value)} className="ml-2" />
                                </label>
                                <label className="mr-2">
                                    To:
                                    <input type="date" value={timeTo} onChange={e => setTimeTo(e.target.value)} className="ml-2" />
                                </label>
                                <button onClick={fetchOrders} disabled={loading} className="px-4 py-2 bg-blue-500 text-white rounded">
                                    {loading ? 'Fetching...' : 'Fetch Orders'}
                                </button>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full bg-white dark:bg-gray-800">
                                    <thead>
                                        <tr>
                                            <th className="py-2 px-4 border-b">Order SN</th>
                                            <th className="py-2 px-4 border-b">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {orders.length > 0 ? (
                                            orders.map(order => (
                                                <tr key={order.order_sn}>
                                                    <td className="py-2 px-4 border-b">{order.order_sn}</td>
                                                    <td className="py-2 px-4 border-b">{order.order_status}</td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="2" className="py-4 text-center">No orders found.</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default OrderSync;
