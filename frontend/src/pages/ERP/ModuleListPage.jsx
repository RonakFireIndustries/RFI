import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { Edit2, Plus, Search, Trash2, X } from 'lucide-react';
import DataTable from '../../components/Shared/DataTable';

const getPathValue = (row, path) => typeof path === 'string' ? path.split('.').reduce((value, key) => value?.[key], row) : undefined;

const coercePayload = (fields, formData) => fields.reduce((payload, field) => {
  if (field.type === 'heading' || !field.name) return payload;
  const value = formData[field.name];
  if (value === '' && field.emptyAsNull) {
    payload[field.name] = null;
  } else if (field.type === 'number' && value !== '') {
    payload[field.name] = Number(value);
  } else {
    payload[field.name] = value;
  }
  return payload;
}, {});

export default function ModuleListPage({
  title,
  description,
  store,
  columns,
  fields,
  detailBasePath,
  searchPlaceholder,
  lookups = {},
}) {
  const { items, loading, fetchItems, createItem, updateItem, deleteItem } = store();
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState(null);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [formData, setFormData] = useState({});

  useEffect(() => {
    fetchItems();
  }, [fetchItems]);

  const openModal = (item = null) => {
    setEditing(item);
    setFormData(fields.reduce((data, field) => {
      if (field.type === 'heading' || !field.name) return data;
      data[field.name] = item ? (getPathValue(item, field.valuePath || field.name) ?? '') : (field.defaultValue ?? '');
      return data;
    }, {}));
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setEditing(null);
    setIsModalOpen(false);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    const payload = coercePayload(fields, formData);
    
    const hasFiles = Object.values(payload).some((val) => val instanceof File);
    let finalPayload = payload;
    
    if (hasFiles) {
      finalPayload = new FormData();
      Object.entries(payload).forEach(([key, value]) => {
        if (value !== null && value !== undefined) {
          finalPayload.append(key, value instanceof File ? value : String(value));
        }
      });
    }

    try {
      if (editing) {
        await updateItem(editing.id, finalPayload);
      } else {
        await createItem(finalPayload);
      }
      closeModal();
      fetchItems();
    } catch (error) {
      console.error(error);
      const errors = error.response?.data?.errors;
      if (errors) {
        const errorMessages = Object.entries(errors)
          .map(([field, msgs]) => `${field}: ${msgs.join(', ')}`)
          .join('\n');
        alert(`Validation Error:\n${errorMessages}`);
      } else {
        alert(error.response?.data?.message || error.message || 'An error occurred while saving.');
      }
    }
  };

  const tableColumns = useMemo(() => [
    ...columns.map((column) => ({
      accessorFn: (row) => column.accessor ? getPathValue(row, column.accessor) : column.cellValue?.(row),
      id: column.id || column.accessor || column.header,
      header: column.header,
      cell: ({ row }) => column.cell ? column.cell(row.original) : (column.accessor ? getPathValue(row.original, column.accessor) : ''),
    })),
    {
      id: 'actions',
      header: 'Actions',
      enableSorting: false,
      cell: ({ row }) => (
        <div className="flex justify-end gap-2">
          <Link className="rounded-md border border-gray-200 px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50" to={`${detailBasePath}/${row.original.id}`}>
            View
          </Link>
          <button type="button" className="rounded-md p-1.5 text-blue-600 hover:bg-blue-50" onClick={() => openModal(row.original)} title="Edit">
            <Edit2 className="h-4 w-4" />
          </button>
          <button
            type="button"
            className="rounded-md p-1.5 text-red-600 hover:bg-red-50"
            onClick={() => {
              if (window.confirm(`Delete ${title.slice(0, -1).toLowerCase()}?`)) deleteItem(row.original.id);
            }}
            title="Delete"
          >
            <Trash2 className="h-4 w-4" />
          </button>
        </div>
      ),
    },
  ], [columns, deleteItem, detailBasePath, fields, title, updateItem]);

  return (
    <div className="space-y-5 pb-10">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{title}</h1>
          <p className="mt-1 text-sm text-gray-500">{description}</p>
        </div>
        <button type="button" onClick={() => openModal()} className="inline-flex items-center justify-center gap-2 rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
          <Plus className="h-4 w-4" />
          Add
        </button>
      </div>

      <div className="relative max-w-md">
        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <input
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          placeholder={searchPlaceholder}
          className="w-full rounded-md border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]"
        />
      </div>

      {loading ? (
        <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading {title.toLowerCase()}...</div>
      ) : (
        <DataTable columns={tableColumns} data={items} globalFilter={search} onGlobalFilterChange={setSearch} />
      )}

      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6 sm:py-12">
          <div className="flex w-full max-w-2xl flex-col max-h-full rounded-lg bg-white shadow-xl">
            <div className="flex items-center justify-between border-b border-gray-100 px-5 py-4">
              <h2 className="text-lg font-semibold text-gray-900">{editing ? 'Edit' : 'Add'} {title.slice(0, -1)}</h2>
              <button type="button" onClick={closeModal} className="rounded-md p-1 text-gray-500 hover:bg-gray-100">
                <X className="h-5 w-5" />
              </button>
            </div>
            <form onSubmit={handleSubmit} className="flex flex-col overflow-hidden">
              <div className="overflow-y-auto p-5">
                <div className="grid gap-4 sm:grid-cols-2">
              {fields.map((field) => 
                field.type === 'heading' ? (
                  <div key={field.label} className="col-span-full border-b border-gray-200 pb-2 pt-6">
                    <h3 className="text-sm font-semibold uppercase tracking-wider text-[#1a56db]">{field.label}</h3>
                  </div>
                ) : (
                <label key={field.name} className={field.fullWidth ? 'sm:col-span-2' : ''}>
                  <span className="mb-1 block text-sm font-medium text-gray-700">{field.label}</span>
                  {field.type === 'select' ? (
                    <select
                      required={field.required}
                      value={formData[field.name] ?? ''}
                      onChange={(event) => setFormData((current) => ({ ...current, [field.name]: event.target.value }))}
                      className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]"
                    >
                      <option value="">{field.placeholder || `Select ${field.label}`}</option>
                      {(lookups[field.optionsKey] || field.options || []).map((option) => (
                        <option key={option.id ?? option.value} value={option.id ?? option.value}>
                          {option.name ?? option.label}
                        </option>
                      ))}
                    </select>
                  ) : field.type === 'file' ? (
                    <input
                      required={field.required && !editing}
                      type="file"
                      accept={field.accept}
                      onChange={(event) => setFormData((current) => ({ ...current, [field.name]: event.target.files[0] }))}
                      className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]"
                    />
                  ) : (
                    <input
                      required={field.required}
                      type={field.type || 'text'}
                      step={field.step}
                      value={formData[field.name] ?? ''}
                      onChange={(event) => setFormData((current) => ({ ...current, [field.name]: event.target.value }))}
                      className="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-[#1a56db] focus:outline-none focus:ring-1 focus:ring-[#1a56db]"
                    />
                  )}
                </label>
              ))}
                </div>
              </div>
              <div className="flex shrink-0 justify-end gap-3 border-t border-gray-100 bg-gray-50 p-4 rounded-b-lg">
                <button type="button" onClick={closeModal} className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="rounded-md bg-[#1a56db] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Save</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
