<?php
// app/Models/AttendanceCorrection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrection extends Model
{
    use HasFactory;

    protected $table = 'attendance_corrections';

    protected $fillable = [
        'user_id', 'attendance_date', 'requested_clock_in', 'requested_clock_out',
        'reason', 'status', 'admin_remarks', 'processed_by'
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}