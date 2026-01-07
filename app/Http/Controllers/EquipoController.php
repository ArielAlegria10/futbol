<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Obtener equipos paginados para la vista
            $equipos = Equipo::query()
                ->orderBy('nombre', 'asc')
                ->paginate(25);
            
            // Obtener estadísticas para las tarjetas
            $equiposActivos = Equipo::where('activo', true)->count();
            $equiposInactivos = Equipo::where('activo', false)->count();
            $equiposConEstadio = Equipo::whereNotNull('estadio')->count();
            
            // Retornar vista Blade con datos
            return view('equipos.index', [
                'equipos' => $equipos,
                'equiposActivos' => $equiposActivos,
                'equiposInactivos' => $equiposInactivos,
                'equiposConEstadio' => $equiposConEstadio
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener equipos: ' . $e->getMessage());
            
            // Redirigir con error para vista
            return redirect()->route('dashboard')
                ->with('error', 'Error al cargar la lista de equipos. Por favor, intente nuevamente.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('equipos.create');
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
                'max:150',
                Rule::unique('equipos')
            ],
            'abreviacion' => [
                'nullable',
                'string',
                'max:10',
                'alpha_dash',
                Rule::unique('equipos')
            ],
            'ciudad' => 'required|string|max:100',
            'estadio' => 'nullable|string|max:150',
            'fundacion' => [
                'nullable',
                'integer',
                'min:1800',
                'max:' . (date('Y') + 1)
            ],
            'escudo_url' => 'nullable|url|max:255',
            'colores' => 'nullable|array',
            'colores.*' => 'string|max:7|starts_with:#',
            'presidente' => 'nullable|string|max:200',
            'entrenador' => 'nullable|string|max:200',
            'capacidad_estadio' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean'
        ], [
            'nombre.unique' => 'Ya existe un equipo con ese nombre',
            'abreviacion.unique' => 'La abreviatura ya está en uso',
            'colores.*.starts_with' => 'Cada color debe comenzar con # (ej: #FF0000)',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        
        try {
            $validated = $validator->validated();
            
            // Procesamiento de datos antes de guardar
            if (isset($validated['abreviacion'])) {
                $validated['abreviacion'] = strtoupper(trim($validated['abreviacion']));
            }
            
            if (isset($validated['colores'])) {
                $validated['colores'] = json_encode($validated['colores']);
            }
            
            // Asegurar que activo tenga un valor por defecto
            if (!isset($validated['activo'])) {
                $validated['activo'] = true;
            }
            
            $equipo = Equipo::create($validated);
            
            DB::commit();
            
            \Log::info('Equipo creado exitosamente', [
                'equipo_id' => $equipo->id,
                'nombre' => $equipo->nombre
            ]);
            
            return redirect()->route('equipos.index')
                ->with('success', 'Equipo registrado exitosamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al crear equipo: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al registrar el equipo. Por favor, intente nuevamente.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $equipo = Equipo::findOrFail($id);
            
            return view('equipos.show', compact('equipo'));
            
        } catch (\Exception $e) {
            \Log::warning('Equipo no encontrado: ' . $e->getMessage());
            
            return redirect()->route('equipos.index')
                ->with('error', 'Equipo no encontrado');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $equipo = Equipo::findOrFail($id);
            
            // Decodificar colores si existen
            if ($equipo->colores) {
                $equipo->colores = json_decode($equipo->colores, true);
            }
            
            return view('equipos.edit', compact('equipo'));
            
        } catch (\Exception $e) {
            \Log::warning('Equipo no encontrado para editar: ' . $e->getMessage());
            
            return redirect()->route('equipos.index')
                ->with('error', 'Equipo no encontrado');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $equipo = Equipo::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nombre' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('equipos')->ignore($equipo->id)
                ],
                'abreviacion' => [
                    'nullable',
                    'string',
                    'max:10',
                    'alpha_dash',
                    Rule::unique('equipos')->ignore($equipo->id)
                ],
                'ciudad' => 'sometimes|string|max:100',
                'estadio' => 'nullable|string|max:150',
                'fundacion' => [
                    'nullable',
                    'integer',
                    'min:1800',
                    'max:' . (date('Y') + 1)
                ],
                'escudo_url' => 'nullable|url|max:255',
                'colores' => 'nullable|array',
                'colores.*' => 'string|max:7|starts_with:#',
                'presidente' => 'nullable|string|max:200',
                'entrenador' => 'nullable|string|max:200',
                'capacidad_estadio' => 'nullable|integer|min:0',
                'activo' => 'nullable|boolean'
            ], [
                'colores.*.starts_with' => 'Cada color debe comenzar con # (ej: #FF0000)',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            DB::beginTransaction();
            
            try {
                $validated = $validator->validated();
                
                // Procesamiento de datos antes de actualizar
                if (isset($validated['abreviacion'])) {
                    $validated['abreviacion'] = strtoupper(trim($validated['abreviacion']));
                }
                
                if (isset($validated['colores'])) {
                    $validated['colores'] = json_encode($validated['colores']);
                }
                
                $equipo->update($validated);
                
                DB::commit();
                
                \Log::info('Equipo actualizado exitosamente', [
                    'equipo_id' => $equipo->id,
                    'changes' => $equipo->getChanges()
                ]);
                
                return redirect()->route('equipos.index')
                    ->with('success', 'Equipo actualizado exitosamente');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al actualizar equipo: ' . $e->getMessage());
            
            $message = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException 
                ? 'Equipo no encontrado' 
                : 'Error al actualizar el equipo';
            
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
            $equipo = Equipo::findOrFail($id);
            
            // Verificar si hay relaciones antes de eliminar
            $tieneRelaciones = false;
            $mensajeRelaciones = '';
            
            // Verificar relaciones comunes (ajusta según tu modelo)
            if (method_exists($equipo, 'partidosLocal') && $equipo->partidosLocal()->exists()) {
                $tieneRelaciones = true;
                $mensajeRelaciones .= 'Tiene partidos como local. ';
            }
            
            if (method_exists($equipo, 'partidosVisitante') && $equipo->partidosVisitante()->exists()) {
                $tieneRelaciones = true;
                $mensajeRelaciones .= 'Tiene partidos como visitante. ';
            }
            
            if (method_exists($equipo, 'clasificaciones') && $equipo->clasificaciones()->exists()) {
                $tieneRelaciones = true;
                $mensajeRelaciones .= 'Tiene registros en clasificaciones. ';
            }
            
            if (method_exists($equipo, 'historialPosiciones') && $equipo->historialPosiciones()->exists()) {
                $tieneRelaciones = true;
                $mensajeRelaciones .= 'Tiene historial de posiciones. ';
            }
            
            if ($tieneRelaciones) {
                return redirect()->route('equipos.index')
                    ->with('warning', 'No se puede eliminar el equipo porque tiene relaciones con otros registros. ' . $mensajeRelaciones . 'Considere desactivarlo en lugar de eliminarlo.');
            }
            
            DB::beginTransaction();
            
            try {
                $equipo->delete();
                
                DB::commit();
                
                \Log::info('Equipo eliminado exitosamente', [
                    'equipo_id' => $id,
                    'nombre' => $equipo->nombre
                ]);
                
                return redirect()->route('equipos.index')
                    ->with('success', 'Equipo eliminado exitosamente');
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al eliminar equipo: ' . $e->getMessage());
            
            $message = $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException 
                ? 'Equipo no encontrado' 
                : 'Error al eliminar el equipo';
            
            return redirect()->route('equipos.index')
                ->with('error', $message);
        }
    }

    /**
     * Toggle status - para AJAX en la vista
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $equipo = Equipo::findOrFail($id);
            
            $equipo->update([
                'activo' => !$equipo->activo
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente',
                'nuevoEstado' => $equipo->activo ? 'Activo' : 'Inactivo',
                'claseBadge' => $equipo->activo ? 'bg-success' : 'bg-danger',
                'activo' => $equipo->activo
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al cambiar estado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado'
            ], 500);
        }
    }

    /**
     * Bulk actions - para operaciones masivas desde la vista
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:equipos,id',
            'action' => 'required|in:activate,deactivate,delete'
        ]);
        
        DB::beginTransaction();
        
        try {
            $ids = $request->ids;
            $action = $request->action;
            
            switch ($action) {
                case 'activate':
                    Equipo::whereIn('id', $ids)->update(['activo' => true]);
                    $message = 'Equipos activados exitosamente';
                    $logMessage = 'Equipos activados masivamente';
                    break;
                    
                case 'deactivate':
                    Equipo::whereIn('id', $ids)->update(['activo' => false]);
                    $message = 'Equipos desactivados exitosamente';
                    $logMessage = 'Equipos desactivados masivamente';
                    break;
                    
                case 'delete':
                    // Verificar que no tengan dependencias antes de eliminar
                    $equiposConDependencias = collect();
                    $equipos = Equipo::whereIn('id', $ids)->get();
                    
                    foreach ($equipos as $equipo) {
                        $tieneDependencias = false;
                        
                        if (method_exists($equipo, 'partidosLocal') && $equipo->partidosLocal()->exists()) {
                            $tieneDependencias = true;
                        }
                        
                        if (method_exists($equipo, 'partidosVisitante') && $equipo->partidosVisitante()->exists()) {
                            $tieneDependencias = true;
                        }
                        
                        if (method_exists($equipo, 'clasificaciones') && $equipo->clasificaciones()->exists()) {
                            $tieneDependencias = true;
                        }
                        
                        if (method_exists($equipo, 'historialPosiciones') && $equipo->historialPosiciones()->exists()) {
                            $tieneDependencias = true;
                        }
                        
                        if ($tieneDependencias) {
                            $equiposConDependencias->push($equipo->nombre);
                        }
                    }
                    
                    if ($equiposConDependencias->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Los siguientes equipos tienen dependencias y no pueden ser eliminados: ' . 
                                         $equiposConDependencias->implode(', ')
                        ], 409);
                    }
                    
                    Equipo::whereIn('id', $ids)->delete();
                    $message = 'Equipos eliminados exitosamente';
                    $logMessage = 'Equipos eliminados masivamente';
                    break;
                    
                default:
                    throw new \Exception('Acción no válida');
            }
            
            DB::commit();
            
            \Log::info($logMessage, ['ids' => $ids, 'count' => count($ids)]);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'count' => count($ids)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error en acción masiva: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error en la operación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para obtener datos de equipo en formato JSON (opcional)
     */
    public function getEquipoData($id)
    {
        try {
            $equipo = Equipo::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $this->transformEquipo($equipo)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Equipo no encontrado'
            ], 404);
        }
    }

    /**
     * Transformar equipo para respuesta JSON (solo para métodos AJAX)
     */
    private function transformEquipo(Equipo $equipo): array
    {
        return [
            'id' => $equipo->id,
            'nombre' => $equipo->nombre,
            'abreviacion' => $equipo->abreviacion,
            'ciudad' => $equipo->ciudad,
            'estadio' => $equipo->estadio,
            'fundacion' => $equipo->fundacion,
            'escudo_url' => $equipo->escudo_url,
            'colores' => $equipo->colores ? json_decode($equipo->colores, true) : null,
            'presidente' => $equipo->presidente,
            'entrenador' => $equipo->entrenador,
            'capacidad_estadio' => $equipo->capacidad_estadio,
            'activo' => $equipo->activo,
            'created_at' => $equipo->created_at?->toISOString(),
            'updated_at' => $equipo->updated_at?->toISOString(),
        ];
    }

    /**
     * Buscar equipos por término (para autocompletado)
     */
    public function search(Request $request)
    {
        $term = $request->input('q', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }
        
        $equipos = Equipo::where('nombre', 'LIKE', "%{$term}%")
            ->orWhere('abreviacion', 'LIKE', "%{$term}%")
            ->orWhere('ciudad', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get(['id', 'nombre', 'abreviacion', 'ciudad']);
        
        return response()->json($equipos);
    }
}