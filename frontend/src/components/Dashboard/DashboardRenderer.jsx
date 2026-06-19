import { useState, useEffect } from 'react';
import { useBranchStore } from '../../store/branchStore';
import { dashboardService } from '../../services/dashboardService';
import DashboardCard from './DashboardCard';
import DashboardChart from './DashboardChart';
import DashboardQuickActions from './DashboardQuickActions';
import DashboardAlerts from './DashboardAlerts';
import DashboardWidgets from './DashboardWidgets';
import DashboardSkeleton from './DashboardSkeleton';
import DashboardEmptyState from './DashboardEmptyState';
import { Download, Plus } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

const DASHBOARD_TITLES = {
  admin: { title: 'Admin Dashboard', subtitle: 'Full system overview at a glance.' },
  executive: { title: 'Executive Dashboard', subtitle: 'High-level business performance metrics.' },
  hr: { title: 'HR Dashboard', subtitle: 'Workforce and people operations overview.' },
  finance: { title: 'Finance Dashboard', subtitle: 'Payroll, costs, and financial health.' },
  inventory: { title: 'Inventory Dashboard', subtitle: 'Stock levels, movements, and warehouse status.' },
  production: { title: 'Production Dashboard', subtitle: 'Site workforce and daily production metrics.' },
  sales: { title: 'Sales Dashboard', subtitle: 'Revenue, customers, and sales performance.' },
  it: { title: 'IT Dashboard', subtitle: 'System health, users, and security.' },
  employee: { title: 'My Dashboard', subtitle: 'Your personal attendance, leave, and payslips.' },
};

export default function DashboardRenderer() {
  const navigate = useNavigate();
  const activeBranchId = useBranchStore(state => state.activeBranchId);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetch = async () => {
      setLoading(true);
      try {
        const res = await dashboardService.getDashboard();
        setData(res?.data || res);
      } catch (error) {
        console.error('Failed to fetch dashboard data', error);
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [activeBranchId]);

  if (loading) return <DashboardSkeleton />;
  if (!data) return null;

  const dashboardType = data.dashboard_type || 'employee';
  const dashboardInfo = DASHBOARD_TITLES[dashboardType] || DASHBOARD_TITLES.employee;
  const { cards, charts, quick_actions, alerts, widgets } = data;
  const hasContent = cards?.length || charts?.length || quick_actions?.length || alerts?.length || widgets?.length;

  if (!hasContent) return <DashboardEmptyState dashboardType={dashboardType} />;

  return (
    <div className="space-y-6 pb-12">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">{dashboardInfo.title}</h1>
          <p className="text-sm text-gray-500">{dashboardInfo.subtitle}</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button className="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <Download className="w-4 h-4 mr-2" />
            Export Report
          </button>
          <button
            onClick={() => navigate('/dashboard/sales')}
            className="inline-flex items-center px-3 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700"
          >
            <Plus className="w-4 h-4 mr-2" />
            New Transaction
          </button>
        </div>
      </div>

      {cards?.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {cards.map((card) => (
            <DashboardCard key={card.key} card={card} />
          ))}
        </div>
      )}

      {alerts?.length > 0 && <DashboardAlerts alerts={alerts} />}

      {charts?.length > 0 && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {charts.map((chart) => (
            <DashboardChart key={chart.key} chart={chart} />
          ))}
        </div>
      )}

      {widgets?.length > 0 && <DashboardWidgets widgets={widgets} />}

      {quick_actions?.length > 0 && <DashboardQuickActions quickActions={quick_actions} />}

      <div className="text-center text-xs text-gray-400 font-medium tracking-wider mt-12 mb-4">
        &copy; 2024 NEXUS INTELLIGENCE SYSTEMS &bull; PLATFORM V4.2.0
      </div>
    </div>
  );
}
