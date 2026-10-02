<?php

use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::post('/users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('/users/{user}/password/reset-link', [UserController::class, 'sendResetLink'])->name('users.password.reset-link');

    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approval/{user}/approve', [ApprovalController::class, 'approve'])->name('approval.approve');
    Route::post('/approval/{user}/reject', [ApprovalController::class, 'reject'])->name('approval.reject');

    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{schoolClass}', [ClassController::class, 'show'])->name('classes.show');
    Route::post('/classes/{schoolClass}/teachers', [ClassController::class, 'assignTeacher'])->name('classes.teachers.store');
    Route::delete('/classes/{schoolClass}/teachers/{user}', [ClassController::class, 'removeTeacher'])->name('classes.teachers.destroy');
    Route::post('/classes/{schoolClass}/students', [ClassController::class, 'enrollStudent'])->name('classes.students.store');
    Route::delete('/classes/{schoolClass}/students/{user}', [ClassController::class, 'removeStudent'])->name('classes.students.destroy');
    Route::post('/classes/{schoolClass}/subject-teachers', [ClassController::class, 'assignSubjectTeacher'])->name('classes.subject-teachers.store');
    Route::patch('/classes/{schoolClass}/subject-teachers/{subjectTeacher}', [ClassController::class, 'updateSubjectTeacher'])->name('classes.subject-teachers.update');
    Route::delete('/classes/{schoolClass}/subject-teachers/{subjectTeacher}', [ClassController::class, 'removeSubjectTeacher'])->name('classes.subject-teachers.destroy');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Assignments
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/download', [AssignmentController::class, 'download'])->name('assignments.download');

    // Materials
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/sessions', [AttendanceController::class, 'store'])->name('attendance.sessions.store');
    Route::post('/attendance/sessions/close', [AttendanceController::class, 'close'])->name('attendance.sessions.close');
    Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
});
