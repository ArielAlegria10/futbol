<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JornadaController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\TemporadaController;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\ClasificacionController;

// ==============================================
// RUTA INICIAL
// ==============================================

Route::get('/', [DashboardController::class, 'index'])->name('home');

// ==============================================
// DASHBOARD CONTROLLER ROUTES
// ==============================================

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/exportar', [DashboardController::class, 'exportar'])->name('dashboard.exportar');
Route::get('/dashboard/widget', [DashboardController::class, 'widget'])->name('dashboard.widget');

// ==============================================
// EQUIPO CONTROLLER ROUTES (CORREGIDAS)
// ==============================================

// Rutas básicas CRUD para equipos
Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
Route::get('/equipos/create', [EquipoController::class, 'create'])->name('equipos.create');
Route::post('/equipos', [EquipoController::class, 'store'])->name('equipos.store');
Route::get('/equipos/{equipo}', [EquipoController::class, 'show'])->name('equipos.show');
Route::get('/equipos/{equipo}/edit', [EquipoController::class, 'edit'])->name('equipos.edit');
Route::put('/equipos/{equipo}', [EquipoController::class, 'update'])->name('equipos.update');
Route::delete('/equipos/{equipo}', [EquipoController::class, 'destroy'])->name('equipos.destroy');

// Rutas adicionales para funcionalidades AJAX
Route::post('/equipos/{equipo}/toggle-status', [EquipoController::class, 'toggleStatus'])
    ->name('equipos.toggle-status');

Route::post('/equipos/bulk-action', [EquipoController::class, 'bulkAction'])
    ->name('equipos.bulk-action');

Route::get('/equipos/search', [EquipoController::class, 'search'])
    ->name('equipos.search');

Route::get('/equipos/{equipo}/data', [EquipoController::class, 'getEquipoData'])
    ->name('equipos.data');

// ==============================================
// JORNADA CONTROLLER ROUTES (REORGANIZADAS)
// ==============================================

// Rutas públicas de jornadas
Route::get('/jornadas', [JornadaController::class, 'indexPublico'])->name('jornadas.publico.index');
Route::get('/jornadas/{jornada}', [JornadaController::class, 'showPublico'])->name('jornadas.publico.show');
Route::get('/calendario', [JornadaController::class, 'calendarioPublico'])->name('jornadas.publico.calendario');

// Rutas de administración de jornadas
Route::prefix('admin')->group(function () {
    Route::get('/jornadas', [JornadaController::class, 'index'])->name('jornadas.index');
    Route::get('/jornadas/create', [JornadaController::class, 'create'])->name('jornadas.create');
    Route::post('/jornadas', [JornadaController::class, 'store'])->name('jornadas.store');
    Route::get('/jornadas/{jornada}', [JornadaController::class, 'show'])->name('jornadas.show');
    Route::get('/jornadas/{jornada}/edit', [JornadaController::class, 'edit'])->name('jornadas.edit');
    Route::put('/jornadas/{jornada}', [JornadaController::class, 'update'])->name('jornadas.update');
    Route::delete('/jornadas/{jornada}', [JornadaController::class, 'destroy'])->name('jornadas.destroy');
    
    // Rutas adicionales de jornadas
    Route::get('/jornadas/{jornada}/completar', [JornadaController::class, 'completar'])->name('jornadas.completar');
    Route::get('/jornadas/{jornada}/exportar', [JornadaController::class, 'exportar'])->name('jornadas.exportar');
    Route::get('/jornadas/{jornada}/duplicar', [JornadaController::class, 'duplicar'])->name('jornadas.duplicar');
    Route::post('/jornadas/{jornada}/reorganizar', [JornadaController::class, 'reorganizar'])->name('jornadas.reorganizar');
    
    Route::get('/jornadas/generar/calendario', [JornadaController::class, 'createGenerarCalendario'])->name('jornadas.calendario.create');
    Route::post('/jornadas/generar/calendario', [JornadaController::class, 'generarCalendario'])->name('jornadas.calendario.store');
    Route::get('/jornadas/estadisticas', [JornadaController::class, 'estadisticas'])->name('jornadas.estadisticas');
});

// ==============================================
// RUTAS ADICIONALES PARA EL MENÚ
// ==============================================

// Temporadas
Route::get('/temporadas', [TemporadaController::class, 'index'])->name('temporadas.index');
Route::get('/temporadas/create', [TemporadaController::class, 'create'])->name('temporadas.create');
Route::post('/temporadas', [TemporadaController::class, 'store'])->name('temporadas.store');
Route::get('/temporadas/actual', [TemporadaController::class, 'actual'])->name('temporadas.actual');
Route::get('/temporadas/historial', [TemporadaController::class, 'historial'])->name('temporadas.historial');

// Partidos
// routes/web.php


