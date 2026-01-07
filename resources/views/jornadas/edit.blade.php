@extends('layouts.app')

@section('title', 'Editar Jornada')
@section('icon', 'bi-pencil-square')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('jornadas.index') }}" class="text-decoration-none text-primary">
            <i class="bi bi-calendar-week me-1"></i> Jornadas
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-pencil-square me-1"></i> Editar Jornada
    </li>
@endsection

@section('header-buttons')
    <a href="{{ route('jornadas.show', $jornada->id) }}" class="btn btn-outline-primary">
        <i class="bi bi-eye me-2"></i> Ver Jornada
    </a>
    <a href="{{ route('jornadas.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i> Volver
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-lg bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 70px; height: 70px;">
                                <i class="bi bi-pencil-square fs-3"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-4">
                            <h4 class="card-title mb-1">Editar Jornada</h4>
                            <p class="text-muted mb-2">Actualice los detalles de la jornada {{ $jornada->nombre }}</p>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="badge bg-primary">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    {{ $jornada->temporada->nombre ?? 'Sin temporada' }}
                                </span>
                                <span class="badge bg-info">
                                    <i class="bi bi-hash me-1"></i>
                                    Jornada {{ $jornada->numero }}
                                </span>
                                <span class="badge {{ $jornada->completada ? 'bg-success' : 'bg-warning' }}">
                                    <i class="bi bi-{{ $jornada->completada ? 'check-circle' : 'clock' }} me-1"></i>
                                    {{ $jornada->completada ? 'Completada' : 'Pendiente' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Alert if has finished matches -->
            @if($jornada->partidos()->where('estado', 'Finalizado')->exists())
            <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="alert-heading mb-1">¡Atención!</h6>
                        <p class="mb-0">
                            Esta jornada contiene partidos finalizados. Las modificaciones se limitarán a información general.
                            Los resultados de partidos no se verán afectados.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Edit Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-gear me-2"></i> Configuración de Jornada
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('jornadas.update', $jornada->id) }}" method="POST" id="editJornadaForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Nombre de Jornada -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label fw-semibold required">
                                        <i class="bi bi-card-heading me-2"></i> Nombre de la Jornada
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-tag"></i>
                                        </span>
                                        <input type="text" 
                                               class="form-control @error('nombre') is-invalid @enderror" 
                                               id="nombre" 
                                               name="nombre" 
                                               value="{{ old('nombre', $jornada->nombre) }}"
                                               placeholder="Ej: Jornada de Clásicos, Fecha Especial"
                                               required>
                                    </div>
                                    @error('nombre')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Identificador único para esta jornada</small>
                                </div>
                            </div>

                            <!-- Fechas -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label fw-semibold required">
                                        <i class="bi bi-calendar-plus me-2"></i> Fecha de Inicio
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar-date"></i>
                                        </span>
                                        <input type="date" 
                                               class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                               id="fecha_inicio" 
                                               name="fecha_inicio" 
                                               value="{{ old('fecha_inicio', $jornada->fecha_inicio->format('Y-m-d')) }}"
                                               required>
                                    </div>
                                    @error('fecha_inicio')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label fw-semibold required">
                                        <i class="bi bi-calendar-minus me-2"></i> Fecha de Fin
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-calendar-date"></i>
                                        </span>
                                        <input type="date" 
                                               class="form-control @error('fecha_fin') is-invalid @enderror" 
                                               id="fecha_fin" 
                                               name="fecha_fin" 
                                               value="{{ old('fecha_fin', $jornada->fecha_fin->format('Y-m-d')) }}"
                                               required>
                                    </div>
                                    @error('fecha_fin')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label fw-semibold">
                                        <i class="bi bi-text-paragraph me-2"></i> Descripción
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-chat-left-text"></i>
                                        </span>
                                        <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                                  id="descripcion" 
                                                  name="descripcion" 
                                                  rows="3"
                                                  placeholder="Descripción detallada de la jornada...">{{ old('descripcion', $jornada->descripcion) }}</textarea>
                                    </div>
                                    @error('descripcion')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Información adicional sobre esta jornada</small>
                                </div>
                            </div>

                            <!-- Configuración Avanzada -->
                            <div class="col-md-12">
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-header bg-transparent border-bottom py-3">
                                        <h6 class="mb-0">
                                            <i class="bi bi-sliders me-2"></i> Configuración Avanzada
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Jornada Especial -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       role="switch" 
                                                       id="especial" 
                                                       name="especial" 
                                                       value="1" 
                                                       {{ old('especial', $jornada->especial) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="especial">
                                                    <i class="bi bi-star-fill text-warning me-2"></i> Jornada Especial
                                                </label>
                                            </div>
                                            <small class="text-muted ms-4">
                                                Marca esta jornada como especial (doble puntos, eventos especiales, etc.)
                                            </small>
                                        </div>

                                        <!-- Completada -->
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       role="switch" 
                                                       id="completada" 
                                                       name="completada" 
                                                       value="1" 
                                                       {{ old('completada', $jornada->completada) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-semibold" for="completada">
                                                    <i class="bi bi-check-circle-fill text-success me-2"></i> Jornada Completada
                                                </label>
                                            </div>
                                            <small class="text-muted ms-4">
                                                Marca esta jornada como completada para el cálculo de clasificación
                                            </small>
                                        </div>

                                        <!-- Notas Internas -->
                                        <div class="mb-0">
                                            <label for="notas" class="form-label fw-semibold">
                                                <i class="bi bi-sticky-fill me-2"></i> Notas Internas
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                                <textarea class="form-control @error('notas') is-invalid @enderror" 
                                                          id="notas" 
                                                          name="notas" 
                                                          rows="2"
                                                          placeholder="Notas internas para administradores...">{{ old('notas', $jornada->notas) }}</textarea>
                                            </div>
                                            @error('notas')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">Solo visible para administradores</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas -->
                            <div class="col-md-12">
                                <div class="card bg-info bg-opacity-10 border-info border-0">
                                    <div class="card-body">
                                        <h6 class="text-info mb-3">
                                            <i class="bi bi-bar-chart me-2"></i> Estadísticas de la Jornada
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold">{{ $jornada->partidos->count() }}</div>
                                                    <small class="text-muted">Total Partidos</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-success">{{ $jornada->partidos->where('estado', 'Finalizado')->count() }}</div>
                                                    <small class="text-muted">Finalizados</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-warning">{{ $jornada->partidos->where('estado', 'Programado')->count() }}</div>
                                                    <small class="text-muted">Programados</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-primary">{{ $jornada->partidos->where('estado', 'Jugando')->count() }}</div>
                                                    <small class="text-muted">En Juego</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                    <div>
                                        <a href="{{ route('jornadas.show', $jornada->id) }}" 
                                           class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-x-circle me-2"></i> Cancelar
                                        </a>
                                        <button type="reset" class="btn btn-outline-warning">
                                            <i class="bi bi-arrow-clockwise me-2"></i> Restablecer
                                        </button>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('jornadas.duplicar', $jornada->id) }}" 
                                           class="btn btn-outline-info"
                                           onclick="return confirm('¿Duplicar esta jornada?')">
                                            <i class="bi bi-copy me-2"></i> Duplicar
                                        </a>
                                        
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <a href="{{ route('jornadas.show', $jornada->id) }}" class="card border-0 shadow-sm text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-eye text-primary fs-3 mb-3"></i>
                            <h6 class="mb-2">Ver Jornada</h6>
                            <small class="text-muted">Ver detalles completos</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('partidos.index', ['jornada_id' => $jornada->id]) }}" class="card border-0 shadow-sm text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-calendar-event text-success fs-3 mb-3"></i>
                            <h6 class="mb-2">Gestionar Partidos</h6>
                            <small class="text-muted">{{ $jornada->partidos->count() }} partidos</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('jornadas.exportar', $jornada->id) }}" class="card border-0 shadow-sm text-decoration-none h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-download text-warning fs-3 mb-3"></i>
                            <h6 class="mb-2">Exportar</h6>
                            <small class="text-muted">Descargar información</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .required::after {
        content: " *";
        color: #dc3545;
    }
    
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .avatar-lg {
        transition: all 0.3s ease;
    }
    
    .avatar-lg:hover {
        transform: scale(1.05);
    }
    
    .card-link {
        transition: all 0.3s ease;
    }
    
    .card-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .input-group-text {
        transition: all 0.3s ease;
    }
    
    .input-group:focus-within .input-group-text {
        background-color: #e3f2fd;
        border-color: #86b7fe;
    }
    
    /* Date input styling */
    input[type="date"]::-webkit-calendar-picker-indicator {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' class='bi bi-calendar' viewBox='0 0 16 16'%3E%3Cpath d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z'/%3E%3C/svg%3E");
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize form validation
        $('#editJornadaForm').validate({
            rules: {
                nombre: {
                    required: true,
                    minlength: 3,
                    maxlength: 100
                },
                fecha_inicio: {
                    required: true,
                    date: true
                },
                fecha_fin: {
                    required: true,
                    date: true,
                    greaterThanStart: true
                }
            },
            messages: {
                nombre: {
                    required: "El nombre de la jornada es obligatorio",
                    minlength: "Mínimo 3 caracteres",
                    maxlength: "Máximo 100 caracteres"
                },
                fecha_inicio: {
                    required: "La fecha de inicio es obligatoria",
                    date: "Formato de fecha inválido"
                },
                fecha_fin: {
                    required: "La fecha de fin es obligatoria",
                    date: "Formato de fecha inválido",
                    greaterThanStart: "La fecha fin debe ser posterior a la fecha inicio"
                }
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.mb-3').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            }
        });

        // Custom validation rule for date comparison
        $.validator.addMethod("greaterThanStart", function(value, element) {
            const startDate = $('#fecha_inicio').val();
            const endDate = value;
            
            if (!startDate || !endDate) return true;
            
            return new Date(endDate) >= new Date(startDate);
        }, "La fecha fin debe ser igual o posterior a la fecha inicio");

        // Auto-calculate end date
        $('#fecha_inicio').on('change', function() {
            const startDate = new Date(this.value);
            if (startDate && !isNaN(startDate.getTime())) {
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 2);
                
                const formattedEndDate = endDate.toISOString().split('T')[0];
                $('#fecha_fin').val(formattedEndDate);
                
                // Trigger validation
                $('#fecha_fin').valid();
            }
        });

        // Toggle special jornada effects
        $('#especial').on('change', function() {
            const card = $(this).closest('.card');
            if (this.checked) {
                card.addClass('border-warning');
                $('.form-check-label', card).addClass('text-warning');
            } else {
                card.removeClass('border-warning');
                $('.form-check-label', card).removeClass('text-warning');
            }
        });

        // Form submission loading state
        $('#editJornadaForm').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.html(`
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Guardando...
            `);
            
            // Add processing overlay
            $(this).prepend(`
                <div class="processing-overlay position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center" style="z-index: 1050;">
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <h6>Actualizando jornada...</h6>
                    </div>
                </div>
            `);
        });

        // Character counter for description
        $('#descripcion').on('input', function() {
            const maxLength = 500;
            const currentLength = $(this).val().length;
            const counter = $(this).siblings('.char-counter');
            
            if (counter.length === 0) {
                $(this).after(`
                    <div class="char-counter text-end mt-1">
                        <small class="text-muted">
                            <span class="char-count">${currentLength}</span> / ${maxLength} caracteres
                        </small>
                    </div>
                `);
            } else {
                $('.char-count', counter).text(currentLength);
                
                if (currentLength > maxLength) {
                    counter.addClass('text-danger');
                } else {
                    counter.removeClass('text-danger');
                }
            }
        });

        // Trigger initial character count
        $('#descripcion').trigger('input');

        // Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip({
            animation: true,
            placement: 'top'
        });

        // Preview changes
        $('#nombre').on('keyup', function() {
            $('.badge.bg-light').text($(this).val() || '{{ $jornada->nombre }}');
        });
    });
</script>
@endpush