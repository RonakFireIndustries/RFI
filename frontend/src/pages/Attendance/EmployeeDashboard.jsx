import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { useAuthStore } from '../../store/authStore';
import { useAttendanceStore } from '../../store/attendanceStore';
import { useSiteStore } from '../../store/siteStore';
import { employeeSiteService } from '../../services/employeeSiteService';
import { Calendar, Clock, MapPin, User, Shield, RefreshCw } from 'lucide-react';
import useGeolocation from '../../hooks/useGeolocation';
import AttendanceLocationCard from '../../components/Attendance/AttendanceLocationCard';
import SlideAttendanceButton from '../../components/Attendance/SlideAttendanceButton';

export default function EmployeeDashboard() {
  const { user } = useAuthStore();
  const { logs, fetchLogs, checkIn, checkOut } = useAttendanceStore();
  const { items: sites } = useSiteStore();

  const [currentSite, setCurrentSite] = useState(null);
  const [todayAttendance, setTodayAttendance] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [actionError, setActionError] = useState(null);
  const [actionSuccess, setActionSuccess] = useState(null);

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

  useEffect(() => {
    const loadToday = async () => {
      try {
        const today = new Date().toISOString().split('T')[0];
        const records = await fetchLogs({ date: today, per_page: 10 });
        if (records?.length > 0) {
          setTodayAttendance(records[0]);
        } else {
          setTodayAttendance(null);
        }
      } catch (e) {
        console.error('Failed to load today attendance:', e);
      }
    };
    loadToday();
  }, []);

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
    if (!currentSite?.geo_fencing_enabled) return 'ready_checkin';
    if (gpsLoading) return 'gps_loading';
    if (gpsError) return gpsError.includes('denied') ? 'permission_denied' : 'gps_error';
    if (!position) return 'gps_loading';
    if (!currentSite) return 'outside_radius';
    if (distance !== null && !isInsideRadius) return 'outside_radius';
    return 'ready_checkin';
  }, [gpsLoading, gpsError, position, currentSite, distance, isInsideRadius]);

  const handleCheckIn = useCallback(async () => {
    if (!currentSite) return;
    if (currentSite.geo_fencing_enabled && !position) return;
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
      const today = new Date().toISOString().split('T')[0];
      const records = await fetchLogs({ date: today, per_page: 10 });
      if (records?.length > 0) {
        setTodayAttendance(records[0]);
      }
    } catch (e) {
      const msg = e?.response?.data?.message || e?.message || 'Check-in failed';
      setActionError(msg);
    } finally {
      setActionLoading(false);
    }
  }, [position, currentSite, checkIn, fetchLogs]);

  const handleCheckOut = useCallback(async () => {
    if (!currentSite) return;
    if (currentSite.geo_fencing_enabled && !position) return;
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
      const today = new Date().toISOString().split('T')[0];
      const records = await fetchLogs({ date: today, per_page: 10 });
      if (records?.length > 0) {
        setTodayAttendance(records[0]);
      }
    } catch (e) {
      const msg = e?.response?.data?.message || e?.message || 'Check-out failed';
      setActionError(msg);
    } finally {
      setActionLoading(false);
    }
  }, [position, currentSite, checkOut, fetchLogs]);

  useEffect(() => {
    if (actionSuccess) {
      const t = setTimeout(() => setActionSuccess(null), 4000);
      return () => clearTimeout(t);
    }
  }, [actionSuccess]);

  const formatTime = (dt) => {
    if (!dt) return '--';
    return new Date(dt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  return (
    <div className="w-full max-w-2xl mx-auto space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-extrabold text-gray-900">My Attendance</h1>
          <p className="text-sm text-gray-500 mt-1">
            {new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}
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
      />

      <div className="bg-white border border-gray-200 rounded-xl p-5">
        <h2 className="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
          <Clock className="w-4 h-4" />
          Today's Attendance Status
        </h2>

        <div className="grid grid-cols-2 gap-4 mb-4">
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
