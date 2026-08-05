import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';

function Order({ orders, filters, sortColumn, sortDirection }) {
    const columns = [
        { key: 'detail', label: '' },
        { key: 'invoice', label: 'Invoice' },
        { key: 'buyer_username', label: 'Buyer Username' },
        { key: 'courier', label: 'Courier' },
        { key: 'status', label: 'Status' },
        { key: 'total_price', label: 'Total Price', render: (row) => `Rp ${parseFloat(row.total_price).toLocaleString()}` },
        { key: 'order_time', label: 'Order Time' },
    ];

    const filterConfig = [
        { key: 'invoice', label: 'Invoice', type: 'text' },
        { key: 'buyer_username', label: 'Buyer Username', type: 'text' },
        { key: 'courier', label: 'Courier', type: 'text' },
        { key: 'status', label: 'Status', type: 'select', options: [
            { value: '', label: 'All' },
            { value: 'unpaid', label: 'Unpaid' },
            { value: 'paid', label: 'Paid' },
            { value: 'shipped', label: 'Shipped' },
            { value: 'completed', label: 'Completed' },
            { value: 'cancelled', label: 'Cancelled' },
        ]},
        { key: 'start_date', label: 'Start Date', type: 'date' },
        { key: 'end_date', label: 'End Date', type: 'date' },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Order
                </h2>
            }
        >
            <Head title="Order" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <DataTable
                                columns={columns}
                                data={orders.data.map(order => ({ ...order, items: order.products }))}
                                pagination={orders}
                                filterConfig={filterConfig}
                                filterValues={filters}
                                sortColumn={sortColumn}
                                sortDirection={sortDirection}
                                sortableColumns={['invoice', 'order_time', 'status']}
                                baseUrl={route('order')}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Order;
