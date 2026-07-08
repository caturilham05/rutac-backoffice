import { Fragment, useState, useEffect, useMemo } from "react";
import { router } from "@inertiajs/react";
import { ChevronRight, ChevronLeft, ChevronDown, ChevronUp, Funnel, X } from 'lucide-react';

export default function DataTable({
    columns = [],
    data = [],
    pagination = null,
    filterConfig = [],  // renamed from 'filters' (array of config objects)
    filterValues = {},  // current filter values object
    sortColumn = null,
    sortDirection = 'asc',
    sortableColumns = [],
    baseUrl = null,
}) {
    const [expandedRows, setExpandedRows] = useState({});
    const [localFilters, setLocalFilters] = useState(filterValues);
    const [localSortColumn, setLocalSortColumn] = useState(sortColumn);
    const [localSortDirection, setLocalSortDirection] = useState(sortDirection);
    const [showFilters, setShowFilters] = useState(false);

    const toggleRow = (id) => {
        setExpandedRows(prev => ({
            ...prev,
            [id]: !prev[id]
        }));
    };

    const renderDetail = (col, item, row) => {
        if (col.renderDetail) {
            return col.renderDetail(item, row)
        }
    }

    const applyFilters = (newFilters = localFilters, newSortColumn = localSortColumn, newSortDirection = localSortDirection) => {
        if (!baseUrl) return;

        const params = new URLSearchParams();

        Object.entries(newFilters).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                params.set(key, value);
            }
        });

        if (newSortColumn) {
            params.set('sort', newSortColumn);
            params.set('direction', newSortDirection);
        }

        if (pagination?.current_page && pagination.current_page > 1) {
            params.set('page', pagination.current_page);
        }

        router.visit(`${baseUrl}?${params.toString()}`, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const handleFilterChange = (key, value) => {
        const newFilters = { ...localFilters, [key]: value };
        setLocalFilters(newFilters);
        applyFilters(newFilters);
    };

    const handleSort = (columnKey) => {
        if (!sortableColumns.includes(columnKey)) return;

        let newDirection = 'asc';
        if (localSortColumn === columnKey && localSortDirection === 'asc') {
            newDirection = 'desc';
        }
        setLocalSortColumn(columnKey);
        setLocalSortDirection(newDirection);
        applyFilters(localFilters, columnKey, newDirection);
    };

    const clearFilters = () => {
        const clearedFilters = {};
        filterConfig.forEach(filter => {
            clearedFilters[filter.key] = '';
        });
        setLocalFilters(clearedFilters);
        applyFilters(clearedFilters, null, 'asc');
    };

    const hasActiveFilters = useMemo(() => {
        return Object.values(localFilters).some(v => v !== '' && v !== null && v !== undefined);
    }, [localFilters]);

    const renderFilterInput = (filter) => {
        const value = localFilters[filter.key] || '';

        switch (filter.type) {
            case 'select':
                return (
                    <select
                        value={value}
                        onChange={(e) => handleFilterChange(filter.key, e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All {filter.label}</option>
                        {filter.options?.map(opt => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                );
            case 'date':
                return (
                    <input
                        type="date"
                        value={value}
                        onChange={(e) => handleFilterChange(filter.key, e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                );
            case 'number':
                return (
                    <input
                        type="number"
                        value={value}
                        onChange={(e) => handleFilterChange(filter.key, e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder={filter.placeholder}
                    />
                );
            default:
                return (
                    <input
                        type="text"
                        value={value}
                        onChange={(e) => handleFilterChange(filter.key, e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder={filter.placeholder || `Search ${filter.label}...`}
                    />
                );
        }
    };

    const getSortIcon = (columnKey) => {
        if (localSortColumn !== columnKey) return null;
        return localSortDirection === 'asc' ? <ChevronUp size={14} className="text-blue-500" /> : <ChevronDown size={14} className="text-blue-500" />;
    };

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            {/* FILTER BAR */}
            {filterConfig.length > 0 && (
                <div className={`border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 transition-all duration-300 ${showFilters ? '' : 'hidden'}`}>
                    <div className="p-4 space-y-4">
                        <div className="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                            <div className="flex gap-2 flex-wrap">
                                {filterConfig.map((filter) => (
                                    <div key={filter.key} className="flex items-center gap-2 min-w-[200px] flex-1 sm:min-w-0">
                                        <label className="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">{filter.label}</label>
                                        {renderFilterInput(filter)}
                                    </div>
                                ))}
                            </div>
                            <div className="flex gap-2">
                                {hasActiveFilters && (
                                    <button
                                        onClick={clearFilters}
                                        className="px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-red-500 flex items-center gap-1"
                                    >
                                        <X size={14} /> Clear
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* TOGGLE FILTERS BUTTON */}
            {filterConfig.length > 0 && (
                <button
                    onClick={() => setShowFilters(!showFilters)}
                    className={`w-full px-4 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50`}
                >
                    <span className="flex items-center gap-2">
                        <Funnel size={16} />
                        Filters
                        {hasActiveFilters && (
                            <span className="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs">
                                {Object.values(localFilters).filter(v => v).length}
                            </span>
                        )}
                    </span>
                    <ChevronDown size={16} className={`${showFilters ? 'rotate-180' : ''} transition-transform`} />
                </button>
            )}

            <div className="overflow-x-auto">
                <table className="w-full text-sm text-left">
                    {/* HEADER */}
                    <thead className="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300">
                        <tr>
                            {columns.map((col) => (
                                <th
                                    key={col.key}
                                    className="px-4 py-3 font-semibold"
                                    style={{ cursor: sortableColumns.includes(col.key) ? 'pointer' : 'default' }}
                                    onClick={() => handleSort(col.key)}
                                >
                                    <div className="flex items-center gap-1">
                                        {col.label}
                                        {sortableColumns.includes(col.key) && getSortIcon(col.key)}
                                    </div>
                                </th>
                            ))}
                        </tr>
                    </thead>

                    {/* BODY */}
                    <tbody className="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                        {data.length > 0 ? (
                            data.map((row) => (
                                <Fragment key={row.id}>
                                    {/* Main row */}
                                    <tr className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        {columns.map((col) => (
                                            <td key={col.key} className="px-4 py-3">
                                                {col.key === 'detail' ? (
                                                    <button
                                                        onClick={() => toggleRow(row.id)}
                                                        className="flex items-center justify-center"
                                                    >
                                                        {expandedRows[row.id]
                                                            ? <ChevronDown size={18} />
                                                            : <ChevronRight size={18} />}
                                                    </button>
                                                ) : (
                                                    col.render
                                                        ? col.render(row)
                                                        : row[col.key]
                                                )}
                                            </td>
                                        ))}
                                    </tr>

                                    {/* Expanded detail rows */}
                                    {expandedRows[row.id] && row.items?.map((item) => (
                                        <tr
                                            key={`${row.id}-item-${item.id}`}
                                            className="bg-gray-50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-700/50 last:border-0"
                                        >
                                            {columns.map((col) => (
                                                <td key={col.key} className="py-2">
                                                    <span className="pl-3 text-gray-600 dark:text-gray-400">
                                                        {renderDetail(col, item, row)}
                                                    </span>
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
                        {pagination.links.map((link, i) => {
                            const isPrev = i === 0;
                            const isNext = i === pagination.links.length - 1;
                            const icon = isPrev ? <ChevronLeft size={16} /> : isNext ? <ChevronRight size={16} /> : null;

                            if (link.url === null) {
                                return (
                                    <span key={i} className="px-3 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed flex items-center gap-1">
                                        {icon || <span dangerouslySetInnerHTML={{ __html: link.label }} />}
                                    </span>
                                );
                            }

                            return (
                                <button
                                    key={i}
                                    onClick={() => router.visit(link.url, { preserveScroll: true })}
                                    className={`px-3 py-1 rounded border flex items-center gap-1 ${
                                        link.active
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-600 hover:bg-gray-50'
                                    }`}
                                >
                                    {icon || <span dangerouslySetInnerHTML={{ __html: link.label }} />}
                                </button>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
