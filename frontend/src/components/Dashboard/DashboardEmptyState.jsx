import { LayoutDashboard } from 'lucide-react';

export default function DashboardEmptyState({ dashboardType }) {
  return (
    <div className="flex flex-col items-center justify-center py-20 text-center">
      <div className="rounded-full bg-gray-100 p-6 mb-4">
        <LayoutDashboard className="h-12 w-12 text-gray-400" />
      </div>
      <h3 className="text-lg font-semibold text-gray-900 mb-1">No dashboard data</h3>
      <p className="text-sm text-gray-500 max-w-sm">
        No widgets are configured for your {dashboardType ? `"${dashboardType}"` : ''} dashboard yet. Contact an administrator.
      </p>
    </div>
  );
}
