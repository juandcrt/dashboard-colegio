<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Redirigir la raíz '/' al Dashboard por defecto
Route::get('/', function () {
    return redirect()->route('dashboard.classroom.default');
});

// Grupo de rutas del Dashboard Pedagógico
Route::prefix('dashboard')->group(function () {
    
    // Vista del salón por defecto (primer salón disponible)
    Route::get('/salon', [DashboardController::class, 'showClassroomDashboard'])
        ->name('dashboard.classroom.default');

    // Vista de un salón específico
    Route::get('/salon/{id}', [DashboardController::class, 'showClassroomDashboard'])
        ->name('dashboard.classroom');

    // Vista de detalle individual por estudiante
    Route::get('/estudiante/{id}', [DashboardController::class, 'showStudentDetail'])
        ->name('dashboard.student');

    // NUEVA RUTA: Módulo de Vistas de Notas (Filtro por AD, A, B, C)
    // Al pasar parámetros en la vista blade, Laravel los convertirá automáticamente 
    // en parámetros de consulta (ej: /dashboard/vistasnotas?letra=AD&competencia_id=1)
    Route::get('/vistasnotas', [DashboardController::class, 'showVistasNotas'])
        ->name('vistasnotas.index');
});