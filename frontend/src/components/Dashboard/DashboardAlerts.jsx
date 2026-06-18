import { AlertTriangle, FileWarning, Clock, UserX, Bell, CalendarClock, AlertCircle, Info } from 'lucide-react';

const iconMap = {
  AlertTriangle, FileWarning, Clock, UserX, Bell, CalendarClock, AlertCircle, Info,
};

const severityStyles = {
  critical: {
    bg: 'bg-red-50 border-red-200',
    icon: 'text-red-500',
    text: 'text-red-800',
    subtitle: 'text-red-600',
  },
  warning: {
    bg: 'bg-yellow-50 border-yellow-200',
    icon: 'text-yellow-500',
    text: 'text-yellow-800',
    subtitle: 'text-yellow-700',
  },
  info: {
    bg: 'bg-blue-50 border-blue-200',
    icon: 'text-blue-500',
    text: 'text-blue-800',
    subtitle: 'text-blue-600',
  },
};

export default function DashboardAlerts({ alerts }) {
  if (!alerts || alerts.length === 0) return null;

  return (
    <div className="space-y-3">
      <h2 className="text-lg font-bold text-gray-900">Alerts</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        {alerts.map((alert) => {
          const style = severityStyles[alert.severity] || severityStyles.info;
          const Icon = iconMap[alert.icon] || Bell;
          return (
            <div
              key={alert.key}
              className={`rounded-xl border p-4 flex items-start gap-3 ${style.bg}`}
            >
              <div className={`shrink-0 mt-0.5 ${style.icon}`}>
                <Icon className="w-5 h-5" />
              </div>
              <div className="min-w-0">
                <p className={`text-sm font-semibold ${style.text}`}>{alert.value}</p>
                {alert.subtitle && (
                  <p className={`text-xs mt-0.5 ${style.subtitle}`}>{alert.subtitle}</p>
                )}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}
