import { LayoutDashboard } from 'lucide-react';

export default function DashboardEmptyState({ dashboardType }) {
  return (
    <div className="flex flex-col items-center justify-center py-20 text-center">
      <div className="rounded-full bg-muted p-6 mb-4">
        <LayoutDashboard className="h-12 w-12 text-muted-foreground" />
      </div>
      <h3 className="text-lg font-semibold text-foreground mb-1">No dashboard data</h3>
      <p className="text-sm text-muted-foreground max-w-sm">
        No widgets are configured for your {dashboardType ? `"${dashboardType}"` : ''} dashboard yet. Contact an administrator.
      </p>
    </div>
  );
}
