import { useState, useEffect, useCallback } from 'react';
import {
  LifeBuoy, Loader2, Search, RefreshCw, X, Trash2, Save,
  Image as ImageIcon, AlertCircle, User as UserIcon,
} from 'lucide-react';
import {
  issueReportService,
  ISSUE_CATEGORIES,
  ISSUE_PRIORITIES,
  ISSUE_STATUSES,
  categoryLabel,
  statusLabel,
  STATUS_STYLES,
  PRIORITY_STYLES,
} from '../../services/issueReportService';

export default function IssueReportsPage() {
  const [reports, setReports] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({ status: '', priority: '', category: '', search: '' });
  const [selected, setSelected] = useState(null);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [detailForm, setDetailForm] = useState({ status: 'open', priority: 'medium', admin_notes: '' });

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const list = await issueReportService.list(filters);
      setReports(Array.isArray(list) ? list : []);
    } catch (err) {
      console.error('Failed to load issue reports', err);
      setReports([]);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    const timer = setTimeout(load, 250);
    return () => clearTimeout(timer);
  }, [load]);

  const openDetail = (report) => {
    setSelected(report);
    setDetailForm({
      status: report.status || 'open',
      priority: report.priority || 'medium',
      admin_notes: report.admin_notes || '',
    });
    setError('');
  };

  const handleSave = async () => {
    if (!selected) return;
    setSaving(true);
    setError('');
    try {
      const updated = await issueReportService.update(selected.id, detailForm);
      setReports((prev) => prev.map((r) => (r.id === selected.id ? { ...r, ...updated } : r)));
      setSelected(null);
    } catch (err) {
      setError(err?.response?.data?.message || 'Failed to update the report.');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (report) => {
    if (!window.confirm('Delete this issue report? This cannot be undone.')) return;
    try {
      await issueReportService.remove(report.id);
      setReports((prev) => prev.filter((r) => r.id !== report.id));
      if (selected?.id === report.id) setSelected(null);
    } catch (err) {
      console.error('Failed to delete issue report', err);
    }
  };

  const reporterName = (report) => report.user?.name || 'Unknown';

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between gap-4 flex-wrap">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
            <LifeBuoy className="w-5 h-5" />
          </div>
          <div>
            <h1 className="text-xl font-bold text-foreground">Issue Reports</h1>
            <p className="text-sm text-muted-foreground">Issues reported by employees across the application.</p>
          </div>
        </div>
        <button
          onClick={load}
          className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium border border-input rounded-lg hover:bg-muted transition-colors"
        >
          <RefreshCw className={`w-4 h-4 ${loading ? 'animate-spin' : ''}`} />
          Refresh
        </button>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
          <input
            type="text"
            placeholder="Search title or description..."
            value={filters.search}
            onChange={(e) => setFilters((p) => ({ ...p, search: e.target.value }))}
            className="w-full rounded-lg border border-input bg-card pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
          />
        </div>
        <select
          value={filters.status}
          onChange={(e) => setFilters((p) => ({ ...p, status: e.target.value }))}
          className="w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
        >
          <option value="">All statuses</option>
          {ISSUE_STATUSES.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
        </select>
        <select
          value={filters.priority}
          onChange={(e) => setFilters((p) => ({ ...p, priority: e.target.value }))}
          className="w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
        >
          <option value="">All priorities</option>
          {ISSUE_PRIORITIES.map((p) => <option key={p.value} value={p.value}>{p.label}</option>)}
        </select>
        <select
          value={filters.category}
          onChange={(e) => setFilters((p) => ({ ...p, category: e.target.value }))}
          className="w-full rounded-lg border border-input bg-card px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
        >
          <option value="">All categories</option>
          {ISSUE_CATEGORIES.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
        </select>
      </div>

      <div className="bg-card border border-border rounded-xl overflow-hidden">
        {loading ? (
          <div className="flex items-center justify-center py-16">
            <Loader2 className="w-6 h-6 animate-spin text-muted-foreground" />
          </div>
        ) : reports.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-center">
            <LifeBuoy className="w-10 h-10 text-muted-foreground/40 mb-2" />
            <p className="text-sm text-muted-foreground">No issue reports found.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-muted/50 text-muted-foreground">
                <tr>
                  <th className="text-left font-medium px-4 py-3">Title</th>
                  <th className="text-left font-medium px-4 py-3">Reported by</th>
                  <th className="text-left font-medium px-4 py-3">Category</th>
                  <th className="text-left font-medium px-4 py-3">Priority</th>
                  <th className="text-left font-medium px-4 py-3">Status</th>
                  <th className="text-left font-medium px-4 py-3">Date</th>
                  <th className="text-right font-medium px-4 py-3"></th>
                </tr>
              </thead>
              <tbody>
                {reports.map((report) => (
                  <tr
                    key={report.id}
                    className="border-t border-border hover:bg-muted/40 cursor-pointer"
                    onClick={() => openDetail(report)}
                  >
                    <td className="px-4 py-3">
                      <p className="font-medium text-foreground">{report.title}</p>
                      {report.screenshots?.length > 0 && (
                        <span className="inline-flex items-center gap-1 text-[11px] text-muted-foreground mt-0.5">
                          <ImageIcon className="w-3 h-3" />
                          {report.screenshots.length}
                        </span>
                      )}
                    </td>
                    <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">{reporterName(report)}</td>
                    <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">{categoryLabel(report.category)}</td>
                    <td className="px-4 py-3">
                      <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${PRIORITY_STYLES[report.priority] || 'bg-muted text-muted-foreground'}`}>
                        {report.priority}
                      </span>
                    </td>
                    <td className="px-4 py-3">
                      <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${STATUS_STYLES[report.status] || 'bg-muted text-muted-foreground'}`}>
                        {statusLabel(report.status)}
                      </span>
                    </td>
                    <td className="px-4 py-3 text-muted-foreground whitespace-nowrap">
                      {new Date(report.created_at).toLocaleDateString()}
                    </td>
                    <td className="px-4 py-3 text-right">
                      <button
                        onClick={(e) => { e.stopPropagation(); handleDelete(report); }}
                        className="p-1.5 rounded text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors"
                        title="Delete"
                      >
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {selected && (
        <div
          className="fixed inset-0 z-50 flex items-start justify-center p-3 sm:p-6 bg-black/40 overflow-y-auto"
          onClick={(e) => e.target === e.currentTarget && setSelected(null)}
        >
          <div className="bg-card rounded-xl shadow-2xl border border-border w-full max-w-2xl my-4">
            <div className="flex items-start justify-between px-5 py-4 border-b border-border">
              <div className="min-w-0">
                <h2 className="text-base font-semibold text-foreground break-words">{selected.title}</h2>
                <div className="flex flex-wrap items-center gap-2 mt-1 text-xs text-muted-foreground">
                  <span className="inline-flex items-center gap-1">
                    <UserIcon className="w-3.5 h-3.5" />
                    {reporterName(selected)}
                  </span>
                  <span>· {categoryLabel(selected.category)}</span>
                  <span>· {new Date(selected.created_at).toLocaleString()}</span>
                  {selected.page_url && (
                    <span className="truncate max-w-[240px]">· {selected.page_url}</span>
                  )}
                </div>
              </div>
              <button
                onClick={() => setSelected(null)}
                className="text-muted-foreground hover:text-foreground p-1.5 rounded hover:bg-muted transition-colors shrink-0"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="p-5 space-y-4 max-h-[70vh] overflow-y-auto">
              {selected.description && (
                <div>
                  <p className="text-xs font-medium text-muted-foreground mb-1">Description</p>
                  <p className="text-sm text-foreground whitespace-pre-wrap">{selected.description}</p>
                </div>
              )}

              {selected.screenshots?.length > 0 && (
                <div>
                  <p className="text-xs font-medium text-muted-foreground mb-2">Screenshots</p>
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    {selected.screenshots.map((shot) => (
                      <a
                        key={shot.id}
                        href={shot.url}
                        target="_blank"
                        rel="noreferrer"
                        className="block rounded-lg overflow-hidden border border-border"
                      >
                        <img src={shot.url} alt={shot.file_name} className="w-full h-32 object-cover" />
                      </a>
                    ))}
                  </div>
                </div>
              )}

              {error && (
                <div className="flex items-center gap-2 p-3 text-sm text-destructive bg-destructive/10 rounded-lg border border-destructive/20">
                  <AlertCircle className="w-4 h-4 shrink-0" />
                  {error}
                </div>
              )}

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-medium text-muted-foreground mb-1.5">Status</label>
                  <select
                    value={detailForm.status}
                    onChange={(e) => setDetailForm((p) => ({ ...p, status: e.target.value }))}
                    className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                  >
                    {ISSUE_STATUSES.map((s) => <option key={s.value} value={s.value}>{s.label}</option>)}
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium text-muted-foreground mb-1.5">Priority</label>
                  <select
                    value={detailForm.priority}
                    onChange={(e) => setDetailForm((p) => ({ ...p, priority: e.target.value }))}
                    className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                  >
                    {ISSUE_PRIORITIES.map((p) => <option key={p.value} value={p.value}>{p.label}</option>)}
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium text-muted-foreground mb-1.5">Internal notes / response</label>
                <textarea
                  rows={3}
                  value={detailForm.admin_notes}
                  onChange={(e) => setDetailForm((p) => ({ ...p, admin_notes: e.target.value }))}
                  placeholder="Add notes about the resolution (visible to the reporter)..."
                  className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring resize-y"
                />
              </div>

              <div className="flex items-center justify-between pt-2">
                <button
                  onClick={() => handleDelete(selected)}
                  className="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                >
                  <Trash2 className="w-4 h-4" />
                  Delete
                </button>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => setSelected(null)}
                    className="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    onClick={handleSave}
                    disabled={saving}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
                  >
                    {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
                    {saving ? 'Saving...' : 'Save'}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
