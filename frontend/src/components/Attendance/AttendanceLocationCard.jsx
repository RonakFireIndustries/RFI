import React from 'react';
import { MapPin, Crosshair, Navigation, AlertTriangle, CheckCircle, Signal } from 'lucide-react';

const statusConfig = {
  inside: {
    color: 'green',
    bg: 'bg-green-50 border-green-200',
    icon: CheckCircle,
    iconColor: 'text-green-600',
    text: 'text-green-800',
    label: 'Inside Radius',
  },
  boundary: {
    color: 'orange',
    bg: 'bg-orange-50 border-orange-200',
    icon: AlertTriangle,
    iconColor: 'text-orange-600',
    text: 'text-orange-800',
    label: 'Near Boundary',
  },
  outside: {
    color: 'red',
    bg: 'bg-red-50 border-red-200',
    icon: Navigation,
    iconColor: 'text-red-600',
    text: 'text-red-800',
    label: 'Outside Radius',
  },
  loading: {
    color: 'gray',
    bg: 'bg-gray-50 border-gray-200',
    icon: Signal,
    iconColor: 'text-gray-400',
    text: 'text-gray-500',
    label: 'Acquiring GPS...',
  },
};

export default function AttendanceLocationCard({
  position,
  gpsError,
  gpsLoading,
  site,
  distance,
  allowedRadius,
}) {
  if (gpsLoading || (!position && !gpsError)) {
    const cfg = statusConfig.loading;
    const Icon = cfg.icon;
    return (
      <div className={`rounded-xl border p-5 ${cfg.bg}`}>
        <div className="flex items-center gap-3">
          <div className="animate-pulse"><Icon className={`w-6 h-6 ${cfg.iconColor}`} /></div>
          <div>
            <p className={`text-sm font-semibold ${cfg.text}`}>{cfg.label}</p>
            <p className="text-xs text-gray-400">Please allow location access</p>
          </div>
        </div>
      </div>
    );
  }

  if (gpsError) {
    return (
      <div className="rounded-xl border border-red-200 bg-red-50 p-5">
        <div className="flex items-start gap-3">
          <AlertTriangle className="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" />
          <div>
            <p className="text-sm font-semibold text-red-800">GPS Error</p>
            <p className="text-xs text-red-600 mt-1">{gpsError}</p>
          </div>
        </div>
      </div>
    );
  }

  if (!site) {
    return (
      <div className="rounded-xl border border-gray-200 bg-gray-50 p-5">
        <div className="flex items-center gap-3">
          <MapPin className="w-6 h-6 text-gray-400" />
          <div>
            <p className="text-sm font-semibold text-gray-600">No Site Assigned</p>
            <p className="text-xs text-gray-400">You are not assigned to any active site.</p>
          </div>
        </div>
      </div>
    );
  }

  let statusKey = 'outside';
  if (distance !== null && allowedRadius) {
    if (distance <= allowedRadius) {
      statusKey = 'inside';
    } else if (distance <= allowedRadius * 1.2) {
      statusKey = 'boundary';
    } else {
      statusKey = 'outside';
    }
  }

  const cfg = statusConfig[statusKey];
  const Icon = cfg.icon;

  const distancePercent = distance !== null && allowedRadius
    ? Math.min(Math.round((distance / allowedRadius) * 100), 200)
    : 0;

  return (
    <div className={`rounded-xl border p-5 ${cfg.bg}`}>
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2">
          <MapPin className="w-5 h-5 text-gray-500" />
          <span className="text-sm font-semibold text-gray-700">Assigned Site</span>
        </div>
        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${cfg.text} bg-white/80`}>
          <Icon className={`w-3.5 h-3.5 ${cfg.iconColor}`} />
          {cfg.label}
        </span>
      </div>

      <div className="mb-4">
        <p className="text-lg font-bold text-gray-900">{site.name}</p>
        {site.address && (
          <p className="text-xs text-gray-500 mt-0.5">{site.address}</p>
        )}
      </div>

      <div className="grid grid-cols-2 gap-4 mb-4">
        <div>
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Distance</p>
          <p className={`text-xl font-bold ${statusKey === 'inside' ? 'text-green-700' : statusKey === 'boundary' ? 'text-orange-700' : 'text-red-700'}`}>
            {distance !== null ? `${distance.toFixed(1)}m` : '--'}
          </p>
        </div>
        <div>
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Radius Limit</p>
          <p className="text-xl font-bold text-gray-700">{allowedRadius || 100}m</p>
        </div>
      </div>

      <div className="mb-4">
        <div className="flex justify-between text-xs text-gray-500 mb-1">
          <span>Distance</span>
          <span>{distancePercent}% of limit</span>
        </div>
        <div className="w-full bg-gray-200 rounded-full h-2.5">
          <div
            className={`h-2.5 rounded-full transition-all duration-500 ${
              statusKey === 'inside' ? 'bg-green-500' :
              statusKey === 'boundary' ? 'bg-orange-500' : 'bg-red-500'
            }`}
            style={{ width: `${Math.min(distancePercent, 100)}%` }}
          />
        </div>
      </div>

      <div className="border-t border-gray-200/60 pt-3 space-y-2">
        <div className="flex justify-between text-xs">
          <span className="text-gray-500 flex items-center gap-1">
            <Crosshair className="w-3 h-3" />
            GPS Accuracy
          </span>
          <span className={`font-semibold ${position?.accuracy <= 15 ? 'text-green-600' : position?.accuracy <= 50 ? 'text-orange-600' : 'text-red-600'}`}>
            {position?.accuracy ? `${position.accuracy.toFixed(1)}m` : '--'}
          </span>
        </div>
        <div className="flex justify-between text-xs">
          <span className="text-gray-500">Latitude</span>
          <span className="font-mono font-semibold text-gray-700">
            {position?.latitude?.toFixed(6) ?? '--'}
          </span>
        </div>
        <div className="flex justify-between text-xs">
          <span className="text-gray-500">Longitude</span>
          <span className="font-mono font-semibold text-gray-700">
            {position?.longitude?.toFixed(6) ?? '--'}
          </span>
        </div>
      </div>
    </div>
  );
}
