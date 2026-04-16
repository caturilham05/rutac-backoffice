export default function DataTable({
    columns = [],
    data = [],
    pagination = null,
}) {
    console.log(columns)
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
                            data.map((row, i) => (
                                <tr key={i} className="hover:bg-gray-50 dark:hover:bg-gray-800">
                                    {columns.map((col) => (
                                        <td key={col.key} className="px-4 py-3">
                                            {col.render
                                                ? col.render(row)
                                                : row[col.key]}
                                        </td>
                                    ))}
                                </tr>
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
            {/* {pagination && (
                <div className="p-3 border-t flex justify-between items-center text-sm">
                    <div className="text-gray-500">
                        Page {pagination.current_page} of {pagination.last_page}
                    </div>

                    <div className="flex gap-2">
                        {pagination.links.map((link, i) => (
                            <button
                                key={i}
                                disabled={!link.url}
                                onClick={() => link.url && window.location.href = link.url}
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
            )} */}
        </div>
    );
}
