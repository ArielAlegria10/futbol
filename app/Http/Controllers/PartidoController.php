<?php

namespace App\Http\Controllers;

use App\Models\Partido;
use App\Models\Equipo;
use App\Models\Jornada;
use App\Models\Temporada;
use App\Models\EstadisticaPartido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PartidoController extends Controller
{
    /**
     * Display a listing of partidos.
     */
    public function index(Request $request)
    {
        try {
            $query = Partido::with(['equipoLocal', 'equipoVisitante', 'jornada', 'temporada'])
                ->orderBy('fecha_hora', 'DESC');
            
            // Filtro por estado
            if ($request->filled('estado') && $request->estado !== 'todos') {
                $query->where('estado', $request->estado);
            }
            
            // Filtro por temporada
            if ($request->filled('temporada_id')) {
                $query->where('temporada_id', $request->temporada_id);
            }
            
            // Filtro por equipo
            if ($request->filled('equipo_id')) {
                $equipoId = $request->equipo_id;
                $query->where(function($q) use ($equipoId) {
                    $q->where('equipo_local_id', $equipoId)
                      ->orWhere('equipo_visitante_id', $equipoId);
                });
            }
            
            // Filtro por fecha
            if ($request->filled('fecha_inicio')) {
                $query->whereDate('fecha_hora', '>=', $request->fecha_inicio);
            }
            
            if ($request->filled('fecha_fin')) {
                $query->whereDate('fecha_hora', '<=', $request->fecha_fin);
            }
            
            // Búsqueda por texto
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->whereHas('equipoLocal', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('equipoVisitante', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    })
                    ->orWhereHas('jornada', function($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });
                });
            }
            
            // Estadísticas para la vista
            $estadisticas = [
                'total' => Partido::count(),
                'finalizados' => Partido::where('estado', 'Finalizado')->count(),
                'programados' => Partido::where('estado', 'Programado')->count(),
                'en_juego' => Partido::where('estado', 'Jugando')->count(),
                'suspendidos' => Partido::where('estado', 'Suspendido')->count(),
            ];
            
            // Datos para filtros
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            $equipos = Equipo::where('activo', true)->orderBy('nombre')->get();
            
            // Estados disponibles
            $estados = [
                'todos' => 'Todos los estados',
                'Programado' => 'Programados',
                'Jugando' => 'En juego',
                'Finalizado' => 'Finalizados',
                'Suspendido' => 'Suspendidos',
                'Cancelado' => 'Cancelados',
            ];
            
            // Paginación
            $perPage = $request->get('per_page', 15);
            $partidos = $query->paginate($perPage)->withQueryString();
            
            return view('partidos.index', compact(
                'partidos',
                'estadisticas',
                'temporadas',
                'equipos',
                'estados'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@index: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la lista de partidos.');
        }
    }

    /**
     * Show the form for creating a new partido.
     */
