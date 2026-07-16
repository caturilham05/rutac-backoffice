import { router } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    ChevronUp,
    Funnel,
    X,
} from 'lucide-react';
import { Fragment, useMemo, useState } from 'react';

export default function DataTable({
    columns = [],
    data = [],
    pagination = null,
    filterConfig = [],
    filterValues = {},
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
        setExpandedRows((prev) => ({
            ...prev,
            [id]: !prev[id],
        }));
    };

    const renderDetail = (col, item, row) => {
        if (col.renderDetail) {
            return col.renderDetail(item, row);
        }
    };

    const applyFilters = (
        newFilters = localFilters,
        newSortColumn = localSortColumn,
        newSortDirection = localSortDirection,
    ) => {
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
        filterConfig.forEach((filter) => {
            clearedFilters[filter.key] = '';
        });
        setLocalFilters(clearedFilters);
        applyFilters(clearedFilters, null, 'asc');
    };

    const hasActiveFilters = useMemo(() => {
        return Object.values(localFilters).some(
            (v) => v !== '' && v !== null && v !== undefined,
        );
    }, [localFilters]);

    const renderFilterInput = (filter) => {
        const value = localFilters[filter.key] || '';

        switch (filter.type) {
            case 'select':
                return (
                    <select
                        value={value}
                        onChange={(e) =>
                            handleFilterChange(filter.key, e.target.value)
                        }
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    >
                        {/* <option value="">All {filter.label}</option> */}
                        {filter.options?.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                                {opt.label}
                            </option>
                        ))}
                    </select>
                );
            case 'autocomplete':
                return (
                    <>
                        <input
                            list={`${filter.key}-options`}
                            value={value}
                            onChange={(e) =>
                                handleFilterChange(filter.key, e.target.value)
                            }
                            className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                            placeholder={
                                filter.placeholder ||
                                `Search ${filter.label}...`
                            }
                        />
                        <datalist id={`${filter.key}-options`}>
                            {filter.options?.map((opt) => (
                                <option key={opt.value} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </datalist>
                    </>
                );
            case 'date':
                return (
                    <input
                        type="date"
                        value={value}
                        onChange={(e) =>
                            handleFilterChange(filter.key, e.target.value)
                        }
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                    />
                );
            case 'number':
                return (
                    <input
                        type="number"
                        value={value}
                        onChange={(e) =>
                            handleFilterChange(filter.key, e.target.value)
                        }
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                        placeholder={filter.placeholder}
                    />
                );
            default:
                return (
                    <input
                        type="text"
                        value={value}
                        onChange={(e) =>
                            handleFilterChange(filter.key, e.target.value)
                        }
                        className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
                        placeholder={
                            filter.placeholder || `Search ${filter.label}...`
                        }
                    />
                );
        }
    };

    const getSortIcon = (columnKey) => {
        if (localSortColumn !== columnKey) {
            return (
                <ChevronUp
                    size={14}
                    className="text-gray-300 opacity-60 dark:text-gray-600"
                />
            );
        }

        return localSortDirection === 'asc' ? (
            <ChevronUp size={14} className="text-blue-500" />
        ) : (
            <ChevronDown size={14} className="text-blue-500" />
        );
    };

    return (
        <div className="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
            {filterConfig.length > 0 && (
                <div
                    className={`border-b border-gray-200 bg-gray-50 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800/50 ${
                        showFilters ? '' : 'hidden'
                    }`}
                >
                    <div className="space-y-4 p-4">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div className="flex flex-wrap gap-2">
                                {filterConfig.map((filter) => (
                                    <div
                                        key={filter.key}
                                        className="flex min-w-[200px] flex-1 items-center gap-2 sm:min-w-0"
                                    >
                                        <label className="whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-300">
                                            {filter.label}
                                        </label>
                                        {renderFilterInput(filter)}
                                    </div>
                                ))}
                            </div>

                            <div className="flex gap-2">
                                {hasActiveFilters && (
                                    <button
                                        onClick={clearFilters}
                                        className="flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-red-500 dark:text-gray-300"
                                    >
                                        <X size={14} /> Clear
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {filterConfig.length > 0 && (
                <button
                    onClick={() => setShowFilters(!showFilters)}
                    className="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300 dark:hover:bg-gray-700/50"
                >
                    <span className="flex items-center gap-2">
                        <Funnel size={16} />
                        Filters
                        {hasActiveFilters && (
                            <span className="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                {
                                    Object.values(localFilters).filter((v) => v)
                                        .length
                                }
                            </span>
                        )}
                    </span>
                    <ChevronDown
                        size={16}
                        className={`${showFilters ? 'rotate-180' : ''} transition-transform`}
                    />
                </button>
            )}

            <div className="overflow-x-auto">
                <table className="w-full text-left text-sm">
                    <thead className="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            {columns.map((col) => {
                                const isSortable = sortableColumns.includes(
                                    col.key,
                                );

                                return (
                                    <th
                                        key={col.key}
                                        className={`px-4 py-3 font-semibold ${
                                            isSortable
                                                ? 'cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700'
                                                : ''
                                        }`}
                                        onClick={() =>
                                            isSortable && handleSort(col.key)
                                        }
                                        title={
                                            isSortable
                                                ? `Sort by ${col.label}`
                                                : undefined
                                        }
                                    >
                                        <div className="flex items-center gap-1">
                                            {col.label}
                                            {isSortable && getSortIcon(col.key)}
                                        </div>
                                    </th>
                                );
                            })}
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        {data.length > 0 ? (
                            data.map((row) => (
                                <Fragment key={row.id}>
                                    <tr className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        {columns.map((col) => (
                                            <td
                                                key={col.key}
                                                className="px-4 py-3"
                                            >
                                                {col.key === 'detail' ? (
                                                    <button
                                                        onClick={() =>
                                                            toggleRow(row.id)
                                                        }
                                                        className="flex items-center justify-center"
                                                    >
                                                        {expandedRows[
                                                            row.id
                                                        ] ? (
                                                            <ChevronDown
                                                                size={18}
                                                            />
                                                        ) : (
                                                            <ChevronRight
                                                                size={18}
                                                            />
                                                        )}
                                                    </button>
                                                ) : col.render ? (
                                                    col.render(row)
                                                ) : (
                                                    row[col.key]
                                                )}
                                            </td>
                                        ))}
                                    </tr>

                                    {expandedRows[row.id] &&
                                        row.items?.map((item) => (
                                            <tr
                                                key={`${row.id}-item-${item.id}`}
                                                className="border-b border-gray-100 bg-gray-50 last:border-0 dark:border-gray-700/50 dark:bg-gray-800/30"
                                            >
                                                {columns.map((col) => (
                                                    <td
                                                        key={col.key}
                                                        className="py-2"
                                                    >
                                                        <span className="pl-3 text-gray-600 dark:text-gray-400">
                                                            {renderDetail(
                                                                col,
                                                                item,
                                                                row,
                                                            )}
                                                        </span>
                                                    </td>
                                                ))}
                                            </tr>
                                        ))}
                                </Fragment>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan={columns.length}
                                    className="py-6 text-center text-gray-400"
                                >
                                    Tidak ada data
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {pagination && (
                <div className="flex items-center justify-between border-t p-3 text-sm">
                    <div className="text-gray-500">
                        Total {data.length}
                    </div>

                    <div className="text-gray-500">
                        Page {pagination.current_page} of {pagination.last_page}
                    </div>

                    <div className="flex gap-2">
                        {pagination.links.map((link, i) => {
                            const isPrev = i === 0;
                            const isNext = i === pagination.links.length - 1;
                            const icon = isPrev ? (
                                <ChevronLeft size={16} />
                            ) : isNext ? (
                                <ChevronRight size={16} />
                            ) : null;

                            if (link.url === null) {
                                return (
                                    <span
                                        key={i}
                                        className="flex cursor-not-allowed items-center gap-1 rounded border border-gray-200 px-3 py-1 text-gray-400"
                                    >
                                        {icon || (
                                            <span
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                            />
                                        )}
                                    </span>
                                );
                            }

                            return (
                                <button
                                    key={i}
                                    onClick={() =>
                                        router.visit(link.url, {
                                            preserveScroll: true,
                                        })
                                    }
                                    className={`flex items-center gap-1 rounded border px-3 py-1 ${
                                        link.active
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-600 hover:bg-gray-50'
                                    }`}
                                >
                                    {icon || (
                                        <span
                                            dangerouslySetInnerHTML={{
                                                __html: link.label,
                                            }}
                                        />
                                    )}
                                </button>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
