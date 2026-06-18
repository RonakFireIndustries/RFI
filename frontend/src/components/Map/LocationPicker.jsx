import React, { useEffect } from 'react';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

// Fix for default marker icon issues in react-leaflet
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

function LocationMarker({ position, setPosition }) {
  useMapEvents({
    click(e) {
      setPosition(e.latlng);
    },
  });

  return position === null ? null : (
    <Marker position={position}></Marker>
  );
}

export default function LocationPicker({ latitude, longitude, onLocationChange }) {
  const defaultCenter = [19.0760, 72.8777]; // Default to Mumbai
  const center = latitude && longitude ? [latitude, longitude] : defaultCenter;
  const position = latitude && longitude ? { lat: latitude, lng: longitude } : null;

  const handlePositionChange = (latlng) => {
    onLocationChange(latlng.lat, latlng.lng);
  };

  return (
    <div className="h-64 w-full rounded-lg overflow-hidden border border-gray-300 relative z-0">
      <MapContainer 
        center={center} 
        zoom={latitude && longitude ? 15 : 5} 
        scrollWheelZoom={true} 
        style={{ height: '100%', width: '100%' }}
      >
        <TileLayer
          attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />
        <LocationMarker position={position} setPosition={handlePositionChange} />
      </MapContainer>
    </div>
  );
}
