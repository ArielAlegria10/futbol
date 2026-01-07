@extends('layouts.app')

@section('title', 'Jornadas - ' . ($temporada->nombre ?? 'Selecciona Temporada'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Jornadas
                        @if($temporada)
                            <span class="badge bg-primary ms-2">{{ $temporada->nombre }}</span>
                        @endif
                    </h5>
                    
                    <div class="d-flex gap-2">
                        <!-- Selector de temporada -->
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-exchange-alt"></i> Cambiar Temporada
                            </button>
                            <ul class="dropdown-menu">
                                @foreach($temporadas as $temp)
                                    <li>
                                        <a class="dropdown-item {{ $temporada && $temporada->id == $temp->id ? 'active' : '' }}"
                                           href="{{ route('jornadas.index', ['temporada_id' => $temp->id]) }}">
                                            {{ $temp->nombre }}
                                            @if($temp->estado == 'En Curso')
                                                <span class="badge bg-success float-end">Activa</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        
                        @if($temporada)
                            <!-- Botón generar calendario -->
                            <a href="{{ route('jornadas.generar-calendario') }}?temporada_id={{ $temporada->id }}" 
                               class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Generar Calendario
                            </a>
                            
                            <!-- Botón crear jornada -->
                            <a href="{{ route('jornadas.create', ['temporada_id' => $temporada->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nueva Jornada
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if(!$temporada)
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            Por favor, selecciona una temporada para gestionar las jornadas.
                            <a href="{{ route('temporadas.index') }}" class="btn btn-sm btn-warning ms-2">
                                Ir a Temporadas
                            </a>
                        </div>
                    @else
                        <!-- Estadísticas rápidas -->
                        @if($estadisticasJornadas)
                            <div class="row mb-4">
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info">
                                            <i class="fas fa-calendar"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Jornadas</span>
                                            <span class="info-box-number">
                                                {{ $estadisticasJornadas['total_jornadas'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Completadas</span>
                                            <span class="info-box-number">
                                                {{ $estadisticasJornadas['jornadas_completadas'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-clock"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Pendientes</span>
                                            <span class="info-box-number">
                                                {{ $estadisticasJornadas['jornadas_pendientes'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-3 col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary">
                                            <i class="fas fa-running"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Jornada Actual</span>
                                            <span class="info-box-number">
                                                @if($estadisticasJornadas['jornada_actual'])
                                                    Jornada {{ $estadisticasJornadas['jornada_actual']->numero }}
                                                @else
                                                    Sin jornada
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Filtros -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-filter"></i> Filtros</h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('jornadas.index') }}" class="row g-3">
                                    <input type="hidden" name="temporada_id" value="{{ $temporada->id }}">
                                    
                                    <div class="col-md-3">
                                        <label for="search" class="form-label">Buscar</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="{{ request('search') }}" placeholder="Nombre o número...">
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label for="estado" class="form-label">Estado Partidos</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="">Todos los estados</option>
                                            @foreach($estadosPartidos as $key => $label)
                                                <option value="{{ $key }}" 
                                                    {{ request('estado') == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="fecha_inicio" class="form-label">Desde</label>
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                               value="{{ request('fecha_inicio') }}">
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="fecha_fin" class="form-label">Hasta</label>
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                               value="{{ request('fecha_fin') }}">
                                    </div>
                                    
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Filtrar
                                        </button>
                                        <a href="{{ route('jornadas.index', ['temporada_id' => $temporada->id]) }}" 
                                           class="btn btn-secondary">
                                            <i class="fas fa-redo"></i>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tabla de jornadas -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="80">
                                            <a href="{{ route('jornadas.index', array_merge(request()->except(['sort', 'order']), ['sort' => 'numero', 'order' => request('order') == 'asc' ? 'desc' : 'asc'])) }}">
                                                N°
                                                @if(request('sort') == 'numero')
                                                    <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                                @else
                                                    <i class="fas fa-sort"></i>
                                                @endif
                                            </a>
                                        </th>
                                        <th>Nombre</th>
                                        <th width="150">Fecha Inicio</th>
                                        <th width="150">Fecha Fin</th>
                                        <th width="120">Partidos</th>
                                        <th width="100">Estado</th>
                                        <th width="150" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($jornadas as $jornada)
                                        @php
                                            $partidosCount = $jornada->partidos->count();
                                            $partidosCompletados = $jornada->partidos->where('estado', 'Finalizado')->count();
                                            $porcentajeCompletado = $partidosCount > 0 ? round(($partidosCompletados / $partidosCount) * 100) : 0;
                                        @endphp
                                        
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-primary fs-6">#{{ $jornada->numero }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $jornada->nombre }}</strong>
                                                @if($jornada->especial)
                                                    <span class="badge bg-warning ms-1">Especial</span>
                                                @endif
                                                @if($jornada->descripcion)
                                                    <p class="text-muted mb-0 small">{{ Str::limit($jornada->descripcion, 50) }}</p>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $jornada->fecha_inicio->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">{{ $jornada->fecha_inicio->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                {{ $jornada->fecha_fin->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $jornada->fecha_fin->diffInDays($jornada->fecha_inicio) }} días
                                                </small>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{ $porcentajeCompletado == 100 ? 'bg-success' : 'bg-info' }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $porcentajeCompletado }}%;"
                                                         aria-valuenow="{{ $porcentajeCompletado }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $partidosCompletados }}/{{ $partidosCount }}
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block text-center">
                                                    {{ $porcentajeCompletado }}% completado
                                                </small>
                                            </td>
                                            <td>
                                                @if($jornada->completada)
                                                    <span class="badge bg-success">Completada</span>
                                                @elseif(now()->between($jornada->fecha_inicio, $jornada->fecha_fin))
                                                    <span class="badge bg-primary">En Curso</span>
                                                @elseif(now()->gt($jornada->fecha_fin))
                                                    <span class="badge bg-warning">Vencida</span>
                                                @else
                                                    <span class="badge bg-secondary">Programada</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('jornadas.show', $jornada->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(!$jornada->completada)
                                                        <a href="{{ route('jornadas.edit', $jornada->id) }}" 
                                                           class="btn btn-sm btn-warning" 
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($jornada->partidos->count() == 0)
                                                        <form action="{{ route('jornadas.destroy', $jornada->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Eliminar esta jornada?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                    
                                                    @if($jornada->partidos->count() > 0 && !$jornada->completada)
                                                        <form action="{{ route('jornadas.completar', $jornada->id) }}" 
                                                              method="POST" 
                                                              class="d-inline"
                                                              onsubmit="return confirm('¿Marcar jornada como completada?')">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-success" title="Completar">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i>
                                                    No hay jornadas registradas para esta temporada.
                                                    @if($temporada)
                                                        <a href="{{ route('jornadas.create', ['temporada_id' => $temporada->id]) }}" 
                                                           class="btn btn-sm btn-primary ms-2">
                                                            Crear Primera Jornada
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        @if($jornadas->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    Mostrando {{ $jornadas->firstItem() }} - {{ $jornadas->lastItem() }} de {{ $jornadas->total() }} jornadas
                                </div>
                                <div>
                                    {{ $jornadas->withQueryString()->links() }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-submit al cambiar select de temporada
    document.addEventListener('DOMContentLoaded', function() {
        const temporadaSelect = document.querySelector('select[name="temporada_id"]');
        if (temporadaSelect) {
            temporadaSelect.addEventListener('change', function() {
                this.form.submit();
            });
        }
        
        // Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection