import { useState, useEffect, lazy, Suspense } from 'react';
import { useBuildingStore } from '../../../store/buildingStore';
import { useMapUsageTracker } from '../../../hooks/useGoogleMapsOptimizer';
import { MapPin, BarChart3, AlertTriangle, RefreshCw } from 'lucide-react';

const BuildingMap = lazy(() => import('./BuildingMap'));

function MapFallback() {
  return (
    <div className="flex items-center justify-center h-[600px] bg-gray-50 rounded-lg border">
      <div className="text-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600 mx-auto" />
        <p className="text-sm text-gray-500 mt-3">Loading map component...</p>
      </div>
    </div>
  );
}

export default function BuildingsMap() {
  const { items: buildings, fetchItems, loading } = useBuildingStore();
  const [selectedBuilding, setSelectedBuilding] = useState(null);
  const [mapLoaded, setMapLoaded] = useState(false);
  const usage = useMapUsageTracker();

  useEffect(() => {
    fetchItems({ per_page: 1000 });
  }, [fetchItems]);

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Buildings Map</h1>
          <p className="text-sm text-gray-500">
            {buildings.length} buildings • {buildings.filter(b => b.latitude && b.longitude).length} located on map
          </p>
        </div>
        <div className="flex items-center gap-2">
          <UsageStats usage={usage} />
        </div>
      </div>

      {loading ? (
        <div className="flex items-center justify-center h-64">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600" />
        </div>
      ) : !mapLoaded ? (
        <div className="bg-white rounded-lg border overflow-hidden">
          <div className="flex flex-col items-center justify-center h-[600px] bg-gradient-to-br from-gray-50 to-gray-100">
            <div className="text-center max-w-md">
              <MapPin className="w-16 h-16 text-red-400 mx-auto mb-4" />
              <h2 className="text-lg font-semibold text-gray-900 mb-2">Load Building Map</h2>
              {usage.isOverLimit && (
                <div className="flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
                  <AlertTriangle className="w-4 h-4 flex-shrink-0" />
                  You've reached the monthly free tier limit. Google Maps may charge per additional event.
                </div>
              )}

              {usage.isNearLimit && !usage.isOverLimit && (
                <div className="flex items-center gap-2 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-lg mb-4 text-sm text-yellow-700">
                  <AlertTriangle className="w-4 h-4 flex-shrink-0" />
                  You've used {usage.percentUsed}% of your free tier ({usage.remaining.toLocaleString()} events remaining).
                </div>
              )}

              <div className="flex flex-col gap-3 items-center">
                <button
                  onClick={() => setMapLoaded(true)}
                  disabled={usage.isOverLimit}
                  className="px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  {usage.isOverLimit ? 'Monthly Limit Reached' : 'Load Map'}
                </button>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="bg-white rounded-lg border overflow-hidden">
          <Suspense fallback={<MapFallback />}>
            <BuildingMap
              buildings={buildings}
              onBuildingSelect={setSelectedBuilding}
              height="600px"
            />
          </Suspense>
          <div className="p-3 border-t bg-gray-50 flex items-center justify-between">
            <p className="text-xs text-gray-500">
              Map loaded • Usage: {usage.totalEvents.toLocaleString()} / 70,000 monthly events
            </p>
            <button
              onClick={() => setMapLoaded(false)}
              className="text-xs text-red-600 hover:text-red-700 flex items-center gap-1"
            >
              <RefreshCw className="w-3 h-3" /> Unload Map
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

function UsageStats({ usage }) {
  const [expanded, setExpanded] = useState(false);

  return (
    <div className="relative">
      <button
        onClick={() => setExpanded(!expanded)}
        className={`flex items-center gap-2 px-3 py-2 rounded-lg text-xs border transition-colors ${
          usage.isOverLimit ? 'bg-red-50 border-red-200 text-red-700' :
          usage.isNearLimit ? 'bg-yellow-50 border-yellow-200 text-yellow-700' :
          'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'
        }`}
      >
        <BarChart3 className="w-3.5 h-3.5" />
        <span className="font-medium">{usage.totalEvents.toLocaleString()} / 70,000</span>
        <span className="text-[10px] opacity-70">({usage.percentUsed}%)</span>
      </button>

      {expanded && (
        <div className="absolute right-0 top-full mt-1 w-64 bg-white border rounded-lg shadow-lg p-4 z-30">
          <h4 className="text-sm font-semibold text-gray-900 mb-3">Google Maps Usage</h4>
          <div className="w-full bg-gray-200 rounded-full h-2 mb-3">
            <div
              className={`h-2 rounded-full transition-all ${
                usage.isOverLimit ? 'bg-red-500' :
                usage.isNearLimit ? 'bg-yellow-500' : 'bg-green-500'
              }`}
              style={{ width: `${Math.min(usage.percentUsed, 100)}%` }}
            />
          </div>
          <div className="space-y-2 text-xs">
            <div className="flex justify-between">
              <span className="text-gray-500">Map Loads</span>
              <span className="font-medium">{usage.usage.mapLoads.toLocaleString()}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-500">Geocoding</span>
              <span className="font-medium">{usage.usage.geocodeRequests.toLocaleString()}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-500">Places API</span>
              <span className="font-medium">{usage.usage.placesRequests.toLocaleString()}</span>
            </div>
            <div className="border-t pt-2 flex justify-between font-medium">
              <span className="text-gray-700">Remaining</span>
              <span className={usage.isOverLimit ? 'text-red-600' : 'text-green-600'}>
                {usage.remaining.toLocaleString()}
              </span>
            </div>
          </div>
          <p className="text-[10px] text-gray-400 mt-3">Resets on the 1st of each month</p>
        </div>
      )}
    </div>
  );
}
