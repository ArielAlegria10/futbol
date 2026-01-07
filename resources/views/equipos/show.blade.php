@extends('layouts.app')

@section('title', $equipo->nombre)
@section('icon', 'bi-people-fill')
@section('subtitle', 'Detalles y estadísticas del equipo')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('equipos.index') }}">Equipos</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{ $equipo->abreviacion }}</li>
@endsection

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                data-bs-toggle="dropdown">
            <span class="visually-hidden">Más opciones</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <button class="dropdown-item toggle-status" 
                        data-id="{{ $equipo->id }}"
                        data-status="{{ $equipo->activo ? 'active' : 'inactive' }}">
                    <i class="bi bi-{{ $equipo->activo ? 'x-circle' : 'check-circle' }} me-2"></i>
                    {{ $equipo->activo ? 'Desactivar' : 'Activar' }} Equipo
                </button>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('equipos.estadisticas', $equipo) }}">
                    <i class="bi bi-bar-chart me-2"></i> Estadísticas Avanzadas
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('equipos.partidos', $equipo) }}">
                    <i class="bi bi-calendar-event me-2"></i> Ver Partidos
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button class="dropdown-item text-danger" data-bs-toggle="modal" 
                        data-bs-target="#deleteModal">
                    <i class="bi bi-trash me-2"></i> Eliminar Equipo
                </button>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i> Información del Equipo
                </h5>
            </div>
            <div class="card-body text-center">
                <!-- Escudo -->
                <div class="mb-4">
                    @if($equipo->escudo_url)
                        <img src="{{ $equipo->escudo_url }}" 
                             alt="{{ $equipo->nombre }}" 
                             class="img-thumbnail rounded-circle border-3 border-primary"
                             style="width: 150px; height: 150px; object-fit: cover;"
                             onerror="this.src='{{ asset('img/default-team.png') }}'">
                    @else
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border-3 border-primary" 
                             style="width: 150px; height: 150px;">
                            <i class="bi bi-shield text-primary" style="font-size: 4rem;"></i>
                        </div>
                    @endif
                </div>
                
                <!-- Nombre y Abreviación -->
                <h2 class="mb-1">{{ $equipo->nombre }}</h2>
                <h4 class="text-muted mb-4">{{ $equipo->abreviacion }}</h4>
                
                <!-- Estado -->
                <div class="mb-4">
                    <span class="badge bg-{{ $equipo->activo ? 'success' : 'danger' }} fs-6">
                        <i class="bi bi-{{ $equipo->activo ? 'check-circle' : 'x-circle' }} me-1"></i>
                        {{ $equipo->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                
                <!-- Colores del Equipo -->
                @if($equipo->colores && count($equipo->colores) > 0)
                    <div class="mb-4">
                        <h6 class="mb-2">Colores Representativos</h6>
                        <div class="d-flex justify-content-center">
                            @foreach($equipo->colores as $color)
                                <div class="color-badge mx-1" 
                                     style="background-color: {{ $color }}; width: 30px; height: 30px; border-radius: 50%;"
                                     data-bs-toggle="tooltip" 
                                     title="{{ $color }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Datos de Contacto -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="bi bi-geo-alt me-2"></i> Ubicación
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <i class="bi bi-building text-primary me-2"></i>
                        <strong>Ciudad:</strong>
                        <span class="float-end">{{ $equipo->ciudad }}</span>
                    </li>
                    @if($equipo->estadio)
                        <li class="mb-3">
                            <i class="bi bi-house-door text-primary me-2"></i>
                            <strong>Estadio:</strong>
                            <span class="float-end">{{ $equipo->estadio }}</span>
                        </li>
                    @endif
                    @if($equipo->capacidad_estadio)
                        <li class="mb-3">
                            <i class="bi bi-people text-primary me-2"></i>
                            <strong>Capacidad:</strong>
                            <span class="float-end">{{ number_format($equipo->capacidad_estadio) }} espectadores</span>
                        </li>
                    @endif
                    @if($equipo->fundacion)
                        <li class="mb-3">
                            <i class="bi bi-calendar-event text-primary me-2"></i>
                            <strong>Fundación:</strong>
                            <span class="float-end">{{ $equipo->fundacion }}</span>
                        </li>
                    @endif
                    @if($equipo->presidente)
                        <li class="mb-3">
                            <i class="bi bi-person-badge text-primary me-2"></i>
                            <strong>Presidente:</strong>
                            <span class="float-end">{{ $equipo->presidente }}</span>
                        </li>
                    @endif
                    @if($equipo->entrenador)
                        <li>
                            <i class="bi bi-person-video3 text-primary me-2"></i>
                            <strong>Entrenador:</strong>
                            <span class="float-end">{{ $equipo->entrenador }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas y Contenido Principal -->
    <div class="col-lg-8">
        <!-- Pestañas -->
        <div class="card shadow mb-4">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs" id="equipoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="estadisticas-tab" data-bs-toggle="tab" 
                                data-bs-target="#estadisticas" type="button" role="tab">
                            <i class="bi bi-bar-chart me-1"></i> Estadísticas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="partidos-tab" data-bs-toggle="tab" 
                                data-bs-target="#partidos" type="button" role="tab">
                            <i class="bi bi-calendar-event me-1"></i> Partidos
                            @if($estadisticas['total_partidos'] > 0)
                                <span class="badge bg-primary rounded-pill ms-1">
                                    {{ $estadisticas['total_partidos'] }}
                                </span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="clasificacion-tab" data-bs-toggle="tab" 
                                data-bs-target="#clasificacion" type="button" role="tab">
                            <i class="bi bi-trophy me-1"></i> Clasificaciones
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" 
                                data-bs-target="#historial" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Historial
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                <div class="tab-content" id="equipoTabsContent">
                    <!-- Estadísticas -->
                    <div class="tab-pane fade show active" id="estadisticas" role="tabpanel">
                        <div class="row">
                            <!-- Estadísticas Generales -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-graph-up me-2"></i> Estadísticas Generales
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-primary">
                                                    {{ $estadisticas['total_partidos'] }}
                                                </div>
                                                <small class="text-muted">Partidos Totales</small>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $estadisticas['victorias'] }}
                                                </div>
                                                <small class="text-muted">Victorias</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="display-6 fw-bold text-warning">
                                                    {{ $estadisticas['derrotas'] }}
                                                </div>
                                                <small class="text-muted">Derrotas</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="display-6 fw-bold text-info">
                                                    {{ $estadisticas['empates'] }}
                                                </div>
                                                <small class="text-muted">Empates</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Estadísticas de Goles -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-bullseye me-2"></i> Estadísticas de Goles
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-success">
                                                    {{ $estadisticas['goles_a_favor'] }}
                                                </div>
                                                <small class="text-muted">Goles a Favor</small>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <div class="display-6 fw-bold text-danger">
                                                    {{ $estadisticas['goles_en_contra'] }}
                                                </div>
                                                <small class="text-muted">Goles en Contra</small>
                                            </div>
                                            <div class="col-12">
                                                <div class="display-6 fw-bold text-primary">
                                                    {{ $estadisticas['diferencia_goles'] }}
                                                </div>
                                                <small class="text-muted">Diferencia de Goles</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Porcentajes y Métricas -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-percent me-2"></i> Porcentajes de Rendimiento
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" 
                                                         role="progressbar" 
                                                         style="width: {{ $estadisticas['porcentaje_victorias'] }}%;"
                                                         aria-valuenow="{{ $estadisticas['porcentaje_victorias'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $estadisticas['porcentaje_victorias'] }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">Porcentaje de Victorias</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-primary" 
                                                         role="progressbar" 
                                                         style="width: {{ $estadisticas['porcentaje_puntos'] ?? 0 }}%;"
                                                         aria-valuenow="{{ $estadisticas['porcentaje_puntos'] ?? 0 }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $estadisticas['porcentaje_puntos'] ?? 0 }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">Porcentaje de Puntos</small>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-info" 
                                                         role="progressbar" 
                                                         style="width: {{ $estadisticas['eficiencia'] ?? 0 }}%;"
                                                         aria-valuenow="{{ $estadisticas['eficiencia'] ?? 0 }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        {{ $estadisticas['eficiencia'] ?? 0 }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-1">Eficiencia General</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Partidos -->
                    <div class="tab-pane fade" id="partidos" role="tabpanel">
                        @if($partidos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Jornada</th>
                                            <th>Partido</th>
                                            <th class="text-center">Resultado</th>
                                            <th>Estado</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($partidos as $partido)
                                            <tr>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $partido->fecha_hora->format('d/m/Y') }}
                                                    </small>
                                                    <br>
                                                    <small>
                                                        {{ $partido->fecha_hora->format('H:i') }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        J{{ $partido->jornada->numero }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            @if($partido->equipoLocal->escudo_url)
                                                                <img src="{{ $partido->equipoLocal->escudo_url }}" 
                                                                     alt="{{ $partido->equipoLocal->nombre }}" 
                                                                     class="rounded-circle" 
                                                                     width="24" height="24">
                                                            @endif
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <small>{{ $partido->equipoLocal->abreviacion }}</small>
                                                        </div>
                                                        <div class="px-2 fw-bold">vs</div>
                                                        <div class="flex-grow-1 text-end">
                                                            <small>{{ $partido->equipoVisitante->abreviacion }}</small>
                                                        </div>
                                                        <div class="flex-shrink-0 ms-2">
                                                            @if($partido->equipoVisitante->escudo_url)
                                                                <img src="{{ $partido->equipoVisitante->escudo_url }}" 
                                                                     alt="{{ $partido->equipoVisitante->nombre }}" 
                                                                     class="rounded-circle" 
                                                                     width="24" height="24">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @if($partido->estado === 'Finalizado')
                                                        <span class="badge bg-dark fs-6">
                                                            {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                                                        </span>
                                                        <br>
                                                        <small class="{{ $partido->ganador_id === $equipo->id ? 'text-success' : ($partido->perdedor_id === $equipo->id ? 'text-danger' : 'text-warning') }}">
                                                            @if($partido->empate)
                                                                Empate
                                                            @elseif($partido->ganador_id === $equipo->id)
                                                                Victoria
                                                            @else
                                                                Derrota
                                                            @endif
                                                        </small>
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $partido->estado_color }}">
                                                        {{ $partido->estado }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('partidos.show', $partido) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación de Partidos -->
                            @if($partidos->hasPages())
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $partidos->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-calendar-x display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay partidos registrados</h5>
                                <p class="text-muted">Este equipo aún no ha participado en partidos.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Clasificaciones -->
                    <div class="tab-pane fade" id="clasificacion" role="tabpanel">
                        @if($equipo->clasificaciones->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Temporada</th>
                                            <th class="text-center">Posición</th>
                                            <th class="text-center">PJ</th>
                                            <th class="text-center">PG</th>
                                            <th class="text-center">PE</th>
                                            <th class="text-center">PP</th>
                                            <th class="text-center">GF</th>
                                            <th class="text-center">GC</th>
                                            <th class="text-center">DG</th>
                                            <th class="text-center">PTS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equipo->clasificaciones as $clasificacion)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('temporadas.show', $clasificacion->temporada) }}">
                                                        {{ $clasificacion->temporada->nombre }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $clasificacion->posicion <= 3 ? 'success' : ($clasificacion->posicion <= 6 ? 'warning' : 'secondary') }}">
                                                        {{ $clasificacion->posicion }}°
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $clasificacion->partidos_jugados }}</td>
                                                <td class="text-center">{{ $clasificacion->partidos_ganados }}</td>
                                                <td class="text-center">{{ $clasificacion->partidos_empatados }}</td>
                                                <td class="text-center">{{ $clasificacion->partidos_perdidos }}</td>
                                                <td class="text-center">{{ $clasificacion->goles_a_favor }}</td>
                                                <td class="text-center">{{ $clasificacion->goles_en_contra }}</td>
                                                <td class="text-center">{{ $clasificacion->diferencia_goles }}</td>
                                                <td class="text-center fw-bold">{{ $clasificacion->puntos }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-trophy display-4 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay clasificaciones registradas</h5>
                                <p class="text-muted">Este equipo no aparece en ninguna clasificación de temporada.</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Historial -->
                    <div class="tab-pane fade" id="historial" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-calendar-check me-2"></i> Participaciones
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="bi bi-calendar-week text-primary me-2"></i>
                                                <strong>Temporadas:</strong>
                                                <span class="float-end">{{ $equipo->temporadasActivas->count() }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-trophy text-primary me-2"></i>
                                                <strong>Campeonatos:</strong>
                                                <span class="float-end">{{ $campeonatos }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-award text-primary me-2"></i>
                                                <strong>Subcampeonatos:</strong>
                                                <span class="float-end">{{ $subcampeonatos }}</span>
                                            </li>
                                            <li>
                                                <i class="bi bi-three-dots text-primary me-2"></i>
                                                <strong>Mejor Posición:</strong>
                                                <span class="float-end">{{ $mejorPosicion }}°</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="bi bi-graph-up-arrow me-2"></i> Récords
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="bi bi-arrow-up-right text-success me-2"></i>
                                                <strong>Mayor Victoria:</strong>
                                                <span class="float-end">{{ $records['mayor_victoria'] ?? '-' }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-arrow-down-right text-danger me-2"></i>
                                                <strong>Mayor Derrota:</strong>
                                                <span class="float-end">{{ $records['mayor_derrota'] ?? '-' }}</span>
                                            </li>
                                            <li class="mb-2">
                                                <i class="bi bi-lightning text-warning me-2"></i>
                                                <strong>Racha Victorias:</strong>
                                                <span class="float-end">{{ $records['racha_victorias'] ?? '0' }}</span>
                                            </li>
                                            <li>
                                                <i class="bi bi-calendar-range text-info me-2"></i>
                                                <strong>Inscrito desde:</strong>
                                                <span class="float-end">{{ $equipo->created_at->format('d/m/Y') }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timeline de Eventos -->
                        @if($eventos->count() > 0)
                            <div class="card border-0">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="bi bi-clock-history me-2"></i> Últimos Eventos
                                    </h6>
                                    <div class="timeline">
                                        @foreach($eventos as $evento)
                                            <div class="timeline-item mb-3">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-{{ $evento['color'] }} text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 36px; height: 36px;">
                                                            <i class="bi bi-{{ $evento['icon'] }}"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1">{{ $evento['titulo'] }}</h6>
                                                        <p class="mb-1 small">{{ $evento['descripcion'] }}</p>
                                                        <small class="text-muted">
                                                            <i class="bi bi-clock me-1"></i>{{ $evento['tiempo'] }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading">
                        <i class="bi bi-exclamation-octagon me-2"></i> ¡Advertencia!
                    </h6>
                    <p class="mb-0">
                        ¿Está seguro de eliminar el equipo <strong>{{ $equipo->nombre }}</strong>?
                        Esta acción no se puede deshacer y se eliminarán todos los datos asociados.
                    </p>
                </div>
                
                @if($equipo->partidos->count() > 0)
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="bi bi-info-circle me-2"></i> Información Importante
                        </h6>
                        <p class="mb-0">
                            Este equipo tiene <strong>{{ $equipo->partidos->count() }}</strong> partidos asociados. 
                            Al eliminar el equipo, también se eliminarán estos registros.
                        </p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <form action="{{ route('equipos.destroy', $equipo) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar Equipo
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .color-badge {
        transition: transform 0.2s;
        cursor: pointer;
    }
    
    .color-badge:hover {
        transform: scale(1.1);
    }
    
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Toggle status
        $('.toggle-status').on('click', function() {
            const id = $(this).data('id');
            const currentStatus = $(this).data('status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            $.ajax({
                url: `/equipos/${id}/toggle-status`,
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al cambiar estado');
                }
            });
        });
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Color badges tooltip
        $('.color-badge').on('click', function() {
            const color = $(this).attr('style').match(/background-color: ([^;]+)/)[1];
            navigator.clipboard.writeText(color).then(() => {
                toastr.success(`Color ${color} copiado al portapapeles`);
            });
        });
        
        // Tab persistence
        $('#equipoTabs button').on('click', function() {
            const tabId = $(this).attr('id');
            localStorage.setItem('lastTab', tabId);
        });
        
        // Restore last active tab
        const lastTab = localStorage.getItem('lastTab');
        if (lastTab) {
            const tab = document.querySelector(`#${lastTab}`);
            if (tab) {
                new bootstrap.Tab(tab).show();
            }
        }
    });
</script>
@endpush
@endsection