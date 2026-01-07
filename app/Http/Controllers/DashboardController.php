<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Temporada;
use App\Models\Partido;
use App\Models\Clasificacion;
use App\Models\Jornada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(Request $request)
    {
        try {
            // Obtener temporada activa
            $temporada = $this->obtenerTemporadaActiva();
            
            // Métricas principales
            $metricas = $this->calcularMetricas($temporada);
            
            // Próximos partidos
            $proximosPartidos = $this->obtenerProximosPartidos($temporada);
            
            // Clasificación actual
            $clasificacion = $this->obtenerClasificacion($temporada);
            
            // Últimos resultados
            $ultimosResultados = $this->obtenerUltimosResultados($temporada);
            
            // Equipos destacados
            $equiposDestacados = $this->obtenerEquiposDestacados($temporada);
            
            // Alertas del sistema
            $alertas = $this->generarAlertas($temporada);
            
            return view('dashboard', compact(
                'temporada',
                'metricas',
                'proximosPartidos',
                'clasificacion',
                'ultimosResultados',
                'equiposDestacados',
                'alertas'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController@index: ' . $e->getMessage());
            
            // Datos por defecto en caso de error
            return view('dashboard', [
                'temporada' => null,
                'metricas' => [
                    'total_equipos' => 0,
                    'total_temporadas' => 0,
                    'partidos_jugados' => 0,
                    'partidos_programados' => 0,
                    'promedio_goles' => 0,
                    'total_goles' => 0,
                ],
                'proximosPartidos' => collect(),
                'clasificacion' => collect(),
                'ultimosResultados' => collect(),
                'equiposDestacados' => collect(),
                'alertas' => []
            ]);
        }
    }

    /**
     * Obtiene la temporada activa
     */
    private function obtenerTemporadaActiva(): ?Temporada
    {
        // Primero intenta obtener la temporada activa
        $temporada = Temporada::where('estado', 'En Curso')->first();
        
        // Si no hay temporada activa, obtén la última creada
        if (!$temporada) {
            $temporada = Temporada::orderBy('anio', 'desc')->first();
        }
        
        return $temporada;
    }

    /**
     * Calcula las métricas principales del dashboard
     */
    private function calcularMetricas(?Temporada $temporada): array
    {
        $temporadaId = $temporada ? $temporada->id : null;
        
        // Total de equipos activos
        $totalEquipos = Equipo::where('activo', true)->count();
        
        // Total de temporadas
        $totalTemporadas = Temporada::count();
        
        // Estadísticas de partidos
        if ($temporadaId) {
            $estadisticasPartidos = Partido::where('temporada_id', $temporadaId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = "Finalizado" THEN 1 ELSE 0 END) as jugados,
                    SUM(CASE WHEN estado = "Programado" THEN 1 ELSE 0 END) as programados,
                    SUM(goles_local + goles_visitante) as total_goles
                ')
                ->first();
            
            $partidosJugados = $estadisticasPartidos->jugados ?? 0;
            $partidosProgramados = $estadisticasPartidos->programados ?? 0;
            $totalGoles = $estadisticasPartidos->total_goles ?? 0;
            $promedioGoles = $partidosJugados > 0 ? round($totalGoles / $partidosJugados, 2) : 0;
        } else {
            $partidosJugados = 0;
            $partidosProgramados = 0;
            $totalGoles = 0;
            $promedioGoles = 0;
        }
        
        return [
            'total_equipos' => $totalEquipos,
            'total_temporadas' => $totalTemporadas,
            'partidos_jugados' => $partidosJugados,
            'partidos_programados' => $partidosProgramados,
            'total_goles' => $totalGoles,
            'promedio_goles' => $promedioGoles,
        ];
    }

    /**
     * Obtiene los próximos partidos
     */
    private function obtenerProximosPartidos(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        return Partido::with(['equipoLocal', 'equipoVisitante', 'jornada'])
            ->where('temporada_id', $temporada->id)
            ->whereIn('estado', ['Programado', 'Jugando'])
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora', 'asc')
            ->limit(5)
            ->get();
    }

    /**
     * Obtiene la clasificación actual
     */
    private function obtenerClasificacion(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        return Clasificacion::with('equipo')
            ->where('temporada_id', $temporada->id)
            ->orderBy('puntos', 'desc')
            ->orderBy('diferencia_goles', 'desc')
            ->orderBy('goles_a_favor', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item, $index) {
                $item->posicion = $index + 1;
                return $item;
            });
    }

    /**
     * Obtiene los últimos resultados
     */
    private function obtenerUltimosResultados(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        return Partido::with(['equipoLocal', 'equipoVisitante'])
            ->where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->orderBy('fecha_hora', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * Obtiene equipos destacados
     */
    private function obtenerEquiposDestacados(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        // Obtener los 3 mejores equipos de la clasificación
        $clasificacion = Clasificacion::with('equipo')
            ->where('temporada_id', $temporada->id)
            ->orderBy('puntos', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($clasificacion) use ($temporada) {
                $equipo = $clasificacion->equipo;
                if ($equipo) {
                    // Calcular partidos jugados del equipo en la temporada
                    $totalPartidos = Partido::where('temporada_id', $temporada->id)
                        ->where(function($query) use ($equipo) {
                            $query->where('equipo_local_id', $equipo->id)
                                  ->orWhere('equipo_visitante_id', $equipo->id);
                        })
                        ->count();
                    
                    $equipo->total_partidos = $totalPartidos;
                    return $equipo;
                }
                return null;
            })
            ->filter();
        
        return $clasificacion;
    }

    /**
     * Genera alertas del sistema
     */
    private function generarAlertas(?Temporada $temporada): array
    {
        $alertas = [];
        
        // Alerta si no hay temporada activa
        if (!$temporada) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'No hay temporada activa. Configura una temporada para comenzar.'
            ];
            return $alertas;
        }
        
        // Alerta si la temporada no tiene equipos
        $equiposTemporada = Clasificacion::where('temporada_id', $temporada->id)->count();
        if ($equiposTemporada === 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'mensaje' => 'La temporada actual no tiene equipos registrados.'
            ];
        }
        
        // Alerta si no hay partidos programados próximamente
        $proximosPartidos = Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Programado')
            ->where('fecha_hora', '>=', now())
            ->count();
        
        if ($proximosPartidos === 0) {
            $alertas[] = [
                'tipo' => 'info',
                'mensaje' => 'No hay partidos programados próximamente.'
            ];
        }
        
        // Alerta si hay partidos en juego
        $partidosEnJuego = Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Jugando')
            ->count();
        
        if ($partidosEnJuego > 0) {
            $alertas[] = [
                'tipo' => 'success',
                'mensaje' => "Hay {$partidosEnJuego} partido(s) en juego actualmente."
            ];
        }
        
        // Alerta para jornadas sin partidos
        $jornadasSinPartidos = $this->verificarJornadasSinPartidos($temporada);
        if ($jornadasSinPartidos > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'mensaje' => "Hay {$jornadasSinPartidos} jornada(s) sin partidos asignados."
            ];
        }
        
        return $alertas;
    }

    /**
     * Verifica jornadas sin partidos
     */
    private function verificarJornadasSinPartidos(Temporada $temporada): int
    {
        $jornadas = Jornada::where('temporada_id', $temporada->id)->get();
        
        $jornadasSinPartidos = 0;
        foreach ($jornadas as $jornada) {
            $partidosJornada = Partido::where('jornada_id', $jornada->id)->count();
            if ($partidosJornada === 0) {
                $jornadasSinPartidos++;
            }
        }
        
        return $jornadasSinPartidos;
    }

    /**
     * Exportar datos del dashboard
     */
    public function exportar(Request $request)
    {
        try {
            $temporada = $this->obtenerTemporadaActiva();
            $metricas = $this->calcularMetricas($temporada);
            $clasificacion = $this->obtenerClasificacion($temporada);
            
            $formato = $request->get('formato', 'pdf');
            $nombreArchivo = 'dashboard_' . date('Ymd_His');
            
            if ($formato === 'excel') {
                // Implementar exportación a Excel
                return response()->json(['message' => 'Exportación a Excel no implementada']);
            } else {
                // Implementar exportación a PDF
                return response()->json(['message' => 'Exportación a PDF no implementada']);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController@exportar: ' . $e->getMessage());
            return response()->json(['error' => 'Error al exportar datos'], 500);
        }
    }

    /**
     * Obtener datos para widget
     */
    public function widget(Request $request)
    {
        try {
            $temporada = $this->obtenerTemporadaActiva();
            
            $widgetData = [
                'temporada' => $temporada ? $temporada->nombre : 'Sin temporada',
                'clasificacion_top' => $this->obtenerClasificacion($temporada)->take(3),
                'proximo_partido' => $this->obtenerProximosPartidos($temporada)->first(),
                'metricas' => $this->calcularMetricas($temporada),
            ];
            
            return response()->json($widgetData);
            
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController@widget: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos del widget'], 500);
        }
    }

    /**
     * Datos estadísticos avanzados
     */
    public function estadisticasAvanzadas()
    {
        try {
            $temporada = $this->obtenerTemporadaActiva();
            
            $estadisticas = [
                'distribucion_resultados' => $this->calcularDistribucionResultados($temporada),
                'equipos_mejor_local' => $this->obtenerEquiposMejorLocal($temporada),
                'equipos_mejor_visitante' => $this->obtenerEquiposMejorVisitante($temporada),
                'partidos_mas_goleados' => $this->obtenerPartidosMasGoleados($temporada),
                'tendencias_mensuales' => $this->calcularTendenciasMensuales($temporada),
            ];
            
            return response()->json($estadisticas);
            
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController@estadisticasAvanzadas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener estadísticas'], 500);
        }
    }

    /**
     * Calcular distribución de resultados
     */
    private function calcularDistribucionResultados(?Temporada $temporada): array
    {
        if (!$temporada) {
            return [];
        }
        
        $partidos = Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->get();
        
        $total = $partidos->count();
        if ($total === 0) {
            return [];
        }
        
        $victoriasLocal = 0;
        $victoriasVisitante = 0;
        $empates = 0;
        
        foreach ($partidos as $partido) {
            if ($partido->goles_local > $partido->goles_visitante) {
                $victoriasLocal++;
            } elseif ($partido->goles_local < $partido->goles_visitante) {
                $victoriasVisitante++;
            } else {
                $empates++;
            }
        }
        
        return [
            'total' => $total,
            'victorias_local' => $victoriasLocal,
            'victorias_visitante' => $victoriasVisitante,
            'empates' => $empates,
            'porcentaje_victorias_local' => round(($victoriasLocal / $total) * 100, 1),
            'porcentaje_victorias_visitante' => round(($victoriasVisitante / $total) * 100, 1),
            'porcentaje_empates' => round(($empates / $total) * 100, 1),
        ];
    }

    /**
     * Obtener equipos con mejor rendimiento local
     */
    private function obtenerEquiposMejorLocal(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        $equipos = Equipo::where('activo', true)->get();
        $resultados = [];
        
        foreach ($equipos as $equipo) {
            $partidosLocal = Partido::where('temporada_id', $temporada->id)
                ->where('equipo_local_id', $equipo->id)
                ->where('estado', 'Finalizado')
                ->get();
            
            if ($partidosLocal->count() > 0) {
                $victorias = $partidosLocal->filter(function($partido) {
                    return $partido->goles_local > $partido->goles_visitante;
                })->count();
                
                $empates = $partidosLocal->filter(function($partido) {
                    return $partido->goles_local == $partido->goles_visitante;
                })->count();
                
                $derrotas = $partidosLocal->filter(function($partido) {
                    return $partido->goles_local < $partido->goles_visitante;
                })->count();
                
                $puntos = ($victorias * 3) + $empates;
                $rendimiento = $partidosLocal->count() > 0 ? round(($puntos / ($partidosLocal->count() * 3)) * 100, 1) : 0;
                
                $resultados[] = [
                    'equipo' => $equipo,
                    'partidos' => $partidosLocal->count(),
                    'victorias' => $victorias,
                    'empates' => $empates,
                    'derrotas' => $derrotas,
                    'puntos' => $puntos,
                    'rendimiento' => $rendimiento,
                ];
            }
        }
        
        return collect($resultados)->sortByDesc('rendimiento')->take(5);
    }

    /**
     * Obtener equipos con mejor rendimiento visitante
     */
    private function obtenerEquiposMejorVisitante(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        $equipos = Equipo::where('activo', true)->get();
        $resultados = [];
        
        foreach ($equipos as $equipo) {
            $partidosVisitante = Partido::where('temporada_id', $temporada->id)
                ->where('equipo_visitante_id', $equipo->id)
                ->where('estado', 'Finalizado')
                ->get();
            
            if ($partidosVisitante->count() > 0) {
                $victorias = $partidosVisitante->filter(function($partido) {
                    return $partido->goles_visitante > $partido->goles_local;
                })->count();
                
                $empates = $partidosVisitante->filter(function($partido) {
                    return $partido->goles_local == $partido->goles_visitante;
                })->count();
                
                $derrotas = $partidosVisitante->filter(function($partido) {
                    return $partido->goles_visitante < $partido->goles_local;
                })->count();
                
                $puntos = ($victorias * 3) + $empates;
                $rendimiento = $partidosVisitante->count() > 0 ? round(($puntos / ($partidosVisitante->count() * 3)) * 100, 1) : 0;
                
                $resultados[] = [
                    'equipo' => $equipo,
                    'partidos' => $partidosVisitante->count(),
                    'victorias' => $victorias,
                    'empates' => $empates,
                    'derrotas' => $derrotas,
                    'puntos' => $puntos,
                    'rendimiento' => $rendimiento,
                ];
            }
        }
        
        return collect($resultados)->sortByDesc('rendimiento')->take(5);
    }

    /**
     * Obtener partidos más goleados
     */
    private function obtenerPartidosMasGoleados(?Temporada $temporada)
    {
        if (!$temporada) {
            return collect();
        }
        
        return Partido::with(['equipoLocal', 'equipoVisitante'])
            ->where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->select('*', DB::raw('(goles_local + goles_visitante) as total_goles'))
            ->orderByDesc('total_goles')
            ->limit(5)
            ->get();
    }

    /**
     * Calcular tendencias mensuales
     */
    private function calcularTendenciasMensuales(?Temporada $temporada): array
    {
        if (!$temporada) {
            return [];
        }
        
        $partidos = Partido::where('temporada_id', $temporada->id)
            ->where('estado', 'Finalizado')
            ->selectRaw('
                MONTH(fecha_hora) as mes,
                COUNT(*) as total_partidos,
                AVG(goles_local + goles_visitante) as promedio_goles,
                SUM(goles_local + goles_visitante) as total_goles
            ')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        
        return $partidos->toArray();
    }

    /**
     * Actualizar cache del dashboard
     */
    public function actualizarCache()
    {
        try {
            // Limpiar cache relacionado con dashboard
            \Cache::forget('dashboard_metricas');
            \Cache::forget('dashboard_clasificacion');
            \Cache::forget('dashboard_proximos_partidos');
            
            return response()->json(['message' => 'Cache actualizado correctamente']);
            
        } catch (\Exception $e) {
            \Log::error('Error en DashboardController@actualizarCache: ' . $e->getMessage());
            return response()->json(['error' => 'Error al actualizar cache'], 500);
        }
    }
}