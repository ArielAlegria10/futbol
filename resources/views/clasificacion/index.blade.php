@extends('layouts.app')

@section('title', 'Clasificación')
@section('icon', 'bi-trophy')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}" class="text-decoration-none text-primary">
            <i class="bi bi-house-door me-1"></i> Dashboard
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-trophy me-1"></i> Clasificación
    </li>
@endsection

@section('header-buttons')
    @if(isset($temporada) && $clasificacion->count() > 0)
    <div class="btn-group" role="group">
        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-download me-2"></i> Exportar
        </button>
        <ul class="dropdown-menu">
            <li>
                <form action="{{ route('clasificacion.exportar') }}" method="POST" class="mb-0">
                    @csrf
                    <input type="hidden" name="temporada_id" value="{{ $temporada->id }}">
                    <input type="hidden" name="formato" value="pdf">
                    <button type="submit" class="dropdown-item">
                        <i class="bi bi-file-pdf text-danger me-2"></i> PDF
                    </button>
                </form>
            </li>
            <li>
                <form action="{{ route('clasificacion.exportar') }}" method="POST" class="mb-0">
                    @csrf
                    <input type="hidden" name="temporada_id" value="{{ $temporada->id }}">
                    <input type="hidden" name="formato" value="excel">
                    <button type="submit" class="dropdown-item">
                        <i class="bi bi-file-excel text-success me-2"></i> Excel
                    </button>
                </form>
            </li>
        </ul>
    </div>
    
    <a href="{{ route('clasificacion.comparar') }}?temporada_id={{ $temporada->id }}" 
       class="btn btn-warning">
        <i class="bi bi-balance-scale me-2"></i> Comparar
    </a>
    
    <a href="{{ route('clasificacion.estadisticas-avanzadas') }}?temporada_id={{ $temporada->id }}" 
       class="btn btn-purple">
        <i class="bi bi-graph-up-arrow me-2"></i> Estadísticas
    </a>
    @endif
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-soccer">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h1 class="page-title-soccer mb-2">
                            <i class="bi bi-trophy me-3"></i>
                            Clasificación de Liga
                        </h1>
                        <p class="text-muted mb-0">
                            Tabla oficial de posiciones - Temporada 
                            <span class="fw-bold">{{ $temporada->nombre ?? 'No seleccionada' }}</span>
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @yield('header-buttons')
                        @auth
                        <form action="{{ route('clasificacion.actualizar') }}" method="POST" 
                              onsubmit="return confirm('¿Recalcular clasificación?')">
                            @csrf
                            <input type="hidden" name="temporada_id" value="{{ $temporada->id ?? '' }}">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-clockwise me-2"></i> Recalcular
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Temporada Selector -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="card-title mb-2">
                        <i class="bi bi-filter me-2"></i> Seleccionar Temporada
                    </h5>
                    <p class="text-muted mb-0">Filtra la clasificación por temporada específica</p>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('clasificacion.index') }}" method="GET">
                        <select name="temporada_id" onchange="this.form.submit()" 
                                class="form-select form-select-lg">
                            <option value="">Todas las temporadas</option>
                            @foreach($temporadas as $temporadaItem)
                                <option value="{{ $temporadaItem->id }}" 
                                        {{ ($filtrosActivos['temporada_id'] ?? '') == $temporadaItem->id ? 'selected' : '' }}>
                                    {{ $temporadaItem->nombre }} ({{ $temporadaItem->anio }})
                                    @if($temporadaItem->estado == 'En Curso')
                                        ⚡
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    @if(isset($estadisticas))
    <div class="row mb-4">
        <!-- Total Teams -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-soccer">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon-soccer bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Equipos Totales</h6>
                            <h4 class="mb-0 fw-bold">{{ $estadisticas['total_equipos'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Matches Played -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-soccer">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon-soccer bg-success bg-opacity-10 text-success">
                            <i class="bi bi-futbol"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Partidos Jugados</h6>
                            <h4 class="mb-0 fw-bold">{{ $estadisticas['total_partidos_jugados'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Goals -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-soccer">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon-soccer bg-purple bg-opacity-10 text-purple">
                            <i class="bi bi-bullseye"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Goles Totales</h6>
                            <h4 class="mb-0 fw-bold">{{ $estadisticas['total_goles'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Average Goals -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card-soccer">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon-soccer bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted mb-0">Promedio Goles</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['promedio_goles_por_partido'] ?? 0, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Highlight Cards -->
    <div class="row mb-4">
        @if(isset($estadisticas['equipo_mejor_ataque']))
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-gradient-primary text-white shadow border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-fire fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Mejor Ataque</h6>
                            <h5 class="mb-1">{{ $estadisticas['equipo_mejor_ataque']->equipo->nombre ?? 'N/A' }}</h5>
                            <p class="mb-0">
                                {{ $estadisticas['equipo_mejor_ataque']->goles_a_favor ?? 0 }} goles
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if(isset($estadisticas['equipo_mejor_defensa']))
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-gradient-info text-white shadow border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-shield-check fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Mejor Defensa</h6>
                            <h5 class="mb-1">{{ $estadisticas['equipo_mejor_defensa']->equipo->nombre ?? 'N/A' }}</h5>
                            <p class="mb-0">
                                {{ $estadisticas['equipo_mejor_defensa']->goles_en_contra ?? 0 }} goles encajados
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        @if(isset($estadisticas['equipo_mejor_rendimiento']))
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card bg-gradient-success text-white shadow border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-graph-up-arrow fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-white-50 mb-1">Mejor Rendimiento</h6>
                            <h5 class="mb-1">{{ $estadisticas['equipo_mejor_rendimiento']->equipo->nombre ?? 'N/A' }}</h5>
                            <p class="mb-0">
                                {{ $estadisticas['equipo_mejor_rendimiento']->rendimiento ?? 0 }}% efectividad
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Classification Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-table me-2"></i> Tabla de Posiciones
                    </h5>
                    @if(isset($temporada))
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($temporada->fecha_inicio)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($temporada->fecha_fin)->format('d M Y') }}
                        </small>
                    @endif
                </div>
                <div>
                    <span class="badge bg-success me-2">
                        <i class="bi bi-trophy"></i> Clasificación
                    </span>
                    <span class="badge bg-danger">
                        <i class="bi bi-arrow-down"></i> Descenso
                    </span>
                </div>
            </div>
        </div>
        
        @if(isset($clasificacion) && $clasificacion->count() > 0)
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3" style="width: 50px;">#</th>
                            <th class="py-3">Equipo</th>
                            <th class="text-center py-3">PJ</th>
                            <th class="text-center py-3">PG</th>
                            <th class="text-center py-3">PE</th>
                            <th class="text-center py-3">PP</th>
                            <th class="text-center py-3">GF</th>
                            <th class="text-center py-3">GC</th>
                            <th class="text-center py-3">DG</th>
                            <th class="text-center py-3 fw-bold">PTS</th>
                            <th class="text-center py-3">Forma</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clasificacion as $index => $item)
                        <tr class="{{ $index < 3 ? 'table-success' : ($index >= count($clasificacion) - 3 ? 'table-danger' : '') }}">
                            <td class="ps-4 py-3">
                                <div class="position-container">
                                    <span class="position-badge 
                                        {{ $index < 3 ? 'bg-success text-white' : 
                                           ($index >= count($clasificacion) - 3 ? 'bg-danger text-white' : 'bg-light text-dark') }}">
                                        {{ $item->posicion ?? $index + 1 }}
                                    </span>
                                    @if($index == 0)
                                        <span class="crown-badge" data-bs-toggle="tooltip" title="Líder del campeonato">
                                            <i class="bi bi-crown-fill text-warning"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        @if(isset($item->equipo) && $item->equipo->escudo_url)
                                            <img src="{{ $item->equipo->escudo_url }}" 
                                                 alt="{{ $item->equipo->nombre }}"
                                                 class="rounded-circle border" 
                                                 style="width: 36px; height: 36px; object-fit: cover;">
                                        @else
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 36px; height: 36px;">
                                                <i class="bi bi-people text-primary"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">
                                            <a href="{{ route('clasificacion.show', $item->id) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $item->equipo->nombre ?? 'Equipo' }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $item->equipo->abreviacion ?? '' }}</small>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="text-center py-3">
                                <span class="fw-medium">{{ $item->partidos_jugados ?? 0 }}</span>
                            </td>
                            
                            <td class="text-center py-3">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    {{ $item->partidos_ganados ?? 0 }}
                                </span>
                            </td>
                            
                            <td class="text-center py-3">
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    {{ $item->partidos_empatados ?? 0 }}
                                </span>
                            </td>
                            
                            <td class="text-center py-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger">
                                    {{ $item->partidos_perdidos ?? 0 }}
                                </span>
                            </td>
                            
                            <td class="text-center py-3 fw-bold">
                                {{ $item->goles_a_favor ?? 0 }}
                            </td>
                            
                            <td class="text-center py-3">
                                {{ $item->goles_en_contra ?? 0 }}
                            </td>
                            
                            <td class="text-center py-3 fw-bold">
                                <span class="{{ $item->diferencia_goles > 0 ? 'text-success' : 
                                               ($item->diferencia_goles < 0 ? 'text-danger' : 'text-muted') }}">
                                    {{ $item->diferencia_goles > 0 ? '+' : '' }}{{ $item->diferencia_goles ?? 0 }}
                                </span>
                            </td>
                            
                            <td class="text-center py-3">
                                <span class="badge bg-primary px-3 py-2 fw-bold fs-6">
                                    {{ $item->puntos ?? 0 }}
                                </span>
                                @if(isset($item->rendimiento))
                                    <div class="text-muted small mt-1">{{ $item->rendimiento }}%</div>
                                @endif
                            </td>
                            
                            <td class="text-center py-3">
                                <div class="d-flex justify-content-center gap-1">
                                    @php
                                        $ultimosPartidos = isset($item->ultima_racha['partidos']) && !empty($item->ultima_racha['partidos']) 
                                            ? array_slice($item->ultima_racha['partidos'], -5) 
                                            : ['W', 'D', 'W', 'L', 'W'];
                                    @endphp
                                    @foreach($ultimosPartidos as $resultado)
                                        <span class="result-badge 
                                            {{ $resultado == 'W' ? 'bg-success text-white' : 
                                               ($resultado == 'D' ? 'bg-warning text-dark' : 'bg-danger text-white') }}">
                                            {{ $resultado }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="card-footer bg-white border-top py-3">
            <div class="row">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success me-2">Champions</span>
                        <small class="text-muted">Clasificación a fase de campeonato</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger me-2">Descenso</span>
                        <small class="text-muted">Zona de descenso directo</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-crown-fill text-warning me-2"></i>
                        <small class="text-muted">Líder del campeonato</small>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Empty State -->
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-trophy text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted mb-3">No hay datos de clasificación</h4>
            <p class="text-muted mb-4">No se encontraron datos para la temporada seleccionada</p>
            @if(isset($temporadas) && $temporadas->count() > 0)
                <a href="{{ route('temporadas.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i> Crear nueva temporada
                </a>
            @endif
        </div>
        @endif
    </div>

    <!-- Top Scorers -->
    @if(isset($topGoleadores) && $topGoleadores->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-stars me-2"></i> Top Goleadores
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($topGoleadores->take(6) as $index => $goleador)
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card border h-100 hover-shadow">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <span class="scorer-rank 
                                                {{ $index == 0 ? 'gold' : 
                                                   ($index == 1 ? 'silver' : 
                                                   ($index == 2 ? 'bronze' : 'normal')) }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">{{ $goleador['nombre'] ?? 'Jugador' }}</h6>
                                            <div class="d-flex align-items-center">
                                                <small class="text-muted me-3">{{ $goleador['equipo'] ?? 'Equipo' }}</small>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $goleador['partidos'] ?? 0 }} PJ
                                                </small>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div class="text-end">
                                                <h3 class="mb-0 text-primary">{{ $goleador['goles'] ?? 0 }}</h3>
                                                <small class="text-muted">goles</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>Promedio:</span>
                                            <span class="fw-semibold">
                                                {{ isset($goleador['goles']) && isset($goleador['partidos']) && $goleador['partidos'] > 0 ? 
                                                   round($goleador['goles'] / $goleador['partidos'], 2) : 0 }} goles/partido
                                            </span>
                                        </div>
                                        @php
                                            $maxGoles = $topGoleadores->max('goles') ?? 1;
                                            $widthPercentage = isset($goleador['goles']) ? min(100, ($goleador['goles'] / $maxGoles) * 100) : 0;
                                        @endphp
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: {{ $widthPercentage }}%">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($topGoleadores->count() > 6)
                    <div class="text-center mt-4">
                        <a href="#" class="btn btn-outline-primary">
                            Ver todos los goleadores <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Position Evolution -->
    @if(isset($evolucionPosiciones) && !empty($evolucionPosiciones))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0">
                        <i class="bi bi-graph-up me-2"></i> Evolución de Posiciones
                    </h5>
                    <small class="text-muted">Seguimiento de la posición de cada equipo durante la temporada</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Equipo</th>
                                    <th class="text-center">Posición Actual</th>
                                    <th class="text-center">Mejor Posición</th>
                                    <th class="text-center">Peor Posición</th>
                                    <th class="text-center">Tendencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($evolucionPosiciones as $evolucion)
                                @if(isset($evolucion['equipo']))
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if(isset($evolucion['equipo']->escudo_url))
                                                <img src="{{ $evolucion['equipo']->escudo_url }}" 
                                                     alt="{{ $evolucion['equipo']->nombre }}"
                                                     class="rounded-circle me-3" 
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                            @endif
                                            <span class="fw-medium">{{ $evolucion['equipo']->nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $posicionActual = $clasificacion->firstWhere('equipo_id', $evolucion['equipo']->id)->posicion ?? 'N/A';
                                        @endphp
                                        <span class="position-indicator 
                                            {{ $posicionActual <= 3 ? 'bg-success' : 
                                               ($posicionActual >= count($clasificacion) - 2 ? 'bg-danger' : 'bg-secondary') }}">
                                            {{ $posicionActual }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $evolucion['mejor_posicion'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $evolucion['peor_posicion'] ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $tendencia = $evolucion['tendencia'] ?? 'estable';
                                            $tendenciaIcon = $tendencia == 'ascendente' ? 'bi-arrow-up' : 
                                                           ($tendencia == 'descendente' ? 'bi-arrow-down' : 'bi-dash');
                                            $tendenciaColor = $tendencia == 'ascendente' ? 'text-success' : 
                                                            ($tendencia == 'descendente' ? 'text-danger' : 'text-muted');
                                        @endphp
                                        <span class="{{ $tendenciaColor }}">
                                            <i class="bi {{ $tendenciaIcon }} me-1"></i>
                                            {{ ucfirst($tendencia) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Custom colors */
    .bg-purple {
        background-color: #6f42c1 !important;
    }
    
    .text-purple {
        color: #6f42c1 !important;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .bg-gradient-success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    
    /* Position badges */
    .position-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 0.875rem;
    }
    
    .position-container {
        position: relative;
        display: inline-block;
    }
    
    .crown-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: white;
        border-radius: 50%;
        padding: 2px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Result badges */
    .result-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    /* Scorer ranks */
    .scorer-rank {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        font-weight: bold;
        font-size: 1.25rem;
    }
    
    .scorer-rank.gold {
        background: linear-gradient(135deg, #FFD700, #FFED4E);
        color: #000;
    }
    
    .scorer-rank.silver {
        background: linear-gradient(135deg, #C0C0C0, #E0E0E0);
        color: #000;
    }
    
    .scorer-rank.bronze {
        background: linear-gradient(135deg, #CD7F32, #E6A756);
        color: #000;
    }
    
    .scorer-rank.normal {
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
    
    /* Position indicators */
    .position-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        color: white;
        font-weight: bold;
    }
    
    /* Hover effects */
    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    /* Table row hover */
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    /* Progress bar custom */
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Auto-refresh every 60 seconds
        let refreshInterval = setInterval(function() {
            if (!document.hidden) {
                location.reload();
            }
        }, 60000);
        
        // Stop auto-refresh when tab is not active
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(refreshInterval);
            } else {
                refreshInterval = setInterval(function() {
                    location.reload();
                }, 60000);
            }
        });
        
        // Smooth scroll for table
        $('table').on('mouseenter', 'tr', function() {
            $(this).css('transition', 'all 0.2s ease');
        });
        
        // Season selector enhancement
        $('select[name="temporada_id"]').on('change', function() {
            $(this).addClass('loading');
            setTimeout(() => {
                $(this).removeClass('loading');
            }, 1000);
        });
        
        // Export buttons loading state
        $('form[action*="exportar"]').on('submit', function() {
            $(this).find('button').prop('disabled', true).html('<i class="bi bi-hourglass-split me-2"></i> Generando...');
        });
    });
</script>
@endpush