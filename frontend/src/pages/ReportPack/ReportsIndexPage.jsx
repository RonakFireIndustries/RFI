import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { BarChart3, ChevronRight, FileText, LayoutGrid, List, Loader2, Search } from 'lucide-react';
import api from '../../services/api';
import { useAuthStore } from '../../store/authStore';

/*
 * The module report pack.
 *
 * Every module in the application contributes a handful of reports - a
 * register, a summary, a breakdown - and the backend describes all of them in
 * one catalogue (see app/Support/Reports). This page is the index into that
 * catalogue: a module selector on the left, the reports it offers on the
 * right, and a search box over both.
 *
 * The catalogue is already permission filtered by the backend, so this page
 * never has to decide what the user may see - it only has to draw what it was
 * given.
 */

const Category = ({ title, modules, activeKey, onSelect, collapsed, onToggle }) => {
  if (modules.length === 0) return null;

  return (
    <div className="mb-4">
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center justify-between px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider text-gray-400 hover:text-gray-600"
      >
        {title}
        <ChevronRight className={`w-3.5 h-3.5 transition-transform ${collapsed ? '' : 'rotate-90'}`} />
      </button>

      {!collapsed && (
        <ul className="space-y-0.5">
          {modules.map((module) => {
            const isActive = module.key === activeKey;
            return (
              <li key={module.key}>
                <button
                  type="button"
                  onClick={() => onSelect(module.key)}
                  className={`flex w-full items-center justify-between gap-2 rounded-md px-3 py-2 text-left text-sm transition-colors ${
                    isActive
                      ? 'bg-orange-50 text-orange-700 font-medium'
                      : 'text-gray-700 hover:bg-gray-50'
                  }`}
                >
                  <span className="truncate">{module.label}</span>
                  <span className={`shrink-0 rounded-full px-1.5 py-0.5 text-[10px] ${
                    isActive ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500'
                  }`}>
                    {module.report_count}
                  </span>
                </button>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
};

export default function ReportsIndexPage() {
  const user = useAuthStore((s) => s.user);

  const [catalog, setCatalog] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [activeKey, setActiveKey] = useState(null);
  const [query, setQuery] = useState('');
  const [collapsed, setCollapsed] = useState({});

  useEffect(() => {
    let active = true;

    (async () => {
      try {
        const response = await api.get('/module-reports');
        if (active) setCatalog(response.data);
      } catch (err) {
        if (active) setError(err?.response?.data?.message || 'Could not load the report catalogue.');
      } finally {
        if (active) setLoading(false);
      }
    })();

    return () => { active = false; };
  }, []);

  const modules = catalog?.modules ?? [];

  const filtered = useMemo(() => {
    const needle = query.trim().toLowerCase();
    if (!needle) return modules;

    return modules
      .map((module) => ({
        ...module,
        reports: module.reports.filter((report) =>
          `${report.label} ${report.key} ${report.description ?? ''}`.toLowerCase().includes(needle)
        ),
      }))
      .filter((module) => module.reports.length > 0 || module.label.toLowerCase().includes(needle))
      .map((module) => ({
        ...module,
        // A module whose name matched keeps all of its reports, so the search
        // never hides what the user is looking at.
        reports: module.label.toLowerCase().includes(needle) && module.reports.length === 0
          ? modules.find((m) => m.key === module.key).reports
          : module.reports,
      }))
      .filter((module) => module.reports.length > 0);
  }, [modules, query]);

  const byCategory = useMemo(() => {
    const groups = new Map();
    filtered.forEach((module) => {
      const list = groups.get(module.category) ?? [];
      list.push(module);
      groups.set(module.category, list);
    });
    return [...groups.entries()];
  }, [filtered]);

  const active = useMemo(() => {
    if (filtered.length === 0) return null;
    return filtered.find((module) => module.key === activeKey) ?? filtered[0];
  }, [filtered, activeKey]);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-24 text-gray-500">
        <Loader2 className="w-6 h-6 mr-2 animate-spin" /> Loading the report catalogue…
      </div>
    );
  }

  if (error) {
    return (
      <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {error}
      </div>
    );
  }

  if (modules.length === 0) {
    return (
      <div className="rounded-lg bg-white p-10 text-center text-gray-500 shadow">
        <BarChart3 className="w-8 h-8 mx-auto mb-3 text-gray-300" />
        <h2 className="font-semibold text-gray-900">No reports available</h2>
        <p className="mt-1 text-sm">
          Your account does not have access to any module reports yet. Ask an administrator for the
          read permission of the module you need.
        </p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold text-gray-900">Reports</h1>
          <p className="text-sm text-gray-500">
            {catalog.report_count} reports across {catalog.module_count} modules
            {user?.name ? `, filtered to what ${user.name} may read` : ''}.
          </p>
        </div>

        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            type="search"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Search reports…"
            className="w-64 rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
          />
        </div>
      </div>

      <div className="grid gap-4 lg:grid-cols-[18rem_1fr]">
        <aside className="h-fit rounded-lg bg-white p-3 shadow">
          <div className="mb-2 flex items-center gap-2 px-3 text-[11px] font-bold uppercase tracking-wider text-gray-400">
            <LayoutGrid className="w-3.5 h-3.5" /> Modules
          </div>

          {byCategory.length === 0 ? (
            <p className="px-3 py-4 text-sm text-gray-500">No report matches “{query}”.</p>
          ) : (
            byCategory.map(([category, list]) => (
              <Category
                key={category}
                title={category}
                modules={list}
                activeKey={active?.key}
                onSelect={setActiveKey}
                collapsed={!!collapsed[category]}
                onToggle={() => setCollapsed((prev) => ({ ...prev, [category]: !prev[category] }))}
              />
            ))
          )}
        </aside>

        {/* The selected module's reports. A report is a link, not a button, so
            a deep link can be shared and the page can be bookmarked. */}
        <section className="rounded-lg bg-white shadow">
          {active ? (
            <>
              <div className="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4">
                <div>
                  <h2 className="font-semibold text-gray-900">{active.label}</h2>
                  {active.description && (
                    <p className="mt-0.5 text-xs text-gray-500">{active.description}</p>
                  )}
                </div>
                <span className="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">
                  {active.report_count} reports
                </span>
              </div>

              <ul className="divide-y divide-gray-100">
                {active.reports.map((report) => (
                  <li key={report.key}>
                    <Link
                      to={`/dashboard/reports/${active.key}/${report.key}`}
                      className="flex items-center gap-3 px-5 py-3 hover:bg-gray-50"
                    >
                      <FileText className="w-4 h-4 shrink-0 text-gray-400" />
                      <span className="min-w-0 flex-1">
                        <span className="block truncate text-sm font-medium text-gray-900">
                          {report.label}
                        </span>
                        {report.description && (
                          <span className="block truncate text-xs text-gray-500">
                            {report.description}
                          </span>
                        )}
                      </span>
                      {report.filters?.length > 0 && (
                        <span className="shrink-0 text-[11px] text-gray-400">
                          {report.filters.length} filters
                        </span>
                      )}
                      <ChevronRight className="w-4 h-4 shrink-0 text-gray-300" />
                    </Link>
                  </li>
                ))}
              </ul>
            </>
          ) : (
            <p className="px-5 py-10 text-center text-sm text-gray-500">
              Select a module to see its reports.
            </p>
          )}
        </section>
      </div>
    </div>
  );
}
