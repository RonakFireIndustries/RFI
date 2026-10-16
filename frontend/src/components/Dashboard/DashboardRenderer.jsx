import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { dashboardService } from '../../services/dashboardService';
import DashboardCard from './DashboardCard';
import DashboardChart from './DashboardChart';
import DashboardQuickActions from './DashboardQuickActions';
import DashboardAlerts from './DashboardAlerts';
import DashboardWidgets from './DashboardWidgets';
import DashboardSkeleton from './DashboardSkeleton';
import DashboardEmptyState from './DashboardEmptyState';
import MonthlyAttendanceReport from './MonthlyAttendanceReport';
import MyPendingLeaves from './MyPendingLeaves';
import LeaveApprovalsPanel from './LeaveApprovalsPanel';
import { usePermission } from '../../hooks/usePermission';

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

/*
 * Titles used on the role-scoped route (/dashboard). A user with no role
 * dashboard resolves to the 'employee' type there as well, but that page is
 * the "Dashboard" entry point, not the personal one - labelling it "My
 * Dashboard" made pressing Dashboard look like a redirect to My Dashboard.
 */
const ROLE_TITLES = {
  ...DASHBOARD_TITLES,
  employee: { title: 'Dashboard', subtitle: 'Your personal attendance, leave, and payslips.' },
};

export default function DashboardRenderer({ scope = 'role' }) {
  const navigate = useNavigate();
  const { canModule } = usePermission();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const isPersonal = scope === 'my';

  useEffect(() => {
    const fetch = async () => {
      setLoading(true);
      setError(null);
      try {
        const res = isPersonal
          ? await dashboardService.getMyDashboard()
          : await dashboardService.getDashboard();
        setData(res?.data || res);
      } catch (err) {
        // No permission for the assigned role dashboard: send the user to
        // the personal dashboard they always have access to.
        if (!isPersonal && err?.response?.status === 403) {
          navigate('/dashboard/my-dashboard', { replace: true });
          return;
        }
        console.error('Failed to fetch dashboard data', err);
        setError(err?.response?.data?.message || err?.message || 'Could not load your dashboard.');
      } finally {
        setLoading(false);
      }
    };
    fetch();
  }, [isPersonal, navigate]);

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
  const titleMap = isPersonal ? DASHBOARD_TITLES : ROLE_TITLES;
  const dashboardInfo = titleMap[dashboardType] || titleMap.employee;
  const { cards, charts, quick_actions, alerts, widgets } = data;
  const hasContent = cards?.length || charts?.length || quick_actions?.length || alerts?.length || widgets?.length;
  const isEmployee = dashboardType === 'employee';
  const showMonthlyReport = isPersonal || isEmployee;

  // Reviewers get an actionable approve/reject panel on their role dashboard.
  // The personal dashboard already carries the read-only "Pending Leaves" list.
  const canReviewLeaves = !isPersonal
    && (canModule('leaves', 'approve') || canModule('leaves', 'reject'));

  if (!hasContent && !showMonthlyReport && !canReviewLeaves) return <DashboardEmptyState dashboardType={dashboardType} />;

  return (
    <div className="space-y-6 pb-12">
      <div>
        <h1 className="text-2xl font-bold text-foreground">{dashboardInfo.title}</h1>
        <p className="text-sm text-muted-foreground">{dashboardInfo.subtitle}</p>
      </div>

      {canReviewLeaves && <LeaveApprovalsPanel />}

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

      {showMonthlyReport && <MyPendingLeaves />}

      {showMonthlyReport && <MonthlyAttendanceReport />}

      {quick_actions?.length > 0 && <DashboardQuickActions quickActions={quick_actions} />}

      <div className="text-center text-xs text-muted-foreground font-medium tracking-wider mt-12 mb-4">
        &copy; {new Date().getFullYear()} Copywrited By Ronak Fire Industries &bull; PLATFORM V4.2.0
        <a href="https://prasad-droid.netlify.app" target="_blank" rel="noopener noreferrer" className="ml-1 underline hidden">Develpoed By Prasad Kalvikatti </a>
      </div>
    </div>
  );
}
