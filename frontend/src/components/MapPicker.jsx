import { useState, useCallback, useRef, useEffect } from 'react';
import { GoogleMap, useJsApiLoader, Marker } from '@react-google-maps/api';
import { MapPin, Search, Loader2, Crosshair, X } from 'lucide-react';
import { useMapUsageTracker, useSessionToken } from '../hooks/useGoogleMapsOptimizer';
import { config } from '../config/environment';

const GOOGLE_MAPS_LIBRARIES = ['places'];
const mapContainerStyle = { width: '100%', height: '350px', borderRadius: '12px' };
const defaultCenter = { lat: 19.0760, lng: 72.8777 };
const mapOptions = {
  zoomControl: true,
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
  disableDefaultUI: false,
  gestureHandling: 'cooperative',
};

function GoogleMapPicker({ value, onChange }) {
  const [marker, setMarker] = useState(null);
  const [mapInstance, setMapInstance] = useState(null);
  const [searchQuery, setSearchQuery] = useState('');
  const [searching, setSearching] = useState(false);
  const [geocoding, setGeocoding] = useState(false);
  const searchInputRef = useRef(null);
  const autocompleteRef = useRef(null);
  const usageTracker = useMapUsageTracker();
  const { getToken } = useSessionToken();

  useEffect(() => {
    if (value?.latitude && value?.longitude) {
      const lat = parseFloat(value.latitude);
      const lng = parseFloat(value.longitude);
      if (!isNaN(lat) && !isNaN(lng)) {
        setMarker({ lat, lng });
      }
    }
  }, [value?.latitude, value?.longitude]);

  const reverseGeocode = useCallback(async (lat, lng) => {
    setGeocoding(true);
    try {
      const geocoder = new window.google.maps.Geocoder();
      const results = await new Promise((resolve, reject) => {
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
          if (status === 'OK' && results?.[0]) resolve(results[0]);
          else reject(new Error('Geocoding failed'));
        });
      });
      usageTracker.trackGeocode();

      const address = {};
      const components = results.address_components || [];
      for (const comp of components) {
        const types = comp.types;
        if (types.includes('street_number') || types.includes('route')) {
          address.address = address.address ? `${comp.long_name} ${address.address}` : comp.long_name;
        }
        if (types.includes('locality')) address.city = comp.long_name;
        if (types.includes('administrative_area_level_1')) address.state = comp.long_name;
        if (types.includes('country')) address.country = comp.long_name;
        if (types.includes('postal_code')) address.pincode = comp.long_name;
      }

      onChange({
        latitude: lat.toFixed(6),
        longitude: lng.toFixed(6),
        ...(results.formatted_address ? { address: results.formatted_address } : {}),
        ...(address.city ? { city: address.city } : {}),
        ...(address.state ? { state: address.state } : {}),
        ...(address.country ? { country: address.country } : {}),
        ...(address.pincode ? { pincode: address.pincode } : {}),
      });
    } catch {
      onChange({ latitude: lat.toFixed(6), longitude: lng.toFixed(6) });
    } finally {
      setGeocoding(false);
    }
  }, [onChange, usageTracker]);

  const handleMapClick = useCallback((e) => {
    const lat = e.latLng.lat();
    const lng = e.latLng.lng();
    setMarker({ lat, lng });
    reverseGeocode(lat, lng);
  }, [reverseGeocode]);

  const handleMapLoad = useCallback((map) => {
    setMapInstance(map);
    usageTracker.trackMapLoad();

    if (searchInputRef.current && !autocompleteRef.current) {
      const autocomplete = new window.google.maps.places.Autocomplete(searchInputRef.current, {
        fields: ['geometry', 'formatted_address', 'address_components'],
        types: ['establishment', 'geocode'],
      });
      autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (place.geometry?.location) {
          const lat = place.geometry.location.lat();
          const lng = place.geometry.location.lng();
          setMarker({ lat, lng });
          if (mapInstance) {
            mapInstance.panTo({ lat, lng });
            mapInstance.setZoom(16);
          }
          usageTracker.trackPlaces();

          const address = {};
          const components = place.address_components || [];
          for (const comp of components) {
            const types = comp.types;
            if (types.includes('street_number') || types.includes('route')) {
              address.address = address.address ? `${comp.long_name} ${address.address}` : comp.long_name;
            }
            if (types.includes('locality')) address.city = comp.long_name;
            if (types.includes('administrative_area_level_1')) address.state = comp.long_name;
            if (types.includes('country')) address.country = comp.long_name;
            if (types.includes('postal_code')) address.pincode = comp.long_name;
          }

          onChange({
            latitude: lat.toFixed(6),
            longitude: lng.toFixed(6),
            address: place.formatted_address || address.address || '',
            ...(address.city ? { city: address.city } : {}),
            ...(address.state ? { state: address.state } : {}),
            ...(address.country ? { country: address.country } : {}),
            ...(address.pincode ? { pincode: address.pincode } : {}),
          });
        }
      });
      autocompleteRef.current = autocomplete;
    }
  }, [usageTracker, mapInstance, onChange]);

  const handleSearchSubmit = async (e) => {
    e.preventDefault();
    if (!searchQuery.trim()) return;
    setSearching(true);
    try {
      const geocoder = new window.google.maps.Geocoder();
      const results = await new Promise((resolve, reject) => {
        geocoder.geocode({ address: searchQuery }, (results, status) => {
          if (status === 'OK' && results?.[0]) resolve(results[0]);
          else reject(new Error('Not found'));
        });
      });
      usageTracker.trackGeocode();

      const loc = results.geometry.location;
      const lat = loc.lat();
      const lng = loc.lng();
      setMarker({ lat, lng });
      if (mapInstance) {
        mapInstance.panTo({ lat, lng });
        mapInstance.setZoom(16);
      }

      const address = {};
      const components = results.address_components || [];
      for (const comp of components) {
        const types = comp.types;
        if (types.includes('street_number') || types.includes('route')) {
          address.address = address.address ? `${comp.long_name} ${address.address}` : comp.long_name;
        }
        if (types.includes('locality')) address.city = comp.long_name;
        if (types.includes('administrative_area_level_1')) address.state = comp.long_name;
        if (types.includes('country')) address.country = comp.long_name;
        if (types.includes('postal_code')) address.pincode = comp.long_name;
      }

      onChange({
        latitude: lat.toFixed(6),
        longitude: lng.toFixed(6),
        address: results.formatted_address || address.address || '',
        ...(address.city ? { city: address.city } : {}),
        ...(address.state ? { state: address.state } : {}),
        ...(address.country ? { country: address.country } : {}),
        ...(address.pincode ? { pincode: address.pincode } : {}),
      });
    } catch {
      // ignore
    } finally {
      setSearching(false);
    }
  };

  const handleUseMyLocation = () => {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        setMarker({ lat, lng });
        if (mapInstance) {
          mapInstance.panTo({ lat, lng });
          mapInstance.setZoom(16);
        }
        reverseGeocode(lat, lng);
      },
      () => {},
      { enableHighAccuracy: true, timeout: 10000 }
    );
  };

  const center = marker || (value?.latitude && value?.longitude
    ? { lat: parseFloat(value.latitude), lng: parseFloat(value.longitude) }
    : defaultCenter);

  return (
    <div className="space-y-3">
      <div className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input
            ref={searchInputRef}
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault();
                handleSearchSubmit(e);
              }
            }}
            placeholder="Search address or place..."
            className="w-full pl-9 pr-3 py-2 border border-gray-250 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <button type="button" onClick={handleSearchSubmit} disabled={searching} className="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 flex items-center gap-1.5 whitespace-nowrap">
          <Search className="w-4 h-4" />
          Search
        </button>
        <button type="button" onClick={handleUseMyLocation} className="px-3 py-2 border border-gray-250 rounded-lg text-sm text-gray-600 hover:bg-gray-50 flex items-center gap-1.5 whitespace-nowrap" title="Use my current location">
          <Crosshair className="w-4 h-4" />
          Current
        </button>
      </div>

      <div className="relative rounded-xl overflow-hidden border border-gray-200">
        <GoogleMap
          mapContainerStyle={mapContainerStyle}
          center={center}
          zoom={marker ? 16 : 12}
          options={mapOptions}
          onLoad={handleMapLoad}
          onClick={handleMapClick}
        >
          {marker && <Marker position={marker} />}
        </GoogleMap>

        {geocoding && (
          <div className="absolute top-2 left-2 bg-white/90 backdrop-blur-sm rounded-lg px-3 py-1.5 text-xs text-gray-600 flex items-center gap-1.5 shadow-sm">
            <Loader2 className="w-3 h-3 animate-spin" />
            Fetching address...
          </div>
        )}

        {!marker && (
          <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div className="text-center">
              <MapPin className="w-8 h-8 text-gray-400 mx-auto mb-1 animate-bounce" />
              <p className="text-xs text-gray-500 bg-white/80 px-3 py-1 rounded-full">Click on map to set location</p>
            </div>
          </div>
        )}
      </div>

      {marker && (
        <div className="flex items-center gap-2 text-xs text-gray-500">
          <MapPin className="w-3.5 h-3.5 text-green-500" />
          <span>Location set: {marker.lat.toFixed(6)}, {marker.lng.toFixed(6)}</span>
          <button type="button" onClick={() => { setMarker(null); onChange({ latitude: '', longitude: '' }); }} className="text-red-400 hover:text-red-600 ml-auto">
            <X className="w-3.5 h-3.5" />
          </button>
        </div>
      )}
    </div>
  );
}

