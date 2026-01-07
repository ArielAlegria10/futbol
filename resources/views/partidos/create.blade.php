@extends('layouts.app')

@section('title', 'Crear Partido')
@section('icon', 'bi-plus-circle')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('partidos.index') }}" class="text-decoration-none text-primary">
            <i class="bi bi-calendar-event me-1"></i> Partidos
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-plus-circle me-1"></i> Crear Partido
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-header-soccer">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h1 class="page-title-soccer mb-2">
                            <i class="bi bi-plus-circle me-3"></i>
                            Crear Nuevo Partido
                        </h1>
                        <p class="text-muted mb-0">
                            Complete el formulario para programar un nuevo partido
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('partidos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('partidos.store') }}" method="POST" id="partidoForm">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Equipos -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipo_local_id" class="form-label required">
                                        <i class="bi bi-house-door me-1"></i> Equipo Local
                                    </label>
                                    <select name="equipo_local_id" id="equipo_local_id" 
                                            class="form-select @error('equipo_local_id') is-invalid @enderror" required>
                                        <option value="">Seleccione equipo local</option>
                                        @foreach($equipos as $equipo)
                                            <option value="{{ $equipo->id }}" 
                                                {{ old('equipo_local_id') == $equipo->id ? 'selected' : '' }}>
                                                {{ $equipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('equipo_local_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipo_visitante_id" class="form-label required">
                                        <i class="bi bi-airplane me-1"></i> Equipo Visitante
                                    </label>
                                    <select name="equipo_visitante_id" id="equipo_visitante_id" 
                                            class="form-select @error('equipo_visitante_id') is-invalid @enderror" required>
                                        <option value="">Seleccione equipo visitante</option>
                                        @foreach($equipos as $equipo)
                                            <option value="{{ $equipo->id }}" 
                                                {{ old('equipo_visitante_id') == $equipo->id ? 'selected' : '' }}>
                                                {{ $equipo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('equipo_visitante_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Fecha y Hora -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label required">
                                        <i class="bi bi-calendar-date me-1"></i> Fecha y Hora
                                    </label>
                                    <input type="datetime-local" name="fecha_hora" id="fecha_hora"
                                           class="form-control @error('fecha_hora') is-invalid @enderror"
                                           value="{{ old('fecha_hora', $defaults['fecha_hora'] ?? '') }}"
                                           required>
                                    @error('fecha_hora')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Estado -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label required">
                                        <i class="bi bi-gear me-1"></i> Estado
                                    </label>
                                    <select name="estado" id="estado" 
                                            class="form-select @error('estado') is-invalid @enderror" required>
                                        @foreach($estados as $key => $value)
                                            <option value="{{ $key }}" 
                                                {{ old('estado', $defaults['estado'] ?? '') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Jornada y Temporada -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="temporada_id" class="form-label required">
                                        <i class="bi bi-calendar-range me-1"></i> Temporada
                                    </label>
                                    <select name="temporada_id" id="temporada_id" 
                                            class="form-select @error('temporada_id') is-invalid @enderror" required>
                                        <option value="">Seleccione temporada</option>
                                        @foreach($temporadas as $temporada)
                                            <option value="{{ $temporada->id }}" 
                                                {{ old('temporada_id') == $temporada->id ? 'selected' : '' }}>
                                                {{ $temporada->nombre ?? $temporada->anio }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('temporada_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jornada_id" class="form-label required">
                                        <i class="bi bi-calendar-week me-1"></i> Jornada
                                    </label>
                                    <select name="jornada_id" id="jornada_id" 
                                            class="form-select @error('jornada_id') is-invalid @enderror" required>
                                        <option value="">Seleccione jornada</option>
                                        @foreach($jornadas as $jornada)
                                            <option value="{{ $jornada->id }}" 
                                                {{ old('jornada_id') == $jornada->id ? 'selected' : '' }}>
                                                Jornada {{ $jornada->numero }} - {{ $jornada->temporada->nombre ?? $jornada->temporada->anio }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jornada_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Estadio y Árbitro -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estadio" class="form-label">
                                        <i class="bi bi-geo-alt me-1"></i> Estadio
                                    </label>
                                    <input type="text" name="estadio" id="estadio"
                                           class="form-control @error('estadio') is-invalid @enderror"
                                           value="{{ old('estadio') }}"
                                           placeholder="Ej: Estadio Santiago Bernabéu">
                                    @error('estadio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="arbitro_principal" class="form-label">
                                        <i class="bi bi-whistle me-1"></i> Árbitro Principal
                                    </label>
                                    <input type="text" name="arbitro_principal" id="arbitro_principal"
                                           class="form-control @error('arbitro_principal') is-invalid @enderror"
                                           value="{{ old('arbitro_principal') }}"
                                           placeholder="Nombre del árbitro principal">
                                    @error('arbitro_principal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Marcador (si es necesario) -->
                            <div class="col-12" id="marcadorFields" style="display: none;">
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-header bg-transparent border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-scoreboard me-2"></i> Marcador
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="goles_local" class="form-label">Goles Local</label>
                                                    <input type="number" name="goles_local" id="goles_local"
                                                           class="form-control @error('goles_local') is-invalid @enderror"
                                                           value="{{ old('goles_local', 0) }}"
                                                           min="0">
                                                    @error('goles_local')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="goles_visitante" class="form-label">Goles Visitante</label>
                                                    <input type="number" name="goles_visitante" id="goles_visitante"
                                                           class="form-control @error('goles_visitante') is-invalid @enderror"
                                                           value="{{ old('goles_visitante', 0) }}"
                                                           min="0">
                                                    @error('goles_visitante')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="penales_local" class="form-label">Penales Local</label>
                                                    <input type="number" name="penales_local" id="penales_local"
                                                           class="form-control @error('penales_local') is-invalid @enderror"
                                                           value="{{ old('penales_local') }}"
                                                           min="0">
                                                    @error('penales_local')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="penales_visitante" class="form-label">Penales Visitante</label>
                                                    <input type="number" name="penales_visitante" id="penales_visitante"
                                                           class="form-control @error('penales_visitante') is-invalid @enderror"
                                                           value="{{ old('penales_visitante') }}"
                                                           min="0">
                                                    @error('penales_visitante')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Observaciones -->
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="observaciones" class="form-label">
                                        <i class="bi bi-chat-left-text me-1"></i> Observaciones
                                    </label>
                                    <textarea name="observaciones" id="observaciones" 
                                              class="form-control @error('observaciones') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Observaciones adicionales del partido">{{ old('observaciones') }}</textarea>
                                    @error('observaciones')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notificar_equipos" name="notificar_equipos" checked>
                                <label class="form-check-label" for="notificar_equipos">
                                    Notificar a los equipos
                                </label>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i> Crear Partido
                                </button>
                            </div>
                        </div>
                    </form>
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
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Mostrar/ocultar campos de marcador según el estado
    $('#estado').change(function() {
        const estado = $(this).val();
        const marcadorFields = $('#marcadorFields');
        
        if (estado === 'Finalizado' || estado === 'Jugando') {
            marcadorFields.slideDown();
            if (estado === 'Finalizado') {
                $('#goles_local, #goles_visitante').attr('required', true);
            } else {
                $('#goles_local, #goles_visitante').removeAttr('required');
            }
        } else {
            marcadorFields.slideUp();
            $('#goles_local, #goles_visitante').removeAttr('required').val(0);
            $('#penales_local, #penales_visitante').val('');
        }
    });
    
    // Inicializar estado
    $('#estado').trigger('change');
    
    // Validar que no sea el mismo equipo
    $('#partidoForm').submit(function(e) {
        const equipoLocal = $('#equipo_local_id').val();
        const equipoVisitante = $('#equipo_visitante_id').val();
        
        if (equipoLocal && equipoVisitante && equipoLocal === equipoVisitante) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'El equipo local no puede ser el mismo que el equipo visitante',
                confirmButtonText: 'Entendido'
            });
        }
    });
    
    // Filtrar jornadas por temporada
    $('#temporada_id').change(function() {
        const temporadaId = $(this).val();
        const jornadaSelect = $('#jornada_id');
        
        if (temporadaId) {
            // Aquí podrías hacer una petición AJAX para cargar las jornadas de esa temporada
            // Por ahora, solo filtramos las que ya están cargadas
            jornadaSelect.find('option').each(function() {
                const optionText = $(this).text();
                if (optionText.includes('Temporada ' + temporadaId)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            jornadaSelect.val('').trigger('change');
        } else {
            jornadaSelect.find('option').show();
        }
    });
    
    // Inicializar Select2 para selects
    $('.form-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione una opción',
        allowClear: true
    });
});
</script>
@endpush