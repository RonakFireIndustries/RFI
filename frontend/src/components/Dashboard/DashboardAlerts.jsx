import { useNavigate } from 'react-router-dom';
import { AlertTriangle, FileWarning, Clock, UserX, Bell, CalendarClock, AlertCircle, Info, ChevronRight } from 'lucide-react';

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
  const navigate = useNavigate();

  if (!alerts || alerts.length === 0) return null;

  return (
    <div className="space-y-3">
      <h2 className="text-lg font-bold text-gray-900">Alerts</h2>
      <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
        {alerts.map((alert) => {
          const style = severityStyles[alert.severity] || severityStyles.info;
          const Icon = iconMap[alert.icon] || Bell;
          const target = alert.link && alert.link !== '#' ? alert.link : null;

          const open = () => { if (target) navigate(target); };
          const onKeyDown = (event) => {
            if (!target) return;
            if (event.key === 'Enter' || event.key === ' ') {
              event.preventDefault();
              open();
            }
          };

          return (
            <div
              key={alert.key}
              onClick={open}
              onKeyDown={onKeyDown}
              role={target ? 'link' : undefined}
              tabIndex={target ? 0 : undefined}
              aria-label={target ? `Go to ${alert.value}` : undefined}
              className={`rounded-xl border p-4 flex items-start gap-3 ${style.bg} ${target ? 'cursor-pointer hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 transition-all' : ''}`}
            >
              <div className={`shrink-0 mt-0.5 ${style.icon}`}>
                <Icon className="w-5 h-5" />
              </div>
              <div className="min-w-0 flex-1">
                <p className={`text-sm font-semibold ${style.text}`}>{alert.value}</p>
                {alert.subtitle && (
                  <p className={`text-xs mt-0.5 ${style.subtitle}`}>{alert.subtitle}</p>
                )}
              </div>
              {target && <ChevronRight className="w-4 h-4 mt-0.5 shrink-0 opacity-50" />}
            </div>
          );
        })}
      </div>
    </div>
  );
}