export default function MapPicker({ value, onChange }) {
  const apiKey = config.GOOGLE_MAPS_API_KEY;
  const [loaded, setLoaded] = useState(false);

  const { isLoaded, loadError } = useJsApiLoader({
    googleMapsApiKey: apiKey || '',
    preventGoogleFontsLoading: true,
    libraries: GOOGLE_MAPS_LIBRARIES,
  });

  if (!apiKey) {
    return (
      <div className="border border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50">
        <MapPin className="w-8 h-8 text-gray-400 mx-auto mb-2" />
        <p className="text-sm text-gray-500">Google Maps API key not configured</p>
        <p className="text-xs text-gray-400 mt-1">Set GOOGLE_MAPS_API_KEY in src/config/environment.js</p>
      </div>
    );
  }

  if (loadError) {
    return (
      <div className="border border-dashed border-red-300 rounded-xl p-6 text-center bg-red-50">
        <MapPin className="w-8 h-8 text-red-400 mx-auto mb-2" />
        <p className="text-sm text-red-500">Failed to load Google Maps</p>
      </div>
    );
  }

  if (!isLoaded) {
    return (
      <div className="border border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50">
        <Loader2 className="w-6 h-6 text-gray-400 mx-auto mb-2 animate-spin" />
        <p className="text-xs text-gray-500">Loading Maps...</p>
      </div>
    );
  }

  return <GoogleMapPicker value={value} onChange={onChange} />;
}
