import { useState, useEffect, useRef, useCallback } from 'react';

export default function useGeolocation(options = {}) {
  const {
    enableHighAccuracy = true,
    timeout = 10000,
    maximumAge = 0,
    refreshInterval = 10000,
  } = options;

  const [position, setPosition] = useState(null);
  const [error, setError] = useState(null);
  const [loading, setLoading] = useState(true);
  const [permissionState, setPermissionState] = useState('prompt');
  const watchIdRef = useRef(null);
  const intervalRef = useRef(null);
  const mountedRef = useRef(true);

  const handleSuccess = useCallback((pos) => {
    if (!mountedRef.current) return;
    const { latitude, longitude, accuracy, altitude, altitudeAccuracy, heading, speed } = pos.coords;
    setPosition({
      latitude,
      longitude,
      accuracy: Math.round(accuracy * 100) / 100,
      altitude: altitude || null,
      altitudeAccuracy: altitudeAccuracy || null,
      heading: heading || null,
      speed: speed || null,
      timestamp: pos.timestamp,
    });
    setError(null);
    setLoading(false);
  }, []);

  const handleError = useCallback((err) => {
    if (!mountedRef.current) return;
    let message;
    switch (err.code) {
      case err.PERMISSION_DENIED:
        message = 'GPS permission denied. Enable location services in your browser settings.';
        setPermissionState('denied');
        break;
      case err.POSITION_UNAVAILABLE:
        message = 'GPS signal unavailable. Move to an open area.';
        break;
      case err.TIMEOUT:
        message = 'GPS request timed out. Try again.';
        break;
      default:
        message = 'An unknown GPS error occurred.';
    }
    setError(message);
    setLoading(false);
  }, []);

  const startWatching = useCallback(() => {
    if (!navigator.geolocation) {
      setError('Geolocation is not supported by this browser.');
      setLoading(false);
      return;
    }

    if (watchIdRef.current !== null) {
      navigator.geolocation.clearWatch(watchIdRef.current);
    }

    watchIdRef.current = navigator.geolocation.watchPosition(
      handleSuccess,
      handleError,
      { enableHighAccuracy, timeout, maximumAge }
    );

    if (intervalRef.current) {
      clearInterval(intervalRef.current);
    }

    intervalRef.current = setInterval(() => {
      navigator.geolocation.getCurrentPosition(
        handleSuccess,
        handleError,
        { enableHighAccuracy, timeout, maximumAge }
      );
    }, refreshInterval);
  }, [enableHighAccuracy, timeout, maximumAge, refreshInterval, handleSuccess, handleError]);

  const stopWatching = useCallback(() => {
    if (watchIdRef.current !== null) {
      navigator.geolocation.clearWatch(watchIdRef.current);
      watchIdRef.current = null;
    }
    if (intervalRef.current) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  }, []);

  const refresh = useCallback(() => {
    if (!navigator.geolocation) {
      setError('Geolocation is not supported by this browser.');
      setLoading(false);
      return;
    }
    setLoading(true);
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        handleSuccess(pos);
        setLoading(false);
      },
      (err) => {
        handleError(err);
        setLoading(false);
      },
      { enableHighAccuracy, timeout, maximumAge }
    );
  }, [enableHighAccuracy, timeout, maximumAge, handleSuccess, handleError]);

  useEffect(() => {
    mountedRef.current = true;
    startWatching();
    return () => {
      mountedRef.current = false;
      stopWatching();
    };
  }, [startWatching, stopWatching]);

  const calculateDistance = useCallback((siteLat, siteLng) => {
    if (!position || siteLat === null || siteLng === null) return null;
    const R = 6371000;
    const lat1 = (position.latitude * Math.PI) / 180;
    const lat2 = (siteLat * Math.PI) / 180;
    const deltaLat = ((siteLat - position.latitude) * Math.PI) / 180;
    const deltaLng = ((siteLng - position.longitude) * Math.PI) / 180;
    const a =
      Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
      Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c * 100) / 100;
  }, [position]);

  const isWithinRadius = useCallback((siteLat, siteLng, radius) => {
    const distance = calculateDistance(siteLat, siteLng);
    if (distance === null) return null;
    return distance <= radius;
  }, [calculateDistance]);

  return {
    position,
    error,
    loading,
    permissionState,
    calculateDistance,
    isWithinRadius,
    refresh,
    startWatching,
    stopWatching,
  };
}
