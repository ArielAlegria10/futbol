<?php

namespace App\Http\Controllers;

use App\Models\Jornada;
use App\Models\Temporada;
use App\Models\Partido;
use App\Models\Equipo;
use App\Models\Clasificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JornadaExport;

class JornadaController extends Controller
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
                    ->with('warning', 'Selecciona una temporada para ver sus jornadas.');
            }
            
            // Construir query con filtros avanzados
            $query = Jornada::with([
                'temporada',
                'partidos' => function($q) {
                    $q->with(['equipoLocal', 'equipoVisitante'])
                      ->orderBy('fecha_hora');
                },
                'partidos.equipoLocal',
                'partidos.equipoVisitante'
            ])->where('temporada_id', $temporada->id);
            
            // Filtros avanzados
            if ($request->filled('estado')) {
                $estado = $request->get('estado');
                $query->whereHas('partidos', function($q) use ($estado) {
                    $q->where('estado', $estado);
                });
            }
            
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('numero', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha_inicio', '>=', $request->get('fecha_inicio'));
            }
            
            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha_fin', '<=', $request->get('fecha_fin'));
            }
            
            // Ordenamiento
            $sort = $request->get('sort', 'numero');
            $order = $request->get('order', 'asc');
            $validSorts = ['numero', 'fecha_inicio', 'fecha_fin', 'created_at'];
            $sort = in_array($sort, $validSorts) ? $sort : 'numero';
            $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';
            $query->orderBy($sort, $order);
            
            // Conteo total antes de paginación
            $totalJornadas = $query->count();
            
            // Paginación
            $perPage = $request->get('per_page', 15);
            $jornadas = $query->paginate($perPage)->withQueryString();
            
            // Calcular estadísticas de jornadas
            $estadisticasJornadas = $this->calcularEstadisticasJornadas($temporada);
            
            // Temporadas disponibles
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            
            // Estados disponibles para filtros
            $estadosPartidos = [
                'Programado' => 'Programados',
                'Jugando' => 'En Juego',
                'Finalizado' => 'Finalizados',
                'Suspendido' => 'Suspendidos',
            ];
            
            return view('jornadas.index', compact(
                'jornadas',
                'temporada',
                'temporadas',
                'totalJornadas',
                'estadisticasJornadas',
                'estadosPartidos'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@index: ' . $e->getMessage());
            
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar las jornadas. Por favor, intente nuevamente.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $temporadaId = $request->get('temporada_id');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->route('temporadas.index')
                    ->with('warning', 'Selecciona una temporada para crear jornadas.');
            }
            
            // Obtener siguiente número de jornada
            $ultimaJornada = Jornada::where('temporada_id', $temporada->id)
                ->orderBy('numero', 'DESC')
                ->first();
            
            $siguienteNumero = $ultimaJornada ? $ultimaJornada->numero + 1 : 1;
            
            // Obtener fechas sugeridas
            $fechasSugeridas = $this->calcularFechasSugeridas($temporada, $siguienteNumero);
            
            return view('jornadas.create', compact(
                'temporada',
                'siguienteNumero',
                'fechasSugeridas'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@create: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validator = Validator::make($request->all(), [
                'temporada_id' => 'required|exists:temporadas,id',
                'numero' => 'required|integer|min:1',
                'nombre' => 'nullable|string|max:100',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'descripcion' => 'nullable|string|max:500',
                'especial' => 'boolean',
                'notas' => 'nullable|string|max:1000',
            ], [
                'numero.min' => 'El número de jornada debe ser mayor a 0.',
                'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
                'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            $data = $validator->validated();
            
            // Verificar unicidad de número de jornada en la temporada
            $existe = Jornada::where('temporada_id', $data['temporada_id'])
                ->where('numero', $data['numero'])
                ->exists();
                
            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe una jornada con el número ' . $data['numero'] . ' en esta temporada.');
            }
            
            // Verificar superposición de fechas
            $superpuesta = Jornada::where('temporada_id', $data['temporada_id'])
                ->where(function($q) use ($data) {
                    $q->whereBetween('fecha_inicio', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhere(function($q) use ($data) {
                          $q->where('fecha_inicio', '<=', $data['fecha_inicio'])
                            ->where('fecha_fin', '>=', $data['fecha_fin']);
                      });
                })
                ->exists();
                
            if ($superpuesta) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Las fechas se superponen con otra jornada existente.');
            }
            
            // Generar slug
            $temporada = Temporada::find($data['temporada_id']);
            $data['slug'] = Str::slug($temporada->nombre . ' jornada ' . $data['numero']);
            
            // Crear jornada
            $jornada = Jornada::create($data);
            
            DB::commit();
            
            // Limpiar cache
            Cache::forget('jornadas_temporada_' . $data['temporada_id']);
            
            return redirect()->route('jornadas.show', $jornada->id)
                ->with('success', 'Jornada creada exitosamente.')
                ->with('jornada_nueva', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@store: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la jornada. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id, Request $request)
    {
        try {
            $jornada = Jornada::with([
                'temporada',
                'partidos' => function($query) {
                    $query->with([
                        'equipoLocal',
                        'equipoVisitante',
                        'resultadosDetalle'
                    ])->orderBy('fecha_hora');
                },
                'historialPosiciones.equipo'
            ])->findOrFail($id);
            
            // Estadísticas de la jornada
            $estadisticasJornada = $this->calcularEstadisticasJornada($jornada);
            
            // Tabla de posiciones al inicio de la jornada
            $posicionesInicio = $this->obtenerPosicionesInicioJornada($jornada);
            
            // Tabla de posiciones al final de la jornada
            $posicionesFin = $this->obtenerPosicionesFinJornada($jornada);
            
            // Cambios en posiciones
            $cambiosPosiciones = $this->calcularCambiosPosiciones($posicionesInicio, $posicionesFin);
            
            // Partidos destacados
            $partidosDestacados = $this->identificarPartidosDestacados($jornada);
            
            // Jornadas adyacentes
            $jornadasAdyacentes = $this->obtenerJornadasAdyacentes($jornada);
            
            // Vista detallada o resumida
            $vistaDetallada = $request->has('detallado');
            
            return view('jornadas.show', compact(
                'jornada',
                'estadisticasJornada',
                'posicionesInicio',
                'posicionesFin',
                'cambiosPosiciones',
                'partidosDestacados',
                'jornadasAdyacentes',
                'vistaDetallada'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@show: ' . $e->getMessage());
            
            return redirect()->route('jornadas.index')
                ->with('error', 'Jornada no encontrada o error al cargar la información.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $jornada = Jornada::with('temporada')->findOrFail($id);
            
            // Verificar si la jornada tiene partidos jugados
            $partidosJugados = $jornada->partidos()->where('estado', 'Finalizado')->exists();
            
            if ($partidosJugados) {
                return redirect()->route('jornadas.show', $jornada->id)
                    ->with('warning', 'No se puede editar una jornada que ya tiene partidos jugados.');
            }
            
            return view('jornadas.edit', compact('jornada'));
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@edit: ' . $e->getMessage());
            
            return redirect()->route('jornadas.index')
                ->with('error', 'Jornada no encontrada.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $jornada = Jornada::findOrFail($id);
            
            // Verificar si la jornada tiene partidos jugados
            $partidosJugados = $jornada->partidos()->where('estado', 'Finalizado')->exists();
            
            if ($partidosJugados) {
                return redirect()->route('jornadas.show', $jornada->id)
                    ->with('error', 'No se puede modificar una jornada que ya tiene partidos jugados.');
            }
            
            $validator = Validator::make($request->all(), [
                'nombre' => 'nullable|string|max:100',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'descripcion' => 'nullable|string|max:500',
                'especial' => 'boolean',
                'notas' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            $data = $validator->validated();
            
            // Verificar superposición de fechas (excluyendo esta jornada)
            $superpuesta = Jornada::where('temporada_id', $jornada->temporada_id)
                ->where('id', '!=', $id)
                ->where(function($q) use ($data) {
                    $q->whereBetween('fecha_inicio', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhere(function($q) use ($data) {
                          $q->where('fecha_inicio', '<=', $data['fecha_inicio'])
                            ->where('fecha_fin', '>=', $data['fecha_fin']);
                      });
                })
                ->exists();
                
            if ($superpuesta) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Las nuevas fechas se superponen con otra jornada existente.');
            }
            
            // Actualizar jornada
            $jornada->update($data);
            
            DB::commit();
            
            // Limpiar cache
            Cache::forget('jornadas_temporada_' . $jornada->temporada_id);
            
            return redirect()->route('jornadas.show', $jornada->id)
                ->with('success', 'Jornada actualizada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@update: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la jornada. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $jornada = Jornada::with('partidos')->findOrFail($id);
            
            // Verificar si la jornada tiene partidos
            if ($jornada->partidos()->exists()) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la jornada porque tiene partidos asociados. Elimina primero los partidos.');
            }
            
            $temporadaId = $jornada->temporada_id;
            $jornada->delete();
            
            DB::commit();
            
            // Limpiar cache
            Cache::forget('jornadas_temporada_' . $temporadaId);
            
            return redirect()->route('jornadas.index', ['temporada_id' => $temporadaId])
                ->with('success', 'Jornada eliminada exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@destroy: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al eliminar la jornada. Por favor, intente nuevamente.');
        }
    }

    /**
     * Genera calendario completo para una temporada
     */
    public function generarCalendario(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'temporada_id' => 'required|exists:temporadas,id',
                'equipos' => 'required|array|min:2',
                'equipos.*' => 'exists:equipos,id',
                'fecha_inicio' => 'required|date',
                'dias_entre_jornadas' => 'required|integer|min:1|max:14',
                'horarios' => 'required|array',
                'horarios.*' => 'date_format:H:i',
                'generar_vuelta' => 'boolean',
                'estadio_por_defecto' => 'nullable|string|max:150',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            DB::beginTransaction();
            
            $temporada = Temporada::findOrFail($request->temporada_id);
            $equipos = $request->equipos;
            $fechaInicio = Carbon::parse($request->fecha_inicio);
            $diasEntreJornadas = $request->dias_entre_jornadas;
            $horarios = $request->horarios;
            $generarVuelta = $request->boolean('generar_vuelta', true);
            $estadioPorDefecto = $request->estadio_por_defecto;
            
            // Verificar que no existan jornadas en la temporada
            if ($temporada->jornadas()->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Esta temporada ya tiene jornadas. No se puede generar un nuevo calendario.');
            }
            
            // Algoritmo round-robin profesional
            $numEquipos = count($equipos);
            $numJornadasIda = $numEquipos - 1;
            $numJornadasTotal = $generarVuelta ? $numJornadasIda * 2 : $numJornadasIda;
            
            $calendarioGenerado = [];
            $equiposArray = $equipos;
            $fechaActual = $fechaInicio;
            
            // Generar jornadas de ida
            for ($jornadaNumero = 1; $jornadaNumero <= $numJornadasIda; $jornadaNumero++) {
                $jornada = $this->crearJornadaCalendario(
                    $temporada,
                    $jornadaNumero,
                    $fechaActual,
                    $diasEntreJornadas
                );
                
                $enfrentamientos = $this->generarEnfrentamientosJornada(
                    $equiposArray,
                    $jornadaNumero,
                    $horarios,
                    $estadioPorDefecto
                );
                
                $partidosJornada = $this->crearPartidosJornada(
                    $jornada,
                    $enfrentamientos,
                    $fechaActual
                );
                
                $calendarioGenerado[] = [
                    'jornada' => $jornada,
                    'partidos' => $partidosJornada,
                ];
                
                // Rotar equipos para siguiente jornada
                $equiposArray = $this->rotarEquipos($equiposArray);
                
                $fechaActual->addDays($diasEntreJornadas);
            }
            
            // Generar jornadas de vuelta si corresponde
            if ($generarVuelta) {
                for ($jornadaNumero = $numJornadasIda + 1; $jornadaNumero <= $numJornadasTotal; $jornadaNumero++) {
                    $jornadaIda = $calendarioGenerado[$jornadaNumero - $numJornadasIda - 1];
                    
                    $jornada = $this->crearJornadaCalendario(
                        $temporada,
                        $jornadaNumero,
                        $fechaActual,
                        $diasEntreJornadas
                    );
                    
                    $partidosJornada = $this->crearPartidosVuelta(
                        $jornada,
                        $jornadaIda['partidos'],
                        $fechaActual,
                        $horarios,
                        $estadioPorDefecto
                    );
                    
                    $calendarioGenerado[] = [
                        'jornada' => $jornada,
                        'partidos' => $partidosJornada,
                    ];
                    
                    $fechaActual->addDays($diasEntreJornadas);
                }
            }
            
            // Crear registros iniciales en clasificación
            $this->crearClasificacionesIniciales($temporada, $equipos);
            
            DB::commit();
            
            // Limpiar cache
            Cache::forget('jornadas_temporada_' . $temporada->id);
            
            return redirect()->route('jornadas.index', ['temporada_id' => $temporada->id])
                ->with('success', 'Calendario generado exitosamente: ' . $numJornadasTotal . ' jornadas creadas.')
                ->with('calendario_generado', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@generarCalendario: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al generar el calendario: ' . $e->getMessage());
        }
    }

    /**
     * Marca jornada como completada
     */
    public function completar($id)
    {
        try {
            DB::beginTransaction();
            
            $jornada = Jornada::with(['partidos', 'temporada'])->findOrFail($id);
            
            // Verificar que todos los partidos estén finalizados
            $partidosPendientes = $jornada->partidos()
                ->where('estado', '!=', 'Finalizado')
                ->where('estado', '!=', 'Cancelado')
                ->count();
                
            if ($partidosPendientes > 0) {
                return redirect()->back()
                    ->with('warning', 'No se puede completar la jornada. Hay ' . $partidosPendientes . ' partidos pendientes.');
            }
            
            // Marcar jornada como completada
            $jornada->update([
                'completada' => true,
                'fecha_completada' => now(),
            ]);
            
            // Recalcular clasificación
            $this->recalcularClasificacionJornada($jornada);
            
            DB::commit();
            
            // Limpiar cache
            Cache::forget('jornadas_temporada_' . $jornada->temporada_id);
            Cache::forget('clasificacion_' . $jornada->temporada_id . '_*');
            
            return redirect()->route('jornadas.show', $jornada->id)
                ->with('success', 'Jornada marcada como completada y clasificación actualizada.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@completar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al completar la jornada.');
        }
    }

    /**
     * Exporta información de jornada
     */
    public function exportar($id, Request $request)
    {
        try {
            $jornada = Jornada::with(['temporada', 'partidos.equipoLocal', 'partidos.equipoVisitante'])
                ->findOrFail($id);
            
            $formato = $request->get('formato', 'pdf');
            $incluirEstadisticas = $request->boolean('incluir_estadisticas', true);
            
            $nombreArchivo = 'jornada_' . $jornada->temporada->nombre . '_' . $jornada->numero . '_' . date('Ymd_His');
            
            switch ($formato) {
                case 'excel':
                    return Excel::download(new JornadaExport($jornada, $incluirEstadisticas), $nombreArchivo . '.xlsx');
                    
                case 'csv':
                    return Excel::download(new JornadaExport($jornada, $incluirEstadisticas), $nombreArchivo . '.csv');
                    
                case 'pdf':
                default:
                    $estadisticas = $incluirEstadisticas ? $this->calcularEstadisticasJornada($jornada) : null;
                    
                    $pdf = PDF::loadView('exports.jornada.pdf', [
                        'jornada' => $jornada,
                        'estadisticas' => $estadisticas
                    ])->setPaper('a4', 'landscape');
                    
                    return $pdf->download($nombreArchivo . '.pdf');
            }
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@exportar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al exportar la jornada.');
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
     * Calcula estadísticas de jornadas
     */
    private function calcularEstadisticasJornadas(Temporada $temporada): array
    {
        return [
            'total_jornadas' => $temporada->jornadas()->count(),
            'jornadas_completadas' => $temporada->jornadas()->where('completada', true)->count(),
            'jornadas_pendientes' => $temporada->jornadas()->where('completada', false)->count(),
            'jornada_actual' => $temporada->jornadas()
                ->where('fecha_inicio', '<=', now())
                ->where('fecha_fin', '>=', now())
                ->first(),
            'proxima_jornada' => $temporada->jornadas()
                ->where('fecha_inicio', '>', now())
                ->where('completada', false)
                ->orderBy('fecha_inicio')
                ->first(),
            'ultima_jornada' => $temporada->jornadas()
                ->where('completada', true)
                ->orderBy('numero', 'DESC')
                ->first(),
        ];
    }

    /**
     * Calcula fechas sugeridas para nueva jornada
     */
    private function calcularFechasSugeridas(Temporada $temporada, int $numeroJornada): array
    {
        $ultimaJornada = Jornada::where('temporada_id', $temporada->id)
            ->orderBy('numero', 'DESC')
            ->first();
        
        if ($ultimaJornada) {
            $fechaInicio = Carbon::parse($ultimaJornada->fecha_fin)->addDays(7);
        } else {
            $fechaInicio = Carbon::parse($temporada->fecha_inicio);
        }
        
        return [
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaInicio->addDays(2)->format('Y-m-d'),
            'nombre_sugerido' => "Jornada {$numeroJornada}",
        ];
    }

    /**
     * Calcula estadísticas de una jornada específica
     */
    private function calcularEstadisticasJornada(Jornada $jornada): array
    {
        $partidos = $jornada->partidos;
        $partidosFinalizados = $partidos->where('estado', 'Finalizado');
        
        return [
            'total_partidos' => $partidos->count(),
            'partidos_finalizados' => $partidosFinalizados->count(),
            'partidos_programados' => $partidos->where('estado', 'Programado')->count(),
            'partidos_en_juego' => $partidos->where('estado', 'Jugando')->count(),
            'total_goles' => $partidosFinalizados->sum(function($partido) {
                return $partido->goles_local + $partido->goles_visitante;
            }),
            'promedio_goles' => $partidosFinalizados->count() > 0 
                ? round($partidosFinalizados->sum(function($partido) {
                    return $partido->goles_local + $partido->goles_visitante;
                }) / $partidosFinalizados->count(), 2)
                : 0,
            'victorias_local' => $partidosFinalizados->where('goles_local', '>', 'goles_visitante')->count(),
            'victorias_visitante' => $partidosFinalizados->where('goles_visitante', '>', 'goles_local')->count(),
            'empates' => $partidosFinalizados->where('goles_local', '=', 'goles_visitante')->count(),
            'goles_local_total' => $partidosFinalizados->sum('goles_local'),
            'goles_visitante_total' => $partidosFinalizados->sum('goles_visitante'),
            'partido_mas_goles' => $partidosFinalizados->sortByDesc(function($partido) {
                return $partido->goles_local + $partido->goles_visitante;
            })->first(),
            'partido_mas_cerrado' => $partidosFinalizados->sortBy(function($partido) {
                return abs($partido->goles_local - $partido->goles_visitante);
            })->first(),
            'porcentaje_completado' => $partidos->count() > 0 
                ? round(($partidosFinalizados->count() / $partidos->count()) * 100, 1)
                : 0,
        ];
    }

    /**
     * Obtiene posiciones al inicio de la jornada
     */
    private function obtenerPosicionesInicioJornada(Jornada $jornada): array
    {
        $jornadaAnterior = Jornada::where('temporada_id', $jornada->temporada_id)
            ->where('numero', '<', $jornada->numero)
            ->orderBy('numero', 'DESC')
            ->first();
        
        if ($jornadaAnterior) {
            $cacheKey = 'clasificacion_' . $jornada->temporada_id . '_jornada_' . $jornadaAnterior->numero;
            
            return Cache::remember($cacheKey, 3600, function () use ($jornadaAnterior) {
                $clasificacion = Clasificacion::with('equipo')
                    ->where('temporada_id', $jornadaAnterior->temporada_id)
                    ->orderBy('puntos', 'DESC')
                    ->orderBy('diferencia_goles', 'DESC')
                    ->orderBy('goles_a_favor', 'DESC')
                    ->orderBy('nombre')
                    ->get()
                    ->map(function ($item, $key) {
                        return [
                            'posicion' => $key + 1,
                            'equipo_id' => $item->equipo_id,
                            'equipo_nombre' => $item->equipo->nombre,
                            'puntos' => $item->puntos,
                            'pj' => $item->partidos_jugados,
                            'pg' => $item->partidos_ganados,
                            'pe' => $item->partidos_empatados,
                            'pp' => $item->partidos_perdidos,
                            'gf' => $item->goles_a_favor,
                            'gc' => $item->goles_en_contra,
                            'dg' => $item->diferencia_goles,
                        ];
                    })
                    ->toArray();
                
                return $clasificacion;
            });
        }
        
        // Si es la primera jornada, retornar array vacío
        return [];
    }

    /**
     * Obtiene posiciones al final de la jornada
     */
    private function obtenerPosicionesFinJornada(Jornada $jornada): array
    {
        // Si la jornada está completada, usar la clasificación actualizada
        if ($jornada->completada) {
            return $this->obtenerClasificacionActualizada($jornada);
        }
        
        // Si no está completada, simular resultados
        return $this->simularClasificacionConResultados($jornada);
    }

    /**
     * Obtiene clasificación actualizada
     */
    private function obtenerClasificacionActualizada(Jornada $jornada): array
    {
        return Clasificacion::with('equipo')
            ->where('temporada_id', $jornada->temporada_id)
            ->orderBy('puntos', 'DESC')
            ->orderBy('diferencia_goles', 'DESC')
            ->orderBy('goles_a_favor', 'DESC')
            ->orderBy('nombre')
            ->get()
            ->map(function ($item, $key) {
                return [
                    'posicion' => $key + 1,
                    'equipo_id' => $item->equipo_id,
                    'equipo_nombre' => $item->equipo->nombre,
                    'puntos' => $item->puntos,
                    'pj' => $item->partidos_jugados,
                    'pg' => $item->partidos_ganados,
                    'pe' => $item->partidos_empatados,
                    'pp' => $item->partidos_perdidos,
                    'gf' => $item->goles_a_favor,
                    'gc' => $item->goles_en_contra,
                    'dg' => $item->diferencia_goles,
                ];
            })
            ->toArray();
    }

    /**
     * Simula clasificación con resultados de partidos jugados
     */
    private function simularClasificacionConResultados(Jornada $jornada): array
    {
        // Obtener posiciones al inicio
        $posiciones = $this->obtenerPosicionesInicioJornada($jornada);
        
        // Si no hay posiciones iniciales (primera jornada), crear estructura
        if (empty($posiciones)) {
            $equiposTemporada = Equipo::whereHas('clasificaciones', function($q) use ($jornada) {
                $q->where('temporada_id', $jornada->temporada_id);
            })->get();
            
            $posiciones = [];
            foreach ($equiposTemporada as $equipo) {
                $posiciones[] = [
                    'posicion' => 0,
                    'equipo_id' => $equipo->id,
                    'equipo_nombre' => $equipo->nombre,
                    'puntos' => 0,
                    'pj' => 0,
                    'pg' => 0,
                    'pe' => 0,
                    'pp' => 0,
                    'gf' => 0,
                    'gc' => 0,
                    'dg' => 0,
                ];
            }
        }
        
        // Convertir a colección para facilitar manipulación
        $posicionesColeccion = collect($posiciones)->keyBy('equipo_id');
        
        // Aplicar resultados de partidos jugados en esta jornada
        $partidosJugados = $jornada->partidos()->where('estado', 'Finalizado')->get();
        
        foreach ($partidosJugados as $partido) {
            $localId = $partido->equipo_local_id;
            $visitanteId = $partido->equipo_visitante_id;
            
            if (isset($posicionesColeccion[$localId]) && isset($posicionesColeccion[$visitanteId])) {
                // Actualizar estadísticas del local
                $posicionesColeccion[$localId]['pj']++;
                $posicionesColeccion[$localId]['gf'] += $partido->goles_local;
                $posicionesColeccion[$localId]['gc'] += $partido->goles_visitante;
                $posicionesColeccion[$localId]['dg'] = 
                    $posicionesColeccion[$localId]['gf'] - $posicionesColeccion[$localId]['gc'];
                
                // Actualizar estadísticas del visitante
                $posicionesColeccion[$visitanteId]['pj']++;
                $posicionesColeccion[$visitanteId]['gf'] += $partido->goles_visitante;
                $posicionesColeccion[$visitanteId]['gc'] += $partido->goles_local;
                $posicionesColeccion[$visitanteId]['dg'] = 
                    $posicionesColeccion[$visitanteId]['gf'] - $posicionesColeccion[$visitanteId]['gc'];
                
                // Determinar resultado
                if ($partido->goles_local > $partido->goles_visitante) {
                    $posicionesColeccion[$localId]['pg']++;
                    $posicionesColeccion[$localId]['puntos'] += 3;
                    $posicionesColeccion[$visitanteId]['pp']++;
                } elseif ($partido->goles_local < $partido->goles_visitante) {
                    $posicionesColeccion[$visitanteId]['pg']++;
                    $posicionesColeccion[$visitanteId]['puntos'] += 3;
                    $posicionesColeccion[$localId]['pp']++;
                } else {
                    $posicionesColeccion[$localId]['pe']++;
                    $posicionesColeccion[$localId]['puntos'] += 1;
                    $posicionesColeccion[$visitanteId]['pe']++;
                    $posicionesColeccion[$visitanteId]['puntos'] += 1;
                }
            }
        }
        
        // Ordenar por criterios de clasificación
        $posicionesOrdenadas = $posicionesColeccion->sort(function($a, $b) {
            if ($b['puntos'] !== $a['puntos']) {
                return $b['puntos'] - $a['puntos'];
            }
            if ($b['dg'] !== $a['dg']) {
                return $b['dg'] - $a['dg'];
            }
            if ($b['gf'] !== $a['gf']) {
                return $b['gf'] - $a['gf'];
            }
            return strcmp($a['equipo_nombre'], $b['equipo_nombre']);
        });
        
        // Asignar posiciones
        $resultado = [];
        $posicion = 1;
        foreach ($posicionesOrdenadas as $item) {
            $item['posicion'] = $posicion++;
            $resultado[] = $item;
        }
        
        return $resultado;
    }

    /**
     * Calcula cambios en posiciones
     */
    private function calcularCambiosPosiciones(array $posicionesInicio, array $posicionesFin): array
    {
        $cambios = [];
        
        foreach ($posicionesFin as $posFin) {
            $equipoId = $posFin['equipo_id'];
            $posicionFin = $posFin['posicion'];
            
            // Buscar posición inicial
            $posicionInicio = null;
            foreach ($posicionesInicio as $index => $posIni) {
                if ($posIni['equipo_id'] == $equipoId) {
                    $posicionInicio = $index + 1;
                    break;
                }
            }
            
            // Calcular cambio
            $cambio = null;
            if ($posicionInicio !== null) {
                $cambio = $posicionInicio - $posicionFin;
            }
            
            $cambios[$equipoId] = [
                'equipo_nombre' => $posFin['equipo_nombre'],
                'posicion_inicio' => $posicionInicio,
                'posicion_fin' => $posicionFin,
                'cambio' => $cambio,
                'tendencia' => $cambio > 0 ? 'sube' : ($cambio < 0 ? 'baja' : 'igual'),
                'diferencia' => abs($cambio),
            ];
        }
        
        // Ordenar por magnitud de cambio
        usort($cambios, function($a, $b) {
            return $b['diferencia'] - $a['diferencia'];
        });
        
        return $cambios;
    }

    /**
     * Identifica partidos destacados de la jornada
     */
    private function identificarPartidosDestacados(Jornada $jornada): array
    {
        $partidos = $jornada->partidos()
            ->where('estado', 'Finalizado')
            ->with(['equipoLocal', 'equipoVisitante'])
            ->get();
        
        $destacados = [
            'mas_goles' => null,
            'mas_cerrado' => null,
            'mayor_remontada' => null,
            'derby' => null,
        ];
        
        if ($partidos->isEmpty()) {
            return $destacados;
        }
        
        // Partido con más goles
        $destacados['mas_goles'] = $partidos->sortByDesc(function($partido) {
            return $partido->goles_local + $partido->goles_visitante;
        })->first();
        
        // Partido más cerrado (diferencia mínima)
        $destacados['mas_cerrado'] = $partidos->sortBy(function($partido) {
            return abs($partido->goles_local - $partido->goles_visitante);
        })->first();
        
        // Buscar derbys (mismos equipos en distintas categorías)
        // Esta lógica dependerá de tu estructura de datos
        // Por ahora, buscar por nombre similar
        foreach ($partidos as $partido) {
            if (str_contains($partido->equipoLocal->nombre, $partido->equipoVisitante->nombre) ||
                str_contains($partido->equipoVisitante->nombre, $partido->equipoLocal->nombre)) {
                $destacados['derby'] = $partido;
                break;
            }
        }
        
        return $destacados;
    }

    /**
     * Obtiene jornadas adyacentes (anterior y siguiente)
     */
    private function obtenerJornadasAdyacentes(Jornada $jornada): array
    {
        $jornadaAnterior = Jornada::where('temporada_id', $jornada->temporada_id)
            ->where('numero', '<', $jornada->numero)
            ->orderBy('numero', 'DESC')
            ->first();
            
        $jornadaSiguiente = Jornada::where('temporada_id', $jornada->temporada_id)
            ->where('numero', '>', $jornada->numero)
            ->orderBy('numero', 'ASC')
            ->first();
        
        return [
            'anterior' => $jornadaAnterior,
            'siguiente' => $jornadaSiguiente,
        ];
    }

    /**
     * Crea jornada para calendario
     */
    private function crearJornadaCalendario(
        Temporada $temporada,
        int $numero,
        Carbon $fecha,
        int $diasEntreJornadas
    ): Jornada {
        $fechaInicio = $fecha->copy();
        $fechaFin = $fecha->copy()->addDays(2);
        
        $nombre = $temporada->nombre . ' - Jornada ' . $numero;
        $slug = Str::slug($temporada->nombre . ' jornada ' . $numero);
        
        return Jornada::create([
            'temporada_id' => $temporada->id,
            'numero' => $numero,
            'nombre' => $nombre,
            'slug' => $slug,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'descripcion' => 'Jornada ' . $numero . ' de ' . $temporada->nombre,
        ]);
    }

    /**
     * Genera enfrentamientos para una jornada usando algoritmo round-robin
     */
    private function generarEnfrentamientosJornada(
        array $equipos,
        int $jornadaNumero,
        array $horarios,
        ?string $estadioPorDefecto
    ): array {
        $numEquipos = count($equipos);
        $enfrentamientos = [];
        $horarioIndex = 0;
        
        // Algoritmo round-robin clásico
        for ($i = 0; $i < $numEquipos / 2; $i++) {
            $equipo1 = $equipos[$i];
            $equipo2 = $equipos[$numEquipos - 1 - $i];
            
            // Alternar localía basado en jornada
            if (($jornadaNumero % 2 == 0 && $i % 2 == 0) || ($jornadaNumero % 2 == 1 && $i % 2 == 1)) {
                $local = $equipo2;
                $visitante = $equipo1;
            } else {
                $local = $equipo1;
                $visitante = $equipo2;
            }
            
            $enfrentamientos[] = [
                'equipo_local_id' => $local,
                'equipo_visitante_id' => $visitante,
                'horario' => $horarios[$horarioIndex % count($horarios)],
                'estadio' => $estadioPorDefecto,
            ];
            
            $horarioIndex++;
        }
        
        return $enfrentamientos;
    }

    /**
     * Crea partidos para una jornada
     */
    private function crearPartidosJornada(
        Jornada $jornada,
        array $enfrentamientos,
        Carbon $fechaBase
    ): array {
        $partidos = [];
        
        foreach ($enfrentamientos as $enfrentamiento) {
            $fechaHora = $fechaBase->copy();
            $horario = Carbon::parse($enfrentamiento['horario']);
            $fechaHora->setTime($horario->hour, $horario->minute);
            
            $partido = Partido::create([
                'jornada_id' => $jornada->id,
                'equipo_local_id' => $enfrentamiento['equipo_local_id'],
                'equipo_visitante_id' => $enfrentamiento['equipo_visitante_id'],
                'fecha_hora' => $fechaHora,
                'estadio' => $enfrentamiento['estadio'],
                'estado' => 'Programado',
            ]);
            
            $partidos[] = $partido;
        }
        
        return $partidos;
    }

    /**
     * Crea partidos de vuelta intercambiando localía
     */
    private function crearPartidosVuelta(
        Jornada $jornada,
        array $partidosIda,
        Carbon $fechaBase,
        array $horarios,
        ?string $estadioPorDefecto
    ): array {
        $partidos = [];
        $horarioIndex = 0;
        
        foreach ($partidosIda as $partidoIda) {
            $fechaHora = $fechaBase->copy();
            $horario = Carbon::parse($horarios[$horarioIndex % count($horarios)]);
            $fechaHora->setTime($horario->hour, $horario->minute);
            
            $partido = Partido::create([
                'jornada_id' => $jornada->id,
                'equipo_local_id' => $partidoIda->equipo_visitante_id,
                'equipo_visitante_id' => $partidoIda->equipo_local_id,
                'fecha_hora' => $fechaHora,
                'estadio' => $estadioPorDefecto ?? $partidoIda->estadio,
                'estado' => 'Programado',
            ]);
            
            $partidos[] = $partido;
            $horarioIndex++;
        }
        
        return $partidos;
    }

    /**
     * Rota equipos para siguiente jornada
     */
    private function rotarEquipos(array $equipos): array
    {
        $numEquipos = count($equipos);
        $rotados = array_slice($equipos, 0, 1);
        $rotados = array_merge($rotados, array_slice($equipos, $numEquipos - 1, 1));
        $rotados = array_merge($rotados, array_slice($equipos, 1, $numEquipos - 2));
        
        return $rotados;
    }

    /**
     * Crea registros iniciales en clasificación
     */
    private function crearClasificacionesIniciales(Temporada $temporada, array $equipos): void
    {
        foreach ($equipos as $equipoId) {
            Clasificacion::create([
                'temporada_id' => $temporada->id,
                'equipo_id' => $equipoId,
                'puntos' => 0,
                'partidos_jugados' => 0,
                'partidos_ganados' => 0,
                'partidos_empatados' => 0,
                'partidos_perdidos' => 0,
                'goles_a_favor' => 0,
                'goles_en_contra' => 0,
                'diferencia_goles' => 0,
            ]);
        }
    }

    /**
     * Recalcula clasificación después de completar jornada
     */
    private function recalcularClasificacionJornada(Jornada $jornada): void
    {
        // Esta función actualiza las clasificaciones de todos los equipos
        // basándose en todos los partidos jugados hasta esta jornada
        
        $equipos = Equipo::whereHas('clasificaciones', function($q) use ($jornada) {
            $q->where('temporada_id', $jornada->temporada_id);
        })->get();
        
        foreach ($equipos as $equipo) {
            // Obtener todos los partidos del equipo hasta esta jornada
            $partidosComoLocal = Partido::where('jornada_id', '<=', $jornada->id)
                ->where('equipo_local_id', $equipo->id)
                ->where('estado', 'Finalizado')
                ->get();
                
            $partidosComoVisitante = Partido::where('jornada_id', '<=', $jornada->id)
                ->where('equipo_visitante_id', $equipo->id)
                ->where('estado', 'Finalizado')
                ->get();
            
            // Calcular estadísticas
            $pj = $partidosComoLocal->count() + $partidosComoVisitante->count();
            $pg = 0;
            $pe = 0;
            $pp = 0;
            $gf = 0;
            $gc = 0;
            
            foreach ($partidosComoLocal as $partido) {
                $gf += $partido->goles_local;
                $gc += $partido->goles_visitante;
                
                if ($partido->goles_local > $partido->goles_visitante) {
                    $pg++;
                } elseif ($partido->goles_local < $partido->goles_visitante) {
                    $pp++;
                } else {
                    $pe++;
                }
            }
            
            foreach ($partidosComoVisitante as $partido) {
                $gf += $partido->goles_visitante;
                $gc += $partido->goles_local;
                
                if ($partido->goles_visitante > $partido->goles_local) {
                    $pg++;
                } elseif ($partido->goles_visitante < $partido->goles_local) {
                    $pp++;
                } else {
                    $pe++;
                }
            }
            
            $puntos = ($pg * 3) + ($pe * 1);
            $dg = $gf - $gc;
            
            // Actualizar clasificación
            Clasificacion::updateOrCreate(
                [
                    'temporada_id' => $jornada->temporada_id,
                    'equipo_id' => $equipo->id,
                ],
                [
                    'puntos' => $puntos,
                    'partidos_jugados' => $pj,
                    'partidos_ganados' => $pg,
                    'partidos_empatados' => $pe,
                    'partidos_perdidos' => $pp,
                    'goles_a_favor' => $gf,
                    'goles_en_contra' => $gc,
                    'diferencia_goles' => $dg,
                ]
            );
        }
    }

    /**
     * Duplica una jornada existente
     */
    public function duplicar($id)
    {
        try {
            DB::beginTransaction();
            
            $jornadaOriginal = Jornada::with('partidos')->findOrFail($id);
            
            // Crear nueva jornada
            $nuevaJornada = $jornadaOriginal->replicate();
            $nuevaJornada->numero = Jornada::where('temporada_id', $jornadaOriginal->temporada_id)
                ->max('numero') + 1;
            $nuevaJornada->nombre = $jornadaOriginal->nombre . ' (Copia)';
            $nuevaJornada->slug = $jornadaOriginal->slug . '-copia-' . time();
            $nuevaJornada->fecha_inicio = Carbon::parse($jornadaOriginal->fecha_inicio)->addDays(7);
            $nuevaJornada->fecha_fin = Carbon::parse($jornadaOriginal->fecha_fin)->addDays(7);
            $nuevaJornada->completada = false;
            $nuevaJornada->fecha_completada = null;
            $nuevaJornada->save();
            
            // Duplicar partidos
            foreach ($jornadaOriginal->partidos as $partidoOriginal) {
                $nuevoPartido = $partidoOriginal->replicate();
                $nuevoPartido->jornada_id = $nuevaJornada->id;
                $nuevoPartido->fecha_hora = Carbon::parse($partidoOriginal->fecha_hora)->addDays(7);
                $nuevoPartido->estado = 'Programado';
                $nuevoPartido->goles_local = null;
                $nuevoPartido->goles_visitante = null;
                $nuevoPartido->save();
            }
            
            DB::commit();
            
            return redirect()->route('jornadas.show', $nuevaJornada->id)
                ->with('success', 'Jornada duplicada exitosamente.')
                ->with('jornada_nueva', true);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@duplicar: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al duplicar la jornada.');
        }
    }

    /**
     * Reorganiza jornada (cambia orden de partidos)
     */
    public function reorganizar($id, Request $request)
    {
        try {
            DB::beginTransaction();
            
            $jornada = Jornada::with('partidos')->findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'partidos' => 'required|array',
                'partidos.*.id' => 'required|exists:partidos,id',
                'partidos.*.fecha_hora' => 'required|date_format:Y-m-d H:i:s',
                'partidos.*.estadio' => 'nullable|string|max:150',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            foreach ($request->partidos as $partidoData) {
                $partido = Partido::find($partidoData['id']);
                
                // Verificar que el partido pertenece a la jornada
                if ($partido->jornada_id != $jornada->id) {
                    throw new \Exception('Partido no pertenece a la jornada');
                }
                
                // Solo permitir reorganizar si no está en juego o finalizado
                if (!in_array($partido->estado, ['Programado', 'Suspendido'])) {
                    throw new \Exception('No se puede reorganizar un partido ' . $partido->estado);
                }
                
                $partido->update([
                    'fecha_hora' => $partidoData['fecha_hora'],
                    'estadio' => $partidoData['estadio'] ?? $partido->estadio,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Jornada reorganizada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en JornadaController@reorganizar: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al reorganizar la jornada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas avanzadas de jornadas
     */
    public function estadisticas(Request $request)
    {
        try {
            $temporadaId = $request->get('temporada_id');
            $temporada = $this->obtenerTemporada($temporadaId);
            
            if (!$temporada) {
                return redirect()->route('temporadas.index')
                    ->with('warning', 'Selecciona una temporada para ver estadísticas.');
            }
            
            $cacheKey = 'estadisticas_jornadas_temporada_' . $temporada->id;
            $estadisticas = Cache::remember($cacheKey, 1800, function () use ($temporada) {
                return $this->calcularEstadisticasAvanzadas($temporada);
            });
            
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            
            return view('jornadas.estadisticas', compact(
                'estadisticas',
                'temporada',
                'temporadas'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@estadisticas: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al cargar estadísticas.');
        }
    }

    /**
     * Calcula estadísticas avanzadas
     */
    private function calcularEstadisticasAvanzadas(Temporada $temporada): array
    {
        $jornadas = Jornada::with(['partidos' => function($query) {
            $query->where('estado', 'Finalizado');
        }])
        ->where('temporada_id', $temporada->id)
        ->orderBy('numero')
        ->get();
        
        $estadisticas = [
            'por_jornada' => [],
            'acumuladas' => [],
            'records' => [],
        ];
        
        $golesAcumulados = 0;
        $partidosAcumulados = 0;
        
        foreach ($jornadas as $jornada) {
            $partidosJornada = $jornada->partidos->count();
            $golesJornada = $jornada->partidos->sum(function($partido) {
                return $partido->goles_local + $partido->goles_visitante;
            });
            
            $estadisticas['por_jornada'][] = [
                'jornada' => $jornada->numero,
                'nombre' => $jornada->nombre,
                'partidos' => $partidosJornada,
                'goles' => $golesJornada,
                'promedio_goles' => $partidosJornada > 0 ? round($golesJornada / $partidosJornada, 2) : 0,
                'fecha' => $jornada->fecha_inicio->format('d/m/Y'),
            ];
            
            $golesAcumulados += $golesJornada;
            $partidosAcumulados += $partidosJornada;
            
            $estadisticas['acumuladas'][] = [
                'jornada' => $jornada->numero,
                'goles_acumulados' => $golesAcumulados,
                'partidos_acumulados' => $partidosAcumulados,
                'promedio_acumulado' => $partidosAcumulados > 0 ? round($golesAcumulados / $partidosAcumulados, 2) : 0,
            ];
        }
        
        // Calcular records
        if (!empty($estadisticas['por_jornada'])) {
            $estadisticas['records'] = [
                'jornada_mas_goleada' => collect($estadisticas['por_jornada'])
                    ->sortByDesc('goles')
                    ->first(),
                'mejor_promedio_goles' => collect($estadisticas['por_jornada'])
                    ->where('partidos', '>', 0)
                    ->sortByDesc('promedio_goles')
                    ->first(),
                'total_temporada' => [
                    'goles' => $golesAcumulados,
                    'partidos' => $partidosAcumulados,
                    'promedio' => $partidosAcumulados > 0 ? round($golesAcumulados / $partidosAcumulados, 2) : 0,
                ],
            ];
        }
        
        return $estadisticas;
    }

    /**
     * Obtiene información para widget/dashboard
     */
    public function widgetInfo(Request $request)
    {
        try {
            $temporada = $this->obtenerTemporada($request->get('temporada_id'));
            
            if (!$temporada) {
                return response()->json(['error' => 'No hay temporada activa'], 404);
            }
            
            $cacheKey = 'widget_jornada_info_' . $temporada->id;
            $info = Cache::remember($cacheKey, 300, function () use ($temporada) {
                $jornadaActual = $temporada->jornadas()
                    ->where('fecha_inicio', '<=', now())
                    ->where('fecha_fin', '>=', now())
                    ->withCount('partidos')
                    ->first();
                
                $proximaJornada = $temporada->jornadas()
                    ->where('fecha_inicio', '>', now())
                    ->where('completada', false)
                    ->orderBy('fecha_inicio')
                    ->withCount('partidos')
                    ->first();
                
                $ultimaJornada = $temporada->jornadas()
                    ->where('completada', true)
                    ->orderBy('numero', 'DESC')
                    ->withCount('partidos')
                    ->first();
                
                return [
                    'jornada_actual' => $jornadaActual,
                    'proxima_jornada' => $proximaJornada,
                    'ultima_jornada' => $ultimaJornada,
                    'temporada' => $temporada->nombre,
                ];
            });
            
            return response()->json($info);
            
        } catch (\Exception $e) {
            \Log::error('Error en JornadaController@widgetInfo: ' . $e->getMessage());
            
            return response()->json(['error' => 'Error al cargar información'], 500);
        }
    }
}