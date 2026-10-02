<?php

use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\ClassController;
use App\Http\Controllers\Teacher\MaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'approved', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{schoolClass}', [ClassController::class, 'show'])->name('classes.show');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/download', [AssignmentController::class, 'download'])->name('assignments.download');
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/sessions', [AttendanceController::class, 'store'])->name('attendance.sessions.store');
    Route::post('/attendance/sessions/close', [AttendanceController::class, 'close'])->name('attendance.sessions.close');
    Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
});