public function create()
{
    $equipos = Equipo::orderBy('nombre')->get();
    $temporadas = Temporada::orderBy('id', 'desc')->get();
    $jornadas = Jornada::with('temporada')->get();

    $estados = [
        'Programado' => 'Programado',
        'Jugando' => 'Jugando',
        'Finalizado' => 'Finalizado',
        'Suspendido' => 'Suspendido'
    ];

    $defaults = [
        'estado' => 'Programado',
        'fecha_hora' => now()->format('Y-m-d\TH:i')
    ];

    return view('partidos.create', compact(
        'equipos',
        'temporadas',
        'jornadas',
        'estados',
        'defaults'
    ));
}


    /**
     * Store a newly created partido in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $validator = Validator::make($request->all(), [
                'equipo_local_id' => 'required|exists:equipos,id',
                'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
                'jornada_id' => 'required|exists:jornadas,id',
                'temporada_id' => 'required|exists:temporadas,id',
                'fecha_hora' => 'required|date',
                'estadio' => 'nullable|string|max:200',
                'arbitro_principal' => 'nullable|string|max:100',
                'arbitro_asistente_1' => 'nullable|string|max:100',
                'arbitro_asistente_2' => 'nullable|string|max:100',
                'cuarto_arbitro' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:500',
                'estado' => 'required|in:Programado,Jugando,Finalizado,Suspendido,Cancelado',
                'goles_local' => 'nullable|integer|min:0',
                'goles_visitante' => 'nullable|integer|min:0',
                'posesion_local' => 'nullable|integer|min:0|max:100',
                'posesion_visitante' => 'nullable|integer|min:0|max:100',
                'tiros_puerta_local' => 'nullable|integer|min:0',
                'tiros_puerta_visitante' => 'nullable|integer|min:0',
                'tiros_fuera_local' => 'nullable|integer|min:0',
                'tiros_fuera_visitante' => 'nullable|integer|min:0',
                'faltas_local' => 'nullable|integer|min:0',
                'faltas_visitante' => 'nullable|integer|min:0',
                'tarjetas_amarillas_local' => 'nullable|integer|min:0',
                'tarjetas_amarillas_visitante' => 'nullable|integer|min:0',
                'tarjetas_rojas_local' => 'nullable|integer|min:0',
                'tarjetas_rojas_visitante' => 'nullable|integer|min:0',
                'es_publico' => 'boolean',
                'boletos_disponibles' => 'nullable|integer|min:0',
                'precio_entrada' => 'nullable|numeric|min:0',
            ], [
                'equipo_local_id.different' => 'El equipo local y visitante deben ser diferentes.',
                'fecha_hora.required' => 'La fecha y hora del partido son obligatorias.',
                'estado.required' => 'El estado del partido es obligatorio.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            $data = $validator->validated();
            
            // Si el partido está finalizado, validar que tenga marcador
            if ($data['estado'] == 'Finalizado') {
                if (!isset($data['goles_local']) || !isset($data['goles_visitante'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Para partidos finalizados, debes ingresar el marcador.');
                }
            }
            
            // Crear partido
            $partido = Partido::create($data);
            
            // Crear estadísticas del partido si está finalizado
            if ($partido->estado == 'Finalizado') {
                EstadisticaPartido::create([
                    'partido_id' => $partido->id,
                    'posesion_local' => $data['posesion_local'] ?? 50,
                    'posesion_visitante' => $data['posesion_visitante'] ?? 50,
                    'tiros_puerta_local' => $data['tiros_puerta_local'] ?? 0,
                    'tiros_puerta_visitante' => $data['tiros_puerta_visitante'] ?? 0,
                    'tiros_fuera_local' => $data['tiros_fuera_local'] ?? 0,
                    'tiros_fuera_visitante' => $data['tiros_fuera_visitante'] ?? 0,
                    'faltas_local' => $data['faltas_local'] ?? 0,
                    'faltas_visitante' => $data['faltas_visitante'] ?? 0,
                    'tarjetas_amarillas_local' => $data['tarjetas_amarillas_local'] ?? 0,
                    'tarjetas_amarillas_visitante' => $data['tarjetas_amarillas_visitante'] ?? 0,
                    'tarjetas_rojas_local' => $data['tarjetas_rojas_local'] ?? 0,
                    'tarjetas_rojas_visitante' => $data['tarjetas_rojas_visitante'] ?? 0,
                    'esquinas_local' => $data['esquinas_local'] ?? 0,
                    'esquinas_visitante' => $data['esquinas_visitante'] ?? 0,
                    'offsides_local' => $data['offsides_local'] ?? 0,
                    'offsides_visitante' => $data['offsides_visitante'] ?? 0,
                ]);
                
                // Actualizar clasificaciones
                $this->actualizarClasificaciones($partido);
            }
            
            DB::commit();
            
            return redirect()->route('partidos.show', $partido->id)
                ->with('success', 'Partido creado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en PartidoController@store: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el partido. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified partido.
     */
    public function show($id)
    {
        try {
            $partido = Partido::with([
                'equipoLocal',
                'equipoVisitante',
                'jornada',
                'temporada',
                'estadisticas',
            ])->findOrFail($id);
            
            // Calcular estadísticas resumidas
            $estadisticasResumen = $this->calcularEstadisticasResumen($partido);
            
            // Obtener partidos relacionados (misma jornada)
            $partidosJornada = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where('jornada_id', $partido->jornada_id)
                ->where('id', '!=', $partido->id)
                ->orderBy('fecha_hora')
                ->get();
            
            // Historial de enfrentamientos
            $historialEnfrentamientos = $this->obtenerHistorialEnfrentamientos(
                $partido->equipo_local_id, 
                $partido->equipo_visitante_id
            );
            
            // Próximos partidos de los equipos
            $proximosPartidosLocal = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where(function($q) use ($partido) {
                    $q->where('equipo_local_id', $partido->equipo_local_id)
                      ->orWhere('equipo_visitante_id', $partido->equipo_local_id);
                })
                ->where('estado', 'Programado')
                ->where('fecha_hora', '>', now())
                ->orderBy('fecha_hora')
                ->limit(3)
                ->get();
                
            $proximosPartidosVisitante = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where(function($q) use ($partido) {
                    $q->where('equipo_local_id', $partido->equipo_visitante_id)
                      ->orWhere('equipo_visitante_id', $partido->equipo_visitante_id);
                })
                ->where('estado', 'Programado')
                ->where('fecha_hora', '>', now())
                ->orderBy('fecha_hora')
                ->limit(3)
                ->get();
            
            return view('partidos.show', compact(
                'partido',
                'estadisticasResumen',
                'partidosJornada',
                'historialEnfrentamientos',
                'proximosPartidosLocal',
                'proximosPartidosVisitante'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@show: ' . $e->getMessage());
            return redirect()->route('partidos.index')
                ->with('error', 'Partido no encontrado.');
        }
    }

    /**
     * Show the form for editing the specified partido.
     */
    public function edit($id)
    {
        try {
            $partido = Partido::with(['equipoLocal', 'equipoVisitante'])->findOrFail($id);
            
            // Solo permitir edición si no está en juego o finalizado
            if ($partido->estado == 'Jugando' || $partido->estado == 'Finalizado') {
                return redirect()->route('partidos.show', $partido->id)
                    ->with('warning', 'No se puede editar un partido que ya está en juego o finalizado.');
            }
            
            $equipos = Equipo::where('activo', true)->orderBy('nombre')->get();
            $jornadas = Jornada::where('temporada_id', $partido->temporada_id)
                ->orderBy('numero')
                ->get();
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            
            return view('partidos.edit', compact('partido', 'equipos', 'jornadas', 'temporadas'));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@edit: ' . $e->getMessage());
            return redirect()->route('partidos.index')
                ->with('error', 'Partido no encontrado o no editable.');
        }
    }

    /**
     * Update the specified partido in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $partido = Partido::findOrFail($id);
            
            // Validar que se pueda editar
            if ($partido->estado == 'Jugando' || $partido->estado == 'Finalizado') {
                return redirect()->route('partidos.show', $partido->id)
                    ->with('warning', 'No se puede editar un partido que ya está en juego o finalizado.');
            }
            
            $validator = Validator::make($request->all(), [
                'equipo_local_id' => 'required|exists:equipos,id',
                'equipo_visitante_id' => 'required|exists:equipos,id|different:equipo_local_id',
                'jornada_id' => 'required|exists:jornadas,id',
                'temporada_id' => 'required|exists:temporadas,id',
                'fecha_hora' => 'required|date',
                'estadio' => 'nullable|string|max:200',
                'arbitro_principal' => 'nullable|string|max:100',
                'arbitro_asistente_1' => 'nullable|string|max:100',
                'arbitro_asistente_2' => 'nullable|string|max:100',
                'cuarto_arbitro' => 'nullable|string|max:100',
                'observaciones' => 'nullable|string|max:500',
                'estado' => 'required|in:Programado,Jugando,Finalizado,Suspendido,Cancelado',
                'goles_local' => 'nullable|integer|min:0',
                'goles_visitante' => 'nullable|integer|min:0',
                'es_publico' => 'boolean',
                'boletos_disponibles' => 'nullable|integer|min:0',
                'precio_entrada' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Por favor, corrige los errores en el formulario.');
            }

            $data = $validator->validated();
            
            // Si el partido se marca como finalizado, validar marcador
            if ($data['estado'] == 'Finalizado' && $partido->estado != 'Finalizado') {
                if (!isset($data['goles_local']) || !isset($data['goles_visitante'])) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Para finalizar el partido, debes ingresar el marcador.');
                }
            }
            
            // Guardar estado anterior para comparar
            $estadoAnterior = $partido->estado;
            $golesLocalAnterior = $partido->goles_local;
            $golesVisitanteAnterior = $partido->goles_visitante;
            
            // Actualizar partido
            $partido->update($data);
            
            // Si cambió de estado a Finalizado, crear estadísticas
            if ($partido->estado == 'Finalizado' && $estadoAnterior != 'Finalizado') {
                EstadisticaPartido::create([
                    'partido_id' => $partido->id,
                    'posesion_local' => 50,
                    'posesion_visitante' => 50,
                    'tiros_puerta_local' => 0,
                    'tiros_puerta_visitante' => 0,
                    'tiros_fuera_local' => 0,
                    'tiros_fuera_visitante' => 0,
                    'faltas_local' => 0,
                    'faltas_visitante' => 0,
                    'tarjetas_amarillas_local' => 0,
                    'tarjetas_amarillas_visitante' => 0,
                    'tarjetas_rojas_local' => 0,
                    'tarjetas_rojas_visitante' => 0,
                    'esquinas_local' => 0,
                    'esquinas_visitante' => 0,
                    'offsides_local' => 0,
                    'offsides_visitante' => 0,
                ]);
                
                // Actualizar clasificaciones
                $this->actualizarClasificaciones($partido);
            }
            
            // Si cambió el marcador y estaba finalizado, recalcular clasificaciones
            if ($partido->estado == 'Finalizado' && $estadoAnterior == 'Finalizado' &&
                ($golesLocalAnterior != $partido->goles_local || $golesVisitanteAnterior != $partido->goles_visitante)) {
                $this->recalcularClasificaciones($partido);
            }
            
            DB::commit();
            
            return redirect()->route('partidos.show', $partido->id)
                ->with('success', 'Partido actualizado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en PartidoController@update: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el partido. Por favor, intente nuevamente.');
        }
    }

    /**
     * Remove the specified partido from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $partido = Partido::findOrFail($id);
            
            // Solo permitir eliminar partidos programados o suspendidos
            if ($partido->estado == 'Jugando' || $partido->estado == 'Finalizado') {
                return redirect()->route('partidos.show', $partido->id)
                    ->with('error', 'No se puede eliminar un partido en juego o finalizado.');
            }
            
            // Si tiene estadísticas, eliminarlas
            if ($partido->estadisticas) {
                $partido->estadisticas->delete();
            }
            
            // Eliminar el partido
            $partido->delete();
            
            DB::commit();
            
            return redirect()->route('partidos.index')
                ->with('success', 'Partido eliminado exitosamente.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en PartidoController@destroy: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al eliminar el partido. Por favor, intente nuevamente.');
        }
    }

    /**
     * Muestra el calendario de partidos.
     */
    public function calendario(Request $request)
    {
        try {
            $query = Partido::with(['equipoLocal', 'equipoVisitante', 'jornada'])
                ->where('estado', 'Programado')
                ->orderBy('fecha_hora', 'ASC');
            
            // Filtro por mes
            if ($request->filled('mes')) {
                $mes = $request->mes;
                $query->whereMonth('fecha_hora', $mes);
            }
            
            // Filtro por equipo
            if ($request->filled('equipo_id')) {
                $equipoId = $request->equipo_id;
                $query->where(function($q) use ($equipoId) {
                    $q->where('equipo_local_id', $equipoId)
                      ->orWhere('equipo_visitante_id', $equipoId);
                });
            }
            
            // Filtro por temporada
            if ($request->filled('temporada_id')) {
                $query->where('temporada_id', $request->temporada_id);
            } else {
                // Por defecto, temporada actual
                $temporadaActual = Temporada::where('estado', 'En Curso')->first();
                if ($temporadaActual) {
                    $query->where('temporada_id', $temporadaActual->id);
                }
            }
            
            // Agrupar por fecha
            $partidos = $query->get()->groupBy(function($partido) {
                return $partido->fecha_hora->format('Y-m-d');
            });
            
            // Datos para filtros
            $equipos = Equipo::where('activo', true)->orderBy('nombre')->get();
            $temporadas = Temporada::orderBy('anio', 'DESC')->get();
            
            // Meses del año
            $meses = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];
            
            return view('partidos.calendario', compact(
                'partidos',
                'equipos',
                'temporadas',
                'meses'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@calendario: ' . $e->getMessage());
            return redirect()->route('partidos.index')
                ->with('error', 'Error al cargar el calendario.');
        }
    }

    /**
     * Muestra los partidos en vivo.
     */
    public function enVivo()
    {
        try {
            $partidosEnVivo = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where('estado', 'Jugando')
                ->orderBy('fecha_hora', 'ASC')
                ->get();
            
            $proximosPartidos = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where('estado', 'Programado')
                ->where('fecha_hora', '>', now())
                ->orderBy('fecha_hora', 'ASC')
                ->limit(10)
                ->get();
            
            $ultimosResultados = Partido::with(['equipoLocal', 'equipoVisitante'])
                ->where('estado', 'Finalizado')
                ->orderBy('fecha_hora', 'DESC')
                ->limit(10)
                ->get();
            
            return view('partidos.en-vivo', compact(
                'partidosEnVivo',
                'proximosPartidos',
                'ultimosResultados'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@enVivo: ' . $e->getMessage());
            return redirect()->route('partidos.index')
                ->with('error', 'Error al cargar los partidos en vivo.');
        }
    }

    /**
     * Actualiza el marcador en tiempo real (AJAX).
     */
    public function actualizarMarcador(Request $request, $id)
    {
        try {
            $partido = Partido::findOrFail($id);
            
            // Solo permitir si el partido está en juego
            if ($partido->estado != 'Jugando') {
                return response()->json([
                    'success' => false,
                    'message' => 'El partido no está en juego.'
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'goles_local' => 'required|integer|min:0',
                'goles_visitante' => 'required|integer|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $partido->update($validator->validated());
            
            return response()->json([
                'success' => true,
                'message' => 'Marcador actualizado.',
                'partido' => [
                    'goles_local' => $partido->goles_local,
                    'goles_visitante' => $partido->goles_visitante
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@actualizarMarcador: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el marcador.'
            ], 500);
        }
    }

    /**
     * Cambia el estado del partido (AJAX).
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $partido = Partido::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'estado' => 'required|in:Programado,Jugando,Finalizado,Suspendido,Cancelado'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $estadoAnterior = $partido->estado;
            $nuevoEstado = $request->estado;
            
            // Validar transiciones de estado
            if ($estadoAnterior == 'Finalizado' && $nuevoEstado != 'Finalizado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cambiar el estado de un partido finalizado.'
                ], 400);
            }
            
            $partido->update(['estado' => $nuevoEstado]);
            
            // Si se finaliza, crear estadísticas básicas si no existen
            if ($nuevoEstado == 'Finalizado' && !$partido->estadisticas) {
                EstadisticaPartido::create([
                    'partido_id' => $partido->id,
                    'posesion_local' => 50,
                    'posesion_visitante' => 50,
                    'tiros_puerta_local' => 0,
                    'tiros_puerta_visitante' => 0,
                    'tiros_fuera_local' => 0,
                    'tiros_fuera_visitante' => 0,
                    'faltas_local' => 0,
                    'faltas_visitante' => 0,
                    'tarjetas_amarillas_local' => 0,
                    'tarjetas_amarillas_visitante' => 0,
                    'tarjetas_rojas_local' => 0,
                    'tarjetas_rojas_visitante' => 0,
                    'esquinas_local' => 0,
                    'esquinas_visitante' => 0,
                    'offsides_local' => 0,
                    'offsides_visitante' => 0,
                ]);
                
                // Actualizar clasificaciones
                $this->actualizarClasificaciones($partido);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'estado' => $partido->estado
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@cambiarEstado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado.'
            ], 500);
        }
    }

    /**
     * Muestra estadísticas avanzadas del partido.
     */
    public function estadisticas($id)
    {
        try {
            $partido = Partido::with(['estadisticas', 'equipoLocal', 'equipoVisitante'])
                ->findOrFail($id);
            
            if (!$partido->estadisticas) {
                return redirect()->route('partidos.show', $partido->id)
                    ->with('warning', 'Este partido no tiene estadísticas registradas.');
            }
            
            // Calcular estadísticas comparativas
            $comparativa = $this->calcularComparativaEstadisticas($partido);
            
            // Gráficos de datos
            $datosGraficos = $this->prepararDatosGraficos($partido);
            
            // Historial de partidos similares
            $partidosSimilares = $this->obtenerPartidosSimilares($partido);
            
            return view('partidos.estadisticas', compact(
                'partido',
                'comparativa',
                'datosGraficos',
                'partidosSimilares'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en PartidoController@estadisticas: ' . $e->getMessage());
            return redirect()->route('partidos.show', $id)
                ->with('error', 'Error al cargar las estadísticas.');
        }
    }

    // =========================================================================
    // MÉTODOS AUXILIARES PRIVADOS
    // =========================================================================
    
    /**
     * Actualiza las clasificaciones después de un partido.
     */
    private function actualizarClasificaciones(Partido $partido)
    {
        if ($partido->estado != 'Finalizado') {
            return;
        }
        
        $temporadaId = $partido->temporada_id;
        $equipoLocalId = $partido->equipo_local_id;
        $equipoVisitanteId = $partido->equipo_visitante_id;
        
        // Obtener o crear clasificaciones
        $clasificacionLocal = $this->obtenerClasificacion($temporadaId, $equipoLocalId);
        $clasificacionVisitante = $this->obtenerClasificacion($temporadaId, $equipoVisitanteId);
        
        // Actualizar estadísticas del equipo local
        $clasificacionLocal->partidos_jugados += 1;
        $clasificacionLocal->goles_a_favor += $partido->goles_local;
        $clasificacionLocal->goles_en_contra += $partido->goles_visitante;
        
        // Actualizar estadísticas del equipo visitante
        $clasificacionVisitante->partidos_jugados += 1;
        $clasificacionVisitante->goles_a_favor += $partido->goles_visitante;
        $clasificacionVisitante->goles_en_contra += $partido->goles_local;
        
        // Determinar resultado y actualizar puntos
        if ($partido->goles_local > $partido->goles_visitante) {
            $clasificacionLocal->partidos_ganados += 1;
            $clasificacionLocal->puntos += 3;
            $clasificacionVisitante->partidos_perdidos += 1;
        } elseif ($partido->goles_local < $partido->goles_visitante) {
            $clasificacionLocal->partidos_perdidos += 1;
            $clasificacionVisitante->partidos_ganados += 1;
            $clasificacionVisitante->puntos += 3;
        } else {
            $clasificacionLocal->partidos_empatados += 1;
            $clasificacionLocal->puntos += 1;
            $clasificacionVisitante->partidos_empatados += 1;
            $clasificacionVisitante->puntos += 1;
        }
        
        // Calcular diferencia de goles
        $clasificacionLocal->diferencia_goles = 
            $clasificacionLocal->goles_a_favor - $clasificacionLocal->goles_en_contra;
        $clasificacionVisitante->diferencia_goles = 
            $clasificacionVisitante->goles_a_favor - $clasificacionVisitante->goles_en_contra;
        
        // Guardar cambios
        $clasificacionLocal->save();
        $clasificacionVisitante->save();
    }
    
    /**
     * Recalcula clasificaciones cuando cambia el marcador de un partido finalizado.
     */
    private function recalcularClasificaciones(Partido $partido)
    {
        // Esta función debería recalcular todas las clasificaciones desde cero
        // o al menos revertir el partido anterior y aplicar el nuevo resultado
        // Por simplicidad, implementaremos la versión simple:
        
        // 1. Revertir el resultado anterior (necesitaríamos guardar el resultado anterior)
        // 2. Aplicar el nuevo resultado
        
        // Por ahora, simplemente llamamos a actualizarClasificaciones
        // Nota: En producción, necesitarías una implementación más robusta
        $this->actualizarClasificaciones($partido);
    }
    
    /**
     * Obtiene o crea una clasificación para equipo y temporada.
     */
    private function obtenerClasificacion($temporadaId, $equipoId)
    {
        $clasificacion = \App\Models\Clasificacion::where([
            'temporada_id' => $temporadaId,
            'equipo_id' => $equipoId
        ])->first();
        
        if (!$clasificacion) {
            $clasificacion = \App\Models\Clasificacion::create([
                'temporada_id' => $temporadaId,
                'equipo_id' => $equipoId,
                'partidos_jugados' => 0,
                'partidos_ganados' => 0,
                'partidos_empatados' => 0,
                'partidos_perdidos' => 0,
                'goles_a_favor' => 0,
                'goles_en_contra' => 0,
                'diferencia_goles' => 0,
                'puntos' => 0,
                'posicion' => 0,
            ]);
        }
        
        return $clasificacion;
    }
    
    /**
     * Calcula estadísticas resumidas del partido.
     */
    private function calcularEstadisticasResumen(Partido $partido): array
    {
        $resumen = [
            'resultado' => '',
            'dominio' => '',
            'efectividad' => '',
            'agresividad' => '',
        ];
        
        if ($partido->estado == 'Finalizado') {
            // Resultado
            if ($partido->goles_local > $partido->goles_visitante) {
                $resumen['resultado'] = 'Victoria Local';
            } elseif ($partido->goles_local < $partido->goles_visitante) {
                $resumen['resultado'] = 'Victoria Visitante';
            } else {
                $resumen['resultado'] = 'Empate';
            }
            
            // Si hay estadísticas
            if ($partido->estadisticas) {
                $est = $partido->estadisticas;
                
                // Dominio (posesión)
                if ($est->posesion_local > 60) {
                    $resumen['dominio'] = 'Dominio Local';
                } elseif ($est->posesion_visitante > 60) {
                    $resumen['dominio'] = 'Dominio Visitante';
                } else {
                    $resumen['dominio'] = 'Equilibrado';
                }
                
                // Efectividad (tiros a puerta / goles)
                $tirosTotalLocal = $est->tiros_puerta_local + $est->tiros_fuera_local;
                $tirosTotalVisitante = $est->tiros_puerta_visitante + $est->tiros_fuera_visitante;
                
                $efectividadLocal = $tirosTotalLocal > 0 
                    ? ($partido->goles_local / $tirosTotalLocal) * 100 
                    : 0;
                $efectividadVisitante = $tirosTotalVisitante > 0 
                    ? ($partido->goles_visitante / $tirosTotalVisitante) * 100 
                    : 0;
                
                if ($efectividadLocal > $efectividadVisitante) {
                    $resumen['efectividad'] = 'Más efectivo Local';
                } elseif ($efectividadVisitante > $efectividadLocal) {
                    $resumen['efectividad'] = 'Más efectivo Visitante';
                } else {
                    $resumen['efectividad'] = 'Similar efectividad';
                }
                
                // Agresividad (faltas y tarjetas)
                $agresividadLocal = $est->faltas_local + ($est->tarjetas_amarillas_local * 2) + ($est->tarjetas_rojas_local * 5);
                $agresividadVisitante = $est->faltas_visitante + ($est->tarjetas_amarillas_visitante * 2) + ($est->tarjetas_rojas_visitante * 5);
                
                if ($agresividadLocal > $agresividadVisitante) {
                    $resumen['agresividad'] = 'Más agresivo Local';
                } elseif ($agresividadVisitante > $agresividadLocal) {
                    $resumen['agresividad'] = 'Más agresivo Visitante';
                } else {
                    $resumen['agresividad'] = 'Similar agresividad';
                }
            }
        }
        
        return $resumen;
    }
    
    /**
     * Obtiene historial de enfrentamientos entre dos equipos.
     */
    private function obtenerHistorialEnfrentamientos($equipoLocalId, $equipoVisitanteId)
    {
        return Partido::with(['equipoLocal', 'equipoVisitante', 'jornada'])
            ->where(function($q) use ($equipoLocalId, $equipoVisitanteId) {
                $q->where(function($q2) use ($equipoLocalId, $equipoVisitanteId) {
                    $q2->where('equipo_local_id', $equipoLocalId)
                       ->where('equipo_visitante_id', $equipoVisitanteId);
                })->orWhere(function($q2) use ($equipoLocalId, $equipoVisitanteId) {
                    $q2->where('equipo_local_id', $equipoVisitanteId)
                       ->where('equipo_visitante_id', $equipoLocalId);
                });
            })
            ->where('estado', 'Finalizado')
            ->orderBy('fecha_hora', 'DESC')
            ->limit(10)
            ->get()
            ->map(function($partido) use ($equipoLocalId) {
                $partido->es_local = $partido->equipo_local_id == $equipoLocalId;
                return $partido;
            });
    }
    
    /**
     * Calcula comparativa de estadísticas.
     */
    private function calcularComparativaEstadisticas(Partido $partido): array
    {
        if (!$partido->estadisticas) {
            return [];
        }
        
        $est = $partido->estadisticas;
        
        return [
            'posesion' => [
                'local' => $est->posesion_local,
                'visitante' => $est->posesion_visitante,
                'diferencia' => abs($est->posesion_local - $est->posesion_visitante),
                'dominador' => $est->posesion_local > $est->posesion_visitante ? 'local' : 'visitante',
            ],
            'tiros' => [
                'total_local' => $est->tiros_puerta_local + $est->tiros_fuera_local,
                'total_visitante' => $est->tiros_puerta_visitante + $est->tiros_fuera_visitante,
                'a_puerta_local' => $est->tiros_puerta_local,
                'a_puerta_visitante' => $est->tiros_puerta_visitante,
                'efectividad_local' => $est->tiros_puerta_local > 0 ? 
                    round(($partido->goles_local / $est->tiros_puerta_local) * 100, 1) : 0,
                'efectividad_visitante' => $est->tiros_puerta_visitante > 0 ? 
                    round(($partido->goles_visitante / $est->tiros_puerta_visitante) * 100, 1) : 0,
            ],
            'faltas_tarjetas' => [
                'faltas_local' => $est->faltas_local,
                'faltas_visitante' => $est->faltas_visitante,
                'amarillas_local' => $est->tarjetas_amarillas_local,
                'amarillas_visitante' => $est->tarjetas_amarillas_visitante,
                'rojas_local' => $est->tarjetas_rojas_local,
                'rojas_visitante' => $est->tarjetas_rojas_visitante,
                'agresividad_local' => $est->faltas_local + ($est->tarjetas_amarillas_local * 2) + ($est->tarjetas_rojas_local * 5),
                'agresividad_visitante' => $est->faltas_visitante + ($est->tarjetas_amarillas_visitante * 2) + ($est->tarjetas_rojas_visitante * 5),
            ],
            'otras' => [
                'esquinas_local' => $est->esquinas_local ?? 0,
                'esquinas_visitante' => $est->esquinas_visitante ?? 0,
                'offsides_local' => $est->offsides_local ?? 0,
                'offsides_visitante' => $est->offsides_visitante ?? 0,
            ]
        ];
    }
    
    /**
     * Prepara datos para gráficos.
     */
    private function prepararDatosGraficos(Partido $partido): array
    {
        if (!$partido->estadisticas) {
            return [];
        }
        
        $est = $partido->estadisticas;
        
        return [
            'posesion' => [
                'labels' => ['Local', 'Visitante'],
                'data' => [$est->posesion_local, $est->posesion_visitante],
                'colors' => ['#3490dc', '#e3342f']
            ],
            'tiros' => [
                'labels' => ['Local - Puerta', 'Local - Fuera', 'Visitante - Puerta', 'Visitante - Fuera'],
                'data' => [
                    $est->tiros_puerta_local,
                    $est->tiros_fuera_local,
                    $est->tiros_puerta_visitante,
                    $est->tiros_fuera_visitante
                ],
                'colors' => ['#3490dc', '#6cb2eb', '#e3342f', '#ef5753']
            ],
            'faltas' => [
                'labels' => ['Faltas Local', 'Faltas Visitante'],
                'data' => [$est->faltas_local, $est->faltas_visitante],
                'colors' => ['#3490dc', '#e3342f']
            ],
            'tarjetas' => [
                'labels' => ['Amarillas Local', 'Rojas Local', 'Amarillas Visitante', 'Rojas Visitante'],
                'data' => [
                    $est->tarjetas_amarillas_local,
                    $est->tarjetas_rojas_local,
                    $est->tarjetas_amarillas_visitante,
                    $est->tarjetas_rojas_visitante
                ],
                'colors' => ['#f2d024', '#e02424', '#f2d024', '#e02424']
            ]
        ];
    }
    
    /**
     * Obtiene partidos similares.
     */
    private function obtenerPartidosSimilares(Partido $partido)
    {
        // Buscar partidos con marcadores similares
        return Partido::with(['equipoLocal', 'equipoVisitante'])
            ->where('id', '!=', $partido->id)
            ->where('estado', 'Finalizado')
            ->where(function($q) use ($partido) {
                $q->where('goles_local', $partido->goles_local)
                  ->orWhere('goles_visitante', $partido->goles_visitante);
            })
            ->orderBy('fecha_hora', 'DESC')
            ->limit(5)
            ->get();
    }
}