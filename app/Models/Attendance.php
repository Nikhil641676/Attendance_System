<?php
// app/Models/Attendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'clock_in_time', 'clock_out_time',
        'clock_in_latitude', 'clock_in_longitude', 'clock_out_latitude', 'clock_out_longitude',
        'clock_in_distance', 'clock_out_distance', 'clock_in_location_id', 'clock_out_location_id',
        'total_hours', 'status', 'remarks', 'is_corrected'
    ];

    protected $casts = [
        'date' => 'date',
        'is_corrected' => 'boolean',
    


    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clockInLocation()
    {
        return $this->belongsTo(Location::class, 'clock_in_location_id');
    }

    public function clockOutLocation()
    {
        return $this->belongsTo(Location::class, 'clock_out_location_id');
    }

    public function gpsTrackings()
    {
        return $this->hasMany(GpsTracking::class);
    }

    public function calculateTotalHours()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $clockIn = strtotime($this->clock_in_time);
            $clockOut = strtotime($this->clock_out_time);
            $diff = $clockOut - $clockIn;
            $this->total_hours = round($diff / 3600, 2);
            $this->save();
        }
    }

    public function calculateStatus()
    {
        if (!$this->clock_in_time) {
            $this->status = 'absent';
            $this->save();
            return;
        }

        $clockInTime = strtotime($this->clock_in_time);

        $cutoffTime = strtotime('09:45:00'); // 9:30 + 15 min grace
        $lateLimit = strtotime('10:00:00'); // 30 min after grace
       // dd($clockInTime);
        if ($clockInTime <= $cutoffTime) {
            $this->status = 'present';
        } elseif ($clockInTime <= $lateLimit) {
            // Check late count for the month
            $lateCount = Attendance::where('user_id', $this->user_id)
                ->whereMonth('date', date('m', strtotime($this->date)))
                ->where('status', 'late')
                ->count();
            
            if ($lateCount < 3) {
                $this->status = 'late';
            } else {
                $this->status = 'half_day';
            }
        } else {
            $this->status = 'half_day';
        }
        
        $this->save();
    }
}