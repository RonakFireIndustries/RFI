import React, { useState, useEffect, useCallback, useMemo, useRef } from 'react';
import { useAuthStore } from '../../store/authStore';
import { useAttendanceStore } from '../../store/attendanceStore';
import { useSiteStore } from '../../store/siteStore';
import { employeeSiteService } from '../../services/employeeSiteService';
import { Calendar, Clock, MapPin, User, Shield, RefreshCw, ExternalLink, X, Download } from 'lucide-react';
import useGeolocation from '../../hooks/useGeolocation';
import AttendanceLocationCard from '../../components/Attendance/AttendanceLocationCard';
import SlideAttendanceButton from '../../components/Attendance/SlideAttendanceButton';

function isStandalone() {
  return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
}

function isIOS() {
  return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
}

function isAndroid() {
  return /Android/.test(navigator.userAgent);
}

function getPermissionHelp(isStandaloneMode) {
  if (isStandaloneMode) {
    if (isIOS()) {
      return {
        title: 'Enable Location for RFI',
        steps: [
          'Open the Settings app on your iPhone/iPad',
          'Scroll down and tap "RFI" in the app list',
          'Tap "Location"',
          'Select "While Using the App" or "Always"',
          'Return to RFI and tap refresh above',
        ],
        icon: '📱',
      };
    }
    if (isAndroid()) {
      return {
        title: 'Enable Location for RFI',
        steps: [
          'Open Settings on your device',
          'Tap "Apps" or "App Management"',
          'Tap "RFI" in the app list',
          'Tap "Permissions"',
          'Tap "Location" and select "Allow only while using the app"',
          'Return to RFI and tap refresh above',
        ],
        icon: '📱',
      };
    }
  }

  if (isIOS()) {
    return {
      title: 'Enable Location on iOS',
      steps: [
        'Open Settings app',
        'Scroll down and tap "Safari"',
        'Tap "Location"',
        'Select "While Using the App" or "Always"',
      ],
      icon: '🌐',
    };
  }
  if (isAndroid()) {
    return {
      title: 'Enable Location on Android',
      steps: [
        'Tap the lock icon in the address bar',
        'Tap "Site Settings" or "Permissions"',
        'Tap "Location"',
        'Select "Allow"',
      ],
      icon: '🌐',
    };
  }
  return {
    title: 'Enable Location in Browser Settings',
    steps: [
      'Click the lock/info icon in the address bar',
      'Go to Site Settings or Permissions',
      'Change Location to "Allow"',
      'Refresh this page',
    ],
    icon: '🌐',
  };
}

