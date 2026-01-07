@extends('layouts.app')

@section('title', 'Dashboard')
@section('icon', 'bi-dribbble')

@section('breadcrumb-items')
    <li class="breadcrumb-item active">Sistema Fútbol</li>
@endsection

@section('content')

{{-- ================= METRICAS ================= --}}
<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 bg-dark text-white h-100">
            <div class="card-body text-center">
                <i class="bi bi-people-fill fs-1 text-warning"></i>
                <h6 class="mt-2 text-uppercase">Equipos Activos</h6>
                <h2 class="fw-bold">{{ $metricas['total_equipos'] ?? 0 }}</h2>
                <small class="text-success">
                    <i class="bi bi-arrow-up"></i>
                    {{ $metricas['total_temporadas'] ?? 0 }} temporadas
                </small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 bg-success text-white h-100">
            <div class="card-body text-center">
                <i class="bi bi-calendar-event fs-1"></i>
                <h6 class="mt-2 text-uppercase">Temporada</h6>
                <h5 class="fw-bold">{{ $temporada?->nombre ?? 'Sin temporada' }}</h5>
                <small>{{ $temporada?->anio }}</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 bg-primary text-white h-100">
            <div class="card-body text-center">
                <i class="bi bi-soccer fs-1"></i>
                <h6 class="mt-2 text-uppercase">Partidos Jugados</h6>
                <h2 class="fw-bold">{{ $metricas['partidos_jugados'] ?? 0 }}</h2>
                <small class="text-warning">
                    {{ $metricas['partidos_programados'] ?? 0 }} programados
                </small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card shadow border-0 bg-warning text-dark h-100">
            <div class="card-body text-center">
                <i class="bi bi-speedometer fs-1"></i>
                <h6 class="mt-2 text-uppercase">Promedio Goles</h6>
                <h2 class="fw-bold">{{ $metricas['promedio_goles'] ?? 0 }}</h2>
                <small>{{ $metricas['total_goles'] ?? 0 }} goles</small>
            </div>
        </div>
    </div>

</div>

{{-- ================= PROXIMOS PARTIDOS ================= --}}
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-clock"></i> Próximos Partidos
    </div>
    <div class="card-body">
        @forelse($proximosPartidos as $partido)
            <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                <div>
                    <strong>
                        {{ $partido->equipoLocal->nombre ?? 'Local' }}
                        vs
                        {{ $partido->equipoVisitante->nombre ?? 'Visitante' }}
                    </strong>
                    <div class="text-muted small">
                        <i class="bi bi-calendar"></i>
                        {{ $partido->fecha_hora->format('d/m/Y H:i') }}
                    </div>
                </div>
                <span class="badge bg-info">{{ $partido->estado }}</span>
            </div>
        @empty
            <p class="text-center text-muted">No hay partidos programados</p>
        @endforelse
    </div>
</div>

{{-- ================= CLASIFICACION ================= --}}
<div class="card shadow mb-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-trophy"></i> Tabla de Posiciones
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Equipo</th>
                    <th class="text-center">PJ</th>
                    <th class="text-center">PTS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clasificacion as $i => $item)
                    <tr class="{{ $i < 3 ? 'table-success' : '' }}">
                        <td class="fw-bold">{{ $item->posicion ?? $i + 1 }}</td>
                        <td>{{ $item->equipo->nombre ?? 'Equipo' }}</td>
                        <td class="text-center">{{ $item->partidos_jugados ?? 0 }}</td>
                        <td class="text-center fw-bold">{{ $item->puntos ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay clasificación disponible
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ================= ULTIMOS RESULTADOS ================= --}}
<div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-bar-chart"></i> Últimos Resultados
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($ultimosResultados as $partido)
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <h5 class="fw-bold">
                                {{ $partido->equipoLocal->abreviacion ?? 'LOC' }}
                                {{ $partido->goles_local ?? 0 }}
                                -
                                {{ $partido->goles_visitante ?? 0 }}
                                {{ $partido->equipoVisitante->abreviacion ?? 'VIS' }}
                            </h5>
                            <small class="text-muted">
                                {{ $partido->fecha_hora->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No hay resultados recientes</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ================= EQUIPOS DESTACADOS ================= --}}
<div class="card shadow mb-4">
    <div class="card-header bg-warning">
        <i class="bi bi-star"></i> Equipos Destacados
    </div>
    <div class="card-body">
        @forelse($equiposDestacados as $equipo)
            <div class="d-flex align-items-center mb-3">
                <img src="{{ $equipo->escudo_url ?? '' }}" class="rounded-circle me-3" width="40">
                <div>
                    <strong>{{ $equipo->nombre }}</strong><br>
                    <small class="text-muted">
                        {{ $equipo->ciudad ?? 'Sin ciudad' }} ·
                        {{ $equipo->total_partidos ?? 0 }} partidos
                    </small>
                </div>
            </div>
        @empty
            <p class="text-muted">No hay equipos destacados</p>
        @endforelse
    </div>
</div>

{{-- ================= ALERTAS ================= --}}
@if($alertas && count($alertas))
<div class="card shadow">
    <div class="card-header">
        <i class="bi bi-exclamation-triangle"></i> Alertas del Sistema
    </div>
    <div class="card-body">
        @foreach($alertas as $alerta)
            <div class="alert alert-{{ $alerta['tipo'] }}">
                {{ $alerta['mensaje'] }}
            </div>
        @endforeach
    </div>
</div>
@endif

@endsection
