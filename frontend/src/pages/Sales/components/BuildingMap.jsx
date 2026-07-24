import { useState, useCallback, useEffect, useMemo, memo, lazy, Suspense } from 'react';
import { GoogleMap, useJsApiLoader, Marker, InfoWindow, MarkerClusterer } from '@react-google-maps/api';
import { useNavigate } from 'react-router-dom';
import { MapPin, AlertTriangle, BarChart3 } from 'lucide-react';
import { useMapUsageTracker, useSessionToken } from '../../../hooks/useGoogleMapsOptimizer';

const mapContainerStyle = {
  width: '100%',
  height: '600px',
  borderRadius: '8px',
};

const defaultCenter = {
  lat: 19.0760,
  lng: 72.8777,
};

const MARKER_COLORS = {
  red: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
  yellow: 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png',
  green: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
  blue: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
  gray: 'http://maps.google.com/mapfiles/ms/icons/gray-dot.png',
};

const STATUS_COLOR_MAP = {
  'Existing Client': 'red',
  'Quotation Submitted': 'yellow',
  'Active Project': 'green',
  'AMC Client': 'blue',
  'Prospect': 'gray',
};

const mapOptions = {
  zoomControl: true,
  streetViewControl: false,
  mapTypeControl: true,
  fullscreenControl: true,
  disableDefaultUI: false,
  gestureHandling: 'cooperative',
};

const clusterStyles = [
  { width: 40, height: 40, className: 'cluster' },
  { width: 50, height: 50, className: 'cluster' },
  { width: 60, height: 60, className: 'cluster' },
];

const MemoizedMarker = memo(function BuildingMarker({ building, onClick }) {
  const icon = useMemo(() => {
    if (!building.statuses || building.statuses.length === 0) {
      return MARKER_COLORS.gray;
    }
    const primary = building.statuses[0];
    const colorKey = STATUS_COLOR_MAP[primary.name] || primary.marker_color || 'gray';
    return MARKER_COLORS[colorKey] || MARKER_COLORS.gray;
  }, [building.statuses]);

  return (
    <Marker
      position={{ lat: parseFloat(building.latitude), lng: parseFloat(building.longitude) }}
      icon={icon}
      title={building.name}
      onClick={() => onClick(building)}
    />
  );
});

const MemoizedInfoContent = memo(function InfoContent({ building, onClose, onViewDetails }) {
  return (
    <div className="p-1 max-w-[250px]">
      <h3 className="font-semibold text-gray-900 text-sm">{building.name}</h3>
      <p className="text-xs text-gray-500 mt-1">{building.address || ''} {building.city || ''}</p>
      {building.statuses?.length > 0 && (
        <div className="flex flex-wrap gap-1 mt-2">
          {building.statuses.map(s => (
            <span key={s.id} className="text-[10px] text-white px-1.5 py-0.5 rounded" style={{ backgroundColor: s.color }}>{s.name}</span>
          ))}
        </div>
      )}
      <div className="mt-2">
        <button onClick={() => onViewDetails(building)} className="text-xs text-red-600 hover:text-red-700 font-medium">
          View Details →
        </button>
      </div>
    </div>
  );
});

function UsageBanner({ usage, totalEvents, isNearLimit, isOverLimit, percentUsed, remaining }) {
  return (
    <div className={`absolute top-2 right-2 z-20 rounded-lg shadow-lg text-xs px-3 py-2 flex items-center gap-2 ${
      isOverLimit ? 'bg-red-600 text-white' :
      isNearLimit ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' :
      'bg-white/90 text-gray-600 border border-gray-200'
    }`}>
      <BarChart3 className="w-3 h-3" />
      <span>{totalEvents.toLocaleString()} / 70,000 ({percentUsed}%)</span>
      {isOverLimit && <span className="font-bold">LIMIT REACHED</span>}
      {isNearLimit && !isOverLimit && <span className="font-medium">{remaining.toLocaleString()} left</span>}
    </div>
  );
}

function LoadingState({ height }) {
  return (
    <div className="flex items-center justify-center bg-gray-100 rounded-lg" style={{ height: height || '600px' }}>
      <div className="text-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto" />
        <p className="text-xs text-gray-500 mt-3">Loading Google Maps...</p>
        <p className="text-[10px] text-gray-400 mt-1">This counts as 1 billable event</p>
      </div>
    </div>
  );
}

