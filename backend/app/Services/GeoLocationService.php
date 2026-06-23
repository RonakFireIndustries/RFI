<?php

namespace App\Services;

class GeoLocationService
{
    /**
     * Calculate the distance between two GPS coordinates using the Haversine formula.
     * 
     * @param float $lat1 Site Latitude
     * @param float $lon1 Site Longitude
     * @param float $lat2 Employee Latitude
     * @param float $lon2 Employee Longitude
     * @return float Distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        // Convert coordinates to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    public function isAccuracyAcceptable($accuracy)
    {
        if ($accuracy === null) return false;
        return $accuracy <= $this->getMaxAccuracyThreshold();
    }

    public function getMaxAccuracyThreshold()
    {
        return 100.0;
    }

    public function isWithinRadius($distance, $allowedRadius)
    {
        return $distance <= $allowedRadius;
    }
}
