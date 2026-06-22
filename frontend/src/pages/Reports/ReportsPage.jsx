import { useState, useEffect, useCallback } from 'react';
import {
  Search, Plus, Download, Clock, Calendar, Filter, ChevronDown,
  TrendingUp, Package, DollarSign, Users, ShoppingBag, Wallet,
  FileText, AlertCircle, CheckCircle, XCircle, RefreshCw,
  BarChart3, PieChart, Activity, BookOpen, Bell, Share2,
  Settings, Trash2, Play, Pause, ChevronRight, ExternalLink,
  Grid, List as ListIcon, SlidersHorizontal, Loader2,
} from 'lucide-react';
import api from '../../services/api';
import { reportsService } from '../../services/reportsService';
import { useAuthStore } from '../../store/authStore';
import { useReportsStore } from '../../store/reportsStore';

const iconMap = {
  TrendingUp, Package, DollarSign, Users, Clock, Wallet, ShoppingBag, Calendar,
};

const categoryIcons = {
  sales: TrendingUp,
  inventory: Package,
  financial: DollarSign,
  employees: Users,
  attendance: Clock,
  payroll: Wallet,
  purchases: ShoppingBag,
  leaves: Calendar,
};

function SkeletonCard() {
  return (
    <div className="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
      <div className="flex items-start gap-3">
        <div className="w-10 h-10 rounded-lg bg-gray-200" />
        <div className="flex-1 space-y-2">
          <div className="h-4 bg-gray-200 rounded w-3/4" />
          <div className="h-3 bg-gray-200 rounded w-full" />
          <div className="h-3 bg-gray-200 rounded w-2/3" />
        </div>
      </div>
      <div className="mt-4 flex gap-2">
        <div className="h-8 bg-gray-200 rounded flex-1" />
        <div className="h-8 bg-gray-200 rounded w-16" />
      </div>
    </div>
  );
}

function SkeletonWidget() {
  return (
    <div className="bg-white rounded-xl border border-gray-200 p-5 animate-pulse">
      <div className="h-5 bg-gray-200 rounded w-1/3 mb-4" />
      <div className="space-y-3">
        <div className="h-12 bg-gray-200 rounded" />
        <div className="h-12 bg-gray-200 rounded" />
        <div className="h-12 bg-gray-200 rounded" />
      </div>
    </div>
  );
}

function EmptyState({ icon: Icon, title, description, action }) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        {Icon && <Icon className="w-8 h-8 text-gray-400" />}
      </div>
      <h3 className="text-lg font-semibold text-gray-900 mb-1">{title}</h3>
      <p className="text-sm text-gray-500 mb-4 max-w-sm">{description}</p>
      {action}
    </div>
  );
}

