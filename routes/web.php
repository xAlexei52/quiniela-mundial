<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\BracketController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

// Setup vía web para hosting sin SSH (protegido por token, ver config/quiniela.php)
Route::get('/setup/{token}', [SetupController::class, 'run'])->name('setup');

// Páginas públicas (solo lectura, sin login)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/participantes', [ParticipantController::class, 'index'])->name('participantes');
Route::get('/grupos', [GroupController::class, 'index'])->name('grupos');
Route::get('/eliminatorias', [BracketController::class, 'index'])->name('bracket');

// Panel de administración protegido por PIN (sin sistema de usuarios)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

    Route::middleware('admin.pin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::post('/participantes', [AdminController::class, 'storeParticipant'])->name('participants.store');
        Route::put('/participantes/{participant}', [AdminController::class, 'updateParticipant'])->name('participants.update');
        Route::delete('/participantes/{participant}', [AdminController::class, 'destroyParticipant'])->name('participants.destroy');

        Route::post('/participantes/{participant}/equipos', [AdminController::class, 'assignTeam'])->name('teams.assign');
        Route::delete('/equipos/{team}/asignacion', [AdminController::class, 'unassignTeam'])->name('teams.unassign');
        Route::post('/equipos/repartir', [AdminController::class, 'randomFill'])->name('teams.random');
        Route::post('/equipos/limpiar', [AdminController::class, 'clearAssignments'])->name('teams.clear');

        Route::post('/sync', [AdminController::class, 'sync'])->name('sync');
    });
});
