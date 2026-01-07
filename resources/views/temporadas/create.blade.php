@extends('layouts.app')

@section('title', 'Crear Nueva Temporada')
@section('icon', 'bi-plus-circle')
@section('subtitle', 'Configura una nueva temporada del torneo')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('temporadas.index') }}">Temporadas</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Nueva Temporada</li>
@endsection

@section('header-buttons')
    <a href="{{ route('temporadas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Volver
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-plus text-primary me-2"></i>
                    Información de la Temporada
                </h5>
            </div>
            <div class="card-body">
                <form id="temporadaForm" action="{{ route('temporadas.store') }}" method="POST" novalidate>
                    @csrf
                    
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
                                   value="{{ old('nombre') }}" 
                                   placeholder="Ej: Temporada 2023-2024"
                                   required
                                   data-parsley-minlength="3"
                                   data-parsley-maxlength="100">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Nombre descriptivo de la temporada (3-100 caracteres)
                            </small>
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
                                   value="{{ old('anio', date('Y')) }}" 
                                   min="1900" 
                                   max="{{ date('Y') + 5 }}"
                                   required
                                   data-parsley-type="integer">
                            @error('anio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Año de la temporada (1900-{{ date('Y') + 5 }})</small>
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
                                   value="{{ old('fecha_inicio') }}" 
                                   required
                                   data-parsley-trigger="change">
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Fecha de inicio de la temporada</small>
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
                                   value="{{ old('fecha_fin') }}" 
                                   required
                                   data-parsley-trigger="change"
                                   data-parsley-greaterthan="#fecha_inicio">
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Fecha de finalización de la temporada</small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label required">
                                <i class="bi bi-activity me-1"></i> Estado Inicial
                            </label>
                            <select class="form-select @error('estado') is-invalid @enderror" 
                                    id="estado" 
                                    name="estado" 
                                    required>
                                <option value="">Seleccionar estado</option>
                                <option value="Programada" {{ old('estado') == 'Programada' ? 'selected' : '' }}>
                                    Programada
                                </option>
                                <option value="En Curso" {{ old('estado') == 'En Curso' ? 'selected' : '' }}>
                                    En Curso
                                </option>
                                <option value="Finalizada" {{ old('estado') == 'Finalizada' ? 'selected' : '' }}>
                                    Finalizada
                                </option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Estado inicial de la temporada</small>
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
                                   value="{{ old('max_equipos', 20) }}" 
                                   min="2" 
                                   max="50">
                            @error('max_equipos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Número máximo de equipos permitidos (2-50)</small>
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
                                  rows="3" 
                                  placeholder="Descripción detallada de la temporada..."
                                  data-parsley-maxlength="500">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Descripción opcional (máximo 500 caracteres). 
                            <span id="descripcionCounter">0/500</span>
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
                                   value="{{ old('reglamento_url') }}" 
                                   placeholder="https://ejemplo.com/reglamento.pdf"
                                   data-parsley-type="url">
                            @error('reglamento_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">URL del reglamento (HTTPS recomendado)</small>
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
                                   value="{{ old('premio_ganador') }}" 
                                   placeholder="Ej: Trofeo + $10,000"
                                   data-parsley-maxlength="200">
                            @error('premio_ganador')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Premio o reconocimiento para el campeón</small>
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
                                                   value="{{ old('min_equipos', 2) }}" 
                                                   min="2" 
                                                   max="50">
                                            @error('min_equipos')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Mínimo de equipos para iniciar</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="tipo_temporada" class="form-label">
                                                Tipo de Temporada
                                            </label>
                                            <select class="form-select @error('tipo_temporada') is-invalid @enderror" 
                                                    id="tipo_temporada" 
                                                    name="tipo_temporada">
                                                <option value="">Seleccionar tipo</option>
                                                <option value="Liga" {{ old('tipo_temporada') == 'Liga' ? 'selected' : '' }}>
                                                    Liga (Todos contra todos)
                                                </option>
                                                <option value="Playoffs" {{ old('tipo_temporada') == 'Playoffs' ? 'selected' : '' }}>
                                                    Playoffs (Eliminación directa)
                                                </option>
                                                <option value="Mixto" {{ old('tipo_temporada') == 'Mixto' ? 'selected' : '' }}>
                                                    Mixto (Liga + Playoffs)
                                                </option>
                                            </select>
                                            @error('tipo_temporada')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Formato de competencia</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="generar_jornadas_auto" 
                                                   name="generar_jornadas_auto" 
                                                   value="1"
                                                   {{ old('generar_jornadas_auto') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="generar_jornadas_auto">
                                                Generar jornadas automáticamente
                                            </label>
                                            <small class="form-text text-muted d-block">
                                                El sistema generará las jornadas automáticamente según el número de equipos
                                            </small>
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
                                <i class="bi bi-eraser me-1"></i> Limpiar
                            </button>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('temporadas.index') }}" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Crear Temporada
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel de Ayuda y Vista Previa -->
    <div class="col-lg-4">
        <!-- Resumen de la Temporada -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-eye me-2"></i> Resumen de la Temporada
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-calendar-week" style="font-size: 2rem;"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h5 id="previewNombre" class="text-center mb-1">Nombre de la Temporada</h5>
                    <p class="text-muted text-center mb-3" id="previewAnio">{{ date('Y') }}</p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Inicio</small>
                                <strong id="previewInicio">--/--/----</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">Fin</small>
                                <strong id="previewFin">--/--/----</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="border rounded p-3 mb-3">
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-warning rounded-pill" id="previewEstado">Programada</span>
                    </div>
                    
                    <div class="border rounded p-3">
                        <small class="text-muted d-block">Configuración</small>
                        <div class="d-flex justify-content-between">
                            <small>Equipos:</small>
                            <strong id="previewEquipos">20 máx.</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small>Duración:</small>
                            <strong id="previewDuracion">0 días</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Guía de Creación -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i> Información Importante
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="bi bi-lightbulb"></i> Recomendaciones
                    </h6>
                    <ul class="mb-0 ps-3">
                        <li>Verifica que el nombre sea único y descriptivo</li>
                        <li>Las fechas no deben solaparse con otras temporadas</li>
                        <li>Planifica suficiente tiempo para todas las jornadas</li>
                        <li>Considera fechas de descanso y días festivos</li>
                        <li>Configura el estado según el momento de creación</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> Campos Obligatorios
                    </h6>
                    <p class="mb-0">
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                    </p>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">
                        <i class="bi bi-clock-history"></i> Siguientes Pasos
                    </h6>
                    <p class="mb-0">
                        Después de crear la temporada podrás:
                    </p>
                    <ul class="mb-0 ps-3">
                        <li>Agregar equipos participantes</li>
                        <li>Generar jornadas automáticas</li>
                        <li>Configurar el sistema de puntuación</li>
                        <li>Programar partidos</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .required:after {
        content: " *";
        color: #dc3545;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    #previewNombre, #previewAnio {
        transition: all 0.3s ease;
    }
    
    .preview-card {
        border-left: 4px solid #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar Parsley
        $('#temporadaForm').parsley({
            excluded: 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], :hidden',
            trigger: 'change',
            errorClass: 'is-invalid',
            successClass: 'is-valid',
            errorsWrapper: '<div class="invalid-feedback"></div>',
            errorTemplate: '<span></span>',
            classHandler: function(el) {
                return el.$element.closest('.form-group');
            }
        });
        
        // Preview en tiempo real
        $('#nombre').on('input', function() {
            $('#previewNombre').text($(this).val() || 'Nombre de la Temporada');
        });
        
        $('#anio').on('input', function() {
            $('#previewAnio').text($(this).val() || '{{ date('Y') }}');
        });
        
        $('#fecha_inicio').on('change', function() {
            updatePreviewDates();
        });
        
        $('#fecha_fin').on('change', function() {
            updatePreviewDates();
        });
        
        $('#estado').on('change', function() {
            const estado = $(this).val();
            const estadoColors = {
                'Programada': 'warning',
                'En Curso': 'success',
                'Finalizada': 'info',
                'Cancelada': 'danger',
                'Archivada': 'secondary'
            };
            
            const color = estadoColors[estado] || 'secondary';
            $('#previewEstado')
                .text(estado || 'Programada')
                .removeClass('bg-warning bg-success bg-info bg-danger bg-secondary')
                .addClass('bg-' + color);
        });
        
        $('#max_equipos').on('input', function() {
            $('#previewEquipos').text($(this).val() + ' máx.');
        });
        
        function updatePreviewDates() {
            const inicio = $('#fecha_inicio').val();
            const fin = $('#fecha_fin').val();
            
            if (inicio) {
                const fechaInicio = new Date(inicio);
                $('#previewInicio').text(formatDate(fechaInicio));
            } else {
                $('#previewInicio').text('--/--/----');
            }
            
            if (fin) {
                const fechaFin = new Date(fin);
                $('#previewFin').text(formatDate(fechaFin));
                
                // Calcular duración
                if (inicio) {
                    const diffTime = Math.abs(fechaFin - fechaInicio);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    $('#previewDuracion').text(diffDays + ' días');
                }
            } else {
                $('#previewFin').text('--/--/----');
                $('#previewDuracion').text('0 días');
            }
        }
        
        function formatDate(date) {
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }
        
        // Contador de caracteres para descripción
        $('#descripcion').on('input', function() {
            const length = $(this).val().length;
            $('#descripcionCounter').text(length + '/500');
            
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
        
        // Auto-fill fechas basadas en el año
        $('#anio').on('change', function() {
            const year = $(this).val();
            if (year) {
                if (!$('#fecha_inicio').val()) {
                    $('#fecha_inicio').val(year + '-01-01');
                }
                if (!$('#fecha_fin').val()) {
                    $('#fecha_fin').val(year + '-12-31');
                }
                updatePreviewDates();
            }
        });
        
        // Inicializar preview
        $('#estado').trigger('change');
        $('#max_equipos').trigger('input');
        updatePreviewDates();
        
        // Form submission
        $('#temporadaForm').on('submit', function(e) {
            if (!$(this).parsley().isValid()) {
                e.preventDefault();
                toastr.error('Por favor, corrija los errores en el formulario');
            }
        });
    });
</script>
@endpush
@endsection