@extends('layouts.app')

@section('title', $temporada->nombre)
@section('icon', 'bi-calendar-week')
@section('subtitle', 'Detalles y gestión de la temporada')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('temporadas.index') }}">Temporadas</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $temporada->nombre }}</li>
@endsection

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('temporadas.edit', $temporada) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                data-bs-toggle="dropdown">
            <span class="visually-hidden">Más opciones</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('temporadas.clasificacion', $temporada) }}">
                    <i class="bi bi-trophy me-2"></i> Ver Clasificación
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('temporadas.estadisticas', $temporada) }}">
                    <i class="bi bi-bar-chart me-2"></i> Estadísticas
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('temporadas.jornadas', $temporada) }}">
                    <i class="bi bi-calendar-range me-2"></i> Gestionar Jornadas
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('temporadas.partidos', $temporada) }}">
                    <i class="bi bi-calendar-event me-2"></i> Ver Partidos
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button class="dropdown-item cambiar-estado" 
                        data-id="{{ $temporada->id }}"
                        data-estado="{{ $temporada->estado }}">
                    <i class="bi bi-arrow-repeat me-2"></i> Cambiar Estado
                </button>
            </li>
            <li>
                <button class="dropdown-item text-danger" data-bs-toggle="modal" 
                        data-bs-target="#deleteModal">
                    <i class="bi bi-trash me-2"></i> Eliminar Temporada
                </button>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-4">
        <!-- Tarjeta de Información -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i> Información de la Temporada
                </h5>
            </div>
            <div class="card-body text-center">
                <!-- Icono de Temporada -->
                <div class="mb-4">
                    <div class="bg-{{ $temporada->estado_color }} text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-calendar-week" style="font-size: 3rem;"></i>
                    </div>
                </div>
                
                <!-- Nombre y Año -->
                <h2 class="mb-1">{{ $temporada->nombre }}</h2>
                <h4 class="text-muted mb-4">{{ $temporada->anio }}</h4>
                
                <!-- Estado -->
                <div class="mb-4">
                    <span class="badge bg-{{ $temporada->estado_color }} fs-6 rounded-pill">
                        <i class="bi bi-{{ $temporada->estado_icon }} me-1"></i>
                        {{ $temporada->estado }}
                    </span>
                </div>
                
                <!-- Progreso -->
                <div class="mb-4">
                    <h6 class="mb-2">Progreso de la Temporada</h6>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar progress-bar-striped 
                                 {{ $temporada->estado === 'En Curso' ? 'progress-bar-animated' : '' }}" 
                             role="progressbar" 
                             style="width: {{ $temporada->progreso }}%;"
                             aria-valuenow="{{ $temporada->progreso }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            {{ $temporada->progreso }}%
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">
                            {{ $temporada->partidos_jugados_count }} de {{ $temporada->partidos_count }} partidos
                        </small>
                        <small class="text-muted">
                            {{ $temporada->dias_restantes }} días restantes
                        </small>
                    </div>
                </div>
                
                <!-- Acciones Rápidas -->
                <div class="btn-group w-100 mb-3">
                    <a href="{{ route('temporadas.clasificacion', $temporada) }}" 
                       class="btn btn-outline-success">
                        <i class="bi bi-trophy"></i>
                    </a>
                    <a href="{{ route('temporadas.estadisticas', $temporada) }}" 
                       class="btn btn-outline-warning">
                        <i class="bi bi-bar-chart"></i>
                    </a>
                    <a href="{{ route('temporadas.jornadas', $temporada) }}" 
                       class="btn btn-outline-info">
                        <i class="bi bi-calendar-range"></i>
                    </a>
                    <a href="{{ route('temporadas.partidos', $temporada) }}" 
                       class="btn btn-outline-primary">
                        <i class="bi bi-calendar-event"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Datos de la Temporada -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-card-checklist me-2"></i> Datos de la Temporada
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="bi bi-calendar-plus text-primary me-2"></i>
                        <strong>Inicio:</strong>
                        <span class="float-end">{{ $temporada->fecha_inicio->format('d/m/Y') }}</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-calendar-minus text-primary me-2"></i>
                        <strong>Fin:</strong>
                        <span class="float-end">{{ $temporada->fecha_fin->format('d/m/Y') }}</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-calendar-range text-primary me-2"></i>
                        <strong>Duración:</strong>
                        <span class="float-end">{{ $temporada->duracion_dias }} días</span>
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-people text-primary me-2"></i>
                        <strong>Equipos Participantes:</strong>
                        <span class="float-end">{{ $temporada->equipos_count }}</span>
                    </li>
                    @if($temporada->max_equipos)
                        <li class="mb-3">
                            <i class="bi bi-people-fill text-primary me-2"></i>
                            <strong>Máximo de Equipos:</strong>
                            <span class="float-end">{{ $temporada->max_equipos }}</span>
                        </li>
                    @endif
                    @if($temporada->tipo_temporada)
                        <li class="mb-3">
                            <i class="bi bi-diagram-3 text-primary me-2"></i>
                            <strong>Formato:</strong>
                            <span class="float-end">{{ $temporada->tipo_temporada }}</span>
                        </li>
                    @endif
                    @if($temporada->premio_ganador)
                        <li class="mb-3">
                            <i class="bi bi-award text-primary me-2"></i>
                            <strong>Premio:</strong>
                            <span class="float-end">{{ $temporada->premio_ganador }}</span>
                        </li>
                    @endif
                    <li class="mb-3">
                        <i class="bi bi-calendar-event text-primary me-2"></i>
                        <strong>Partidos Programados:</strong>
                        <span class="float-end">{{ $temporada->partidos_count }}</span>
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-primary me-2"></i>
                        <strong>Partidos Jugados:</strong>
                        <span class="float-end">{{ $temporada->partidos_jugados_count }}</span>
                    </li>
                </ul>
                
                @if($temporada->reglamento_url)
                    <hr>
                    <a href="{{ $temporada->reglamento_url }}" 
                       target="_blank" 
                       class="btn btn-outline-secondary w-100">
                        <i class="bi bi-file-earmark-text me-1"></i> Ver Reglamento
                    </a>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="col-lg-8">
        <!-- Pestañas de Gestión -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs" id="temporadaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" 
                                data-bs-target="#resumen" type="button" role="tab">
                            <i class="bi bi-house me-1"></i> Resumen
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="equipos-tab" data-bs-toggle="tab" 
                                data-bs-target="#equipos" type="button" role="tab">
                            <i class="bi bi-people me-1"></i> Equipos
                            <span class="badge bg-primary rounded-pill ms-1">
                                {{ $temporada->equipos_count }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="partidos-tab" data-bs-toggle="tab" 
                                data-bs-target="#partidos" type="button" role="tab">
                            <i class="bi bi-calendar-event me-1"></i> Partidos
                            <span class="badge bg-info rounded-pill ms-1">
                                {{ $temporada->partidos_count }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="jornadas-tab" data-bs-toggle="tab" 
                                data-bs-target="#jornadas" type="button" role="tab">
                            <i class="bi bi-calendar-range me-1"></i> Jornadas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="estadisticas-tab" data-bs-toggle="tab" 
                                data-bs-target="#estadisticas" type="button" role="tab">
                            <i class="bi bi-bar-chart me-1"></i> Estadísticas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="configuracion-tab" data-bs-toggle="tab" 
                                data-bs-target="#configuracion" type="button" role="tab">
                            <i class="bi bi-gear me-1"></i> Configuración
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="temporadaTabsContent">
                    <!-- Resumen -->
                    <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                        <!-- Descripción -->
                        @if($temporada->descripcion)
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="bi bi-chat-left-text me-2"></i> Descripción
                                    </h6>
                                    <p class="mb-0">{{ $temporada->descripcion }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Estadísticas Rápidas -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-speedometer2 me-2"></i> Progreso
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-primary">
                                                    {{ $temporada->partidos_jugados_count }}
                                                </div>
                                                <small class="text-muted">Jugados</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $temporada->partidos_pendientes_count }}
                                                </div>
                                                <small class="text-muted">Pendientes</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ round($temporada->progreso) }}%
                                                </div>
                                                <small class="text-muted">Completado</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-graph-up me-2"></i> Rendimiento
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-info">
                                                    {{ $estadisticas['goles_totales'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Goles</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $estadisticas['partidos_jugados'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Partidos</small>
                                            </div>
                                            <div class="col-4">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $estadisticas['promedio_goles'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Promedio</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Últimos Partidos -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history me-2"></i> Últimos Partidos
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($ultimosPartidos->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($ultimosPartidos as $partido)
                                            <div class="list-group-item border-0 px-0 py-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <span class="badge bg-secondary">
                                                                J{{ $partido->jornada->numero }}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                {{ $partido->equipoLocal->abreviacion }} 
                                                                {{ $partido->goles_local }} - 
                                                                {{ $partido->goles_visitante }} 
                                                                {{ $partido->equipoVisitante->abreviacion }}
                                                            </h6>
                                                            <small class="text-muted">
                                                                <i class="bi bi-calendar me-1"></i>
                                                                {{ $partido->fecha_hora->format('d/m H:i') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-{{ $partido->estado_color }}">
                                                        {{ $partido->estado }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('temporadas.partidos', $temporada) }}" 
                                           class="btn btn-outline-primary btn-sm">
                                            Ver todos los partidos
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-calendar-x text-muted fa-2x mb-3"></i>
                                        <p class="text-muted mb-0">No hay partidos registrados</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Top Equipos -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-trophy me-2"></i> Top Equipos
                                    </h6>
                                    <a href="{{ route('temporadas.clasificacion', $temporada) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        Ver clasificación completa
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($topEquipos->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="50">#</th>
                                                    <th>Equipo</th>
                                                    <th class="text-center">PJ</th>
                                                    <th class="text-center">PTS</th>
                                                    <th class="text-center">DG</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topEquipos as $index => $clasificacion)
                                                    <tr class="{{ $index < 3 ? 'table-success' : '' }}">
                                                        <td class="fw-bold">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                @if($clasificacion->equipo->escudo_url)
                                                                    <img src="{{ $clasificacion->equipo->escudo_url }}" 
                                                                         alt="{{ $clasificacion->equipo->nombre }}" 
                                                                         class="rounded-circle me-2" 
                                                                         width="24" height="24">
                                                                @endif
                                                                <span>{{ $clasificacion->equipo->nombre }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $clasificacion->partidos_jugados }}</td>
                                                        <td class="text-center fw-bold">{{ $clasificacion->puntos }}</td>
                                                        <td class="text-center">{{ $clasificacion->diferencia_goles }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-people text-muted fa-2x mb-3"></i>
                                        <p class="text-muted mb-0">No hay equipos en la clasificación</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Equipos -->
                    <div class="tab-pane fade" id="equipos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                Equipos Participantes
                            </h6>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                    data-bs-target="#agregarEquiposModal">
                                <i class="bi bi-plus-circle me-1"></i> Agregar Equipos
                            </button>
                        </div>
                        
                        @if($temporada->equipos->count() > 0)
                            <div class="row">
                                @foreach($temporada->equipos as $equipo)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0 me-3">
                                                        @if($equipo->escudo_url)
                                                            <img src="{{ $equipo->escudo_url }}" 
                                                                 alt="{{ $equipo->nombre }}" 
                                                                 class="rounded-circle border" 
                                                                 width="50" height="50">
                                                        @else
                                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                                 style="width: 50px; height: 50px;">
                                                                <i class="bi bi-shield text-white"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1">{{ $equipo->nombre }}</h6>
                                                        <small class="text-muted">{{ $equipo->abreviacion }}</small>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <span class="badge bg-{{ $equipo->activo ? 'success' : 'secondary' }}">
                                                            {{ $equipo->activo ? 'Activo' : 'Inactivo' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <div class="row text-center mb-3">
                                                    <div class="col-4">
                                                        <div class="text-primary fw-bold">
                                                            {{ $equipo->clasificacion?->partidos_jugados ?? 0 }}
                                                        </div>
                                                        <small class="text-muted">PJ</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-success fw-bold">
                                                            {{ $equipo->clasificacion?->puntos ?? 0 }}
                                                        </div>
                                                        <small class="text-muted">PTS</small>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-info fw-bold">
                                                            {{ $equipo->clasificacion?->diferencia_goles ?? 0 }}
                                                        </div>
                                                        <small class="text-muted">DG</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="btn-group w-100">
                                                    <a href="{{ route('equipos.show', $equipo) }}" 
                                                       class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('equipos.estadisticas', $equipo) }}" 
                                                       class="btn btn-outline-warning btn-sm">
                                                        <i class="bi bi-bar-chart"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm quitar-equipo"
                                                            data-equipo-id="{{ $equipo->id }}"
                                                            data-equipo-nombre="{{ $equipo->nombre }}">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-people display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay equipos participantes</h5>
                                <p class="text-muted mb-4">Agrega equipos para comenzar la temporada.</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" 
                                        data-bs-target="#agregarEquiposModal">
                                    <i class="bi bi-plus-circle me-1"></i> Agregar Equipos
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Partidos -->
                    <div class="tab-pane fade" id="partidos" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-event me-2"></i>
                                Partidos de la Temporada
                            </h6>
                            <div class="btn-group">
                                <a href="{{ route('partidos.create', ['temporada_id' => $temporada->id]) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Nuevo Partido
                                </a>
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                        type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-filter"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item filter-partidos" href="#" data-estado="all">
                                            Todos los partidos
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item filter-partidos" href="#" data-estado="Programado">
                                            Programados
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item filter-partidos" href="#" data-estado="En_Juego">
                                            En Juego
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item filter-partidos" href="#" data-estado="Finalizado">
                                            Finalizados
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item filter-partidos" href="#" data-estado="Cancelado">
                                            Cancelados
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div id="partidosContainer">
                            @include('temporadas.partials.partidos-list', ['partidos' => $partidos])
                        </div>
                    </div>
                    
                    <!-- Jornadas -->
                    <div class="tab-pane fade" id="jornadas" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0">
                                <i class="bi bi-calendar-range me-2"></i>
                                Jornadas de la Temporada
                            </h6>
                            <div class="btn-group">
                                <a href="{{ route('jornadas.create', ['temporada_id' => $temporada->id]) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i> Nueva Jornada
                                </a>
                                <button class="btn btn-outline-success btn-sm" id="generarJornadasAuto">
                                    <i class="bi bi-magic me-1"></i> Generar Automáticamente
                                </button>
                            </div>
                        </div>
                        
                        @if($temporada->jornadas->count() > 0)
                            <div class="accordion" id="jornadasAccordion">
                                @foreach($temporada->jornadas->sortBy('numero') as $jornada)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#jornada{{ $jornada->id }}">
                                                <div class="d-flex justify-content-between w-100 me-3">
                                                    <div>
                                                        <span class="badge bg-primary me-2">J{{ $jornada->numero }}</span>
                                                        {{ $jornada->nombre ?? "Jornada {$jornada->numero}" }}
                                                    </div>
                                                    <div>
                                                        <small class="text-muted me-3">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            {{ $jornada->fecha_inicio->format('d/m/Y') }}
                                                        </small>
                                                        <span class="badge bg-{{ $jornada->estado_color }}">
                                                            {{ $jornada->estado }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="jornada{{ $jornada->id }}" 
                                             class="accordion-collapse collapse" 
                                             data-bs-parent="#jornadasAccordion">
                                            <div class="accordion-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <div>
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-range me-1"></i>
                                                            {{ $jornada->fecha_inicio->format('d/m/Y') }} - 
                                                            {{ $jornada->fecha_fin->format('d/m/Y') }}
                                                        </small>
                                                    </div>
                                                    <div class="btn-group">
                                                        <a href="{{ route('jornadas.show', $jornada) }}" 
                                                           class="btn btn-outline-info btn-sm">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('jornadas.edit', $jornada) }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button class="btn btn-outline-success btn-sm" 
                                                                onclick="programarPartidosJornada({{ $jornada->id }})">
                                                            <i class="bi bi-calendar-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                @if($jornada->partidos->count() > 0)
                                                    <div class="list-group list-group-flush">
                                                        @foreach($jornada->partidos as $partido)
                                                            <div class="list-group-item">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h6 class="mb-1">
                                                                            {{ $partido->equipoLocal->abreviacion }} vs 
                                                                            {{ $partido->equipoVisitante->abreviacion }}
                                                                        </h6>
                                                                        <small class="text-muted">
                                                                            <i class="bi bi-clock me-1"></i>
                                                                            {{ $partido->fecha_hora->format('d/m H:i') }}
                                                                        </small>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <span class="badge bg-{{ $partido->estado_color }} mb-1">
                                                                            {{ $partido->estado }}
                                                                        </span>
                                                                        <br>
                                                                        @if($partido->estado === 'Finalizado')
                                                                            <small class="fw-bold">
                                                                                {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                                                                            </small>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center py-3">
                                                        <i class="bi bi-calendar-x text-muted fa-2x mb-2"></i>
                                                        <p class="text-muted">No hay partidos programados</p>
                                                        <button class="btn btn-outline-primary btn-sm"
                                                                onclick="programarPartidosJornada({{ $jornada->id }})">
                                                            <i class="bi bi-calendar-plus me-1"></i> Programar Partidos
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-range display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay jornadas creadas</h5>
                                <p class="text-muted mb-4">Crea jornadas para organizar los partidos.</p>
                                <div class="btn-group">
                                    <a href="{{ route('jornadas.create', ['temporada_id' => $temporada->id]) }}" 
                                       class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Crear Jornada Manual
                                    </a>
                                    <button class="btn btn-outline-success" id="generarJornadasAuto">
                                        <i class="bi bi-magic me-1"></i> Generar Automáticamente
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="tab-pane fade" id="estadisticas" role="tabpanel">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-bar-chart me-2"></i> Estadísticas Generales
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-primary">
                                                    {{ $estadisticas['partidos_jugados'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Partidos Jugados</small>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $estadisticas['goles_totales'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Goles Totales</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $estadisticas['promedio_goles'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Promedio de Goles</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="display-6 fw-bold text-info">
                                                    {{ $estadisticas['equipos_activos'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Equipos Activos</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-pie-chart me-2"></i> Distribución de Resultados
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-4 mb-3">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $estadisticas['victorias_local'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Victorias Local</small>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <div class="display-6 fw-bold text-danger">
                                                    {{ $estadisticas['victorias_visitante'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Victorias Visitante</small>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $estadisticas['empates'] ?? 0 }}
                                                </div>
                                                <small class="text-muted">Empates</small>
                                            </div>
                                        </div>
                                        <div class="progress" style="height: 20px;">
                                            @php
                                                $total = ($estadisticas['victorias_local'] ?? 0) + 
                                                        ($estadisticas['victorias_visitante'] ?? 0) + 
                                                        ($estadisticas['empates'] ?? 0);
                                                if ($total > 0) {
                                                    $localPct = round(($estadisticas['victorias_local'] ?? 0) / $total * 100);
                                                    $visitantePct = round(($estadisticas['victorias_visitante'] ?? 0) / $total * 100);
                                                    $empatesPct = round(($estadisticas['empates'] ?? 0) / $total * 100);
                                                } else {
                                                    $localPct = $visitantePct = $empatesPct = 0;
                                                }
                                            @endphp
                                            <div class="progress-bar bg-success" style="width: {{ $localPct }}%">
                                                {{ $localPct }}%
                                            </div>
                                            <div class="progress-bar bg-danger" style="width: {{ $visitantePct }}%">
                                                {{ $visitantePct }}%
                                            </div>
                                            <div class="progress-bar bg-warning" style="width: {{ $empatesPct }}%">
                                                {{ $empatesPct }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Gráficos -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0">
                                    <i class="bi bi-graph-up me-2"></i> Evolución de la Temporada
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <canvas id="golesChart" height="200"></canvas>
                                    </div>
                                    <div class="col-md-6">
                                        <canvas id="asistenciaChart" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estadísticas Avanzadas -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0">
                                    <i class="bi bi-award me-2"></i> Récords y Logros
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="p-3 bg-light rounded">
                                            <h4 class="text-primary mb-2">Mayor Goleada</h4>
                                            <h3 class="mb-1">{{ $records['mayor_goleada'] ?? 'N/A' }}</h3>
                                            <small class="text-muted">Resultado más abultado</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="p-3 bg-light rounded">
                                            <h4 class="text-success mb-2">Partido con Más Goles</h4>
                                            <h3 class="mb-1">{{ $records['mas_goles'] ?? 'N/A' }}</h3>
                                            <small class="text-muted">Total de goles en un partido</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center mb-3">
                                        <div class="p-3 bg-light rounded">
                                            <h4 class="text-warning mb-2">Mejor Racha</h4>
                                            <h3 class="mb-1">{{ $records['mejor_racha'] ?? 'N/A' }}</h3>
                                            <small class="text-muted">Victorias consecutivas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración -->
                    <div class="tab-pane fade" id="configuracion" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-gear me-2"></i> Configuración General
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <form id="configForm" action="{{ route('temporadas.update-config', $temporada) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Sistema de Puntuación</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" 
                                                           name="sistema_puntuacion" value="3-1-0" 
                                                           {{ $config['sistema_puntuacion'] == '3-1-0' ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        3 puntos por victoria, 1 por empate, 0 por derrota
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="radio" 
                                                           name="sistema_puntuacion" value="2-1-0" 
                                                           {{ $config['sistema_puntuacion'] == '2-1-0' ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        2 puntos por victoria, 1 por empate, 0 por derrota
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Criterios de Desempate</label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="criterios[]" value="diferencia_goles" 
                                                           {{ in_array('diferencia_goles', $config['criterios']) ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        Diferencia de goles
                                                    </label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="criterios[]" value="goles_a_favor" 
                                                           {{ in_array('goles_a_favor', $config['criterios']) ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        Goles a favor
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="criterios[]" value="enfrentamiento_directo" 
                                                           {{ in_array('enfrentamiento_directo', $config['criterios']) ? 'checked' : '' }}>
                                                    <label class="form-check-label">
                                                        Enfrentamiento directo
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Duración de Partidos</label>
                                                <input type="number" class="form-control" 
                                                       name="duracion_partido" 
                                                       value="{{ $config['duracion_partido'] ?? 90 }}"
                                                       min="60" max="120" step="5">
                                                <small class="form-text text-muted">Duración en minutos</small>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="bi bi-save me-1"></i> Guardar Configuración
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-shield-exclamation me-2"></i> Acciones Peligrosas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-warning">
                                            <h6 class="alert-heading">
                                                <i class="bi bi-exclamation-triangle me-2"></i> ¡Precaución!
                                            </h6>
                                            <p class="mb-0">
                                                Las siguientes acciones pueden afectar permanentemente la temporada.
                                            </p>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#recalcularModal">
                                                <i class="bi bi-calculator me-1"></i> Recalcular Clasificación
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#reiniciarModal">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reiniciar Temporada
                                            </button>
                                            <button class="btn btn-outline-secondary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#exportarModal">
                                                <i class="bi bi-download me-1"></i> Exportar Datos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-white border-bottom">
                                        <h6 class="mb-0">
                                            <i class="bi bi-clock-history me-2"></i> Historial de Cambios
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($historial->count() > 0)
                                            <div class="timeline">
                                                @foreach($historial as $cambio)
                                                    <div class="timeline-item mb-3">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0">
                                                                <div class="bg-{{ $cambio->tipo_color }} text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                                     style="width: 36px; height: 36px;">
                                                                    <i class="bi bi-{{ $cambio->tipo_icon }}"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h6 class="mb-1">{{ $cambio->descripcion }}</h6>
                                                                <p class="mb-1 small">{{ $cambio->detalles }}</p>
                                                                <small class="text-muted">
                                                                    <i class="bi bi-person me-1"></i>{{ $cambio->usuario->name }}
                                                                    <i class="bi bi-clock ms-2 me-1"></i>{{ $cambio->created_at->diffForHumans() }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-3">
                                                <p class="text-muted mb-0">No hay cambios registrados</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales -->
@include('temporadas.modals.agregar-equipos')
@include('temporadas.modals.cambiar-estado')
@include('temporadas.modals.eliminar')
@include('temporadas.modals.recalcular')
@include('temporadas.modals.reiniciar')
@include('temporadas.modals.exportar')

@push('styles')
<style>
    .timeline {
        position: relative;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 18px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
    }
    
    .timeline-item .flex-shrink-0 {
        position: relative;
        z-index: 1;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 1rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        background-color: transparent;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    
    .card.border {
        transition: all 0.3s ease;
    }
    
    .card.border:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Persistencia de pestañas
        $('#temporadaTabs button').on('click', function() {
            const tabId = $(this).attr('id');
            localStorage.setItem('lastTemporadaTab', tabId);
        });
        
        // Restaurar última pestaña activa
        const lastTab = localStorage.getItem('lastTemporadaTab');
        if (lastTab) {
            const tab = document.querySelector(`#${lastTab}`);
            if (tab) {
                new bootstrap.Tab(tab).show();
            }
        }
        
        // Inicializar gráficos
        initCharts();
        
        // Filtrar partidos
        $('.filter-partidos').on('click', function(e) {
            e.preventDefault();
            const estado = $(this).data('estado');
            
            $.ajax({
                url: "{{ route('temporadas.partidos.filtrados', $temporada) }}",
                method: 'GET',
                data: { estado: estado },
                success: function(response) {
                    $('#partidosContainer').html(response);
                },
                error: function() {
                    toastr.error('Error al filtrar partidos');
                }
            });
        });
        
        // Generar jornadas automáticamente
        $('#generarJornadasAuto').on('click', function() {
            if (confirm('¿Generar jornadas automáticamente? Esto creará jornadas basadas en el número de equipos.')) {
                $.ajax({
                    url: "{{ route('temporadas.generar-jornadas', $temporada) }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Error al generar jornadas');
                    }
                });
            }
        });
        
        // Quitar equipo de la temporada
        $('.quitar-equipo').on('click', function() {
            const equipoId = $(this).data('equipo-id');
            const equipoNombre = $(this).data('equipo-nombre');
            
            if (confirm(`¿Quitar a ${equipoNombre} de la temporada?`)) {
                $.ajax({
                    url: "{{ route('temporadas.quitar-equipo', $temporada) }}",
                    method: 'POST',
                    data: {
                        equipo_id: equipoId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Error al quitar equipo');
                    }
                });
            }
        });
        
        // Guardar configuración
        $('#configForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function(field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Error al guardar configuración');
                    }
                }
            });
        });
        
        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
    
    function initCharts() {
        // Gráfico de goles por jornada
        const golesCtx = document.getElementById('golesChart').getContext('2d');
        new Chart(golesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($charts['goles']['jornadas']) !!},
                datasets: [{
                    label: 'Goles por Jornada',
                    data: {!! json_encode($charts['goles']['valores']) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Goles'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Jornadas'
                        }
                    }
                }
            }
        });
        
        // Gráfico de asistencia
        const asistenciaCtx = document.getElementById('asistenciaChart').getContext('2d');
        new Chart(asistenciaCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($charts['asistencia']['partidos']) !!},
                datasets: [{
                    label: 'Asistencia Estimada',
                    data: {!! json_encode($charts['asistencia']['valores']) !!},
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Asistencia'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Partidos'
                        }
                    }
                }
            }
        });
    }
    
    function programarPartidosJornada(jornadaId) {
        $.ajax({
            url: `/jornadas/${jornadaId}/programar-partidos`,
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error al programar partidos');
            }
        });
    }
</script>
@endpush
@endsection