export default function EmployeeDashboard() {
  const { user } = useAuthStore();
  const { logs, fetchLogs, myAttendance, checkIn, checkOut } = useAttendanceStore();
  const { items: sites } = useSiteStore();

  const [currentSite, setCurrentSite] = useState(null);
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [actionError, setActionError] = useState(null);
  const [actionSuccess, setActionSuccess] = useState(null);
  const [showPermissionHelp, setShowPermissionHelp] = useState(false);
  const [installPrompt, setInstallPrompt] = useState(null);
  const deferredPromptRef = useRef(null);

  useEffect(() => {
    const handler = (e) => {
      e.preventDefault();
      deferredPromptRef.current = e;
      setInstallPrompt(e);
    };
    window.addEventListener('beforeinstallprompt', handler);
    return () => window.removeEventListener('beforeinstallprompt', handler);
  }, []);

  const {
    position,
    error: gpsError,
    loading: gpsLoading,
    calculateDistance,
    refresh: refreshGPS,
  } = useGeolocation({ refreshInterval: 10000 });

  const employeeId = user?.employee?.id;

  useEffect(() => {
    if (!employeeId) return;
    const loadSite = async () => {
      try {
        const data = await employeeSiteService.getCurrentSite(employeeId);
        console.log(data);
        
        if (data?.site) {
          setCurrentSite(data.site);
        } else if (data?.id) {
          setCurrentSite(data);
        }
      } catch (e) {
        console.error('Failed to load current site:', e);
      }
    };
    loadSite();
  }, [employeeId]);

  const loadTodayAttendance = useCallback(async () => {
    if (!employeeId) return;
    try {
      const today = new Date().toISOString().split('T')[0];
      const records = await myAttendance({ date: today, per_page: 10 });
      if (records?.length > 0) {
        setTodayAttendance(records[0]);
      } else {
        setTodayAttendance(null);
      }
    } catch (e) {
      console.error('Failed to load today attendance:', e);
    }
  }, [employeeId, myAttendance]);

  useEffect(() => {
    loadTodayAttendance();
  }, [loadTodayAttendance]);

  useEffect(() => {
    const handleVisibility = () => {
      if (document.visibilityState === 'visible') loadTodayAttendance();
    };
    const handleFocus = () => loadTodayAttendance();
    document.addEventListener('visibilitychange', handleVisibility);
    window.addEventListener('focus', handleFocus);
    return () => {
      document.removeEventListener('visibilitychange', handleVisibility);
      window.removeEventListener('focus', handleFocus);
    };
  }, [loadTodayAttendance]);

  const distance = useMemo(() => {
    if (!position || !currentSite) return null;
    return calculateDistance(
      parseFloat(currentSite.latitude),
      parseFloat(currentSite.longitude)
    );
  }, [position, currentSite, calculateDistance]);

  const allowedRadius = currentSite?.allowed_radius ?? 100;
  const isInsideRadius = distance !== null && distance <= allowedRadius;
  const isCheckedIn = !!todayAttendance?.check_in && !todayAttendance?.check_out;
  const isCheckedOut = !!todayAttendance?.check_out;

  const buttonStatus = useMemo(() => {
    if (gpsLoading) return 'gps_loading';
    if (gpsError) return gpsError.includes('denied') ? 'permission_denied' : 'gps_error';
    if (!position) return 'gps_loading';
    if (!currentSite) return 'outside_radius';
    if (currentSite.geo_fencing_enabled && distance !== null && !isInsideRadius) return 'outside_radius';
    return 'ready_checkin';
  }, [gpsLoading, gpsError, position, currentSite, distance, isInsideRadius]);

  const handleCheckIn = useCallback(async () => {
    if (!currentSite) return;
    if (!position) return;
    setActionLoading(true);
    setActionError(null);
    setActionSuccess(null);
    try {
      const payload = {
        latitude: position?.latitude,
        longitude: position?.longitude,
        accuracy: position?.accuracy,
        site_id: currentSite.id,
        device_info: navigator.userAgent,
      };
      const result = await checkIn(payload);
      setActionSuccess('Checked in successfully!');
      await loadTodayAttendance();
    } catch (e) {
      const data = e?.response?.data;
      console.error('Check-in error:', data);
      const msg = data?.message || e?.message || 'Check-in failed';
      setActionError(msg);
      await loadTodayAttendance();
    } finally {
      setActionLoading(false);
    }
  }, [position, currentSite, checkIn, loadTodayAttendance]);

  const handleCheckOut = useCallback(async () => {
    if (!currentSite) return;
    if (!position) return;
    setActionLoading(true);
    setActionError(null);
    setActionSuccess(null);
    try {
      const payload = {
        latitude: position?.latitude,
        longitude: position?.longitude,
        accuracy: position?.accuracy,
        device_info: navigator.userAgent,
      };
      const result = await checkOut(payload);
      setActionSuccess('Checked out successfully!');
      await loadTodayAttendance();
    } catch (e) {
      const data = e?.response?.data;
      console.error('Check-out error:', data);
      const msg = data?.message || e?.message || 'Check-out failed';
      setActionError(msg);
      await loadTodayAttendance();
    } finally {
      setActionLoading(false);
    }
  }, [position, currentSite, checkOut, loadTodayAttendance]);

  const handleInstall = useCallback(async () => {
    const deferred = deferredPromptRef.current;
    if (!deferred) return;
    deferred.prompt();
    const result = await deferred.userChoice;
    if (result.outcome === 'accepted') {
      deferredPromptRef.current = null;
      setInstallPrompt(null);
    }
  }, []);

  const handleRequestPermission = useCallback(async () => {
    if (!isStandalone() && deferredPromptRef.current) {
      deferredPromptRef.current.prompt();
      const result = await deferredPromptRef.current.userChoice;
      if (result.outcome === 'accepted') {
        deferredPromptRef.current = null;
        setInstallPrompt(null);
        return;
      }
    }
    try {
      if (navigator.permissions?.query) {
        const result = await navigator.permissions.query({ name: 'geolocation' });
        if (result.state === 'denied') {
          setShowPermissionHelp(true);
          return;
        }
      }
    } catch {
    }
    refreshGPS();
  }, [refreshGPS]);

  useEffect(() => {
    if (actionSuccess) {
      const t = setTimeout(() => setActionSuccess(null), 4000);
      return () => clearTimeout(t);
    }
  }, [actionSuccess]);

  const formatDate = (dt) => {
    if (!dt) return '--';
    const d = new Date(dt);
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const yy = String(d.getFullYear()).slice(-2);
    return `${dd}/${mm}/${yy}`;
  };

  const formatTime = (dt) => {
    if (!dt) return '--';
    const d = new Date(dt);
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
  };

  const formatHours = (hours) => {
    if (hours == null || hours === '') return '--';
    const h = Math.floor(Number(hours));
    const m = Math.round((Number(hours) - h) * 60);
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
  };

  return (
    <div className="w-full max-w-2xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-extrabold text-gray-900">My Attendance</h1>
          <p className="text-sm text-gray-500 mt-1">
            {formatDate(new Date())} {formatTime(new Date())}
          </p>
        </div>
        <button
          onClick={refreshGPS}
          disabled={gpsLoading}
          className="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
          title="Refresh GPS"
        >
          <RefreshCw className={`w-4 h-4 text-gray-500 ${gpsLoading ? 'animate-spin' : ''}`} />
        </button>
      </div>

      {actionSuccess && (
        <div className="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
            <svg className="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" /></svg>
          </div>
          <p className="text-sm font-semibold text-green-800">{actionSuccess}</p>
        </div>
      )}

      {actionError && (
        <div className="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
          <div className="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg className="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" /></svg>
          </div>
          <p className="text-sm font-semibold text-red-800">{actionError}</p>
        </div>
      )}

      <AttendanceLocationCard
        position={position}
        gpsError={gpsError}
        gpsLoading={gpsLoading}
        site={currentSite}
        distance={distance}
        allowedRadius={allowedRadius}
        onRequestPermission={handleRequestPermission}
      />

      <div className="bg-white border border-gray-200 rounded-xl p-5">
        <h2 className="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
          <Clock className="w-4 h-4" />
          Today's Attendance Status
        </h2>

        <div className="grid grid-cols-2 gap-4 mb-4">
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Date</p>
            <p className="text-lg font-bold text-gray-900 mt-1">
              {todayAttendance ? formatDate(todayAttendance.date) : formatDate(new Date())}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Site</p>
            <p className="text-lg font-bold text-gray-900 mt-1 truncate">
              {todayAttendance?.site?.name || currentSite?.name || '--'}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Check In</p>
            <p className="text-lg font-bold text-gray-900 mt-1">
              {formatTime(todayAttendance?.check_in)}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Check Out</p>
            <p className="text-lg font-bold text-gray-900 mt-1">
              {formatTime(todayAttendance?.check_out)}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Working Hours</p>
            <p className="text-lg font-bold text-gray-900 mt-1">
              {formatHours(todayAttendance?.working_hours)}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-3">
            <p className="text-xs text-gray-500 font-semibold uppercase tracking-wider">Overtime</p>
            <p className="text-lg font-bold text-gray-900 mt-1">
              {formatHours(todayAttendance?.overtime_hours)}
            </p>
          </div>
        </div>

        {todayAttendance && (
          <div className="flex items-center gap-2 mb-4">
            <Shield className={`w-4 h-4 ${todayAttendance.location_verified ? 'text-green-500' : 'text-orange-500'}`} />
            <span className={`text-xs font-semibold ${todayAttendance.location_verified ? 'text-green-700' : 'text-orange-700'}`}>
              {todayAttendance.location_verified ? 'Location Verified' : 'Location Not Verified'}
            </span>
            {todayAttendance.checkin_distance && (
              <span className="text-xs text-gray-400 ml-auto">
                {todayAttendance.checkin_distance}m from site
              </span>
            )}
          </div>
        )}

        <SlideAttendanceButton
          status={buttonStatus}
          onCheckIn={handleCheckIn}
          onCheckOut={handleCheckOut}
          isCheckedIn={isCheckedIn}
          isCheckedOut={isCheckedOut}
        />
      </div>

      {!isStandalone() && installPrompt && (
        <div className="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
          <div className="flex items-start gap-3">
            <div className="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
              <Download className="w-5 h-5 text-indigo-600" />
            </div>
            <div className="flex-1 min-w-0">
              <h3 className="text-sm font-bold text-indigo-800">Install RFI App</h3>
              <p className="text-xs text-indigo-600 mt-1">
                Install this app on your device for app-like location access and a better experience.
              </p>
              <div className="flex gap-2 mt-3">
                <button
                  onClick={handleInstall}
                  className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors"
                >
                  Install App
                </button>
                <button
                  onClick={() => setInstallPrompt(null)}
                  className="px-4 py-2 bg-white border border-indigo-200 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-50 transition-colors"
                >
                  Not Now
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {showPermissionHelp && (
        <div className="bg-blue-50 border border-blue-200 rounded-xl p-5 relative">
          <button
            onClick={() => setShowPermissionHelp(false)}
            className="absolute top-3 right-3 p-1 hover:bg-blue-100 rounded-full transition-colors"
          >
            <X className="w-4 h-4 text-blue-500" />
          </button>
          <h3 className="text-sm font-bold text-blue-800 mb-3 pr-6">
            {getPermissionHelp(isStandalone()).icon} {getPermissionHelp(isStandalone()).title}
          </h3>
          <ol className="space-y-2">
            {getPermissionHelp(isStandalone()).steps.map((step, i) => (
              <li key={i} className="flex items-start gap-2 text-sm text-blue-700">
                <span className="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs font-bold flex items-center justify-center mt-0.5">
                  {i + 1}
                </span>
                <span>{step}</span>
              </li>
            ))}
          </ol>
          <p className="text-xs text-blue-600 mt-3">
            After changing the setting, tap the refresh button <RefreshCw className="w-3 h-3 inline" /> above to retry.
          </p>
        </div>
      )}

      <div className="bg-white border border-gray-200 rounded-xl p-5">
        <h2 className="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
          <MapPin className="w-4 h-4" />
          Current Position
        </h2>
        <div className="grid grid-cols-2 gap-3 text-xs">
          <div className="bg-gray-50 rounded-lg p-2.5">
            <span className="text-gray-500">Latitude</span>
            <p className="font-mono font-bold text-gray-800 mt-0.5">
              {position?.latitude?.toFixed(6) ?? '--'}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-2.5">
            <span className="text-gray-500">Longitude</span>
            <p className="font-mono font-bold text-gray-800 mt-0.5">
              {position?.longitude?.toFixed(6) ?? '--'}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-2.5">
            <span className="text-gray-500">Accuracy</span>
            <p className="font-mono font-bold text-gray-800 mt-0.5">
              {position?.accuracy ? `${position.accuracy.toFixed(1)}m` : '--'}
            </p>
          </div>
          <div className="bg-gray-50 rounded-lg p-2.5">
            <span className="text-gray-500">Site Distance</span>
            <p className="font-mono font-bold text-gray-800 mt-0.5">
              {distance !== null ? `${distance.toFixed(1)}m` : '--'}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
