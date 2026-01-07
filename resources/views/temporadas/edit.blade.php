@extends('layouts.app')

@section('title', 'Editar Temporada: ' . $temporada->nombre)
@section('icon', 'bi-pencil')
@section('subtitle', 'Modificar configuración de la temporada')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('temporadas.index') }}">Temporadas</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('temporadas.show', $temporada) }}">{{ $temporada->nombre }}</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Editar</li>
@endsection

@section('header-buttons')
    <a href="{{ route('temporadas.show', $temporada) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-pencil-square text-primary me-2"></i>
                    Editar Temporada
                </h5>
            </div>
            <div class="card-body">
                <form id="editTemporadaForm" action="{{ route('temporadas.update', $temporada) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Nombre de la Temporada -->
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label required">
                                <i class="bi bi-tag me-1"></i> Nombre de la Temporada
                            </label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $temporada->nombre) }}" 
                                   required
                                   data-parsley-minlength="3"
                                   data-parsley-maxlength="100">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Año -->
                        <div class="col-md-6 mb-3">
                            <label for="anio" class="form-label required">
                                <i class="bi bi-calendar3 me-1"></i> Año
                            </label>
                            <input type="number" 
                                   class="form-control @error('anio') is-invalid @enderror" 
                                   id="anio" 
                                   name="anio" 
                                   value="{{ old('anio', $temporada->anio) }}" 
                                   min="1900" 
                                   max="{{ date('Y') + 5 }}"
                                   required
                                   data-parsley-type="integer">
                            @error('anio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Fecha de Inicio -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label required">
                                <i class="bi bi-calendar-plus me-1"></i> Fecha de Inicio
                            </label>
                            <input type="date" 
                                   class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" 
                                   name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', $temporada->fecha_inicio->format('Y-m-d')) }}" 
                                   required>
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Fecha de Fin -->
                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label required">
                                <i class="bi bi-calendar-minus me-1"></i> Fecha de Fin
                            </label>
                            <input type="date" 
                                   class="form-control @error('fecha_fin') is-invalid @enderror" 
                                   id="fecha_fin" 
                                   name="fecha_fin" 
                                   value="{{ old('fecha_fin', $temporada->fecha_fin->format('Y-m-d')) }}" 
                                   required
                                   data-parsley-greaterthan="#fecha_inicio">
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label required">
                                <i class="bi bi-activity me-1"></i> Estado
                            </label>
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="">Seleccionar estado</option>
                                @foreach(['Programada', 'En Curso', 'Finalizada', 'Cancelada', 'Archivada'] as $estadoOption)
                                    <option value="{{ $estadoOption }}" 
                                            {{ old('estado', $temporada->estado) == $estadoOption ? 'selected' : '' }}>
                                        {{ $estadoOption }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Máximo de Equipos -->
                        <div class="col-md-6 mb-3">
                            <label for="max_equipos" class="form-label">
                                <i class="bi bi-people me-1"></i> Máximo de Equipos
                            </label>
                            <input type="number" 
                                   class="form-control @error('max_equipos') is-invalid @enderror" 
                                   id="max_equipos" 
                                   name="max_equipos" 
                                   value="{{ old('max_equipos', $temporada->max_equipos) }}" 
                                   min="2" 
                                   max="50">
                            @error('max_equipos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            <i class="bi bi-chat-left-text me-1"></i> Descripción
                        </label>
                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                  id="descripcion" 
                                  name="descripcion" 
                                  rows="3">{{ old('descripcion', $temporada->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            <span id="descripcionCounter">{{ strlen(old('descripcion', $temporada->descripcion)) }}</span>/500 caracteres
                        </small>
                    </div>
                    
                    <div class="row">
                        <!-- Reglamento URL -->
                        <div class="col-md-6 mb-3">
                            <label for="reglamento_url" class="form-label">
                                <i class="bi bi-file-earmark-text me-1"></i> URL del Reglamento
                            </label>
                            <input type="url" 
                                   class="form-control @error('reglamento_url') is-invalid @enderror" 
                                   id="reglamento_url" 
                                   name="reglamento_url" 
                                   value="{{ old('reglamento_url', $temporada->reglamento_url) }}">
                            @error('reglamento_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Premio Ganador -->
                        <div class="col-md-6 mb-3">
                            <label for="premio_ganador" class="form-label">
                                <i class="bi bi-trophy me-1"></i> Premio al Ganador
                            </label>
                            <input type="text" 
                                   class="form-control @error('premio_ganador') is-invalid @enderror" 
                                   id="premio_ganador" 
                                   name="premio_ganador" 
                                   value="{{ old('premio_ganador', $temporada->premio_ganador) }}">
                            @error('premio_ganador')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Configuración Avanzada -->
                    <div class="accordion mb-4" id="configAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" 
                                        data-bs-toggle="collapse" data-bs-target="#configAvanzada">
                                    <i class="bi bi-gear me-2"></i>
                                    Configuración Avanzada
                                </button>
                            </h2>
                            <div id="configAvanzada" class="accordion-collapse collapse" 
                                 data-bs-parent="#configAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="min_equipos" class="form-label">
                                                Mínimo de Equipos
                                            </label>
                                            <input type="number" 
                                                   class="form-control @error('min_equipos') is-invalid @enderror" 
                                                   id="min_equipos" 
                                                   name="min_equipos" 
                                                   value="{{ old('min_equipos', $temporada->min_equipos) }}" 
                                                   min="2" 
                                                   max="50">
                                            @error('min_equipos')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_temporada" class="form-label">
                                                Tipo de Temporada
                                            </label>
                                            <select class="form-select @error('tipo_temporada') is-invalid @enderror" 
                                                    id="tipo_temporada" 
                                                    name="tipo_temporada">
                                                <option value="">Seleccionar tipo</option>
                                                @foreach(['Liga', 'Playoffs', 'Mixto'] as $tipo)
                                                    <option value="{{ $tipo }}" 
                                                            {{ old('tipo_temporada', $temporada->tipo_temporada) == $tipo ? 'selected' : '' }}>
                                                        {{ $tipo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('tipo_temporada')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones del Formulario -->
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="bi bi-eraser me-1"></i> Restablecer
                            </button>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('temporadas.show', $temporada) }}" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel de Información -->
    <div class="col-lg-4">
        <!-- Resumen Actual -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i> Resumen Actual
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>{{ $temporada->nombre }}</h5>
                    <p class="text-muted mb-0">{{ $temporada->anio }}</p>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-{{ $temporada->estado_color }} rounded-pill">
                        {{ $temporada->estado }}
                    </span>
                </div>
                
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="bi bi-calendar-range me-2 text-muted"></i>
                        <small>Periodo:</small>
                        <div class="fw-bold">
                            {{ $temporada->fecha_inicio->format('d/m/Y') }} - {{ $temporada->fecha_fin->format('d/m/Y') }}
                        </div>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-people me-2 text-muted"></i>
                        <small>Equipos:</small>
                        <div class="fw-bold">{{ $temporada->equipos_count }} participantes</div>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-calendar-event me-2 text-muted"></i>
                        <small>Partidos:</small>
                        <div class="fw-bold">{{ $temporada->partidos_count }} programados</div>
                    </li>
                    <li>
                        <i class="bi bi-clock-history me-2 text-muted"></i>
                        <small>Progreso:</small>
                        <div class="fw-bold">{{ $temporada->progreso }}% completado</div>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Advertencias -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                    Consideraciones Importantes
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="bi bi-lightbulb"></i> Cambios Restringidos
                    </h6>
                    <ul class="mb-0 ps-3">
                        <li>No se puede cambiar el año si hay partidos programados</li>
                        <li>La fecha de fin no puede ser anterior a partidos ya jugados</li>
                        <li>Cambiar el estado puede afectar operaciones en curso</li>
                        <li>Reducir el máximo de equipos puede afectar participantes</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle"></i> Impacto de Cambios
                    </h6>
                    <p class="mb-0">
                        Los cambios en fechas y estado pueden afectar:
                    </p>
                    <ul class="mb-0 ps-3">
                        <li>Programación de partidos</li>
                        <li>Clasificación y estadísticas</li>
                        <li>Notificaciones a equipos</li>
                        <li>Reportes y análisis</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Parsley
        $('#editTemporadaForm').parsley({
            excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], :hidden',
            trigger: 'change',
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<div class="invalid-feedback"></div>',
            errorTemplate: '<span></span>'
        });
        
        // Contador de caracteres para descripción
        $('#descripcion').on('input', function() {
            const length = $(this).val().length;
            $('#descripcionCounter').text(length);
            
            if (length > 500) {
                $('#descripcionCounter').addClass('text-danger');
            } else {
                $('#descripcionCounter').removeClass('text-danger');
            }
        });
        
        // Validación de fechas
        $('#fecha_fin').parsley().addValidator('greaterthan', {
            requirementType: 'string',
            validateString: function(value, requirement) {
                const fechaInicio = $(requirement).val();
                if (!fechaInicio || !value) return true;
                
                const inicio = new Date(fechaInicio);
                const fin = new Date(value);
                return fin > inicio;
            },
            messages: {
                es: 'La fecha de fin debe ser posterior a la fecha de inicio'
            }
        });
        
        // Validación especial para temporadas con partidos
        @if($temporada->partidos_count > 0)
            $('#anio').on('change', function() {
                const nuevoAnio = $(this).val();
                if (nuevoAnio && nuevoAnio != '{{ $temporada->anio }}') {
                    if (!confirm('¿Cambiar el año de la temporada? Esto puede afectar los partidos programados.')) {
                        $(this).val('{{ $temporada->anio }}');
                    }
                }
            });
            
            $('#fecha_fin').on('change', function() {
                const nuevaFechaFin = new Date($(this).val());
                @if($temporada->ultimo_partido)
                    const ultimoPartido = new Date('{{ $temporada->ultimo_partido->fecha_hora }}');
                    if (nuevaFechaFin < ultimoPartido) {
                        alert('La fecha de fin no puede ser anterior al último partido programado.');
                        $(this).val('{{ $temporada->fecha_fin->format("Y-m-d") }}');
                    }
                @endif
            });
        @endif
        
        // Form submission
        $('#editTemporadaForm').on('submit', function(e) {
            if (!$(this).parsley().isValid()) {
                e.preventDefault();
                toastr.error('Por favor, corrija los errores en el formulario');
                return;
            }
            
            // Validación adicional de estado
            const nuevoEstado = $('#estado').val();
            const estadoActual = '{{ $temporada->estado }}';
            
            if (nuevoEstado !== estadoActual) {
                if (!confirm(`¿Cambiar estado de "${estadoActual}" a "${nuevoEstado}"?`)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    });
</script>
@endpush
@endsection