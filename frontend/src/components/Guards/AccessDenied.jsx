import { ShieldAlert } from 'lucide-react';

/**
 * In-place "no access" panel used by the route-level permission guards.
 *
 * It deliberately renders where the URL already is instead of redirecting to
 * /dashboard. A silent bounce is exactly what made "Dashboard" look like it
 * was redirecting everyone to "My Dashboard" - the guard must decline in
 * place, the same way DashboardController::index() degrades in place rather
 * than 403-ing.
 *
 *   <PermissionGuard permission="payroll.view"
 *                   fallback={<AccessDenied title="Payroll Dashboard"
 *                                            permission="payroll.view" />}>
 *     <PayrollDashboardPage />
 *   </PermissionGuard>
 */
export default function AccessDenied({ title = 'No access', permission, hint }) {
  return (
    <div className="rounded-xl border border-destructive/30 bg-card p-8 text-center">
      <ShieldAlert className="mx-auto h-10 w-10 text-destructive" aria-hidden="true" />
      <h2 className="mt-3 text-lg font-semibold text-foreground">{title}</h2>
      <p className="mt-2 text-sm text-muted-foreground">
        {hint || 'This dashboard is not available to your account.'}
      </p>
      {permission && (
        <p className="mt-3 text-xs text-muted-foreground">
          Requires <code className="rounded bg-muted px-1.5 py-0.5 font-mono">{permission}</code>.
          Ask an administrator to grant it from Access Control.
        </p>
      )}
    </div>
  );
}
