<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\DocumentosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProyeccionSocialController;
use App\Http\Controllers\SeguimientoGraduadoController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\PublicCertificateController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/nosotros', AboutUsController::class)->name('about-us');
Route::get('/proyeccion-social', ProyeccionSocialController::class)->name('proyeccion-social');
Route::get('/seguimiento-graduado', SeguimientoGraduadoController::class)->name('seguimiento-graduado');
Route::get('/documentos', DocumentosController::class)->name('documentos');
Route::get('/eventos', EventosController::class)->name('eventos');
Route::inertia('/sandbox', 'public/Sandbox')->name('sandbox');

// Public Certificates Routes
Route::get('/certificados', [PublicCertificateController::class, 'index'])->name('certificates.search_page');
Route::post('/certificados/buscar', [PublicCertificateController::class, 'search'])->name('certificates.search');
Route::get('/certificados/descargar/{certificate}', [PublicCertificateController::class, 'download'])->name('certificates.download');
Route::get('/verificar-certificado/{identifier}', [PublicCertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';