// Rutas principales de partidos
Route::get('/partidos', [PartidoController::class, 'index'])->name('partidos.index');
Route::get('/partidos/create', [PartidoController::class, 'create'])->name('partidos.create');
Route::post('/partidos', [PartidoController::class, 'store'])->name('partidos.store');
Route::get('/partidos/{partido}', [PartidoController::class, 'show'])->name('partidos.show');
Route::get('/partidos/{partido}/edit', [PartidoController::class, 'edit'])->name('partidos.edit');
Route::put('/partidos/{partido}', [PartidoController::class, 'update'])->name('partidos.update');
Route::delete('/partidos/{partido}', [PartidoController::class, 'destroy'])->name('partidos.destroy');

// Rutas adicionales
Route::get('/partidos/calendario', [PartidoController::class, 'calendario'])->name('partidos.calendario');
Route::get('/partidos/en-vivo', [PartidoController::class, 'enVivo'])->name('partidos.en-vivo');
Route::get('/partidos/{partido}/estadisticas', [PartidoController::class, 'estadisticas'])->name('partidos.estadisticas');
Route::get('/partidos/proximos', [PartidoController::class, 'proximos'])->name('partidos.proximos'); // Nueva ruta

// Rutas AJAX
Route::post('/partidos/{partido}/actualizar-marcador', [PartidoController::class, 'actualizarMarcador'])->name('partidos.actualizar-marcador');
Route::post('/partidos/{partido}/cambiar-estado', [PartidoController::class, 'cambiarEstado'])->name('partidos.cambiar-estado');

// Clasificaciones
Route::middleware(['web'])->group(function () {
    
    // Grupo principal de clasificación
    Route::prefix('clasificacion')->name('clasificacion.')->group(function () {
        
        // Página principal de clasificación
        Route::get('/', [ClasificacionController::class, 'index'])
            ->name('index');
        
        // Detalle de un equipo en la clasificación
        Route::get('/equipo/{id}', [ClasificacionController::class, 'show'])
            ->name('show')
            ->where('id', '[0-9]+');
        
        // Comparar dos equipos
        Route::get('/comparar', [ClasificacionController::class, 'comparar'])
            ->name('comparar');
        
        // Estadísticas avanzadas
        Route::get('/estadisticas-avanzadas', [ClasificacionController::class, 'estadisticasAvanzadas'])
            ->name('estadisticas-avanzadas');
        
        // Exportar clasificación (POST para enviar parámetros)
        Route::post('/exportar', [ClasificacionController::class, 'exportar'])
            ->name('exportar');
        
        // Actualizar manualmente la clasificación
        Route::post('/actualizar', [ClasificacionController::class, 'actualizar'])
            ->name('actualizar');
    });
    
    // Rutas alternativas si prefieres URLs más directas
    Route::get('tabla-posiciones', [ClasificacionController::class, 'index'])
        ->name('tabla-posiciones');
    
    Route::get('comparar-equipos', [ClasificacionController::class, 'comparar'])
        ->name('comparar-equipos');
});

// Si necesitas protección de autenticación para algunas acciones
Route::middleware(['web', 'auth'])->group(function () {
    // Solo usuarios autenticados pueden actualizar
    Route::post('/clasificacion/actualizar', [ClasificacionController::class, 'actualizar'])
        ->name('clasificacion.actualizar');
    
    // Solo usuarios autenticados pueden exportar
    Route::post('/clasificacion/exportar', [ClasificacionController::class, 'exportar'])
        ->name('clasificacion.exportar');
});

// Versión más simple y directa:
Route::group(['prefix' => 'clasificacion', 'as' => 'clasificacion.'], function () {
    Route::get('/', [ClasificacionController::class, 'index'])->name('index');
    Route::get('equipo/{id}', [ClasificacionController::class, 'show'])->name('show');
    Route::get('comparar', [ClasificacionController::class, 'comparar'])->name('comparar');
    Route::get('estadisticas-avanzadas', [ClasificacionController::class, 'estadisticasAvanzadas'])->name('estadisticas-avanzadas');
    Route::post('exportar', [ClasificacionController::class, 'exportar'])->name('exportar');
    Route::post('actualizar', [ClasificacionController::class, 'actualizar'])->name('actualizar');
});

// ==============================================
// RUTAS DE AUTENTICACIÓN SIMULADAS
// ==============================================

Route::get('/profile', function () {
    return redirect()->route('dashboard');
})->name('profile.edit');

Route::get('/settings', function () {
    return redirect()->route('dashboard');
})->name('settings');

Route::post('/logout', function () {
    return redirect()->route('dashboard');
})->name('logout');

Route::get('/login', function () {
    return redirect()->route('dashboard');
})->name('login');

// ==============================================
// RUTAS DE FALLBACK Y ERRORES
// ==============================================

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

Route::resource('partidos', PartidoController::class);

// Rutas adicionales
