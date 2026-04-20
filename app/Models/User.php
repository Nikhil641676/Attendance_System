<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 'manager_id', 'gps_tracking_enabled'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'gps_tracking_enabled' => 'boolean',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'employee_locations');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function gpsTrackings()
    {
        return $this->hasMany(GpsTracking::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function team()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)->where('date', today());
    }
}