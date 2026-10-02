<?php

use App\Http\Controllers\Student\ClassController;
use App\Http\Controllers\Student\AssignmentController;
use App\Http\Controllers\Student\MaterialController;
use App\Http\Controllers\Student\QrCodeController;
use App\Http\Controllers\Student\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'approved', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments/{assignment}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('/assignments/{assignment}/download', [AssignmentController::class, 'download'])->name('assignments.download');
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{schoolClass}', [ClassController::class, 'show'])->name('classes.show');
    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::get('/qr', [QrCodeController::class, 'index'])->name('qr.index');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
});
