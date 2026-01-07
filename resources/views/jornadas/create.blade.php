@extends('layouts.app')

@section('title', 'Crear Nueva Jornada')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle"></i> Crear Nueva Jornada
                        @if($temporada)
                            <span class="badge bg-light text-dark ms-2">{{ $temporada->nombre }}</span>
                        @endif
                    </h5>
                </div>
                
                <div class="card-body">
                    @if(!$temporada)
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            Primero debes seleccionar una temporada.
                            <a href="{{ route('temporadas.index') }}" class="btn btn-sm btn-warning ms-2">
                                Ir a Temporadas
                            </a>
                        </div>
                    @else
                        <!-- Información de temporada -->
                        <div class="alert alert-info mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Temporada:</strong> {{ $temporada->nombre }}<br>
                                    <strong>Año:</strong> {{ $temporada->anio }}
                                </div>
                                <div class="col-md-6 text-end">
                                    <strong>Estado:</strong> 
                                    <span class="badge bg-{{ $temporada->estado == 'En Curso' ? 'success' : 'secondary' }}">
                                        {{ $temporada->estado }}
                                    </span><br>
                                    <strong>Jornadas existentes:</strong> {{ $temporada->jornadas()->count() }}
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('jornadas.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="temporada_id" value="{{ $temporada->id }}">
                            
                            <div class="row">
                                <!-- Número de jornada -->
                                <div class="col-md-6 mb-3">
                                    <label for="numero" class="form-label">
                                        <i class="fas fa-hashtag"></i> Número de Jornada *
                                    </label>
                                    <input type="number" class="form-control @error('numero') is-invalid @enderror" 
                                           id="numero" name="numero" 
                                           value="{{ old('numero', $siguienteNumero) }}" 
                                           min="1" required>
                                    <div class="form-text">
                                        Siguiente número disponible: {{ $siguienteNumero }}
                                    </div>
                                    @error('numero')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nombre -->
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-heading"></i> Nombre
                                    </label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                                           id="nombre" name="nombre" 
                                           value="{{ old('nombre', $fechasSugeridas['nombre_sugerido'] ?? '') }}"
                                           placeholder="Ej: Jornada {{ $siguienteNumero }}">
                                    <div class="form-text">
                                        Déjalo vacío para generar automáticamente.
                                    </div>
                                    @error('nombre')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha Inicio -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">
                                        <i class="fas fa-calendar-plus"></i> Fecha de Inicio *
                                    </label>
                                    <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                           id="fecha_inicio" name="fecha_inicio" 
                                           value="{{ old('fecha_inicio', $fechasSugeridas['fecha_inicio'] ?? '') }}" 
                                           required>
                                    <div class="form-text">
                                        Fecha sugerida: {{ $fechasSugeridas['fecha_inicio'] ?? 'No hay sugerencia' }}
                                    </div>
                                    @error('fecha_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fecha Fin -->
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">
                                        <i class="fas fa-calendar-minus"></i> Fecha de Fin *
                                    </label>
                                    <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                           id="fecha_fin" name="fecha_fin" 
                                           value="{{ old('fecha_fin', $fechasSugeridas['fecha_fin'] ?? '') }}" 
                                           required>
                                    <div class="form-text">
                                        Normalmente 2-3 días después del inicio.
                                    </div>
                                    @error('fecha_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Descripción -->
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">
                                        <i class="fas fa-align-left"></i> Descripción
                                    </label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                              id="descripcion" name="descripcion" 
                                              rows="3" 
                                              placeholder="Descripción opcional de la jornada...">{{ old('descripcion') }}</textarea>
                                    <div class="form-text">
                                        Máximo 500 caracteres.
                                    </div>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Opciones especiales -->
                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" 
                                               id="especial" name="especial" 
                                               value="1" {{ old('especial') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="especial">
                                            <i class="fas fa-star"></i> Jornada Especial
                                        </label>
                                        <div class="form-text">
                                            Marca esta jornada como especial (derby, clásico, etc.)
                                        </div>
                                    </div>
                                </div>

                                <!-- Notas -->
                                <div class="col-md-12 mb-4">
                                    <label for="notas" class="form-label">
                                        <i class="fas fa-sticky-note"></i> Notas Internas
                                    </label>
                                    <textarea class="form-control @error('notas') is-invalid @enderror" 
                                              id="notas" name="notas" 
                                              rows="3" 
                                              placeholder="Notas internas para administradores...">{{ old('notas') }}</textarea>
                                    <div class="form-text">
                                        Máximo 1000 caracteres. Solo visible para administradores.
                                    </div>
                                    @error('notas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Botones -->
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('jornadas.index', ['temporada_id' => $temporada->id]) }}" 
                                           class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Cancelar
                                        </a>
                                        
                                        <div class="btn-group">
                                            <button type="submit" name="action" value="save" class="btn btn-primary">
                                                <i class="fas fa-save"></i> Guardar Jornada
                                            </button>
                                            
                                            <button type="submit" name="action" value="save_and_add" class="btn btn-success">
                                                <i class="fas fa-plus"></i> Guardar y Agregar Partidos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Validación de solapamiento -->
                        <div class="alert alert-warning mt-4">
                            <h6><i class="fas fa-exclamation-triangle"></i> Validaciones</h6>
                            <ul class="mb-0">
                                <li>El número de jornada debe ser único en esta temporada</li>
                                <li>Las fechas no deben solaparse con otras jornadas</li>
                                <li>La fecha de fin no puede ser anterior a la fecha de inicio</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calcular fecha fin (2 días después)
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        
        if (fechaInicio && fechaFin) {
            fechaInicio.addEventListener('change', function() {
                if (!fechaFin.value || fechaFin.value < this.value) {
                    const fecha = new Date(this.value);
                    fecha.setDate(fecha.getDate() + 2);
                    fechaFin.value = fecha.toISOString().split('T')[0];
                }
            });
        }
        
        // Auto-generar nombre si está vacío
        const nombreInput = document.getElementById('nombre');
        const numeroInput = document.getElementById('numero');
        
        if (nombreInput && numeroInput) {
            numeroInput.addEventListener('change', function() {
                if (!nombreInput.value || nombreInput.value.includes('Jornada')) {
                    nombreInput.value = 'Jornada ' + this.value;
                }
            });
        }
    });
</script>
@endsection