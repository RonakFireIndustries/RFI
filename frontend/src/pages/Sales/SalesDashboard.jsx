import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  Building2, TrendingUp, Calendar, CheckCircle, AlertCircle, 
  Clock, MapPin, ArrowRight, Plus 
} from 'lucide-react';
import { useSalesDashboardStore } from '../../store/salesDashboardStore';
import { format } from 'date-fns';

export default function SalesDashboard() {
  const navigate = useNavigate();
  const { stats, loading, fetchStats } = useSalesDashboardStore();

  useEffect(() => {
    fetchStats();
  }, [fetchStats]);

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" />
      </div>
    );
  }

  if (!stats) return null;

  const pipelineStages = ['Prospect', 'Follow-Up', 'Quotation Sent', 'Negotiation', 'Won', 'Lost'];
  const stageColors = {
    'Prospect': 'bg-gray-100 text-gray-700',
    'Follow-Up': 'bg-blue-100 text-blue-700',
    'Quotation Sent': 'bg-yellow-100 text-yellow-700',
    'Negotiation': 'bg-orange-100 text-orange-700',
    'Won': 'bg-green-100 text-green-700',
    'Lost': 'bg-red-100 text-red-700',
  };

  const pipelineMap = {};
  (stats.pipeline || []).forEach(p => {
    pipelineMap[p.stage] = { count: p.count, value: p.value };
  });

  const totalPipelineValue = (stats.pipeline || [])
    .filter(p => !['Won', 'Lost'].includes(p.stage))
    .reduce((sum, p) => sum + parseFloat(p.value || 0), 0);

  const wonValue = (stats.pipeline || [])
    .filter(p => p.stage === 'Won')
    .reduce((sum, p) => sum + parseFloat(p.value || 0), 0);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Sales Dashboard</h1>
          <p className="text-sm text-gray-500">Overview of your sales pipeline and activities</p>
        </div>
        <div className="flex gap-2">
          <button onClick={() => navigate('/dashboard/buildings/new')} className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
            <Plus className="w-4 h-4" /> New Building
          </button>
          <button onClick={() => navigate('/dashboard/opportunities/new')} className="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
            <Plus className="w-4 h-4" /> New Opportunity
          </button>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard icon={<Building2 className="w-5 h-5 text-red-600" />} label="Total Buildings" value={stats.total_buildings} />
        <StatCard icon={<TrendingUp className="w-5 h-5 text-blue-600" />} label="Active Opportunities" value={stats.total_opportunities} />
        <StatCard icon={<Calendar className="w-5 h-5 text-green-600" />} label="Total Site Visits" value={stats.total_site_visits} />
        <StatCard icon={<Clock className="w-5 h-5 text-yellow-600" />} label="Pending Follow-ups" value={stats.total_pending_follow_ups} />
      </div>

      {/* Value Summary */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div className="bg-white rounded-lg border p-4">
          <p className="text-sm text-gray-500">Active Pipeline Value</p>
          <p className="text-2xl font-bold text-gray-900">₹{totalPipelineValue.toLocaleString('en-IN')}</p>
        </div>
        <div className="bg-white rounded-lg border p-4">
          <p className="text-sm text-gray-500">Won Value</p>
          <p className="text-2xl font-bold text-green-600">₹{wonValue.toLocaleString('en-IN')}</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Pipeline */}
        <div className="bg-white rounded-lg border p-4">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Pipeline by Stage</h2>
          <div className="space-y-2">
            {pipelineStages.map(stage => {
              const data = pipelineMap[stage] || { count: 0, value: 0 };
              const maxValue = Math.max(...Object.values(pipelineMap).map(p => p.value || 0), 1);
              const barWidth = ((data.value || 0) / maxValue) * 100;
              return (
                <div key={stage} className="flex items-center gap-3">
                  <span className="text-xs font-medium w-28 text-gray-600 truncate">{stage}</span>
                  <div className="flex-1 bg-gray-100 rounded-full h-5 overflow-hidden">
                    <div className={`h-full rounded-full ${stageColors[stage] || 'bg-gray-200'} flex items-center px-2`} style={{ width: `${Math.max(barWidth, 5)}%` }}>
                      <span className="text-xs font-medium whitespace-nowrap">{data.count}</span>
                    </div>
                  </div>
                  <span className="text-xs text-gray-500 w-20 text-right">₹{(data.value || 0).toLocaleString('en-IN')}</span>
                </div>
              );
            })}
          </div>
        </div>

        {/* Upcoming Follow-ups */}
        <div className="bg-white rounded-lg border p-4">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Upcoming Follow-ups</h2>
            <button onClick={() => navigate('/dashboard/follow-ups')} className="text-sm text-red-600 hover:text-red-700 flex items-center gap-1">
              View All <ArrowRight className="w-3 h-3" />
            </button>
          </div>
          {(stats.upcoming_follow_ups || []).length === 0 ? (
            <p className="text-sm text-gray-500 text-center py-8">No upcoming follow-ups</p>
          ) : (
            <div className="space-y-3">
              {stats.upcoming_follow_ups.slice(0, 5).map(fu => (
                <div key={fu.id} className="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50">
                  <div className="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <AlertCircle className="w-5 h-5 text-yellow-600" />
                  </div>
                  <div className="flex-1 min-w-0">
                    <p className="text-sm font-medium text-gray-900 truncate">{fu.type}</p>
                    <p className="text-xs text-gray-500">{fu.building?.name || 'No building'}</p>
                  </div>
                  <span className="text-xs text-gray-500">{format(new Date(fu.reminder_date), 'dd MMM')}</span>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Recent Activity */}
        <div className="bg-white rounded-lg border p-4 lg:col-span-2">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
          {(stats.recent_activity || []).length === 0 ? (
            <p className="text-sm text-gray-500 text-center py-8">No recent activity</p>
          ) : (
            <div className="space-y-3">
              {stats.recent_activity.map(log => (
                <div key={log.id} className="flex items-start gap-3 p-2">
                  <div className="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2" />
                  <div className="flex-1">
                    <p className="text-sm text-gray-900">{log.description}</p>
                    <p className="text-xs text-gray-500">
                      {log.user?.name || 'System'} • {format(new Date(log.created_at), 'dd MMM HH:mm')}
                    </p>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function StatCard({ icon, label, value }) {
  return (
    <div className="bg-white rounded-lg border p-4">
      <div className="flex items-center gap-3">
        <div className="p-2 bg-gray-50 rounded-lg">{icon}</div>
        <div>
          <p className="text-sm text-gray-500">{label}</p>
          <p className="text-2xl font-bold text-gray-900">{value ?? 0}</p>
        </div>
      </div>
    </div>
  );
}
