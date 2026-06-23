import { Fragment, useState } from "react";
import { ChevronRight, ChevronDown } from 'lucide-react';

export default function DataTable({
    columns = [],
    data = [],
    pagination = null,
}) {
    const [expandedRows, setExpandedRows] = useState({});

    const toggleRow = (id) => {
        setExpandedRows(prev => ({
            ...prev,
            [id]: !prev[id]
        }));
    };

    // Render isi cell untuk baris variant — sejajar dengan kolom parent
    const renderVariantCell = (col, item) => {
        if (col.key === 'detail') return null;
        if (col.key === 'action') return null;
        if (col.key === 'name') {
            return (
                <span className="pl-6 text-gray-600 dark:text-gray-400">
                    {item.name}
                </span>
            );
        }
        if (col.key === 'price') {
            return (
                <span className="text-gray-600 dark:text-gray-400">
                    Rp {Number(item.price).toLocaleString('id-ID')}
                </span>
            );
        }
        if (col.key === 'stock') {
            return (
                <span className="text-gray-600 dark:text-gray-400">
                    {item.stock}
                </span>
            );
        }
        // Fallback: tampilkan item[col.key] jika ada
        return item[col.key] ?? null;
    };

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            <div className="overflow-x-auto">
                <table className="w-full text-sm text-left">

                    {/* HEADER */}
                    <thead className="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300">
                        <tr>
                            {columns.map((col) => (
                                <th key={col.key} className="px-4 py-3 font-semibold">
                                    {col.label}
                                </th>
                            ))}
                        </tr>
                    </thead>

                    {/* BODY */}
                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                        {data.length > 0 ? (
                            data.map((row) => (
                                <Fragment key={row.id}>

                                    {/* Baris utama produk */}
                                    <tr className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        {columns.map((col) => (
                                            <td key={col.key} className="px-4 py-3">
                                                {col.key === 'detail' ? (
                                                    // Fix: tombol langsung di dalam <td>, tidak nested <td> lagi
                                                    <button
                                                        onClick={() => toggleRow(row.id)}
                                                        className="flex items-center justify-center"
                                                    >
                                                        {expandedRows[row.id]
                                                            ? <ChevronDown size={18} />
                                                            : <ChevronRight size={18} />
                                                        }
                                                    </button>
                                                ) : (
                                                    col.render
                                                        ? col.render(row)
                                                        : row[col.key]
                                                )}
                                            </td>
                                        ))}
                                    </tr>

                                    {/* Baris variant — langsung di tbody yang sama agar kolom selaras */}
                                    {expandedRows[row.id] && row.items?.map((item) => (
                                        <tr
                                            key={`${row.id}-item-${item.id}`}
                                            className="bg-gray-50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-700/50 last:border-0"
                                        >
                                            {columns.map((col) => (
                                                <td key={col.key} className="px-4 py-2">
                                                    {renderVariantCell(col, item)}
                                                </td>
                                            ))}
                                        </tr>
                                    ))}

                                </Fragment>
                            ))
                        ) : (
                            <tr>
                                <td colSpan={columns.length} className="text-center py-6 text-gray-400">
                                    Tidak ada data
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* PAGINATION */}
            {pagination && (
                <div className="p-3 border-t flex justify-between items-center text-sm">
                    <div className="text-gray-500">
                        Page {pagination.current_page} of {pagination.last_page}
                    </div>

                    <div className="flex gap-2">
                        {pagination.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && (window.location.href = link.url)}
                                className={`px-3 py-1 rounded border ${
                                    link.active
                                        ? 'bg-blue-500 text-white'
                                        : 'bg-white text-gray-600'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
