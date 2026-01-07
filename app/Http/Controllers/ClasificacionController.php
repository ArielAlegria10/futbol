<?php

namespace App\Http\Controllers;

use App\Models\Clasificacion;
use App\Models\Temporada;
use App\Models\Equipo;
use App\Models\Partido;
use App\Models\Jornada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClasificacionExport;
use Carbon\Carbon;

class ClasificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Obtener temporada activa o específica
            $temporadaId = $request->get('temporada_id');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->route('temporadas.index')
                    ->with('warning', 'No hay temporadas disponibles. Crea una temporada para comenzar.');
            }
            
            // Obtener clasificación con cache
            $cacheKey = 'clasificacion_' . $temporada->id . '_' . $request->get('page', 1);
            $clasificacion = Cache::remember($cacheKey, 300, function () use ($temporada) {
                return $this->obtenerClasificacionCompleta($temporada);
            });
            
            // Estadísticas generales
            $estadisticas = $this->calcularEstadisticasGenerales($clasificacion, $temporada);
            
            // Top goleadores
            $topGoleadores = $this->obtenerTopGoleadores($temporada);
            
            // Evolución de posiciones
            $evolucionPosiciones = $this->obtenerEvolucionPosiciones($temporada);
            
            // Temporadas disponibles
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            
            // Filtros activos
            $filtrosActivos = [
                'temporada_id' => $temporada->id,
                'ver_historico' => $request->has('historico'),
                'ver_detallado' => $request->has('detallado'),
            ];
            
            return view('clasificacion.index', compact(
                'clasificacion',
                'estadisticas',
                'temporada',
                'temporadas',
                'topGoleadores',
                'evolucionPosiciones',
                'filtrosActivos'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@index: ' . $e->getMessage());
            
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la clasificación. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $clasificacion = Clasificacion::with([
                'equipo',
                'temporada',
                'equipo.partidosLocal',
                'equipo.partidosVisitante'
            ])->findOrFail($id);
            
            // Estadísticas detalladas del equipo en esta temporada
            $estadisticasDetalladas = $this->calcularEstadisticasDetalladas($clasificacion);
            
            // Historial de posiciones en la temporada
            $historialPosiciones = $this->obtenerHistorialPosiciones($clasificacion);
            
            // Comparativa con promedio de la liga
            $comparativaLiga = $this->obtenerComparativaLiga($clasificacion);
            
            // Próximos partidos del equipo
            $proximosPartidos = $this->obtenerProximosPartidosEquipo($clasificacion->equipo_id, $clasificacion->temporada_id);
            
            // Últimos partidos del equipo
            $ultimosPartidos = $this->obtenerUltimosPartidosEquipo($clasificacion->equipo_id, $clasificacion->temporada_id);
            
            return view('clasificacion.show', compact(
                'clasificacion',
                'estadisticasDetalladas',
                'historialPosiciones',
                'comparativaLiga',
                'proximosPartidos',
                'ultimosPartidos'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@show: ' . $e->getMessage());
            
            return redirect()->route('clasificacion.index')
                ->with('error', 'Registro de clasificación no encontrado.');
        }
    }

    /**
     * Muestra comparativa entre equipos
     */
    public function comparar(Request $request)
    {
        try {
            $equipo1Id = $request->get('equipo1_id');
            $equipo2Id = $request->get('equipo2_id');
            $temporadaId = $request->get('temporada_id');
            
            $temporada = $this->obtenerTemporada($temporadaId);
            $equipos = Equipo::where('activo', true)->orderBy('nombre')->get();
            
            $comparativa = null;
            $equipo1 = null;
            $equipo2 = null;
            
            if ($equipo1Id && $equipo2Id) {
                $equipo1 = Equipo::find($equipo1Id);
                $equipo2 = Equipo::find($equipo2Id);
                
                if ($equipo1 && $equipo2) {
                    $comparativa = $this->compararEquiposDetallado($equipo1, $equipo2, $temporada);
                }
            }
            
            return view('clasificacion.comparar', compact(
                'equipos',
                'temporada',
                'comparativa',
                'equipo1',
                'equipo2'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@comparar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al comparar equipos.');
        }
    }

    /**
     * Exporta clasificación
     */
    public function exportar(Request $request)
    {
        try {
            $temporadaId = $request->get('temporada_id');
            $formato = $request->get('formato', 'pdf');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->back()
                    ->with('error', 'No hay temporada seleccionada.');
            }
            
            $clasificacion = $this->obtenerClasificacionCompleta($temporada);
            $estadisticas = $this->calcularEstadisticasGenerales($clasificacion, $temporada);
            
            $nombreArchivo = 'clasificacion_' . $temporada->nombre . '_' . date('Ymd_His');
            
            switch ($formato) {
                case 'excel':
                    return Excel::download(new ClasificacionExport($clasificacion, $temporada), $nombreArchivo . '.xlsx');
                    
                case 'csv':
                    return Excel::download(new ClasificacionExport($clasificacion, $temporada), $nombreArchivo . '.csv');
                    
                case 'pdf':
                default:
                    $pdf = PDF::loadView('exports.clasificacion.pdf', [
                        'clasificacion' => $clasificacion,
                        'temporada' => $temporada,
                        'estadisticas' => $estadisticas
                    ]);
                    
                    return $pdf->download($nombreArchivo . '.pdf');
            }
            
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@exportar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al exportar la clasificación.');
        }
    }

    /**
     * Actualiza manualmente la clasificación
     */
    public function actualizar(Request $request)
    {
        try {
            $temporadaId = $request->get('temporada_id');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->back()
                    ->with('error', 'No hay temporada seleccionada.');
            }
            
            // Limpiar cache
            Cache::forget('clasificacion_' . $temporada->id . '_*');
            
            // Recalcular clasificación
            $this->recalcularClasificacion($temporada);
            
            return redirect()->route('clasificacion.index', ['temporada_id' => $temporada->id])
                ->with('success', 'Clasificación actualizada exitosamente.');
                
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@actualizar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al actualizar la clasificación.');
        }
    }

    /**
     * Muestra estadísticas avanzadas
     */
    public function estadisticasAvanzadas(Request $request)
    {
        try {
            $temporadaId = $request->get('temporada_id');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->route('temporadas.index')
                    ->with('warning', 'Selecciona una temporada para ver estadísticas.');
            }
            
            $estadisticas = $this->calcularEstadisticasAvanzadas($temporada);
            $tendencias = $this->analizarTendencias($temporada);
            $predicciones = $this->generarPredicciones($temporada);
            
            return view('clasificacion.estadisticas-avanzadas', compact(
                'temporada',
                'estadisticas',
                'tendencias',
                'predicciones'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en ClasificacionController@estadisticasAvanzadas: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al cargar estadísticas avanzadas.');
        }
    }

    // ==============================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ==============================================

    /**
     * Obtiene temporada por ID o la activa
     */
    private function obtenerTemporada(?int $temporadaId): ?Temporada
    {
        if ($temporadaId) {
            return Temporada::find($temporadaId);
        }
        
        return Temporada::where('estado', 'En Curso')->first() 
            ?? Temporada::orderBy('anio', 'DESC')->first();
    }

    /**
     * Obtiene clasificación completa con posiciones
     */
    private function obtenerClasificacionCompleta(Temporada $temporada)
    {
        return Clasificacion::with(['equipo'])
            ->where('temporada_id', $temporada->id)
            ->orderBy('puntos', 'DESC')
            ->orderBy('diferencia_goles', 'DESC')
            ->orderBy('goles_a_favor', 'DESC')
            ->orderBy('goles_en_contra', 'ASC')
            ->get()
            ->map(function ($item, $index) use ($temporada) {
                $item->posicion = $index + 1;
                
                // Calcular rendimiento
                $item->rendimiento = $item->partidos_jugados > 0 
                    ? round(($item->puntos / ($item->partidos_jugados * 3)) * 100, 1)
                    : 0;
                
                // Calcular promedio de goles
                $item->promedio_goles_favor = $item->partidos_jugados > 0 
                    ? round($item->goles_a_favor / $item->partidos_jugados, 2)
                    : 0;
                $item->promedio_goles_contra = $item->partidos_jugados > 0 
                    ? round($item->goles_en_contra / $item->partidos_jugados, 2)
                    : 0;
                
                // Calcular última racha
                $item->ultima_racha = $this->calcularRachaEquipo($item->equipo_id, $temporada->id);
                
                return $item;
            });
    }

    /**
     * Calcula estadísticas generales
     */
    private function calcularEstadisticasGenerales($clasificacion, Temporada $temporada): array
    {
        return [
            'total_equipos' => $clasificacion->count(),
            'total_partidos_jugados' => $clasificacion->sum('partidos_jugados') / 2,
            'total_goles' => $clasificacion->sum('goles_a_favor'),
            'promedio_goles_por_partido' => ($clasificacion->sum('partidos_jugados') / 2) > 0 
                ? round($clasificacion->sum('goles_a_favor') / ($clasificacion->sum('partidos_jugados') / 2), 2)
                : 0,
            'equipo_mejor_ataque' => $clasificacion->sortByDesc('goles_a_favor')->first(),
            'equipo_mejor_defensa' => $clasificacion->sortBy('goles_en_contra')->first(),
            'equipo_mejor_rendimiento' => $clasificacion->sortByDesc('rendimiento')->first(),
            'equipo_peor_rendimiento' => $clasificacion->where('partidos_jugados', '>', 0)->sortBy('rendimiento')->first(),
            'total_empates' => $clasificacion->sum('partidos_empatados'),
            'total_victorias_local' => $this->calcularVictoriasLocal($temporada),
            'total_victorias_visitante' => $this->calcularVictoriasVisitante($temporada),
        ];
    }

    /**
     * Obtiene top goleadores
     */
    private function obtenerTopGoleadores(Temporada $temporada, int $limit = 10)
    {
        // En un sistema real, esto vendría de una tabla de jugadores
        // Esta es una implementación simulada
        return collect([
            ['nombre' => 'Jugador 1', 'equipo' => 'Equipo A', 'goles' => 15, 'partidos' => 20, 'promedio' => 0.75],
            ['nombre' => 'Jugador 2', 'equipo' => 'Equipo B', 'goles' => 12, 'partidos' => 18, 'promedio' => 0.67],
            ['nombre' => 'Jugador 3', 'equipo' => 'Equipo C', 'goles' => 10, 'partidos' => 19, 'promedio' => 0.53],
            ['nombre' => 'Jugador 4', 'equipo' => 'Equipo D', 'goles' => 9, 'partidos' => 17, 'promedio' => 0.53],
            ['nombre' => 'Jugador 5', 'equipo' => 'Equipo E', 'goles' => 8, 'partidos' => 20, 'promedio' => 0.40],
        ]);
    }

    /**
     * Obtiene evolución de posiciones
     */
    private function obtenerEvolucionPosiciones(Temporada $temporada)
    {
        $equipos = Equipo::where('activo', true)->get();
        $jornadas = Jornada::where('temporada_id', $temporada->id)
            ->orderBy('numero')
            ->get();
        
        $evolucion = [];
        
        foreach ($equipos as $equipo) {
            $posiciones = [];
            
            foreach ($jornadas as $jornada) {
                // Obtener posición del equipo en esa jornada
                $posicion = $this->obtenerPosicionEnJornada($equipo->id, $jornada->id);
                if ($posicion !== null) {
                    $posiciones[] = $posicion;
                }
            }
            
            if (!empty($posiciones)) {
                $evolucion[] = [
                    'equipo' => $equipo,
                    'posiciones' => $posiciones,
                    'mejor_posicion' => min($posiciones),
                    'peor_posicion' => max($posiciones),
                    'tendencia' => $this->calcularTendenciaPosiciones($posiciones),
                ];
            }
        }
        
        return $evolucion;
    }

    /**
     * Calcula estadísticas detalladas
     */
    private function calcularEstadisticasDetalladas(Clasificacion $clasificacion): array
    {
        $equipoId = $clasificacion->equipo_id;
        $temporadaId = $clasificacion->temporada_id;
        
        // Obtener partidos del equipo
        $partidos = Partido::where('temporada_id', $temporadaId)
            ->where(function($q) use ($equipoId) {
                $q->where('equipo_local_id', $equipoId)
                  ->orWhere('equipo_visitante_id', $equipoId);
            })
            ->where('estado', 'Finalizado')
            ->get();
        
        // Estadísticas por localía
        $estadisticasLocal = [
            'total' => $partidos->where('equipo_local_id', $equipoId)->count(),
            'victorias' => $partidos->where('equipo_local_id', $equipoId)
                ->where('goles_local', '>', 'goles_visitante')->count(),
            'empates' => $partidos->where('equipo_local_id', $equipoId)
                ->where('goles_local', '=', 'goles_visitante')->count(),
            'derrotas' => $partidos->where('equipo_local_id', $equipoId)
                ->where('goles_local', '<', 'goles_visitante')->count(),
        ];
        
        $estadisticasVisitante = [
            'total' => $partidos->where('equipo_visitante_id', $equipoId)->count(),
            'victorias' => $partidos->where('equipo_visitante_id', $equipoId)
                ->where('goles_visitante', '>', 'goles_local')->count(),
            'empates' => $partidos->where('equipo_visitante_id', $equipoId)
                ->where('goles_visitante', '=', 'goles_local')->count(),
            'derrotas' => $partidos->where('equipo_visitante_id', $equipoId)
                ->where('goles_visitante', '<', 'goles_local')->count(),
        ];
        
        return [
            'estadisticas_local' => $estadisticasLocal,
            'estadisticas_visitante' => $estadisticasVisitante,
            'partidos_sin_encajar' => $partidos->filter(function($partido) use ($equipoId) {
                return ($partido->equipo_local_id == $equipoId && $partido->goles_visitante == 0) ||
                       ($partido->equipo_visitante_id == $equipoId && $partido->goles_local == 0);
            })->count(),
            'partidos_sin_marcar' => $partidos->filter(function($partido) use ($equipoId) {
                return ($partido->equipo_local_id == $equipoId && $partido->goles_local == 0) ||
                       ($partido->equipo_visitante_id == $equipoId && $partido->goles_visitante == 0);
            })->count(),
        ];
    }

    /**
     * Obtiene historial de posiciones
     */
    private function obtenerHistorialPosiciones(Clasificacion $clasificacion)
    {
        $jornadas = Jornada::where('temporada_id', $clasificacion->temporada_id)
            ->orderBy('numero')
            ->get();
        
        $historial = [];
        
        foreach ($jornadas as $jornada) {
            $posicion = $this->obtenerPosicionEnJornada($clasificacion->equipo_id, $jornada->id);
            
            if ($posicion !== null) {
                $historial[] = [
                    'jornada' => $jornada,
                    'posicion' => $posicion,
                    'puntos' => $this->obtenerPuntosEnJornada($clasificacion->equipo_id, $jornada->id),
                ];
            }
        }
        
        return $historial;
    }

    /**
     * Obtiene comparativa con liga
     */
    private function obtenerComparativaLiga(Clasificacion $clasificacion): array
    {
        $temporadaId = $clasificacion->temporada_id;
        
        $promediosLiga = DB::table('clasificacion')
            ->where('temporada_id', $temporadaId)
            ->selectRaw('
                AVG(puntos) as promedio_puntos,
                AVG(goles_a_favor) as promedio_goles_favor,
                AVG(goles_en_contra) as promedio_goles_contra,
                AVG(diferencia_goles) as promedio_diferencia,
                AVG(partidos_jugados) as promedio_partidos
            ')
            ->first();
        
        return [
            'puntos' => [
                'equipo' => $clasificacion->puntos,
                'promedio_liga' => round($promediosLiga->promedio_puntos, 1),
                'diferencia' => round($clasificacion->puntos - $promediosLiga->promedio_puntos, 1),
                'porcentaje_superior' => $promediosLiga->promedio_puntos > 0 
                    ? round(($clasificacion->puntos / $promediosLiga->promedio_puntos - 1) * 100, 1)
                    : 0,
            ],
            'goles_favor' => [
                'equipo' => $clasificacion->goles_a_favor,
                'promedio_liga' => round($promediosLiga->promedio_goles_favor, 1),
                'diferencia' => round($clasificacion->goles_a_favor - $promediosLiga->promedio_goles_favor, 1),
            ],
            'goles_contra' => [
                'equipo' => $clasificacion->goles_en_contra,
                'promedio_liga' => round($promediosLiga->promedio_goles_contra, 1),
                'diferencia' => round($clasificacion->goles_en_contra - $promediosLiga->promedio_goles_contra, 1),
            ],
        ];
    }

    /**
     * Compara equipos detalladamente
     */
    private function compararEquiposDetallado(Equipo $equipo1, Equipo $equipo2, ?Temporada $temporada): array
    {
        $comparativa = [
            'equipo1' => $equipo1,
            'equipo2' => $equipo2,
            'enfrentamientos_directos' => [],
            'estadisticas_comparativas' => [],
            'clasificacion_actual' => [],
        ];
        
        if ($temporada) {
            // Enfrentamientos directos en la temporada
            $enfrentamientos = Partido::where('temporada_id', $temporada->id)
                ->where(function($q) use ($equipo1, $equipo2) {
                    $q->where('equipo_local_id', $equipo1->id)
                      ->where('equipo_visitante_id', $equipo2->id);
                })
                ->orWhere(function($q) use ($equipo1, $equipo2) {
                    $q->where('equipo_local_id', $equipo2->id)
                      ->where('equipo_visitante_id', $equipo1->id);
                })
                ->where('estado', 'Finalizado')
                ->get();
            
            $comparativa['enfrentamientos_directos'] = [
                'total' => $enfrentamientos->count(),
                'victorias_equipo1' => $enfrentamientos->filter(function($p) use ($equipo1) { 
                    return ($p->equipo_local_id == $equipo1->id && $p->goles_local > $p->goles_visitante) ||
                           ($p->equipo_visitante_id == $equipo1->id && $p->goles_visitante > $p->goles_local);
                })->count(),
                'victorias_equipo2' => $enfrentamientos->filter(function($p) use ($equipo2) {
                    return ($p->equipo_local_id == $equipo2->id && $p->goles_local > $p->goles_visitante) ||
                           ($p->equipo_visitante_id == $equipo2->id && $p->goles_visitante > $p->goles_local);
                })->count(),
                'empates' => $enfrentamientos->filter(fn($p) => $p->goles_local == $p->goles_visitante)->count(),
                'goles_equipo1' => $enfrentamientos->sum(function($p) use ($equipo1) {
                    return $p->equipo_local_id == $equipo1->id ? $p->goles_local : $p->goles_visitante;
                }),
                'goles_equipo2' => $enfrentamientos->sum(function($p) use ($equipo2) {
                    return $p->equipo_local_id == $equipo2->id ? $p->goles_local : $p->goles_visitante;
                }),
                'partidos' => $enfrentamientos,
            ];
            
            // Clasificación actual
            $clasificacionEquipo1 = Clasificacion::with('equipo')
                ->where('temporada_id', $temporada->id)
                ->where('equipo_id', $equipo1->id)
                ->first();
                
            $clasificacionEquipo2 = Clasificacion::with('equipo')
                ->where('temporada_id', $temporada->id)
                ->where('equipo_id', $equipo2->id)
                ->first();
            
            if ($clasificacionEquipo1 && $clasificacionEquipo2) {
                $posicionEquipo1 = $this->obtenerPosicionEnTemporada($equipo1->id, $temporada->id);
                $posicionEquipo2 = $this->obtenerPosicionEnTemporada($equipo2->id, $temporada->id);
                
                $comparativa['clasificacion_actual'] = [
                    'equipo1' => $clasificacionEquipo1,
                    'equipo2' => $clasificacionEquipo2,
                    'diferencia_puntos' => abs($clasificacionEquipo1->puntos - $clasificacionEquipo2->puntos),
                    'diferencia_posicion' => abs($posicionEquipo1 - $posicionEquipo2),
                ];
            }
        }
        
        // Estadísticas comparativas generales
        $comparativa['estadisticas_comparativas'] = [
            'antiguedad' => [
                'equipo1' => date('Y') - $equipo1->fundacion,
                'equipo2' => date('Y') - $equipo2->fundacion,
                'diferencia' => abs((date('Y') - $equipo1->fundacion) - (date('Y') - $equipo2->fundacion)),
            ],
            'partidos_totales' => [
                'equipo1' => $equipo1->partidosLocal()->count() + $equipo1->partidosVisitante()->count(),
                'equipo2' => $equipo2->partidosLocal()->count() + $equipo2->partidosVisitante()->count(),
            ],
        ];
        
        return $comparativa;
    }

    /**
     * Recalcula clasificación completa
     */
    private function recalcularClasificacion(Temporada $temporada): void
    {
        $equipos = Equipo::where('activo', true)->get();
        
        foreach ($equipos as $equipo) {
            // Obtener partidos del equipo en la temporada
            $partidos = Partido::where('temporada_id', $temporada->id)
                ->where(function($q) use ($equipo) {
                    $q->where('equipo_local_id', $equipo->id)
                      ->orWhere('equipo_visitante_id', $equipo->id);
                })
                ->where('estado', 'Finalizado')
                ->get();
            
            // Calcular estadísticas
            $estadisticas = [
                'partidos_jugados' => $partidos->count(),
                'partidos_ganados' => 0,
                'partidos_empatados' => 0,
                'partidos_perdidos' => 0,
                'goles_a_favor' => 0,
                'goles_en_contra' => 0,
                'puntos' => 0,
            ];
            
            foreach ($partidos as $partido) {
                $esLocal = $partido->equipo_local_id == $equipo->id;
                $golesEquipo = $esLocal ? $partido->goles_local : $partido->goles_visitante;
                $golesRival = $esLocal ? $partido->goles_visitante : $partido->goles_local;
                
                $estadisticas['goles_a_favor'] += $golesEquipo;
                $estadisticas['goles_en_contra'] += $golesRival;
                
                if ($golesEquipo > $golesRival) {
                    $estadisticas['partidos_ganados']++;
                    $estadisticas['puntos'] += 3;
                } elseif ($golesEquipo == $golesRival) {
                    $estadisticas['partidos_empatados']++;
                    $estadisticas['puntos'] += 1;
                } else {
                    $estadisticas['partidos_perdidos']++;
                }
            }
            
            $estadisticas['diferencia_goles'] = $estadisticas['goles_a_favor'] - $estadisticas['goles_en_contra'];
            
            // Actualizar o crear clasificación
            Clasificacion::updateOrCreate(
                [
                    'temporada_id' => $temporada->id,
                    'equipo_id' => $equipo->id,
                ],
                $estadisticas
            );
        }
    }

    /**
     * Calcula estadísticas avanzadas
     */
    private function calcularEstadisticasAvanzadas(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        
        return [
            'distribucion_puntos' => $this->calcularDistribucionPuntos($clasificacion),
            'equilibrio_liga' => $this->calcularEquilibrioLiga($clasificacion),
            'prediccion_final' => $this->generarPrediccionFinal($clasificacion, $temporada),
            'partidos_decisivos' => $this->identificarPartidosDecisivos($temporada),
            'racha_actual_liga' => $this->calcularRachaLiga($temporada),
        ];
    }

    /**
     * Analiza tendencias
     */
    private function analizarTendencias(Temporada $temporada): array
    {
        // Implementación de análisis de tendencias
        return [
            'equipos_en_ascenso' => $this->identificarEquiposAscenso($temporada),
            'equipos_en_descenso' => $this->identificarEquiposDescenso($temporada),
            'tendencia_goleadora' => $this->analizarTendenciaGoleadora($temporada),
            'equipos_consistentes' => $this->identificarEquiposConsistentes($temporada),
        ];
    }

    /**
     * Genera predicciones
     */
    private function generarPredicciones(Temporada $temporada): array
    {
        // Implementación de predicciones
        return [
            'campeon_potencial' => $this->predecirCampeon($temporada),
            'descensos_potenciales' => $this->predecirDescensos($temporada),
            'clasificacion_final' => $this->predecirClasificacionFinal($temporada),
        ];
    }

    // Métodos auxiliares específicos
    private function calcularVictoriasLocal(Temporada $temporada): int
    {
        return Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->whereRaw('goles_local > goles_visitante')
            ->count();
    }
    
    private function calcularVictoriasVisitante(Temporada $temporada): int
    {
        return Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->whereRaw('goles_visitante > goles_local')
            ->count();
    }
    
    private function obtenerPosicionEnJornada(int $equipoId, int $jornadaId): ?int
    {
        // Obtener todos los partidos hasta esa jornada
        $partidosHastaJornada = Partido::where('jornada_id', '<=', $jornadaId)
            ->where('estado', 'Finalizado')
            ->get();
        
        // Calcular puntos de cada equipo
        $puntosEquipos = [];
        
        foreach ($partidosHastaJornada as $partido) {
            if (!isset($puntosEquipos[$partido->equipo_local_id])) {
                $puntosEquipos[$partido->equipo_local_id] = 0;
            }
            if (!isset($puntosEquipos[$partido->equipo_visitante_id])) {
                $puntosEquipos[$partido->equipo_visitante_id] = 0;
            }
            
            if ($partido->goles_local > $partido->goles_visitante) {
                $puntosEquipos[$partido->equipo_local_id] += 3;
            } elseif ($partido->goles_local < $partido->goles_visitante) {
                $puntosEquipos[$partido->equipo_visitante_id] += 3;
            } else {
                $puntosEquipos[$partido->equipo_local_id] += 1;
                $puntosEquipos[$partido->equipo_visitante_id] += 1;
            }
        }
        
        // Ordenar equipos por puntos
        arsort($puntosEquipos);
        
        // Encontrar posición del equipo
        $posicion = 1;
        foreach (array_keys($puntosEquipos) as $equipoIdArray) {
            if ($equipoIdArray == $equipoId) {
                return $posicion;
            }
            $posicion++;
        }
        
        return null;
    }
    
    private function obtenerPuntosEnJornada(int $equipoId, int $jornadaId): int
    {
        // Obtener puntos acumulados hasta esa jornada
        $partidosHastaJornada = Partido::where('jornada_id', '<=', $jornadaId)
            ->where('estado', 'Finalizado')
            ->where(function($q) use ($equipoId) {
                $q->where('equipo_local_id', $equipoId)
                  ->orWhere('equipo_visitante_id', $equipoId);
            })
            ->get();
        
        $puntos = 0;
        foreach ($partidosHastaJornada as $partido) {
            $esLocal = $partido->equipo_local_id == $equipoId;
            $golesEquipo = $esLocal ? $partido->goles_local : $partido->goles_visitante;
            $golesRival = $esLocal ? $partido->goles_visitante : $partido->goles_local;
            
            if ($golesEquipo > $golesRival) {
                $puntos += 3;
            } elseif ($golesEquipo == $golesRival) {
                $puntos += 1;
            }
        }
        
        return $puntos;
    }
    
    private function obtenerPosicionEnTemporada(int $equipoId, int $temporadaId): int
    {
        $clasificacion = Clasificacion::where('temporada_id', $temporadaId)
            ->orderBy('puntos', 'DESC')
            ->orderBy('diferencia_goles', 'DESC')
            ->get();
            
        $posicion = $clasificacion->search(function($item) use ($equipoId) {
            return $item->equipo_id == $equipoId;
        });
        
        return $posicion !== false ? $posicion + 1 : 0;
    }
    
    private function calcularRachaEquipo(int $equipoId, int $temporadaId): array
    {
        // Obtener últimos 5 partidos del equipo
        $ultimosPartidos = Partido::where('temporada_id', $temporadaId)
            ->where(function($q) use ($equipoId) {
                $q->where('equipo_local_id', $equipoId)
                  ->orWhere('equipo_visitante_id', $equipoId);
            })
            ->where('estado', 'Finalizado')
            ->orderBy('fecha_hora', 'DESC')
            ->limit(5)
            ->get();
        
        $racha = [];
        $resultados = [];
        
        foreach ($ultimosPartidos as $partido) {
            $esLocal = $partido->equipo_local_id == $equipoId;
            $golesEquipo = $esLocal ? $partido->goles_local : $partido->goles_visitante;
            $golesRival = $esLocal ? $partido->goles_visitante : $partido->goles_local;
            
            if ($golesEquipo > $golesRival) {
                $resultados[] = 'W';
            } elseif ($golesEquipo < $golesRival) {
                $resultados[] = 'L';
            } else {
                $resultados[] = 'D';
            }
        }
        
        $resultados = array_reverse($resultados);
        
        if (empty($resultados)) {
            return ['tipo' => 'neutral', 'cantidad' => 0, 'partidos' => []];
        }
        
        $ultimoResultado = end($resultados);
        $cantidad = 0;
        
        for ($i = count($resultados) - 1; $i >= 0; $i--) {
            if ($resultados[$i] == $ultimoResultado) {
                $cantidad++;
            } else {
                break;
            }
        }
        
        return [
            'tipo' => $ultimoResultado == 'W' ? 'victoria' : ($ultimoResultado == 'L' ? 'derrota' : 'empate'),
            'cantidad' => $cantidad,
            'partidos' => $resultados,
        ];
    }
    
    private function calcularTendenciaPosiciones(array $posiciones): string
    {
        if (count($posiciones) < 2) return 'estable';
        
        $ultima = end($posiciones);
        $penultima = prev($posiciones);
        
        if ($ultima < $penultima) return 'ascendente';
        if ($ultima > $penultima) return 'descendente';
        return 'estable';
    }
    
    private function obtenerProximosPartidosEquipo(int $equipoId, int $temporadaId)
    {
        return Partido::with(['equipoLocal', 'equipoVisitante', 'jornada'])
            ->where('temporada_id', $temporadaId)
            ->where(function($q) use ($equipoId) {
                $q->where('equipo_local_id', $equipoId)
                  ->orWhere('equipo_visitante_id', $equipoId);
            })
            ->where('estado', 'Programado')
            ->where('fecha_hora', '>', now())
            ->orderBy('fecha_hora', 'ASC')
            ->limit(5)
            ->get();
    }
    
    private function obtenerUltimosPartidosEquipo(int $equipoId, int $temporadaId)
    {
        return Partido::with(['equipoLocal', 'equipoVisitante', 'jornada'])
            ->where('temporada_id', $temporadaId)
            ->where(function($q) use ($equipoId) {
                $q->where('equipo_local_id', $equipoId)
                  ->orWhere('equipo_visitante_id', $equipoId);
            })
            ->where('estado', 'Finalizado')
            ->orderBy('fecha_hora', 'DESC')
            ->limit(5)
            ->get();
    }
    
    // Métodos de análisis avanzado
    private function calcularDistribucionPuntos($clasificacion): array
    {
        return [
            'rango_0_10' => $clasificacion->where('puntos', '<=', 10)->count(),
            'rango_11_20' => $clasificacion->whereBetween('puntos', [11, 20])->count(),
            'rango_21_30' => $clasificacion->whereBetween('puntos', [21, 30])->count(),
            'rango_31_40' => $clasificacion->whereBetween('puntos', [31, 40])->count(),
            'rango_41_mas' => $clasificacion->where('puntos', '>=', 41)->count(),
        ];
    }
    
    private function calcularEquilibrioLiga($clasificacion): float
    {
        if ($clasificacion->count() < 2) return 0;
        
        $puntos = $clasificacion->pluck('puntos')->toArray();
        $promedio = array_sum($puntos) / count($puntos);
        $varianza = array_sum(array_map(function($x) use ($promedio) {
            return pow($x - $promedio, 2);
        }, $puntos)) / count($puntos);
        
        return round(1 / (1 + sqrt($varianza)), 3);
    }
    
    private function identificarEquiposAscenso(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $evolucion = $this->obtenerEvolucionPosiciones($temporada);
        
        $ascensores = [];
        foreach ($evolucion as $equipoEvol) {
            if ($equipoEvol['tendencia'] == 'ascendente') {
                $clasificacionActual = $clasificacion->firstWhere('equipo_id', $equipoEvol['equipo']->id);
                if ($clasificacionActual) {
                    $ascensores[] = [
                        'equipo' => $equipoEvol['equipo'],
                        'posicion_actual' => $clasificacionActual->posicion,
                        'mejor_posicion' => $equipoEvol['mejor_posicion'],
                        'racha' => $equipoEvol['posiciones'],
                    ];
                }
            }
        }
        
        return $ascensores;
    }
    
    private function identificarEquiposDescenso(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $evolucion = $this->obtenerEvolucionPosiciones($temporada);
        
        $descensores = [];
        foreach ($evolucion as $equipoEvol) {
            if ($equipoEvol['tendencia'] == 'descendente') {
                $clasificacionActual = $clasificacion->firstWhere('equipo_id', $equipoEvol['equipo']->id);
                if ($clasificacionActual) {
                    $descensores[] = [
                        'equipo' => $equipoEvol['equipo'],
                        'posicion_actual' => $clasificacionActual->posicion,
                        'peor_posicion' => $equipoEvol['peor_posicion'],
                        'racha' => $equipoEvol['posiciones'],
                    ];
                }
            }
        }
        
        return $descensores;
    }
    
    private function predecirCampeon(Temporada $temporada): ?Equipo
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        
        if ($clasificacion->isEmpty()) {
            return null;
        }
        
        $lider = $clasificacion->first();
        return $lider->equipo;
    }
    
    private function predecirDescensos(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $totalEquipos = $clasificacion->count();
        
        // Simular que descienden los últimos 2-3 equipos
        $descensos = [];
        $posicionDescenso = max(1, $totalEquipos - 2);
        
        for ($i = $posicionDescenso; $i <= $totalEquipos; $i++) {
            $equipoDescenso = $clasificacion->where('posicion', $i)->first();
            if ($equipoDescenso) {
                $descensos[] = [
                    'equipo' => $equipoDescenso->equipo,
                    'posicion' => $i,
                    'puntos' => $equipoDescenso->puntos,
                    'diferencia_salvacion' => $equipoDescenso->puntos - 
                        ($clasificacion->where('posicion', $posicionDescenso - 1)->first()->puntos ?? 0),
                ];
            }
        }
        
        return $descensos;
    }
    
    private function predecirClasificacionFinal(Temporada $temporada): array
    {
        $clasificacionActual = $this->obtenerClasificacionCompleta($temporada);
        
        // Simular proyección basada en rendimiento actual
        $proyecciones = [];
        foreach ($clasificacionActual as $equipo) {
            $partidosRestantes = 38 - $equipo->partidos_jugados; // Suponiendo temporada de 38 jornadas
            $puntosPorPartido = $equipo->partidos_jugados > 0 ? $equipo->puntos / $equipo->partidos_jugados : 0;
            $puntosProyectados = round($equipo->puntos + ($partidosRestantes * $puntosPorPartjo), 0);
            
            $proyecciones[] = [
                'equipo' => $equipo->equipo,
                'posicion_actual' => $equipo->posicion,
                'puntos_actuales' => $equipo->puntos,
                'puntos_proyectados' => $puntosProyectados,
                'rendimiento' => $equipo->rendimiento,
            ];
        }
        
        // Ordenar por puntos proyectados
        usort($proyecciones, function($a, $b) {
            return $b['puntos_proyectados'] - $a['puntos_proyectados'];
        });
        
        return $proyecciones;
    }
    
    private function generarPrediccionFinal($clasificacion, Temporada $temporada): array
    {
        return $this->predecirClasificacionFinal($temporada);
    }
    
    private function identificarPartidosDecisivos(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $ultimaJornada = Jornada::where('temporada_id', $temporada->id)
            ->orderBy('numero', 'DESC')
            ->first();
        
        if (!$ultimaJornada) {
            return [];
        }
        
        // Buscar partidos entre equipos cercanos en clasificación
        $partidosDecisivos = [];
        $partidosUltimaJornada = Partido::with(['equipoLocal', 'equipoVisitante'])
            ->where('jornada_id', $ultimaJornada->id)
            ->where('estado', 'Programado')
            ->get();
        
        foreach ($partidosUltimaJornada as $partido) {
            $posicionLocal = $clasificacion->firstWhere('equipo_id', $partido->equipo_local_id)->posicion ?? 0;
            $posicionVisitante = $clasificacion->firstWhere('equipo_id', $partido->equipo_visitante_id)->posicion ?? 0;
            
            if (abs($posicionLocal - $posicionVisitante) <= 3) {
                $partidosDecisivos[] = [
                    'partido' => $partido,
                    'diferencia_posiciones' => abs($posicionLocal - $posicionVisitante),
                    'posicion_local' => $posicionLocal,
                    'posicion_visitante' => $posicionVisitante,
                ];
            }
        }
        
        return $partidosDecisivos;
    }
    
    private function calcularRachaLiga(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $rachaPositiva = 0;
        $rachaNegativa = 0;
        
        foreach ($clasificacion as $equipo) {
            if ($equipo->ultima_racha['tipo'] == 'victoria') {
                $rachaPositiva = max($rachaPositiva, $equipo->ultima_racha['cantidad']);
            } elseif ($equipo->ultima_racha['tipo'] == 'derrota') {
                $rachaNegativa = max($rachaNegativa, $equipo->ultima_racha['cantidad']);
            }
        }
        
        return [
            'mejor_racha_victorias' => $rachaPositiva,
            'peor_racha_derrotas' => $rachaNegativa,
            'equipos_imbatibles' => $clasificacion->filter(fn($e) => $e->ultima_racha['tipo'] == 'victoria' && $e->ultima_racha['cantidad'] >= 3)->count(),
            'equipos_sin_ganar' => $clasificacion->filter(fn($e) => $e->ultima_racha['tipo'] != 'victoria' && $e->ultima_racha['cantidad'] >= 3)->count(),
        ];
    }
    
    private function analizarTendenciaGoleadora(Temporada $temporada): array
    {
        $partidos = Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->get();
        
        $golesPorJornada = [];
        $jornadas = Jornada::where('temporada_id', $temporada->id)
            ->orderBy('numero')
            ->get();
        
        foreach ($jornadas as $jornada) {
            $golesJornada = $partidos->where('jornada_id', $jornada->id)
                ->sum(fn($p) => $p->goles_local + $p->goles_visitante);
            $golesPorJornada[] = $golesJornada;
        }
        
        return [
            'total_goles' => array_sum($golesPorJornada),
            'promedio_goles_por_jornada' => count($golesPorJornada) > 0 ? round(array_sum($golesPorJornada) / count($golesPorJornada), 2) : 0,
            'jornada_mas_goleadora' => !empty($golesPorJornada) ? max($golesPorJornada) : 0,
            'jornada_menos_goleadora' => !empty($golesPorJornada) ? min($golesPorJornada) : 0,
            'tendencia' => count($golesPorJornada) >= 2 ? ($golesPorJornada[count($golesPorJornada)-1] > $golesPorJornada[count($golesPorJornada)-2] ? 'ascendente' : 'descendente') : 'estable',
        ];
    }
    
    private function identificarEquiposConsistentes(Temporada $temporada): array
    {
        $clasificacion = $this->obtenerClasificacionCompleta($temporada);
        $evolucion = $this->obtenerEvolucionPosiciones($temporada);
        
        $consistentes = [];
        foreach ($evolucion as $equipoEvol) {
            $variacion = max($equipoEvol['posiciones']) - min($equipoEvol['posiciones']);
            if ($variacion <= 3 && count($equipoEvol['posiciones']) >= 5) {
                $clasificacionActual = $clasificacion->firstWhere('equipo_id', $equipoEvol['equipo']->id);
                if ($clasificacionActual) {
                    $consistentes[] = [
                        'equipo' => $equipoEvol['equipo'],
                        'posicion_actual' => $clasificacionActual->posicion,
                        'variacion_posiciones' => $variacion,
                        'rendimiento' => $clasificacionActual->rendimiento,
                    ];
                }
            }
        }
        
        return $consistentes;
    }
}