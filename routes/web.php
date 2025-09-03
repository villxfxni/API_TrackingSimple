<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\SolicitanteController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\DonacionController;

Route::get('/', fn() => redirect()->route('usuarios.index'));


//Route::middleware(['auth', 'verified'])->group(function () {
//    Route::get('dashboard', function () {
//        return Inertia::render('dashboard');
//    })->name('dashboard');
//});

Route::resources([
    'usuarios'      => UsuarioController::class,
    'solicitantes'  => SolicitanteController::class,
    'solicitudes'   => SolicitudController::class,
    'donaciones'    => DonacionController::class,
]);
Route::get('/usuarios-ui', function () {
    return view('usuarios.index');
})->name('usuarios.ui');
Route::get('/solicitudes-ui', fn() => view('solicitudes.index'))->name('solicitudes.ui');
Route::get('/donaciones-ui', fn() => view('donaciones.index'))->name('donaciones.ui');
Route::get('/solicitantes-ui', fn() => view('solicitantes.index'))->name('solicitantes.ui');
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
