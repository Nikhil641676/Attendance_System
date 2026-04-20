<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ManagerController;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin Routes
Route::middleware(['auth', 'role:super-admin|admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/employees', [AdminController::class, 'employees'])->name('employees');
    Route::post('/employees', [AdminController::class, 'storeEmployee'])->name('employees.store');
    Route::put('/employees/{user}', [AdminController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{user}', [AdminController::class, 'deleteEmployee'])->name('employees.delete');
    
    Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
    Route::post('/locations', [AdminController::class, 'storeLocation'])->name('locations.store');
    Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('locations.delete');
    
    Route::get('/attendance-report', [AdminController::class, 'attendanceReport'])->name('attendance.report');
    Route::get('/export-attendance', [AdminController::class, 'exportAttendance'])->name('attendance.export');
    
    Route::get('/gps-reports', [AdminController::class, 'gpsReports'])->name('gps.reports');
    Route::get('/gps-data/{user}', [AdminController::class, 'getUserGpsData'])->name('gps.data');
    
    Route::get('/correction-requests', [AdminController::class, 'correctionRequests'])->name('corrections');
    Route::post('/correction-requests/{correction}', [AdminController::class, 'processCorrection'])->name('corrections.process');
});

// Manager Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/team-attendance', [ManagerController::class, 'teamAttendance'])->name('team.attendance');
    Route::get('/team-gps', [ManagerController::class, 'teamGpsTracking'])->name('team.gps');
    Route::get('/team-gps-data', [ManagerController::class, 'getTeamGpsData'])->name('team.gps.data');
});

// Employee Routes
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
    Route::post('/clockin', [EmployeeController::class, 'clockIn'])->name('clockin');
    Route::post('/clockout', [EmployeeController::class, 'clockOut'])->name('clockout');
    Route::post('/gps/save', [EmployeeController::class, 'saveGpsLocation'])->name('gps.save');
    Route::get('/history', [EmployeeController::class, 'history'])->name('history');

    Route::post('/correction', [EmployeeController::class, 'requestCorrection'])->name('correction');
    Route::post('/location-status', [EmployeeController::class, 'getLocationStatus'])->name('location.status');
});