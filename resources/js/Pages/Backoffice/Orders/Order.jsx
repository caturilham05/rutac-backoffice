import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import DataTable from '@/Components/DataTable';

import { useState } from 'react';

const ProductNameCell = ({ name }) => {
    const [isFull, setIsFull] = useState(false);
    const limit = 30;

    return (
        <span
            className="cursor-pointer hover:text-blue-500"
            onClick={() => setIsFull(!isFull)}
            title={isFull ? 'Click to truncate' : 'Click to see full name'}
        >
            {isFull ? name : (name.length > limit ? name.substring(0, limit) + '...' : name)}
        </span>
    );
};

function Order({ orders, filters, sortColumn, sortDirection }) {
    const columns = [
        { key: 'detail', label: '' },
        { key: 'invoice', label: 'Invoice' },
        { key: 'buyer_username', label: 'Buyer Username' },
        { key: 'courier', label: 'Courier' },
        { key: 'status', label: 'Status' },
        {
            key: 'product_name',
            label: 'Product Name',
            renderDetail: (item) => <ProductNameCell name={item.product_name} />,
            render: () => '-'
        },
        {
            key: 'qty',
            label: 'Qty',
            renderDetail: (item) => item.qty,
            render: (row) => row.products.reduce((acc, item) => acc + parseInt(item.qty), 0)
        },
        {
            key: 'total_price',
            label: 'Total Price',
            render: (row) => `Rp ${parseFloat(row.total_price).toLocaleString()}`,
            renderDetail: (item) => `Rp ${parseFloat(item.sale).toLocaleString()}`
        },
        {
            key: 'discount',
            label: 'Discount',
            render: (row) => `Rp ${parseFloat(row.discount).toLocaleString()}`
        },
        {
            key: 'income',
            label: 'Income',
            render: (row) => `Rp ${parseFloat(row.income).toLocaleString()}`
        },
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
