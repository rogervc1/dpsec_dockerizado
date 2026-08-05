<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminDocumentController;
use App\Http\Controllers\Admin\AdminCertificateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard overview
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Events CRUD
    Route::post('/eventos', [AdminEventController::class, 'store'])->name('eventos.store');
    Route::put('/eventos/{event}', [AdminEventController::class, 'update'])->name('eventos.update');
    Route::delete('/eventos/{event}', [AdminEventController::class, 'destroy'])->name('eventos.destroy');

    // Documents CRUD
    Route::post('/documentos', [AdminDocumentController::class, 'store'])->name('documentos.store');
    Route::put('/documentos/{document}', [AdminDocumentController::class, 'update'])->name('documentos.update');
    Route::delete('/documentos/{document}', [AdminDocumentController::class, 'destroy'])->name('documentos.destroy');

    // Certificates CRUD & Management
    Route::post('/certificados/plantilla', [AdminCertificateController::class, 'storeTemplate'])->name('certificados.template.store');
    Route::post('/certificados/plantilla/{template}', [AdminCertificateController::class, 'updateTemplate'])->name('certificados.template.update');
    Route::delete('/certificados/plantilla/{template}', [AdminCertificateController::class, 'destroyTemplate'])->name('certificados.template.destroy');
    Route::post('/certificados/importar', [AdminCertificateController::class, 'import'])->name('certificados.import');
    Route::delete('/certificados/{certificate}', [AdminCertificateController::class, 'destroyCertificate'])->name('certificados.destroy');

    // Font upload
    Route::post('/certificados/tipografia', [AdminCertificateController::class, 'storeFont'])->name('certificados.font.store');
    Route::delete('/certificados/tipografia/{font}', [AdminCertificateController::class, 'destroyFont'])->name('certificados.font.destroy');
});

