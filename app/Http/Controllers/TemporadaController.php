<?php

namespace App\Http\Controllers;

use App\Models\Temporada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class TemporadaController extends Controller
{
    /**
     * Estados válidos de una temporada
     */
    const ESTADOS_VALIDOS = ['Programada', 'En Curso', 'Finalizada', 'Cancelada', 'Archivada'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Temporada::withCount(['equipos', 'partidos', 'jornadas']);
            
            // Filtros avanzados
            $this->applyFilters($query, $request);
            
            // Búsqueda por texto
            if ($request->filled('search')) {
                $this->applySearch($query, $request->input('search'));
            }
            
            // Ordenamiento dinámico
            $sortField = $this->validateSortField($request->input('sort', 'anio'));
            $sortOrder = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
            $query->orderBy($sortField, $sortOrder);
            
            // Paginación con límites
            $perPage = min($request->input('per_page', 10), 100);
            $temporadas = $query->paginate($perPage)->withQueryString();
            
            return view('temporadas.index', [
                'temporadas' => $temporadas,
                'estados' => self::ESTADOS_VALIDOS,
                'filtros' => $request->only(['estado', 'anio', 'search', 'sort', 'order']),
                'sortField' => $sortField,
                'sortOrder' => $sortOrder
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener temporadas: ' . $e->getMessage(), [
                'request_params' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('temporadas.index')
                ->with('error', 'Error al obtener el listado de temporadas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('temporadas.create', [
            'estados' => self::ESTADOS_VALIDOS,
            'anio_actual' => date('Y')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('temporadas')->whereNull('deleted_at')
            ],
            'anio' => [
                'required',
                'integer',
                'min:1900',
                'max:' . (date('Y') + 5),
                Rule::unique('temporadas')->whereNull('deleted_at')
            ],
            'fecha_inicio' => [
                'required',
                'date',
                'after_or_equal:' . ($request->anio ?? date('Y')) . '-01-01',
                'before_or_equal:fecha_fin'
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
                'before_or_equal:' . ($request->anio ?? date('Y')) . '-12-31'
            ],
            'estado' => [
                'required',
                Rule::in(self::ESTADOS_VALIDOS)
            ],
            'descripcion' => 'nullable|string|max:500',
            'reglamento_url' => 'nullable|url|max:255|starts_with:https',
            'premio_ganador' => 'nullable|string|max:200',
            'max_equipos' => 'nullable|integer|min:2|max:50',
            'min_equipos' => 'nullable|integer|min:2|lt:max_equipos'
        ], [
            'anio.unique' => 'Ya existe una temporada para este año',
            'nombre.unique' => 'Ya existe una temporada con este nombre',
            'fecha_inicio.before_or_equal' => 'La fecha de inicio debe ser anterior o igual a la fecha de fin',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'reglamento_url.starts_with' => 'La URL del reglamento debe ser segura (HTTPS)'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Por favor corrige los errores en el formulario.');
        }

        DB::beginTransaction();
        
        try {
            $validated = $validator->validated();
            
            // Asegurar que el estado inicial sea 'Programada' si no se especifica
            if (!isset($validated['estado'])) {
                $validated['estado'] = 'Programada';
            }
            
            // Validar lógica de negocio adicional
            $this->validateTemporadaBusinessRules($validated);
            
            $temporada = Temporada::create($validated);
            
            // Crear estructura inicial de la temporada (jornadas, etc.)
            $this->inicializarEstructuraTemporada($temporada);
            
            DB::commit();
            
            Log::info('Temporada creada exitosamente', [
                'temporada_id' => $temporada->id,
                'nombre' => $temporada->nombre,
                'anio' => $temporada->anio,
                'user_id' => $request->user()?->id
            ]);
            
            return redirect()->route('temporadas.show', $temporada->id)
                ->with('success', 'Temporada creada exitosamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear temporada: ' . $e->getMessage(), [
                'request_data' => $request->except(['reglamento_url']),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la temporada: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $temporada = Temporada::with([
                'jornadas' => function($query) {
                    $query->orderBy('numero', 'asc')
                          ->with(['partidos' => function($q) {
                              $q->with(['equipoLocal', 'equipoVisitante'])
                                ->orderBy('fecha_hora', 'asc');
                          }]);
                },
                'clasificaciones' => function($query) {
                    $query->with(['equipo:id,nombre,abreviacion,escudo_url'])
                          ->orderBy('puntos', 'desc')
                          ->orderBy('diferencia_goles', 'desc')
                          ->orderBy('goles_a_favor', 'desc');
                },
                'partidos' => function($query) {
                    $query->with(['equipoLocal', 'equipoVisitante'])
                          ->orderBy('fecha_hora', 'desc')
                          ->limit(20);
                },
                'equipos' => function($query) {
                    $query->select('equipos.id', 'nombre', 'abreviacion', 'escudo_url')
                          ->orderBy('nombre', 'asc');
                }
            ])->findOrFail($id);
            
            // Calcular estadísticas
            $estadisticas = $this->calcularEstadisticasTemporada($temporada);
            $diasRestantes = $this->calcularDiasRestantes($temporada);
            $progreso = $this->calcularProgreso($temporada);
            
            return view('temporadas.show', [
                'temporada' => $temporada,
                'estadisticas' => $estadisticas,
                'dias_restantes' => $diasRestantes,
                'progreso' => $progreso,
                'estados' => self::ESTADOS_VALIDOS
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Temporada no encontrada: ' . $e->getMessage(), [
                'temporada_id' => $id
            ]);
            
            return redirect()->route('temporadas.index')
                ->with('error', 'Temporada no encontrada');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            return view('temporadas.edit', [
                'temporada' => $temporada,
                'estados' => self::ESTADOS_VALIDOS,
                'anio_actual' => date('Y')
            ]);
            
        } catch (\Exception $e) {
            return redirect()->route('temporadas.index')
                ->with('error', 'Temporada no encontrada');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('temporadas')->ignore($temporada->id)->whereNull('deleted_at')
                ],
                'anio' => [
                    'sometimes',
                    'integer',
                    'min:1900',
                    'max:' . (date('Y') + 5),
                    Rule::unique('temporadas')->ignore($temporada->id)->whereNull('deleted_at')
                ],
                'fecha_inicio' => [
                    'sometimes',
                    'date',
                    'after_or_equal:' . ($temporada->anio) . '-01-01',
                    'before_or_equal:fecha_fin'
                ],
                'fecha_fin' => [
                    'sometimes',
                    'date',
                    'after:fecha_inicio',
                    'before_or_equal:' . ($temporada->anio) . '-12-31'
                ],
                'estado' => [
                    'sometimes',
                    Rule::in(self::ESTADOS_VALIDOS)
                ],
                'descripcion' => 'nullable|string|max:500',
                'reglamento_url' => 'nullable|url|max:255|starts_with:https',
                'premio_ganador' => 'nullable|string|max:200',
                'max_equipos' => 'nullable|integer|min:2|max:50',
                'min_equipos' => 'nullable|integer|min:2|lt:max_equipos'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor corrige los errores en el formulario.');
            }
            
            DB::beginTransaction();
            
            try {
                $validated = $validator->validated();
                
                // Validar transiciones de estado
                if (isset($validated['estado']) && $validated['estado'] !== $temporada->estado) {
                    $this->validarTransicionEstado($temporada->estado, $validated['estado'], $temporada);
                }
                
                $temporada->update($validated);
                
                // Si se cambia a "En Curso", iniciar temporada automáticamente
                if (isset($validated['estado']) && $validated['estado'] === 'En Curso') {
                    $this->iniciarTemporada($temporada);
                }
                
                // Si se cambia a "Finalizada", finalizar temporada automáticamente
                if (isset($validated['estado']) && $validated['estado'] === 'Finalizada') {
                    $this->finalizarTemporada($temporada);
                }
                
                DB::commit();
                
                Log::info('Temporada actualizada exitosamente', [
                    'temporada_id' => $temporada->id,
                    'changes' => $temporada->getChanges(),
                    'user_id' => $request->user()?->id
                ]);
                
                return redirect()->route('temporadas.show', $temporada->id)
                    ->with('success', 'Temporada actualizada exitosamente')
                    ->with('changes', $temporada->getChanges());
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al actualizar temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException 
                ? 'Temporada no encontrada' 
                : 'Error al actualizar la temporada: ' . $e->getMessage();
            
            return redirect()->back()
                ->withInput()
                ->with('error', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $temporada = Temporada::withCount([
                'partidos',
                'jornadas',
                'clasificaciones',
                'equipos'
            ])->findOrFail($id);
            
            // Validaciones de integridad referencial
            $dependencias = [
                'partidos' => $temporada->partidos_count,
                'jornadas' => $temporada->jornadas_count,
                'clasificaciones' => $temporada->clasificaciones_count,
                'equipos' => $temporada->equipos_count,
            ];
            
            $totalDependencias = array_sum($dependencias);
            
            if ($totalDependencias > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la temporada porque tiene ' . $totalDependencias . ' elementos relacionados.')
                    ->with('dependencies', $dependencias);
            }
            
            DB::beginTransaction();
            
            try {
                $nombreTemporada = $temporada->nombre;
                
                // Soft delete si está configurado, sino hard delete
                $temporada->delete();
                
                DB::commit();
                
                Log::info('Temporada eliminada exitosamente', [
                    'temporada_id' => $id,
                    'nombre' => $nombreTemporada,
                    'user_id' => request()->user()?->id
                ]);
                
                return redirect()->route('temporadas.index')
                    ->with('success', 'Temporada "' . $nombreTemporada . '" eliminada exitosamente');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $message = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException 
                ? 'Temporada no encontrada' 
                : 'Error al eliminar la temporada: ' . $e->getMessage();
            
            return redirect()->route('temporadas.index')
                ->with('error', $message);
        }
    }

    /**
     * Obtener la temporada actual en curso
     */
    public function actual()
    {
        try {
            $temporada = Temporada::with([
                'jornadas' => function($query) {
                    $query->where('estado', '!=', 'Finalizada')
                          ->orderBy('numero', 'asc')
                          ->limit(5);
                },
                'clasificaciones' => function($query) {
                    $query->with(['equipo:id,nombre,abreviacion,escudo_url'])
                          ->orderBy('puntos', 'desc')
                          ->orderBy('diferencia_goles', 'desc')
                          ->limit(10);
                }
            ])->enCurso()->first();
            
            if (!$temporada) {
                // Buscar la temporada más reciente si no hay una en curso
                $temporada = Temporada::orderBy('anio', 'desc')
                    ->orderBy('fecha_inicio', 'desc')
                    ->first();
                
                if (!$temporada) {
                    return view('temporadas.actual')
                        ->with('error', 'No hay temporadas disponibles');
                }
            }
            
            $estadisticas = $this->calcularEstadisticasTemporada($temporada);
            
            return view('temporadas.actual', [
                'temporada' => $temporada,
                'estadisticas' => $estadisticas,
                'es_actual' => $temporada->estado === 'En Curso'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener temporada actual: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('temporadas.actual')
                ->with('error', 'Error al obtener la temporada actual');
        }
    }

    /**
     * Obtener estadísticas detalladas de una temporada
     */
    public function estadisticas(string $id)
    {
        try {
            $temporada = Temporada::with(['partidos' => function($query) {
                $query->where('estado', 'Finalizado')
                      ->with(['equipoLocal', 'equipoVisitante']);
            }, 'clasificaciones.equipo'])->findOrFail($id);
            
            $estadisticas = $this->calcularEstadisticasDetalladas($temporada);
            
            return view('temporadas.estadisticas', [
                'temporada' => $temporada,
                'estadisticas' => $estadisticas
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas de temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('temporadas.show', $id)
                ->with('error', 'Error al generar las estadísticas');
        }
    }

    /**
     * Obtener jornadas de una temporada
     */
    public function jornadas(string $id, Request $request)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'estado' => 'nullable|in:Programada,En Juego,Finalizada,Cancelada',
                'numero' => 'nullable|integer|min:1',
                'desde' => 'nullable|date',
                'hasta' => 'nullable|date|after_or_equal:desde',
                'sort' => 'nullable|in:numero,fecha_inicio,fecha_fin',
                'order' => 'nullable|in:asc,desc'
            ]);
            
            if ($validator->fails()) {
                return redirect()->route('temporadas.show', $id)
                    ->with('error', 'Error en los parámetros de búsqueda: ' . implode(', ', $validator->errors()->all()));
            }
            
            $query = $temporada->jornadas()->withCount('partidos');
            
            $this->aplicarFiltrosJornadas($query, $validator->validated());
            
            // Ordenamiento
            $sortField = $request->input('sort', 'numero');
            $sortOrder = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortField, $sortOrder);
            
            // Paginación
            $perPage = min($request->input('per_page', 20), 50);
            $jornadas = $query->paginate($perPage)->withQueryString();
            
            return view('temporadas.jornadas', [
                'temporada' => $temporada,
                'jornadas' => $jornadas,
                'filtros' => $request->only(['estado', 'numero', 'desde', 'hasta', 'sort', 'order'])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener jornadas de temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('temporadas.show', $id)
                ->with('error', 'Error al obtener las jornadas');
        }
    }

    /**
     * Obtener clasificación de una temporada
     */
    public function clasificacion(string $id)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            $clasificaciones = $temporada->clasificaciones()
                ->with(['equipo:id,nombre,abreviacion,escudo_url,ciudad'])
                ->orderBy('puntos', 'desc')
                ->orderBy('diferencia_goles', 'desc')
                ->orderBy('goles_a_favor', 'desc')
                ->orderBy('partidos_jugados', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();
            
            return view('temporadas.clasificacion', [
                'temporada' => $temporada,
                'clasificaciones' => $clasificaciones
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener clasificación de temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('temporadas.show', $id)
                ->with('error', 'Error al obtener la clasificación');
        }
    }

    /**
     * Cambiar estado de una temporada
     */
    public function cambiarEstado(string $id, Request $request)
    {
        try {
            $temporada = Temporada::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'estado' => [
                    'required',
                    Rule::in(self::ESTADOS_VALIDOS)
                ],
                'comentario' => 'nullable|string|max:500'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Error en los datos del formulario');
            }
            
            DB::beginTransaction();
            
            try {
                $nuevoEstado = $validator->validated()['estado'];
                $estadoAnterior = $temporada->estado;
                
                // Validar transición de estado
                $this->validarTransicionEstado($estadoAnterior, $nuevoEstado, $temporada);
                
                $temporada->update(['estado' => $nuevoEstado]);
                
                // Ejecutar acciones según el nuevo estado
                switch ($nuevoEstado) {
                    case 'En Curso':
                        $this->iniciarTemporada($temporada);
                        break;
                    case 'Finalizada':
                        $this->finalizarTemporada($temporada);
                        break;
                    case 'Archivada':
                        $this->archivarTemporada($temporada);
                        break;
                }
                
                DB::commit();
                
                Log::info('Estado de temporada actualizado', [
                    'temporada_id' => $temporada->id,
                    'estado_anterior' => $estadoAnterior,
                    'estado_nuevo' => $nuevoEstado,
                    'comentario' => $request->input('comentario'),
                    'user_id' => $request->user()?->id
                ]);
                
                return redirect()->route('temporadas.show', $temporada->id)
                    ->with('success', 'Estado actualizado de "' . $estadoAnterior . '" a "' . $nuevoEstado . '"');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de temporada: ' . $e->getMessage(), [
                'temporada_id' => $id,
                'nuevo_estado' => $request->input('estado'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }

    /**
     * Obtener historial de temporadas
     */
    public function historial(Request $request)
    {
        try {
            $query = Temporada::whereIn('estado', ['Finalizada', 'Archivada'])
                ->with(['clasificaciones' => function($query) {
                    $query->where('posicion', 1)
                          ->with('equipo:id,nombre,abreviacion');
                }]);
            
            if ($request->filled('anio_desde')) {
                $query->where('anio', '>=', $request->anio_desde);
            }
            
            if ($request->filled('anio_hasta')) {
                $query->where('anio', '<=', $request->anio_hasta);
            }
            
            $temporadas = $query->orderBy('anio', 'desc')->get();
            
            $campeonatosPorEquipo = $this->calcularCampeonatosPorEquipo($temporadas);
            
            return view('temporadas.historial', [
                'temporadas' => $temporadas,
                'campeonatos_por_equipo' => $campeonatosPorEquipo,
                'filtros' => $request->only(['anio_desde', 'anio_hasta']),
                'total_temporadas' => $temporadas->count(),
                'periodo' => $temporadas->count() > 0 
                    ? $temporadas->min('anio') . ' - ' . $temporadas->max('anio')
                    : 'Sin datos'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener historial de temporadas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('temporadas.index')
                ->with('error', 'Error al obtener el historial');
        }
    }

    /**
     * Métodos auxiliares privados
     */
    
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('estado')) {
            $estados = is_array($request->estado) ? $request->estado : [$request->estado];
            $query->whereIn('estado', $estados);
        }
        
        if ($request->filled('anio')) {
            $query->where('anio', $request->anio);
        }
        
        if ($request->filled('anio_desde')) {
            $query->where('anio', '>=', $request->anio_desde);
        }
        
        if ($request->filled('anio_hasta')) {
            $query->where('anio', '<=', $request->anio_hasta);
        }
        
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inicio', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin', '<=', $request->fecha_hasta);
        }
    }
    
    private function applySearch($query, string $search): void
    {
        $query->where(function($q) use ($search) {
            $q->where('nombre', 'like', '%' . $search . '%')
              ->orWhere('descripcion', 'like', '%' . $search . '%')
              ->orWhere('anio', 'like', '%' . $search . '%');
        });
    }
    
    private function validateSortField(string $field): string
    {
        $allowedFields = ['id', 'nombre', 'anio', 'fecha_inicio', 'fecha_fin', 'estado', 'created_at'];
        return in_array($field, $allowedFields) ? $field : 'anio';
    }
    
    private function validarTransicionEstado(string $estadoActual, string $nuevoEstado, Temporada $temporada): void
    {
        $transicionesValidas = [
            'Programada' => ['En Curso', 'Cancelada'],
            'En Curso' => ['Finalizada', 'Cancelada'],
            'Finalizada' => ['Archivada'],
            'Cancelada' => ['Programada'],
            'Archivada' => [] // No se puede cambiar desde archivada
        ];
        
        if (!isset($transicionesValidas[$estadoActual]) || !in_array($nuevoEstado, $transicionesValidas[$estadoActual])) {
            throw new \Exception("Transición de estado no válida: {$estadoActual} -> {$nuevoEstado}");
        }
        
        // Validaciones específicas
        if ($nuevoEstado === 'En Curso' && $temporada->equipos()->count() < 2) {
            throw new \Exception('No se puede iniciar una temporada con menos de 2 equipos');
        }
        
        if ($nuevoEstado === 'Finalizada' && $temporada->partidos()->where('estado', '!=', 'Finalizado')->exists()) {
            throw new \Exception('No se puede finalizar una temporada con partidos pendientes');
        }
    }
    
    private function calcularEstadisticasTemporada(Temporada $temporada): array
    {
        $partidos = $temporada->partidos()->where('estado', 'Finalizado')->get();
        
        return [
            'partidos' => [
                'total' => $temporada->partidos()->count(),
                'jugados' => $partidos->count(),
                'pendientes' => $temporada->partidos()->whereNotIn('estado', ['Finalizado', 'Cancelado'])->count(),
                'cancelados' => $temporada->partidos()->where('estado', 'Cancelado')->count(),
                'promedio_goles' => $partidos->count() > 0 
                    ? round($partidos->sum(function($p) { 
                        return $p->goles_local + $p->goles_visitante; 
                    }) / $partidos->count(), 2)
                    : 0
            ],
            'equipos' => [
                'total' => $temporada->equipos()->count(),
                'activos' => $temporada->equipos()->where('activo', true)->count()
            ],
            'jornadas' => [
                'total' => $temporada->jornadas()->count(),
                'completadas' => $temporada->jornadas()->where('estado', 'Finalizada')->count()
            ]
        ];
    }
    
    private function calcularEstadisticasDetalladas(Temporada $temporada): array
    {
        $partidos = $temporada->partidos()->where('estado', 'Finalizado')->get();
        
        return [
            'general' => $this->calcularEstadisticasTemporada($temporada),
            'goles' => [
                'total' => $partidos->sum(function($p) { return $p->goles_local + $p->goles_visitante; }),
                'por_partido' => $partidos->count() > 0 
                    ? round(($partidos->sum(function($p) { 
                        return $p->goles_local + $p->goles_visitante; 
                    }) / $partidos->count()), 2)
                    : 0,
                'local' => $partidos->sum('goles_local'),
                'visitante' => $partidos->sum('goles_visitante'),
                'maximo_partido' => $partidos->max(function($p) { return $p->goles_local + $p->goles_visitante; }),
                'minimo_partido' => $partidos->min(function($p) { return $p->goles_local + $p->goles_visitante; })
            ],
            'resultados' => [
                'victorias_local' => $partidos->where('goles_local', '>', 'goles_visitante')->count(),
                'victorias_visitante' => $partidos->where('goles_visitante', '>', 'goles_local')->count(),
                'empates' => $partidos->where('goles_local', '=', 'goles_visitante')->count(),
                'porcentaje_victorias_local' => $partidos->count() > 0 
                    ? round(($partidos->where('goles_local', '>', 'goles_visitante')->count() / $partidos->count()) * 100, 2)
                    : 0
            ]
        ];
    }
    
    private function calcularDiasRestantes(Temporada $temporada): ?int
    {
        if ($temporada->estado !== 'En Curso') {
            return null;
        }
        
        return now()->diffInDays($temporada->fecha_fin, false);
    }
    
    private function calcularProgreso(Temporada $temporada): float
    {
        $partidosTotales = $temporada->partidos()->count();
        $partidosJugados = $temporada->partidos()->where('estado', 'Finalizado')->count();
        
        return $partidosTotales > 0 ? round(($partidosJugados / $partidosTotales) * 100, 2) : 0;
    }
    
    private function aplicarFiltrosJornadas($query, array $filtros): void
    {
        if (isset($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        
        if (isset($filtros['numero'])) {
            $query->where('numero', $filtros['numero']);
        }
        
        if (isset($filtros['desde'])) {
            $query->whereDate('fecha_inicio', '>=', $filtros['desde']);
        }
        
        if (isset($filtros['hasta'])) {
            $query->whereDate('fecha_fin', '<=', $filtros['hasta']);
        }
    }
    
    private function calcularCampeonatosPorEquipo($temporadas): array
    {
        $campeonatos = [];
        
        foreach ($temporadas as $temporada) {
            $campeon = $temporada->clasificaciones->first()?->equipo;
            if ($campeon) {
                $campeonatos[$campeon->nombre] = ($campeonatos[$campeon->nombre] ?? 0) + 1;
            }
        }
        
        arsort($campeonatos);
        
        $result = [];
        foreach ($campeonatos as $nombre => $cantidad) {
            $result[] = ['equipo' => $nombre, 'campeonatos' => $cantidad];
        }
        
        return $result;
    }
    
    private function validateTemporadaBusinessRules(array $data): void
    {
        // Validar que el año coincida con las fechas
        if (isset($data['anio']) && isset($data['fecha_inicio'])) {
            $anioInicio = date('Y', strtotime($data['fecha_inicio']));
            if ($anioInicio != $data['anio']) {
                throw new \Exception('El año de la temporada debe coincidir con el año de inicio');
            }
        }
        
        // Validar que no se solapen temporadas
        if (isset($data['fecha_inicio']) && isset($data['fecha_fin'])) {
            $solapada = Temporada::where(function($query) use ($data) {
                $query->whereBetween('fecha_inicio', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhereBetween('fecha_fin', [$data['fecha_inicio'], $data['fecha_fin']])
                      ->orWhere(function($q) use ($data) {
                          $q->where('fecha_inicio', '<=', $data['fecha_inicio'])
                            ->where('fecha_fin', '>=', $data['fecha_fin']);
                      });
            })->exists();
            
            if ($solapada) {
                throw new \Exception('Existe una temporada que se solapa con las fechas especificadas');
            }
        }
    }
    
    private function inicializarEstructuraTemporada(Temporada $temporada): void
    {
        // Aquí iría la lógica para inicializar la estructura de la temporada
        // como crear jornadas automáticamente, etc.
        
        Log::info('Estructura de temporada inicializada', [
            'temporada_id' => $temporada->id,
            'nombre' => $temporada->nombre
        ]);
    }
    
    private function iniciarTemporada(Temporada $temporada): void
    {
        // Lógica para iniciar una temporada
        Log::info('Temporada iniciada', [
            'temporada_id' => $temporada->id,
            'nombre' => $temporada->nombre
        ]);
    }
    
    private function finalizarTemporada(Temporada $temporada): void
    {
        // Lógica para finalizar una temporada
        Log::info('Temporada finalizada', [
            'temporada_id' => $temporada->id,
            'nombre' => $temporada->nombre
        ]);
    }
    
    private function archivarTemporada(Temporada $temporada): void
    {
        // Lógica para archivar una temporada
        Log::info('Temporada archivada', [
            'temporada_id' => $temporada->id,
            'nombre' => $temporada->nombre
        ]);
    }
}