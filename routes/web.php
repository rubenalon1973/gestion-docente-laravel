<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TeacherAvailabilityController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherDocumentController;
use App\Http\Controllers\TeacherExportController;
use App\Http\Controllers\TeacherQualificationController;
use App\Http\Controllers\TeacherServicePriceController;
use App\Http\Controllers\TeacherSubjectController;
use App\Http\Controllers\TeacherToolController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('teachers.index'))->name('home');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::patch('/services/{service}/status', [ServiceController::class, 'updateStatus'])
        ->name('services.update-status');
    Route::resource('services', ServiceController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::patch('/teachers/{teacher}/status', [TeacherController::class, 'updateStatus'])
        ->name('teachers.update-status');
    Route::get('/teachers/export/pdf', [TeacherExportController::class, 'indexPdf'])
        ->name('teachers.export.pdf');
    Route::get('/teachers/export/csv', [TeacherExportController::class, 'indexCsv'])
        ->name('teachers.export.csv');
    Route::get('/teachers/{teacher}/export/pdf', [TeacherExportController::class, 'showPdf'])
        ->name('teachers.export-one.pdf');
    Route::get('/teachers/{teacher}/export/csv', [TeacherExportController::class, 'showCsv'])
        ->name('teachers.export-one.csv');
    Route::resource('teachers.subjects', TeacherSubjectController::class)
        ->only(['store', 'update', 'destroy'])
        ->scoped();
    Route::resource('teachers.availabilities', TeacherAvailabilityController::class)
        ->only(['store', 'edit', 'update', 'destroy'])
        ->scoped();
    Route::resource('teachers.qualifications', TeacherQualificationController::class)
        ->only(['store', 'edit', 'update', 'destroy'])
        ->scoped();
    Route::scopeBindings()->group(function (): void {
        Route::post('/teachers/{teacher}/tools', [TeacherToolController::class, 'store'])
            ->name('teachers.tools.store');
        Route::get('/teachers/{teacher}/tools/{teacherTool}/edit', [TeacherToolController::class, 'edit'])
            ->name('teachers.tools.edit');
        Route::put('/teachers/{teacher}/tools/{teacherTool}', [TeacherToolController::class, 'update'])
            ->name('teachers.tools.update');
        Route::delete('/teachers/{teacher}/tools/{teacherTool}', [TeacherToolController::class, 'destroy'])
            ->name('teachers.tools.destroy');
        Route::post('/teachers/{teacher}/documents', [TeacherDocumentController::class, 'store'])
            ->name('teachers.documents.store');
        Route::get('/teachers/{teacher}/documents/{document}/download', [TeacherDocumentController::class, 'download'])
            ->name('teachers.documents.download');
        Route::delete('/teachers/{teacher}/documents/{document}', [TeacherDocumentController::class, 'destroy'])
            ->name('teachers.documents.destroy');
        Route::post('/teachers/{teacher}/prices', [TeacherServicePriceController::class, 'store'])
            ->name('teachers.prices.store');
        Route::patch('/teachers/{teacher}/prices/{teacherServicePrice}/close', [TeacherServicePriceController::class, 'close'])
            ->name('teachers.prices.close');
    });
    Route::resource('teachers', TeacherController::class)->except('destroy');
});
