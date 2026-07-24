import { useRef, useCallback, useEffect, useState } from 'react';

const USAGE_KEY = 'gmap_usage';
const MONTHLY_LIMIT = 70000;

function getCurrentMonth() {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
}

function getUsage() {
  try {
    const raw = localStorage.getItem(USAGE_KEY);
    if (!raw) return { month: getCurrentMonth(), mapLoads: 0, geocodeRequests: 0, placesRequests: 0 };
    const data = JSON.parse(raw);
    if (data.month !== getCurrentMonth()) {
      return { month: getCurrentMonth(), mapLoads: 0, geocodeRequests: 0, placesRequests: 0 };
    }
    return data;
  } catch {
    return { month: getCurrentMonth(), mapLoads: 0, geocodeRequests: 0, placesRequests: 0 };
  }
}

function saveUsage(data) {
  try {
    localStorage.setItem(USAGE_KEY, JSON.stringify(data));
  } catch { /* ignore */ }
}

export function useMapUsageTracker() {
  const [usage, setUsage] = useState(getUsage);

  const totalEvents = usage.mapLoads + usage.geocodeRequests + usage.placesRequests;
  const remaining = Math.max(0, MONTHLY_LIMIT - totalEvents);
  const percentUsed = ((totalEvents / MONTHLY_LIMIT) * 100).toFixed(1);
  const isNearLimit = percentUsed > 80;
  const isOverLimit = totalEvents >= MONTHLY_LIMIT;

  const trackMapLoad = useCallback(() => {
    setUsage(prev => {
      const current = getUsage();
      const updated = { ...current, mapLoads: current.mapLoads + 1 };
      saveUsage(updated);
      return updated;
    });
  }, []);

  const trackGeocode = useCallback(() => {
    setUsage(prev => {
      const current = getUsage();
      const updated = { ...current, geocodeRequests: current.geocodeRequests + 1 };
      saveUsage(updated);
      return updated;
    });
  }, []);

  const trackPlaces = useCallback(() => {
    setUsage(prev => {
      const current = getUsage();
      const updated = { ...current, placesRequests: current.placesRequests + 1 };
      saveUsage(updated);
      return updated;
    });
  }, []);

  const resetUsage = useCallback(() => {
    const fresh = { month: getCurrentMonth(), mapLoads: 0, geocodeRequests: 0, placesRequests: 0 };
    saveUsage(fresh);
    setUsage(fresh);
  }, []);

  return {
    usage,
    totalEvents,
    remaining,
    percentUsed: parseFloat(percentUsed),
    isNearLimit,
    isOverLimit,
    trackMapLoad,
    trackGeocode,
    trackPlaces,
    resetUsage,
  };
}

export function useMapIdleCache(mapRef, deps = []) {
  const idleStateRef = useRef(null);
  const [, forceUpdate] = useState(0);

  const onIdle = useCallback((map) => {
    if (!map) return;
    const center = map.getCenter();
    const zoom = map.getZoom();
    const bounds = map.getBounds();

    const newState = {
      center: { lat: center.lat(), lng: center.lng() },
      zoom,
      bounds: bounds ? {
        north: bounds.getNorthEast().lat(),
        east: bounds.getNorthEast().lng(),
        south: bounds.getSouthWest().lat(),
        west: bounds.getSouthWest().lng(),
      } : null,
    };

    const prev = idleStateRef.current;
    if (prev && prev.zoom === newState.zoom &&
        Math.abs(prev.center.lat - newState.center.lat) < 0.0001 &&
        Math.abs(prev.center.lng - newState.center.lng) < 0.0001) {
      return;
    }

    idleStateRef.current = newState;
    forceUpdate(c => c + 1);
  }, []);

  return { onIdle, idleState: idleStateRef.current };
}

export function useSessionToken() {
  const tokenRef = useRef(null);
  const lastUsedRef = useRef(0);
  const SESSION_TIMEOUT = 30 * 60 * 1000;

  const getToken = useCallback(() => {
    const now = Date.now();
    if (!tokenRef.current || (now - lastUsedRef.current) > SESSION_TIMEOUT) {
      tokenRef.current = `session_${now}_${Math.random().toString(36).slice(2)}`;
      lastUsedRef.current = now;
    }
    return tokenRef.current;
  }, []);

  const resetToken = useCallback(() => {
    tokenRef.current = null;
    lastUsedRef.current = 0;
  }, []);

  return { getToken, resetToken };
}
