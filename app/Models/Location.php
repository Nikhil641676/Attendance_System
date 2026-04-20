<?php
// app/Models/Location.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'latitude', 'longitude', 'radius', 'address', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_locations');
    }

    
// public function checkDistance($userLat, $userLng)
// {
//     $earthRadius = 6371000; // Earth's radius in meters
    
//     $latFrom = deg2rad($this->latitude);
//     $lonFrom = deg2rad($this->longitude);
//     $latTo   = deg2rad($userLat);
//     $lonTo   = deg2rad($userLng);
    
//     $latDelta = $latTo - $latFrom;
//     $lonDelta = $lonTo - $lonFrom;
    
//     $a = sin($latDelta / 2) ** 2 +
//          cos($latFrom) * cos($latTo) *
//          sin($lonDelta / 2) ** 2;
    
//     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
//     return $earthRadius * $c; // distance in meters
// }


 function checkDistance($lat1, $lon1, $lat2, $lon2)
{
        $earthRadius = 6371 * 1000; // Radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
 }


    // public function isWithinRange($userLat, $userLng)
    // {
    //     $distance = $this->checkDistance($userLat, $userLng);
    //     return $distance <= $this->radius;
    // }
}