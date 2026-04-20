<?php
// app/Models/GpsTracking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'attendance_id', 'latitude', 'longitude', 'accuracy', 'tracked_at'
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}