function StatusBadge({ status }) {
  const styles = {
    active: 'bg-green-50 text-green-700 border-green-200',
    inactive: 'bg-gray-50 text-gray-600 border-gray-200',
    draft: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    requested: 'bg-blue-50 text-blue-700 border-blue-200',
    approved: 'bg-green-50 text-green-700 border-green-200',
    issued: 'bg-purple-50 text-purple-700 border-purple-200',
    received: 'bg-blue-50 text-blue-700 border-blue-200',
    pending: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    processing: 'bg-blue-50 text-blue-700 border-blue-200',
    completed: 'bg-green-50 text-green-700 border-green-200',
    failed: 'bg-red-50 text-red-700 border-red-200',
    paused: 'bg-gray-50 text-gray-600 border-gray-200',
  };
  const cls = styles[status] || 'bg-gray-50 text-gray-600 border-gray-200';
  return (
    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ${cls}`}>
      {status}
    </span>
  );
}

export default function ReportsPage() {
  const user = useAuthStore(s => s.user);
  const roles = useAuthStore(s => s.roles);
  const permissions = useAuthStore(s => s.permissions);
  const {
    reports, categories, generations, schedules, stats, loading, error,
    fetchAll, fetchCategories, fetchReports, fetchGenerations, fetchSchedules,
    generateReport, deleteReport, toggleSchedule, deleteSchedule, fetchStats,
  } = useReportsStore();

  const [searchQuery, setSearchQuery] = useState('');
  const [activeCategory, setActiveCategory] = useState(null);
  const [viewMode, setViewMode] = useState('grid');
  const [dateRange, setDateRange] = useState('all');
  const [branches, setBranches] = useState([]);
  const [selectedBranch, setSelectedBranch] = useState('');
  const [showNewReportModal, setShowNewReportModal] = useState(false);
  const [showScheduleModal, setShowScheduleModal] = useState(false);
  const [selectedReport, setSelectedReport] = useState(null);
  const [generating, setGenerating] = useState(null);
  const [generateProgress, setGenerateProgress] = useState(null);
  const [sidebarOpen, setSidebarOpen] = useState(true);
  const [newReportForm, setNewReportForm] = useState({ name: '', description: '', category_id: '', status: 'active' });
  const [scheduleForm, setScheduleForm] = useState({ report_id: '', name: '', frequency: 'daily', format: 'pdf', recipients: '' });

  const canCreate = permissions?.includes('create_reports') || roles?.includes('Super Admin') || roles?.includes('Admin');
  const canExport = permissions?.includes('export_reports') || roles?.includes('Super Admin') || roles?.includes('Admin');
  const canSchedule = permissions?.includes('schedule_reports') || roles?.includes('Super Admin') || roles?.includes('Admin');

  useEffect(() => {
    fetchAll({ per_page: 50 });
    api.get('/sites?per_page=1000').then(res => {
      const data = res.data?.data || res.data || [];
      setBranches(Array.isArray(data) ? data : []);
    }).catch(() => {});
  }, [fetchAll]);

  const filteredReports = reports?.filter(r => {
    const matchesSearch = !searchQuery || r.name?.toLowerCase().includes(searchQuery.toLowerCase()) || r.description?.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = !activeCategory || r.category_id === activeCategory;
    return matchesSearch && matchesCategory;
  }) || [];

  const getDateFilter = () => {
    const now = new Date();
    const start = new Date();
    switch (dateRange) {
      case 'today': break;
      case 'week': start.setDate(now.getDate() - now.getDay()); break;
      case 'month': start.setDate(1); break;
      case 'quarter': start.setMonth(Math.floor(now.getMonth() / 3) * 3, 1); break;
      case 'year': start.setMonth(0, 1); break;
      default: return {};
    }
    return {
      start_date: start.toISOString().split('T')[0],
      end_date: now.toISOString().split('T')[0],
    };
  };

  const handleGenerate = async (report) => {
    if (!canExport) return;
    setGenerating(report.id);
    setGenerateProgress({ report: report.name, status: 'Generating...' });
    try {
      const dateFilter = getDateFilter();
      await generateReport(report.id, {
        ...dateFilter,
        branch_id: selectedBranch || undefined,
      });
      setGenerateProgress({ report: report.name, status: 'Completed', success: true });
      fetchStats();
      fetchGenerations({ per_page: 5 });
    } catch (err) {
      setGenerateProgress({ report: report.name, status: 'Failed', success: false });
    } finally {
      setGenerating(null);
      setTimeout(() => setGenerateProgress(null), 4000);
    }
  };

  const handleCreateReport = async (e) => {
    e.preventDefault();
    try {
      const payload = {
        ...newReportForm,
        slug: newReportForm.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
      };
      await useReportsStore.getState().createReport(payload);
      setShowNewReportModal(false);
      setNewReportForm({ name: '', description: '', category_id: '', status: 'active' });
      fetchAll({ per_page: 50 });
    } catch (err) {
      alert(err?.response?.data?.message || err.message);
    }
  };

  const handleCreateSchedule = async (e) => {
    e.preventDefault();
    try {
      await useReportsStore.getState().createSchedule(scheduleForm);
      setShowScheduleModal(false);
      setScheduleForm({ report_id: '', name: '', frequency: 'daily', format: 'pdf', recipients: '' });
      fetchSchedules();
    } catch (err) {
      alert(err?.response?.data?.message || err.message);
    }
  };

  const handleDownload = async (gen) => {
    if (!canExport) return;
    try {
      const blob = await reportsService.downloadGeneration(gen.id);
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = gen.file_name;
      a.click();
      URL.revokeObjectURL(url);
    } catch (err) {
      // file may not exist
    }
  };

  const currentCategory = categories?.find(c => c.id === activeCategory);

  return (
    <div className="flex h-[calc(100vh-4rem)] -m-4 md:-m-6">
      {/* Sidebar */}
      <aside className={`${sidebarOpen ? 'w-64' : 'w-0'} transition-all duration-300 bg-white border-r border-gray-200 overflow-hidden flex-shrink-0`}>
        <div className="p-4 border-b border-gray-100">
          <div className="flex items-center gap-2 text-[#1a56db] mb-1">
            <BarChart3 className="w-5 h-5" />
            <span className="font-bold text-sm uppercase tracking-wider">Categories</span>
          </div>
          <p className="text-xs text-gray-500">{categories?.length || 0} report categories</p>
        </div>
        <nav className="p-2 space-y-1 overflow-y-auto max-h-[calc(100vh-12rem)]">
          <button
            onClick={() => setActiveCategory(null)}
            className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
              !activeCategory ? 'bg-[#1a56db]/10 text-[#1a56db]' : 'text-gray-600 hover:bg-gray-50'
            }`}
          >
            <Grid className="w-4 h-4" />
            All Reports
            <span className="ml-auto text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{stats?.total_reports || reports?.length || 0}</span>
          </button>
          {categories?.map(cat => {
            const Icon = categoryIcons[cat.slug] || FileText;
            return (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors ${
                  activeCategory === cat.id ? 'bg-[#1a56db]/10 text-[#1a56db]' : 'text-gray-600 hover:bg-gray-50'
                }`}
              >
                <Icon className="w-4 h-4" />
                {cat.name}
                <span className="ml-auto text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{cat.reports_count || 0}</span>
              </button>
            );
          })}
        </nav>
      </aside>

      {/* Main Content */}
      <div className="flex-1 overflow-y-auto">
        <div className="p-6 max-w-7xl mx-auto space-y-6">
          {/* Header */}
          <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div className="flex items-center gap-3">
              <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 rounded-lg hover:bg-gray-100 text-gray-500">
                <SlidersHorizontal className="w-5 h-5" />
              </button>
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Reports</h1>
                <p className="text-sm text-gray-500 mt-0.5">Generate, schedule, and manage business reports</p>
              </div>
            </div>
            <div className="flex items-center gap-3 flex-wrap">
              {canCreate && (
                <button onClick={() => setShowNewReportModal(true)} className="inline-flex items-center px-4 py-2 bg-[#1a56db] hover:bg-[#1e40af] text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                  <Plus className="w-4 h-4 mr-2" /> New Report
                </button>
              )}
            </div>
          </div>

          {/* Search & Filters Bar */}
          <div className="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                placeholder="Search reports..."
                value={searchQuery}
                onChange={e => setSearchQuery(e.target.value)}
                className="w-full pl-9 pr-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-[#1a56db] focus:border-[#1a56db]"
              />
            </div>
            <div className="flex gap-2 items-center">
              <select value={dateRange} onChange={e => setDateRange(e.target.value)} className="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="quarter">This Quarter</option>
                <option value="year">This Year</option>
              </select>
              <select value={selectedBranch} onChange={e => setSelectedBranch(e.target.value)} className="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white min-w-[140px]">
                <option value="">All Branches</option>
                {branches.map(b => <option key={b.id} value={b.id}>{b.name}</option>)}
              </select>
              <div className="flex border border-gray-300 rounded-lg overflow-hidden">
                <button onClick={() => setViewMode('grid')} className={`p-2 ${viewMode === 'grid' ? 'bg-gray-100 text-[#1a56db]' : 'bg-white text-gray-400'}`}>
                  <Grid className="w-4 h-4" />
                </button>
                <button onClick={() => setViewMode('list')} className={`p-2 ${viewMode === 'list' ? 'bg-gray-100 text-[#1a56db]' : 'bg-white text-gray-400'}`}>
                  <ListIcon className="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          {/* Category Tabs */}
          <div className="flex gap-2 overflow-x-auto pb-2 -mx-1 px-1">
            <button
              onClick={() => setActiveCategory(null)}
              className={`whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                !activeCategory ? 'bg-[#1a56db] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
              }`}
            >
              All Reports
            </button>
            {categories?.map(cat => {
              const Icon = categoryIcons[cat.slug] || FileText;
              return (
                <button
                  key={cat.id}
                  onClick={() => setActiveCategory(cat.id)}
                  className={`whitespace-nowrap px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 ${
                    activeCategory === cat.id ? 'bg-[#1a56db] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'
                  }`}
                >
                  <Icon className="w-4 h-4" />
                  {cat.name}
                </button>
              );
            })}
          </div>

          {/* Report Cards / List */}
          {loading ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {[1,2,3,4,5,6].map(i => <SkeletonCard key={i} />)}
            </div>
          ) : error ? (
            <div className="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
              <AlertCircle className="w-10 h-10 text-red-400 mx-auto mb-3" />
              <h3 className="text-lg font-semibold text-red-700 mb-1">Failed to Load Reports</h3>
              <p className="text-sm text-red-600 mb-3">{error}</p>
              <button onClick={() => fetchAll({ per_page: 50 })} className="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                <RefreshCw className="w-4 h-4 mr-2" /> Retry
              </button>
            </div>
          ) : filteredReports.length === 0 ? (
            <EmptyState
              icon={FileText}
              title={searchQuery || activeCategory ? "No matching reports" : "No reports available"}
              description={searchQuery || activeCategory ? "Try adjusting your search or filter criteria" : "Get started by creating your first report"}
              action={canCreate && !searchQuery && !activeCategory ? (
                <button onClick={() => setShowNewReportModal(true)} className="inline-flex items-center px-4 py-2 bg-[#1a56db] text-white rounded-lg text-sm font-medium hover:bg-[#1e40af]">
                  <Plus className="w-4 h-4 mr-2" /> Create Report
                </button>
              ) : null}
            />
          ) : viewMode === 'grid' ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {filteredReports.map(report => {
                const cat = categories?.find(c => c.id === report.category_id);
                const Icon = categoryIcons[cat?.slug] || FileText;
                return (
                  <div key={report.id} className="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow group">
                    <div className="flex items-start gap-3">
                      <div className={`w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${
                        cat?.slug === 'sales' ? 'bg-green-50 text-green-600' :
                        cat?.slug === 'inventory' ? 'bg-blue-50 text-blue-600' :
                        cat?.slug === 'financial' ? 'bg-yellow-50 text-yellow-600' :
                        cat?.slug === 'employees' ? 'bg-purple-50 text-purple-600' :
                        cat?.slug === 'attendance' ? 'bg-orange-50 text-orange-600' :
                        'bg-gray-50 text-gray-600'
                      }`}>
                        <Icon className="w-5 h-5" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between gap-2">
                          <h3 className="font-semibold text-gray-900 text-sm leading-tight truncate">{report.name}</h3>
                          <StatusBadge status={report.status} />
                        </div>
                        <p className="text-xs text-gray-500 mt-1 line-clamp-2">{report.description || 'No description'}</p>
                      </div>
                    </div>
                    <div className="mt-3 flex items-center gap-3 text-xs text-gray-400">
                      {report.last_generated_at && (
                        <span className="flex items-center gap-1">
                          <Clock className="w-3 h-3" />
                          {new Date(report.last_generated_at).toLocaleDateString()}
                        </span>
                      )}
                      {report.generations_count > 0 && (
                        <span className="flex items-center gap-1">
                          <Download className="w-3 h-3" />
                          {report.generations_count} exports
                        </span>
                      )}
                    </div>
                    <div className="mt-4 flex gap-2">
                      {canExport && (
                        <button
                          onClick={() => handleGenerate(report)}
                          disabled={generating === report.id}
                          className="flex-1 inline-flex items-center justify-center px-3 py-2 bg-[#1a56db]/10 text-[#1a56db] rounded-lg text-xs font-semibold hover:bg-[#1a56db]/20 transition-colors disabled:opacity-50"
                        >
                          {generating === report.id ? (
                            <><Loader2 className="w-3 h-3 mr-1 animate-spin" /> Generating</>
                          ) : (
                            <><Download className="w-3 h-3 mr-1" /> Generate</>
                          )}
                        </button>
                      )}
                      {report.route && (
                        <a
                          href={report.route}
                          className="inline-flex items-center justify-center px-3 py-2 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors"
                        >
                          <ExternalLink className="w-3 h-3 mr-1" /> View
                        </a>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          ) : (
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Report Name</th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th className="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Generated</th>
                    <th className="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                  {filteredReports.map(report => {
                    const cat = categories?.find(c => c.id === report.category_id);
                    return (
                      <tr key={report.id} className="hover:bg-gray-50 transition-colors">
                        <td className="px-6 py-4">
                          <div className="font-medium text-gray-900 text-sm">{report.name}</div>
                          <div className="text-xs text-gray-500">{report.description || '-'}</div>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-600">{cat?.name || '-'}</td>
                        <td className="px-6 py-4"><StatusBadge status={report.status} /></td>
                        <td className="px-6 py-4 text-sm text-gray-500">{report.last_generated_at ? new Date(report.last_generated_at).toLocaleDateString() : 'Never'}</td>
                        <td className="px-6 py-4 text-right">
                          {canExport && (
                            <button onClick={() => handleGenerate(report)} disabled={generating === report.id} className="text-[#1a56db] hover:text-[#1e40af] text-sm font-medium mr-3 disabled:opacity-50">
                              {generating === report.id ? 'Generating...' : 'Generate'}
                            </button>
                          )}
                          {canCreate && (
                            <button onClick={() => { if (confirm('Delete this report?')) deleteReport(report.id); }} className="text-red-400 hover:text-red-600 text-sm">
                              <Trash2 className="w-4 h-4 inline" />
                            </button>
                          )}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}

          {/* Widgets Grid */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Recent Generations */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
                  <Download className="w-4 h-4 text-gray-400" />
                  Recent Generations
                </h3>
                {generations?.length > 0 && (
                  <span className="text-xs text-gray-400">{generations.length} exports</span>
                )}
              </div>
              <div className="divide-y divide-gray-100">
                {loading ? (
                  <div className="p-5 space-y-3">
                    {[1,2,3].map(i => <div key={i} className="h-12 bg-gray-100 rounded animate-pulse" />)}
                  </div>
                ) : !generations || generations.length === 0 ? (
                  <div className="p-5 text-center text-sm text-gray-500 py-8">
                    <FileText className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    No generated reports found.
                  </div>
                ) : (
                  generations.slice(0, 5).map(gen => (
                    <div key={gen.id} className="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                      <div className="flex items-center gap-3 min-w-0 flex-1">
                        <div className={`w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${
                          gen.status === 'completed' ? 'bg-green-50' : gen.status === 'failed' ? 'bg-red-50' : 'bg-blue-50'
                        }`}>
                          {gen.status === 'completed' ? <CheckCircle className="w-4 h-4 text-green-600" /> :
                           gen.status === 'failed' ? <XCircle className="w-4 h-4 text-red-600" /> :
                           <Loader2 className="w-4 h-4 text-blue-600 animate-spin" />}
                        </div>
                        <div className="min-w-0">
                          <p className="text-sm font-medium text-gray-900 truncate">{gen.file_name}</p>
                          <p className="text-xs text-gray-500">
                            {gen.generator || 'System'} &middot; {gen.generated_at ? new Date(gen.generated_at).toLocaleDateString() : '-'}
                            {gen.file_size_for_humans && ` · ${gen.file_size_for_humans}`}
                          </p>
                        </div>
                      </div>
                      {canExport && gen.status === 'completed' && (
                        <button
                          onClick={() => handleDownload(gen)}
                          className="ml-2 p-2 text-gray-400 hover:text-[#1a56db] hover:bg-blue-50 rounded-lg transition-colors"
                          title="Download"
                        >
                          <Download className="w-4 h-4" />
                        </button>
                      )}
                    </div>
                  ))
                )}
              </div>
            </div>

            {/* Active Schedules */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div className="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
                  <Clock className="w-4 h-4 text-gray-400" />
                  Active Schedules
                </h3>
                {canSchedule && (
                  <button onClick={() => setShowScheduleModal(true)} className="text-xs text-[#1a56db] font-medium hover:underline">
                    + New
                  </button>
                )}
              </div>
              <div className="divide-y divide-gray-100">
                {loading ? (
                  <div className="p-5 space-y-3">
                    {[1,2].map(i => <div key={i} className="h-14 bg-gray-100 rounded animate-pulse" />)}
                  </div>
                ) : !schedules || schedules.length === 0 ? (
                  <div className="p-5 text-center text-sm text-gray-500 py-8">
                    <Calendar className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                    No active schedules found.
                  </div>
                ) : (
                  schedules.slice(0, 5).map(sch => (
                    <div key={sch.id} className="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                      <div className="min-w-0 flex-1">
                        <p className="text-sm font-medium text-gray-900 truncate">{sch.name}</p>
                        <p className="text-xs text-gray-500">
                          {sch.frequency} · {sch.next_run_at ? `Next: ${new Date(sch.next_run_at).toLocaleDateString()}` : 'No schedule'}
                          {sch.creator && ` · ${sch.creator}`}
                        </p>
                      </div>
                      <div className="flex items-center gap-2 ml-2">
                        <StatusBadge status={sch.status} />
                        {canSchedule && (
                          <>
                            <button
                              onClick={() => toggleSchedule(sch.id)}
                              className="p-1.5 text-gray-400 hover:text-gray-600 rounded hover:bg-gray-100"
                              title={sch.status === 'active' ? 'Pause' : 'Activate'}
                            >
                              {sch.status === 'active' ? <Pause className="w-3.5 h-3.5" /> : <Play className="w-3.5 h-3.5" />}
                            </button>
                            <button
                              onClick={() => { if (confirm('Delete this schedule?')) deleteSchedule(sch.id); }}
                              className="p-1.5 text-gray-400 hover:text-red-500 rounded hover:bg-red-50"
                              title="Delete"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </>
                        )}
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>

            {/* Learning Center */}
            <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
              <div className="px-5 py-4 border-b border-gray-100">
                <h3 className="font-semibold text-gray-900 text-sm flex items-center gap-2">
                  <BookOpen className="w-4 h-4 text-gray-400" />
                  Learning Center
                </h3>
              </div>
              <div className="p-5 space-y-4">
                <a href="#" className="block p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                  <div className="flex items-start gap-3">
                    <BarChart3 className="w-5 h-5 text-[#1a56db] mt-0.5" />
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Getting Started with Reports</p>
                      <p className="text-xs text-gray-600 mt-0.5">Learn how to create, schedule, and export reports</p>
                    </div>
                  </div>
                </a>
                <a href="#" className="block p-3 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                  <div className="flex items-start gap-3">
                    <Activity className="w-5 h-5 text-purple-600 mt-0.5" />
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Understanding Report Categories</p>
                      <p className="text-xs text-gray-600 mt-0.5">Explore different report types and their use cases</p>
                    </div>
                  </div>
                </a>
                <a href="#" className="block p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                  <div className="flex items-start gap-3">
                    <Share2 className="w-5 h-5 text-green-600 mt-0.5" />
                    <div>
                      <p className="text-sm font-semibold text-gray-900">Scheduling & Sharing Reports</p>
                      <p className="text-xs text-gray-600 mt-0.5">Set up automated report delivery to your team</p>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Export Progress Notification */}
      {generateProgress && (
        <div className={`fixed bottom-6 right-6 z-50 px-5 py-4 rounded-xl shadow-lg border transition-all ${
          generateProgress.success !== undefined
            ? generateProgress.success
              ? 'bg-green-50 border-green-200'
              : 'bg-red-50 border-red-200'
            : 'bg-blue-50 border-blue-200'
        }`}>
          <div className="flex items-center gap-3">
            {generateProgress.success === undefined ? (
              <Loader2 className="w-5 h-5 text-blue-600 animate-spin" />
            ) : generateProgress.success ? (
              <CheckCircle className="w-5 h-5 text-green-600" />
            ) : (
              <XCircle className="w-5 h-5 text-red-600" />
            )}
            <div>
              <p className="text-sm font-medium text-gray-900">{generateProgress.report}</p>
              <p className="text-xs text-gray-500">{generateProgress.status}</p>
            </div>
          </div>
        </div>
      )}

      {/* Floating Action Button */}
      {canCreate && (
        <button
          onClick={() => setShowNewReportModal(true)}
          className="fixed bottom-6 left-6 z-50 w-12 h-12 bg-[#1a56db] hover:bg-[#1e40af] text-white rounded-full shadow-lg flex items-center justify-center transition-colors"
          title="Create Report"
        >
          <Plus className="w-6 h-6" />
        </button>
      )}

      {/* New Report Modal */}
      {showNewReportModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" onClick={() => setShowNewReportModal(false)}>
          <div className="w-full max-w-lg bg-white rounded-xl shadow-xl" onClick={e => e.stopPropagation()}>
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-semibold text-gray-900">Create New Report</h2>
              <button onClick={() => setShowNewReportModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form onSubmit={handleCreateReport} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Report Name *</label>
                <input type="text" required value={newReportForm.name} onChange={e => setNewReportForm({...newReportForm, name: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="e.g., Monthly Sales Summary" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select required value={newReportForm.category_id} onChange={e => setNewReportForm({...newReportForm, category_id: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="">Select category...</option>
                  {categories?.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea value={newReportForm.description} onChange={e => setNewReportForm({...newReportForm, description: e.target.value})} rows={3} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="Brief description of this report" />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select value={newReportForm.status} onChange={e => setNewReportForm({...newReportForm, status: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="active">Active</option>
                  <option value="draft">Draft</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div className="flex justify-end gap-3 pt-2">
                <button type="button" onClick={() => setShowNewReportModal(false)} className="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-[#1a56db] text-white rounded-lg text-sm font-semibold hover:bg-[#1e40af]">Create Report</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* New Schedule Modal */}
      {showScheduleModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4" onClick={() => setShowScheduleModal(false)}>
          <div className="w-full max-w-lg bg-white rounded-xl shadow-xl" onClick={e => e.stopPropagation()}>
            <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
              <h2 className="text-lg font-semibold text-gray-900">New Schedule</h2>
              <button onClick={() => setShowScheduleModal(false)} className="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <form onSubmit={handleCreateSchedule} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Report *</label>
                <select required value={scheduleForm.report_id} onChange={e => setScheduleForm({...scheduleForm, report_id: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                  <option value="">Select report...</option>
                  {reports?.filter(r => r.status === 'active').map(r => <option key={r.id} value={r.id}>{r.name}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Schedule Name *</label>
                <input type="text" required value={scheduleForm.name} onChange={e => setScheduleForm({...scheduleForm, name: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="e.g., Weekly Sales Report" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                  <select value={scheduleForm.frequency} onChange={e => setScheduleForm({...scheduleForm, frequency: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="yearly">Yearly</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Format</label>
                  <select value={scheduleForm.format} onChange={e => setScheduleForm({...scheduleForm, format: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]">
                    <option value="pdf">PDF</option>
                    <option value="csv">CSV</option>
                    <option value="xlsx">Excel</option>
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Email Recipients</label>
                <input type="text" value={scheduleForm.recipients} onChange={e => setScheduleForm({...scheduleForm, recipients: e.target.value})} className="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-[#1a56db] focus:border-[#1a56db]" placeholder="Comma-separated email addresses" />
              </div>
              <div className="flex justify-end gap-3 pt-2">
                <button type="button" onClick={() => setShowScheduleModal(false)} className="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit" className="px-4 py-2 bg-[#1a56db] text-white rounded-lg text-sm font-semibold hover:bg-[#1e40af]">Create Schedule</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