function ErrorState({ height }) {
  return (
    <div className="flex items-center justify-center bg-gray-100 rounded-lg" style={{ height: height || '600px' }}>
      <div className="text-center text-gray-500">
        <MapPin className="w-12 h-12 mx-auto mb-3 text-gray-300" />
        <p>Failed to load Google Maps</p>
        <p className="text-xs mt-1">Please check your Google Maps API key</p>
      </div>
    </div>
  );
}

function NoApiKeyState({ height }) {
  return (
    <div className="flex items-center justify-center bg-gray-100 rounded-lg" style={{ height: height || '600px' }}>
      <div className="text-center text-gray-500">
        <MapPin className="w-12 h-12 mx-auto mb-3 text-gray-300" />
        <p>Google Maps API key not configured</p>
        <p className="text-xs mt-1">Set VITE_GOOGLE_MAPS_API_KEY in .env</p>
      </div>
    </div>
  );
}

function GoogleMapsLoaded({ buildings, onBuildingSelect, height, usageTracker }) {
  const navigate = useNavigate();
  const [selectedBuilding, setSelectedBuilding] = useState(null);
  const [mapCenter, setMapCenter] = useState(defaultCenter);
  const [mapInstance, setMapInstance] = useState(null);
  const { getToken } = useSessionToken();

  useEffect(() => {
    const located = buildings.filter(b => b.latitude && b.longitude);
    if (located.length === 1) {
      setMapCenter({ lat: parseFloat(located[0].latitude), lng: parseFloat(located[0].longitude) });
    } else if (located.length > 1) {
      const avgLat = located.reduce((s, b) => s + parseFloat(b.latitude), 0) / located.length;
      const avgLng = located.reduce((s, b) => s + parseFloat(b.longitude), 0) / located.length;
      setMapCenter({ lat: avgLat, lng: avgLng });
    }
  }, [buildings]);

  const handleMapLoad = useCallback((map) => {
    setMapInstance(map);
    usageTracker.trackMapLoad();
  }, [usageTracker]);

  const handleMarkerClick = useCallback((building) => {
    setSelectedBuilding(building);
    if (onBuildingSelect) onBuildingSelect(building);
  }, [onBuildingSelect]);

  const handleInfoClose = useCallback(() => {
    setSelectedBuilding(null);
  }, []);

  const handleViewDetails = useCallback((building) => {
    navigate(`/buildings/${building.id}`);
  }, [navigate]);

  const locatedBuildings = useMemo(() =>
    buildings.filter(b => b.latitude && b.longitude),
    [buildings]
  );

  const onIdle = useCallback((map) => {
    if (!map) return;
  }, []);

  return (
    <div className="relative">
      <UsageBanner {...usageTracker} />
      <GoogleMap
        mapContainerStyle={{ ...mapContainerStyle, height: height || mapContainerStyle.height }}
        center={mapCenter}
        zoom={12}
        options={mapOptions}
        onLoad={handleMapLoad}
        onIdle={onIdle}
      >
        {locatedBuildings.length > 20 ? (
          <MarkerClusterer>
            {(clusterer) =>
              locatedBuildings.map(building => (
                <MemoizedMarker
                  key={building.id}
                  building={building}
                  onClick={handleMarkerClick}
                  clusterer={clusterer}
                />
              ))
            }
          </MarkerClusterer>
        ) : (
          locatedBuildings.map(building => (
            <MemoizedMarker
              key={building.id}
              building={building}
              onClick={handleMarkerClick}
            />
          ))
        )}

        {selectedBuilding && (
          <InfoWindow
            position={{ lat: parseFloat(selectedBuilding.latitude), lng: parseFloat(selectedBuilding.longitude) }}
            onCloseClick={handleInfoClose}
          >
            <MemoizedInfoContent
              building={selectedBuilding}
              onClose={handleInfoClose}
              onViewDetails={handleViewDetails}
            />
          </InfoWindow>
        )}
      </GoogleMap>
    </div>
  );
}

export default function BuildingMap({ buildings = [], onBuildingSelect, height }) {
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
  const usageTracker = useMapUsageTracker();

  const { isLoaded, loadError } = useJsApiLoader({
    googleMapsApiKey: apiKey || '',
    preventGoogleFontsLoading: true,
    libraries: ['places'],
  });

  if (!apiKey) return <NoApiKeyState height={height} />;
  if (loadError) return <ErrorState height={height} />;
  if (!isLoaded) return <LoadingState height={height} />;

  return (
    <GoogleMapsLoaded
      buildings={buildings}
      onBuildingSelect={onBuildingSelect}
      height={height}
      usageTracker={usageTracker}
    />
  );
}
