import { useState, useEffect, useRef, useCallback } from 'react';
import {
  Bug, X, Loader2, Upload, Trash2, CheckCircle2, AlertCircle,
  Image as ImageIcon, History, Send, RefreshCw,
} from 'lucide-react';
import {
  issueReportService,
  ISSUE_CATEGORIES,
  ISSUE_PRIORITIES,
  categoryLabel,
  statusLabel,
  STATUS_STYLES,
  PRIORITY_STYLES,
} from '../../services/issueReportService';

const emptyForm = () => ({
  title: '',
  category: 'bug',
  priority: 'medium',
  description: '',
});

export default function IssueReportButton() {
  const [open, setOpen] = useState(false);
  const [tab, setTab] = useState('new');
  const [form, setForm] = useState(emptyForm());
  const [files, setFiles] = useState([]);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);
  const [reports, setReports] = useState([]);
  const [loadingReports, setLoadingReports] = useState(false);
  const fileInputRef = useRef(null);

  const loadReports = useCallback(async () => {
    setLoadingReports(true);
    try {
      const list = await issueReportService.list();
      setReports(Array.isArray(list) ? list : []);
    } catch (err) {
      console.error('Failed to load issue reports', err);
    } finally {
      setLoadingReports(false);
    }
  }, []);

  useEffect(() => {
    if (!open) return;
    setTab('new');
    setError('');
    setSuccess(false);
    setForm(emptyForm());
    setFiles([]);
    loadReports();
  }, [open, loadReports]);

  useEffect(() => {
    const onKey = (e) => {
      if (e.key === 'Escape') setOpen(false);
    };
    if (open) document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open]);

  const addFiles = (fileList) => {
    const incoming = Array.from(fileList || []).filter((f) => f.type.startsWith('image/'));
    setFiles((prev) => [...prev, ...incoming].slice(0, 10));
  };

  const removeFile = (index) => {
    setFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess(false);

    if (!form.title.trim()) {
      setError('Please enter a short title for the issue.');
      return;
    }

    setSubmitting(true);
    try {
      const formData = new FormData();
      formData.append('title', form.title.trim());
      formData.append('category', form.category);
      formData.append('priority', form.priority);
      if (form.description.trim()) formData.append('description', form.description.trim());
      formData.append('page_url', `${window.location.pathname}${window.location.search}`);
      files.forEach((file) => formData.append('screenshots[]', file));

      await issueReportService.create(formData);
      setSuccess(true);
      setForm(emptyForm());
      setFiles([]);
      await loadReports();
      setTab('history');
    } catch (err) {
      const data = err?.response?.data;
      let message = 'Failed to submit the report. Please try again.';
      if (data?.message) message = data.message;
      if (data?.errors) {
        const first = Object.values(data.errors)[0];
        if (Array.isArray(first) && first.length) message = first[0];
      }
      setError(message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="relative">
      <button
        onClick={() => setOpen(true)}
        className="text-muted-foreground hover:text-foreground transition-colors"
        title="Report an issue"
      >
        <span className="sr-only">Report an issue</span>
        <Bug className="h-5 w-5" />
      </button>

      {open && (
        <div
          className="fixed inset-0 z-50 flex items-start justify-center p-3 sm:p-6 bg-black/40 overflow-y-auto"
          onClick={(e) => e.target === e.currentTarget && setOpen(false)}
        >
          <div className="bg-card rounded-xl shadow-2xl border border-border w-full max-w-2xl my-4 animate-in fade-in slide-in-from-top-2 duration-150">
            <div className="flex items-center justify-between px-5 py-4 border-b border-border">
              <div className="flex items-center gap-2">
                <Bug className="w-5 h-5 text-primary" />
                <h2 className="text-base font-semibold text-foreground">Report an Issue</h2>
              </div>
              <button
                onClick={() => setOpen(false)}
                className="text-muted-foreground hover:text-foreground p-1.5 rounded hover:bg-muted transition-colors"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="flex border-b border-border">
              <button
                onClick={() => setTab('new')}
                className={`flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium transition-colors ${
                  tab === 'new'
                    ? 'text-primary border-b-2 border-primary'
                    : 'text-muted-foreground hover:text-foreground'
                }`}
              >
                <Send className="w-4 h-4" />
                New Report
              </button>
              <button
                onClick={() => setTab('history')}
                className={`flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium transition-colors ${
                  tab === 'history'
                    ? 'text-primary border-b-2 border-primary'
                    : 'text-muted-foreground hover:text-foreground'
                }`}
              >
                <History className="w-4 h-4" />
                My Reports
                {reports.length > 0 && (
                  <span className="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-muted text-muted-foreground">
                    {reports.length}
                  </span>
                )}
              </button>
            </div>

            {tab === 'new' ? (
              <form onSubmit={handleSubmit} className="p-5 space-y-4">
                {error && (
                  <div className="flex items-center gap-2 p-3 text-sm text-destructive bg-destructive/10 rounded-lg border border-destructive/20">
                    <AlertCircle className="w-4 h-4 shrink-0" />
                    {error}
                  </div>
                )}
                {success && (
                  <div className="flex items-center gap-2 p-3 text-sm text-success bg-success/10 rounded-lg border border-success/20">
                    <CheckCircle2 className="w-4 h-4 shrink-0" />
                    Thanks! Your report was submitted.
                  </div>
                )}

                <div>
                  <label className="block text-xs font-medium text-muted-foreground mb-1.5">
                    Title <span className="text-destructive">*</span>
                  </label>
                  <input
                    type="text"
                    required
                    value={form.title}
                    maxLength={255}
                    onChange={(e) => setForm((p) => ({ ...p, title: e.target.value }))}
                    placeholder="Short summary of the issue"
                    className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                  />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-xs font-medium text-muted-foreground mb-1.5">Category</label>
                    <select
                      value={form.category}
                      onChange={(e) => setForm((p) => ({ ...p, category: e.target.value }))}
                      className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                    >
                      {ISSUE_CATEGORIES.map((c) => (
                        <option key={c.value} value={c.value}>{c.label}</option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-muted-foreground mb-1.5">Priority</label>
                    <select
                      value={form.priority}
                      onChange={(e) => setForm((p) => ({ ...p, priority: e.target.value }))}
                      className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring"
                    >
                      {ISSUE_PRIORITIES.map((p) => (
                        <option key={p.value} value={p.value}>{p.label}</option>
                      ))}
                    </select>
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-medium text-muted-foreground mb-1.5">Description</label>
                  <textarea
                    rows={4}
                    value={form.description}
                    onChange={(e) => setForm((p) => ({ ...p, description: e.target.value }))}
                    placeholder="Describe what happened, what you expected, and the steps to reproduce..."
                    className="w-full rounded-lg border border-input bg-muted px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring resize-y"
                  />
                </div>

                <div>
                  <label className="block text-xs font-medium text-muted-foreground mb-1.5">
                    Screenshots (optional, up to 10)
                  </label>
                  <input
                    ref={fileInputRef}
                    type="file"
                    accept="image/*"
                    multiple
                    className="hidden"
                    onChange={(e) => {
                      addFiles(e.target.files);
                      e.target.value = '';
                    }}
                  />
                  <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="w-full flex items-center justify-center gap-2 px-3 py-4 border border-dashed border-border rounded-lg text-sm text-muted-foreground hover:border-primary hover:text-primary transition-colors"
                  >
                    <Upload className="w-4 h-4" />
                    Click to attach screenshots
                  </button>

                  {files.length > 0 && (
                    <div className="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-2">
                      {files.map((file, index) => (
                        <div key={index} className="relative group rounded-lg overflow-hidden border border-border">
                          <img
                            src={URL.createObjectURL(file)}
                            alt={file.name}
                            className="w-full h-20 object-cover"
                          />
                          <button
                            type="button"
                            onClick={() => removeFile(index)}
                            className="absolute top-1 right-1 p-1 rounded bg-black/60 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                          >
                            <Trash2 className="w-3 h-3" />
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>

                <div className="flex justify-end gap-2 pt-2">
                  <button
                    type="button"
                    onClick={() => setOpen(false)}
                    className="px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={submitting}
                    className="inline-flex items-center gap-2 px-5 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 disabled:opacity-50 transition-colors"
                  >
                    {submitting ? <Loader2 className="w-4 h-4 animate-spin" /> : <Send className="w-4 h-4" />}
                    {submitting ? 'Submitting...' : 'Submit Report'}
                  </button>
                </div>
              </form>
            ) : (
              <div className="p-5">
                <div className="flex items-center justify-between mb-3">
                  <p className="text-sm text-muted-foreground">Your submitted reports</p>
                  <button
                    onClick={loadReports}
                    className="inline-flex items-center gap-1.5 text-xs font-medium text-primary hover:text-primary/80 transition-colors"
                  >
                    <RefreshCw className={`w-3.5 h-3.5 ${loadingReports ? 'animate-spin' : ''}`} />
                    Refresh
                  </button>
                </div>

                {loadingReports ? (
                  <div className="flex items-center justify-center py-10">
                    <Loader2 className="w-5 h-5 animate-spin text-muted-foreground" />
                  </div>
                ) : reports.length === 0 ? (
                  <div className="flex flex-col items-center justify-center py-10 text-center">
                    <Bug className="w-8 h-8 text-muted-foreground/40 mb-2" />
                    <p className="text-sm text-muted-foreground">You have not reported any issues yet.</p>
                  </div>
                ) : (
                  <ul className="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                    {reports.map((report) => (
                      <li key={report.id} className="rounded-lg border border-border p-3">
                        <div className="flex items-start justify-between gap-2">
                          <p className="text-sm font-medium text-foreground">{report.title}</p>
                          <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded whitespace-nowrap ${STATUS_STYLES[report.status] || 'bg-muted text-muted-foreground'}`}>
                            {statusLabel(report.status)}
                          </span>
                        </div>
                        <div className="flex flex-wrap items-center gap-2 mt-2">
                          <span className={`text-[10px] font-semibold px-1.5 py-0.5 rounded ${PRIORITY_STYLES[report.priority] || 'bg-muted text-muted-foreground'}`}>
                            {report.priority}
                          </span>
                          <span className="text-[11px] text-muted-foreground">{categoryLabel(report.category)}</span>
                          <span className="text-[11px] text-muted-foreground ml-auto">
                            {new Date(report.created_at).toLocaleString()}
                          </span>
                        </div>
                        {report.screenshots?.length > 0 && (
                          <div className="flex items-center gap-1.5 mt-2 text-[11px] text-muted-foreground">
                            <ImageIcon className="w-3.5 h-3.5" />
                            {report.screenshots.length} screenshot{report.screenshots.length > 1 ? 's' : ''}
                          </div>
                        )}
                        {report.admin_notes && (
                          <p className="mt-2 text-xs text-muted-foreground border-t border-border pt-2">
                            <span className="font-medium text-foreground">Response: </span>
                            {report.admin_notes}
                          </p>
                        )}
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
