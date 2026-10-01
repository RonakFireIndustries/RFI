import { useState, useEffect } from 'react';
import { dashboardService } from '../../services/dashboardService';
import DashboardCard from './DashboardCard';
import DashboardChart from './DashboardChart';
import DashboardQuickActions from './DashboardQuickActions';
import DashboardAlerts from './DashboardAlerts';
import DashboardWidgets from './DashboardWidgets';
import DashboardSkeleton from './DashboardSkeleton';
import DashboardEmptyState from './DashboardEmptyState';

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
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetch = async () => {
      setLoading(true);
      setError(null);
      try {
        const res = await dashboardService.getDashboard();
        setData(res?.data || res);
      } catch (err) {
        console.error('Failed to fetch dashboard data', err);
        setError(err?.response?.data?.message || err?.message || 'Could not load your dashboard.');
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, []);

  if (loading) return <DashboardSkeleton />;

  if (error) {
    return (
      <div className="rounded-xl border border-destructive/30 bg-card p-6">
        <h1 className="text-lg font-bold text-foreground">Dashboard unavailable</h1>
        <p className="mt-1 text-sm text-muted-foreground">{error}</p>
      </div>
    );
  }

  if (!data) return <DashboardEmptyState dashboardType="employee" />;

  const dashboardType = data.dashboard_type || 'employee';
  const dashboardInfo = DASHBOARD_TITLES[dashboardType] || DASHBOARD_TITLES.employee;
  const { cards, charts, quick_actions, alerts, widgets } = data;
  const hasContent = cards?.length || charts?.length || quick_actions?.length || alerts?.length || widgets?.length;

  if (!hasContent) return <DashboardEmptyState dashboardType={dashboardType} />;

  return (
    <div className="space-y-6 pb-12">
      <div>
        <h1 className="text-2xl font-bold text-foreground">{dashboardInfo.title}</h1>
        <p className="text-sm text-muted-foreground">{dashboardInfo.subtitle}</p>
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

      <div className="text-center text-xs text-muted-foreground font-medium tracking-wider mt-12 mb-4">
        &copy; 2024 NEXUS INTELLIGENCE SYSTEMS &bull; PLATFORM V4.2.0
      </div>
    </div>
  );
}
