import { useState, useEffect } from 'react';
import { useBranchStore } from '../../store/branchStore';
import { dashboardService } from '../../services/dashboardService';
import DashboardCard from './DashboardCard';
import DashboardChart from './DashboardChart';
import DashboardQuickActions from './DashboardQuickActions';
import DashboardAlerts from './DashboardAlerts';
import { Download, Plus } from 'lucide-react';
import { PermissionGuard } from '../Guards/PermissionGuard';
import { useNavigate } from 'react-router-dom';

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

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#1a56db]"></div>
      </div>
    );
  }

  if (!data) return null;

  const { cards, charts, quick_actions, alerts } = data;

  const numberWithCommas = (x) => {
    if (x === undefined || x === null) return '0';
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  };

  return (
    <div className="space-y-6 pb-12">
      <div className="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Overview</h1>
          <p className="text-sm text-gray-500">Welcome back. Here's what's happening today.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <PermissionGuard requiredPermission="report.export" fallback={
            <PermissionGuard requiredPermission="report.view" fallback={null}>
              <button className="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                <Download className="w-4 h-4 mr-2" />
                Export Report
              </button>
            </PermissionGuard>
          }>
            <button className="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
              <Download className="w-4 h-4 mr-2" />
              Export Report
            </button>
          </PermissionGuard>
          <PermissionGuard requiredPermission={['sales-order.create', 'invoice.create']}>
            <button
              onClick={() => navigate('/dashboard/sales')}
              className="inline-flex items-center px-3 py-2 bg-[#1a56db] text-white rounded-md text-sm font-medium hover:bg-blue-700"
            >
              <Plus className="w-4 h-4 mr-2" />
              New Transaction
            </button>
          </PermissionGuard>
        </div>
      </div>

      {cards && cards.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {cards.map((card) => (
            <DashboardCard key={card.key} card={card} />
          ))}
        </div>
      )}

      {alerts && alerts.length > 0 && (
        <DashboardAlerts alerts={alerts} />
      )}

      {charts && charts.length > 0 && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {charts.map((chart) => (
            <DashboardChart key={chart.key} chart={chart} />
          ))}
        </div>
      )}

      {quick_actions && quick_actions.length > 0 && (
        <DashboardQuickActions quickActions={quick_actions} />
      )}

      <div className="text-center text-xs text-gray-400 font-medium tracking-wider mt-12 mb-4">
        &copy; 2024 NEXUS INTELLIGENCE SYSTEMS &bull; PLATFORM V4.2.0
      </div>
    </div>
  );
}
