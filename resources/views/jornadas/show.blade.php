@extends('layouts.app')

@section('title', $jornada->nombre)

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h1 class="mb-1">
                                <i class="fas fa-calendar-week"></i> {{ $jornada->nombre }}
                                @if($jornada->especial)
                                    <span class="badge bg-warning fs-6">Especial</span>
                                @endif
                            </h1>
                            <p class="mb-0">
                                <i class="fas fa-trophy"></i> {{ $jornada->temporada->nombre }} |
                                <i class="fas fa-calendar-alt"></i> 
                                {{ $jornada->fecha_inicio->format('d/m/Y') }} - {{ $jornada->fecha_fin->format('d/m/Y') }}
                            </p>
                            @if($jornada->descripcion)
                                <p class="mt-2 mb-0"><i class="fas fa-info-circle"></i> {{ $jornada->descripcion }}</p>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="btn-group" role="group">
                                @if($jornadasAdyacentes['anterior'])
                                    <a href="{{ route('jornadas.show', $jornadasAdyacentes['anterior']->id) }}" 
                                       class="btn btn-light">
                                        <i class="fas fa-arrow-left"></i> Anterior
                                    </a>
                                @endif
                                
                                <a href="{{ route('jornadas.index', ['temporada_id' => $jornada->temporada_id]) }}" 
                                   class="btn btn-light">
                                    <i class="fas fa-list"></i> Todas
                                </a>
                                
                                @if($jornadasAdyacentes['siguiente'])
                                    <a href="{{ route('jornadas.show', $jornadasAdyacentes['siguiente']->id) }}" 
                                       class="btn btn-light">
                                        Siguiente <i class="fas fa-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                            
                            <!-- Estado -->
                            <div class="mt-3">
                                @if($jornada->completada)
                                    <span class="badge bg-success fs-6 p-2">
                                        <i class="fas fa-check-circle"></i> Completada
                                    </span>
                                    <p class="mb-0 small">
                                        {{ $jornada->fecha_completada->format('d/m/Y H:i') }}
                                    </p>
                                @elseif(now()->between($jornada->fecha_inicio, $jornada->fecha_fin))
                                    <span class="badge bg-primary fs-6 p-2">
                                        <i class="fas fa-running"></i> En Curso
                                    </span>
                                @elseif(now()->gt($jornada->fecha_fin))
                                    <span class="badge bg-warning fs-6 p-2">
                                        <i class="fas fa-clock"></i> Vencida
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6 p-2">
                                        <i class="fas fa-calendar"></i> Programada
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna izquierda: Partidos -->
        <div class="col-md-8">
            <!-- Estadísticas rápidas -->
            @if($estadisticasJornada['total_partidos'] > 0)
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ $estadisticasJornada['total_partidos'] }}</h3>
                                <p>Total Partidos</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-futbol"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ $estadisticasJornada['partidos_finalizados'] }}</h3>
                                <p>Finalizados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $estadisticasJornada['total_goles'] }}</h3>
                                <p>Total Goles</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-futbol"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 col-sm-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>{{ $estadisticasJornada['promedio_goles'] }}</h3>
                                <p>Promedio por Partido</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lista de partidos -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-futbol"></i> Partidos de la Jornada
                        <span class="badge bg-primary ms-2">{{ $jornada->partidos->count() }}</span>
                    </h5>
                    
                    <div class="btn-group">
                        <a href="{{ route('partidos.create', ['jornada_id' => $jornada->id]) }}" 
                           class="btn btn-sm btn-success">
                            <i class="fas fa-plus"></i> Agregar Partido
                        </a>
                        
                        @if(!$jornada->completada)
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#reorganizarModal">
                                <i class="fas fa-sort"></i> Reorganizar
                            </button>
                        @endif
                    </div>
                </div>
                
                <div class="card-body p-0">
                    @if($jornada->partidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="100">Hora</th>
                                        <th>Local</th>
                                        <th width="50" class="text-center">VS</th>
                                        <th>Visitante</th>
                                        <th width="100">Resultado</th>
                                        <th width="100">Estado</th>
                                        <th width="120" class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jornada->partidos->sortBy('fecha_hora') as $partido)
                                        <tr class="{{ $partido->estado == 'Jugando' ? 'table-primary' : '' }}">
                                            <td class="align-middle">
                                                <strong>{{ $partido->fecha_hora->format('H:i') }}</strong><br>
                                                <small class="text-muted">{{ $partido->fecha_hora->format('d/m') }}</small>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    @if($partido->equipoLocal->escudo)
                                                        <img src="{{ asset('storage/' . $partido->equipoLocal->escudo) }}" 
                                                             alt="{{ $partido->equipoLocal->nombre }}" 
                                                             class="img-thumbnail me-2" style="width: 30px; height: 30px;">
                                                    @endif
                                                    <strong>{{ $partido->equipoLocal->nombre }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-secondary">VS</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center">
                                                    @if($partido->equipoVisitante->escudo)
                                                        <img src="{{ asset('storage/' . $partido->equipoVisitante->escudo) }}" 
                                                             alt="{{ $partido->equipoVisitante->nombre }}" 
                                                             class="img-thumbnail me-2" style="width: 30px; height: 30px;">
                                                    @endif
                                                    <strong>{{ $partido->equipoVisitante->nombre }}</strong>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($partido->estado == 'Finalizado')
                                                    <span class="badge bg-dark fs-6">
                                                        {{ $partido->goles_local }} - {{ $partido->goles_visitante }}
                                                    </span>
                                                @elseif($partido->estado == 'Jugando')
                                                    <span class="badge bg-danger fs-6">LIVE</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @switch($partido->estado)
                                                    @case('Programado')
                                                        <span class="badge bg-secondary">Programado</span>
                                                        @break
                                                    @case('Jugando')
                                                        <span class="badge bg-danger">En Juego</span>
                                                        @break
                                                    @case('Finalizado')
                                                        <span class="badge bg-success">Finalizado</span>
                                                        @break
                                                    @case('Suspendido')
                                                        <span class="badge bg-warning">Suspendido</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('partidos.show', $partido->id) }}" 
                                                       class="btn btn-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(in_array($partido->estado, ['Programado', 'Suspendido']))
                                                        <a href="{{ route('partidos.edit', $partido->id) }}" 
                                                           class="btn btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    @if($partido->estado == 'Programado')
                                                        <form action="{{ route('partidos.iniciar', $partido->id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success" title="Iniciar">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-futbol fa-3x text-muted mb-3"></i>
                            <h5>No hay partidos programados</h5>
                            <p class="text-muted">Agrega los partidos de esta jornada.</p>
                            <a href="{{ route('partidos.create', ['jornada_id' => $jornada->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Primer Partido
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Partidos destacados -->
            @if($partidosDestacados['mas_goles'] || $partidosDestacados['mas_cerrado'] || $partidosDestacados['derby'])
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5><i class="fas fa-star"></i> Partidos Destacados</h5>
                        <div class="row">
                            @if($partidosDestacados['mas_goles'])
                                <div class="col-md-4">
                                    <div class="card bg-gradient-info text-white">
                                        <div class="card-body text-center">
                                            <h6><i class="fas fa-bullseye"></i> Más Goleador</h6>
                                            <h4>
                                                {{ $partidosDestacados['mas_goles']->goles_local + $partidosDestacados['mas_goles']->goles_visitante }} goles
                                            </h4>
                                            <p class="mb-0 small">
                                                {{ $partidosDestacados['mas_goles']->equipoLocal->nombre }} vs 
                                                {{ $partidosDestacados['mas_goles']->equipoVisitante->nombre }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($partidosDestacados['mas_cerrado'])
                                <div class="col-md-4">
                                    <div class="card bg-gradient-primary text-white">
                                        <div class="card-body text-center">
                                            <h6><i class="fas fa-balance-scale"></i> Más Cerrado</h6>
                                            <h4>
                                                {{ abs($partidosDestacados['mas_cerrado']->goles_local - $partidosDestacados['mas_cerrado']->goles_visitante) }} diferencia
                                            </h4>
                                            <p class="mb-0 small">
                                                {{ $partidosDestacados['mas_cerrado']->equipoLocal->nombre }} vs 
                                                {{ $partidosDestacados['mas_cerrado']->equipoVisitante->nombre }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($partidosDestacados['derby'])
                                <div class="col-md-4">
                                    <div class="card bg-gradient-danger text-white">
                                        <div class="card-body text-center">
                                            <h6><i class="fas fa-trophy"></i> Derby</h6>
                                            <h4>
                                                {{ $partidosDestacados['derby']->goles_local }} - {{ $partidosDestacados['derby']->goles_visitante }}
                                            </h4>
                                            <p class="mb-0 small">
                                                {{ $partidosDestacados['derby']->equipoLocal->nombre }} vs 
                                                {{ $partidosDestacados['derby']->equipoVisitante->nombre }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Información y clasificación -->
        <div class="col-md-4">
            <!-- Acciones -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cogs"></i> Acciones</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$jornada->completada)
                            <a href="{{ route('jornadas.edit', $jornada->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Jornada
                            </a>
                            
                            @if($jornada->partidos->where('estado', 'Finalizado')->count() == $jornada->partidos->count())
                                <form action="{{ route('jornadas.completar', $jornada->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle"></i> Marcar como Completada
                                    </button>
                                </form>
                            @endif
                            
                            <button type="button" class="btn btn-info" data-bs-toggle="modal" 
                                    data-bs-target="#duplicarModal">
                                <i class="fas fa-copy"></i> Duplicar Jornada
                            </button>
                        @endif
                        
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle w-100" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li>
                                    <a class="dropdown-item" 
                                       href="{{ route('jornadas.exportar', ['jornada' => $jornada->id, 'formato' => 'pdf']) }}">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" 
                                       href="{{ route('jornadas.exportar', ['jornada' => $jornada->id, 'formato' => 'excel']) }}">
                                        <i class="fas fa-file-excel"></i> Excel
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" 
                                       href="{{ route('jornadas.exportar', ['jornada' => $jornada->id, 'formato' => 'csv']) }}">
                                        <i class="fas fa-file-csv"></i> CSV
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        @if($jornada->partidos->count() == 0)
                            <form action="{{ route('jornadas.destroy', $jornada->id) }}" method="POST" 
                                  onsubmit="return confirm('¿Eliminar esta jornada?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Eliminar Jornada
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cambios en clasificación -->
            @if(!empty($cambiosPosiciones))
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-line"></i> Cambios en Clasificación</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th class="text-center">Inicio</th>
                                        <th class="text-center">Fin</th>
                                        <th class="text-center">Cambio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cambiosPosiciones as $cambio)
                                        @if($cambio['cambio'] != 0)
                                            <tr>
                                                <td>
                                                    <small>{{ $cambio['equipo_nombre'] }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary">#{{ $cambio['posicion_inicio'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary">#{{ $cambio['posicion_fin'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if($cambio['cambio'] > 0)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-arrow-up"></i> +{{ $cambio['cambio'] }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-arrow-down"></i> {{ $cambio['cambio'] }}
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Información adicional -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-calendar-plus text-primary me-2"></i>
                            <strong>Creada:</strong> 
                            {{ $jornada->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-check text-success me-2"></i>
                            <strong>Actualizada:</strong> 
                            {{ $jornada->updated_at->format('d/m/Y H:i') }}
                        </li>
                        @if($jornada->notas)
                            <li class="mb-2">
                                <i class="fas fa-sticky-note text-warning me-2"></i>
                                <strong>Notas:</strong><br>
                                <small class="text-muted">{{ $jornada->notas }}</small>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para duplicar jornada -->
<div class="modal fade" id="duplicarModal" tabindex="-1" aria-labelledby="duplicarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicarModalLabel">
                    <i class="fas fa-copy"></i> Duplicar Jornada
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Crear una copia de esta jornada con los mismos partidos?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    Los partidos se crearán programados para 7 días después.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('jornadas.duplicar', $jornada->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary">Duplicar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para reorganizar -->
<div class="modal fade" id="reorganizarModal" tabindex="-1" aria-labelledby="reorganizarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reorganizarModalLabel">
                    <i class="fas fa-sort"></i> Reorganizar Partidos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reorganizarForm">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Partido</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estadio</th>
                                </tr>
                            </thead>
                            <tbody id="partidosContainer">
                                <!-- Los partidos se cargarán aquí con JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarReorganizacion">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar partidos para reorganizar
        const reorganizarModal = document.getElementById('reorganizarModal');
        if (reorganizarModal) {
            reorganizarModal.addEventListener('show.bs.modal', function() {
                fetch(`/jornadas/{{ $jornada->id }}/partidos`)
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('partidosContainer');
                        container.innerHTML = '';
                        
                        data.partidos.forEach(partido => {
                            const fechaHora = new Date(partido.fecha_hora);
                            const fechaStr = fechaHora.toISOString().slice(0, 16);
                            
                            container.innerHTML += `
                                <tr>
                                    <td>
                                        ${partido.equipo_local.nombre} vs ${partido.equipo_visitante.nombre}
                                        <input type="hidden" name="partidos[${partido.id}][id]" value="${partido.id}">
                                    </td>
                                    <td>
                                        <input type="datetime-local" class="form-control form-control-sm" 
                                               name="partidos[${partido.id}][fecha_hora]" 
                                               value="${fechaStr}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" 
                                               name="partidos[${partido.id}][estadio]" 
                                               value="${partido.estadio || ''}">
                                    </td>
                                </tr>
                            `;
                        });
                    });
            });
        }
        
        // Guardar reorganización
        const guardarBtn = document.getElementById('guardarReorganizacion');
        if (guardarBtn) {
            guardarBtn.addEventListener('click', function() {
                const form = document.getElementById('reorganizarForm');
                const formData = new FormData(form);
                const data = {};
                
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                
                fetch(`/jornadas/{{ $jornada->id }}/reorganizar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
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