import { useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import DataTable from '../../components/Shared/DataTable';

const valueAt = (item, path) => path.split('.').reduce((value, key) => value?.[key], item);

export default function ModuleDetailPage({ title, store, backPath, fields, sections, children }) {
  const { id } = useParams();
  const { selected, loading, fetchItem, clearSelected } = store();

  useEffect(() => {
    fetchItem(id);
    return () => clearSelected();
  }, [clearSelected, fetchItem, id]);

  if (loading || !selected) {
    return <div className="rounded-lg border border-gray-200 bg-white p-8 text-center text-sm text-gray-500">Loading detail...</div>;
  }

  return (
    <div className="space-y-5 pb-10">
      <div>
        <Link to={backPath} className="text-sm font-medium text-[#1a56db] hover:underline">Back to list</Link>
        <h1 className="mt-2 text-2xl font-bold text-gray-900">{title(selected)}</h1>
      </div>

      <div className="grid gap-4 rounded-lg border border-gray-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-3">
        {fields.map((field) => (
          <div key={field.label}>
            <div className="text-xs font-semibold uppercase tracking-wider text-gray-500">{field.label}</div>
            <div className="mt-1 text-sm font-medium text-gray-900">{field.render ? field.render(selected) : (valueAt(selected, field.path) || '-')}</div>
          </div>
        ))}
      </div>

      {sections.map((section) => {
        const rows = valueAt(selected, section.path) || [];
        return (
          <section key={section.title} className="space-y-3">
            <h2 className="text-lg font-semibold text-gray-900">{section.title}</h2>
            <DataTable columns={section.columns} data={Array.isArray(rows) ? rows : []} emptyText={section.emptyText} />
          </section>
        );
      })}

      {children}
    </div>
  );
}
