@extends('layouts.app')

@section('title', 'Partidos')
@section('icon', 'bi-calendar-event')

@section('breadcrumb-items')
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-calendar-event me-1"></i> Partidos
    </li>
@endsection

@section('header-buttons')
    <a href="{{ route('partidos.calendario') }}" class="btn btn-outline-primary">
        <i class="bi bi-calendar3 me-2"></i> Vista Calendario
    </a>
    <a href="{{ route('partidos.en-vivo') }}" class="btn btn-danger">
        <i class="bi bi-tv me-2"></i> En Vivo
    </a>
    <a href="{{ route('partidos.proximos') }}" class="btn btn-success">
        <i class="bi bi-clock me-2"></i> Próximos
    </a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-soccer">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h1 class="page-title-soccer mb-2">
                            <i class="bi bi-calendar-event me-3"></i>
                            Gestión de Partidos
                        </h1>
                        <p class="text-muted mb-0">
                            <span class="badge bg-primary me-2">ACTIVO</span>
                            Total: {{ $estadisticas['total'] ?? 0 }} partidos | Última actualización: {{ now()->format('d/m/Y H:i:s') }}
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @yield('header-buttons')
                        <a href="{{ route('partidos.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i> Nuevo Partido
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-futbol"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Total Partidos</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-success bg-opacity-10 text-success">
                        <i class="bi bi-flag-checkered"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Finalizados</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['finalizados'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-info bg-opacity-10 text-info">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Programados</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['programados'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-play-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">En Juego</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['en_juego'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Suspendidos</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['suspendidos'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="stats-card-soccer">
                <div class="d-flex align-items-center">
                    <div class="stats-icon-soccer bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-0">Cancelados</h6>
                        <h4 class="mb-0 fw-bold">{{ $estadisticas['cancelados'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>Filtros de Búsqueda
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('partidos.index') }}" method="GET" class="row g-3">
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Temporada</label>
                    <select name="temporada_id" class="form-select">
                        <option value="">Todas las temporadas</option>
                        @foreach($temporadas as $temporada)
                            <option value="{{ $temporada->id }}" {{ request('temporada_id') == $temporada->id ? 'selected' : '' }}>
                                {{ $temporada->nombre ?? $temporada->anio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="todos" {{ request('estado', 'todos') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                        @foreach($estados as $valor => $texto)
                            @if($valor != 'todos')
                                <option value="{{ $valor }}" {{ request('estado') == $valor ? 'selected' : '' }}>
                                    {{ $texto }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Equipo</label>
                    <select name="equipo_id" class="form-select">
                        <option value="">Todos los equipos</option>
                        @foreach($equipos as $equipo)
                            <option value="{{ $equipo->id }}" {{ request('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                {{ $equipo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Resultados por página</label>
                    <select name="per_page" class="form-select">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 por página</option>
                        <option value="30" {{ request('per_page', 15) == 30 ? 'selected' : '' }}>30 por página</option>
                        <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50 por página</option>
                        <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100 por página</option>
                    </select>
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" 
                           class="form-control">
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" 
                           class="form-control">
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6">
                    <label class="form-label">Buscar</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Equipo, jornada, estadio..."
                           class="form-control">
                </div>

                <div class="col-xl-3 col-md-4 col-sm-6 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-search me-2"></i> Filtrar
                        </button>
                        <a href="{{ route('partidos.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-tabs-custom" id="partidosTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="{{ route('partidos.index') }}" 
                       class="nav-link {{ !request('estado') || request('estado') == 'todos' ? 'active' : '' }}">
                        <i class="bi bi-list-columns me-2"></i>Todos
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('partidos.index') }}?estado=Programado" 
                       class="nav-link {{ request('estado') == 'Programado' ? 'active' : '' }}">
                        <i class="bi bi-calendar me-2"></i>Programados
                        <span class="badge bg-primary ms-2">{{ $estadisticas['programados'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('partidos.index') }}?estado=Jugando" 
                       class="nav-link {{ request('estado') == 'Jugando' ? 'active' : '' }}">
                        <i class="bi bi-play-circle me-2"></i>En Juego
                        <span class="badge bg-warning ms-2">{{ $estadisticas['en_juego'] ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="{{ route('partidos.index') }}?estado=Finalizado" 
                       class="nav-link {{ request('estado') == 'Finalizado' ? 'active' : '' }}">
                        <i class="bi bi-flag-checkered me-2"></i>Finalizados
                        <span class="badge bg-success ms-2">{{ $estadisticas['finalizados'] ?? 0 }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Matches Table -->
    @if($partidos->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-borderless mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Fecha/Hora</th>
                            <th class="py-3">Partido</th>
                            <th class="py-3">Resultado</th>
                            <th class="py-3">Jornada</th>
                            <th class="py-3">Estado</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partidos as $partido)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $partido->fecha_hora->format('d/m/Y') }}</span>
                                    <span class="text-muted small">{{ $partido->fecha_hora->format('H:i') }}</span>
                                    @if($partido->estadio)
                                        <span class="text-muted small mt-1">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $partido->estadio }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="text-end me-3" style="width: 120px;">
                                        <div class="fw-bold">{{ $partido->equipoLocal->nombre }}</div>
                                        <div class="text-muted small">{{ $partido->equipoLocal->abreviacion }}</div>
                                    </div>
                                    
                                    <div class="d-flex flex-column align-items-center">
                                        @if($partido->equipoLocal->escudo_url)
                                            <img src="{{ $partido->equipoLocal->escudo_url }}" 
                                                 alt="{{ $partido->equipoLocal->nombre }}"
                                                 class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-people text-primary small"></i>
                                            </div>
                                        @endif
                                        <span class="text-muted small mt-1">Local</span>
                                    </div>
                                    
                                    <div class="mx-3 text-center">
                                        <div class="text-muted fw-bold">VS</div>
                                    </div>
                                    
                                    <div class="d-flex flex-column align-items-center">
                                        @if($partido->equipoVisitante->escudo_url)
                                            <img src="{{ $partido->equipoVisitante->escudo_url }}" 
                                                 alt="{{ $partido->equipoVisitante->nombre }}"
                                                 class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="bi bi-people text-danger small"></i>
                                            </div>
                                        @endif
                                        <span class="text-muted small mt-1">Visitante</span>
                                    </div>
                                    
                                    <div class="ms-3" style="width: 120px;">
                                        <div class="fw-bold">{{ $partido->equipoVisitante->nombre }}</div>
                                        <div class="text-muted small">{{ $partido->equipoVisitante->abreviacion }}</div>
                                    </div>
                                </div>
                                @if($partido->arbitro_principal)
                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-whistle me-1"></i>{{ $partido->arbitro_principal }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($partido->estado == 'Finalizado')
                                    <div class="d-flex align-items-center">
                                        <div class="text-center">
                                            <div class="fw-bold fs-4">
                                                <span class="{{ $partido->goles_local > $partido->goles_visitante ? 'text-success' : 'text-dark' }}">
                                                    {{ $partido->goles_local }}
                                                </span>
                                                <span class="mx-1">-</span>
                                                <span class="{{ $partido->goles_visitante > $partido->goles_local ? 'text-success' : 'text-dark' }}">
                                                    {{ $partido->goles_visitante }}
                                                </span>
                                            </div>
                                            @if($partido->penales_local !== null)
                                                <div class="text-muted small">
                                                    Penales: {{ $partido->penales_local }}-{{ $partido->penales_visitante }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif($partido->estado == 'Jugando')
                                    <div class="d-flex align-items-center">
                                        <div class="text-center">
                                            <div class="fw-bold fs-4 text-warning animate__animated animate__pulse animate__infinite">
                                                {{ $partido->goles_local ?? 0 }}-{{ $partido->goles_visitante ?? 0 }}
                                            </div>
                                            <div class="badge bg-warning text-dark mt-1">
                                                <i class="bi bi-play-circle me-1"></i>EN VIVO
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center">
                                        <div class="text-muted fw-bold">- -</div>
                                        <div class="badge bg-secondary mt-1">Por jugar</div>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">Jornada {{ $partido->jornada->numero }}</span>
                                    <span class="text-muted small">
                                        {{ $partido->jornada->temporada->nombre ?? $partido->jornada->temporada->anio }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3">
                                @php
                                    $estadoConfig = [
                                        'Finalizado' => ['class' => 'badge bg-success', 'icon' => 'bi-flag-checkered'],
                                        'Jugando' => ['class' => 'badge bg-warning text-dark', 'icon' => 'bi-play-circle'],
                                        'Programado' => ['class' => 'badge bg-info', 'icon' => 'bi-calendar-check'],
                                        'Suspendido' => ['class' => 'badge bg-secondary', 'icon' => 'bi-pause-circle'],
                                        'Cancelado' => ['class' => 'badge bg-danger', 'icon' => 'bi-x-circle']
                                    ];
                                    $config = $estadoConfig[$partido->estado] ?? ['class' => 'badge bg-secondary', 'icon' => 'bi-question-circle'];
                                @endphp
                                <span class="{{ $config['class'] }}">
                                    <i class="bi {{ $config['icon'] }} me-1"></i>{{ $partido->estado }}
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('partidos.show', $partido->id) }}" 
                                       class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($partido->estado == 'Programado' || $partido->estado == 'Suspendido')
                                        <a href="{{ route('partidos.edit', $partido->id) }}" 
                                           class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if($partido->estado == 'Jugando')
                                        <button onclick="cambiarEstado('{{ $partido->id }}', 'Finalizado')" 
                                                class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Finalizar">
                                            <i class="bi bi-flag-checkered"></i>
                                        </button>
                                    @endif
                                    @if($partido->estado == 'Programado' || $partido->estado == 'Suspendido')
                                        <button onclick="eliminarPartido('{{ $partido->id }}')" 
                                                class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Mostrando {{ $partidos->firstItem() }} - {{ $partidos->lastItem() }} de {{ $partidos->total() }} partidos
                </div>
                <div>
                    {{ $partidos->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted mb-3">No se encontraron partidos</h4>
            <p class="text-muted mb-4">No hay partidos que coincidan con los filtros aplicados</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('partidos.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-2"></i> Crear primer partido
                </a>
                <a href="{{ route('partidos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise me-2"></i> Limpiar filtros
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Action Modals -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este partido? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Partido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cambiar el estado del partido?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmStatus">Confirmar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-tabs-custom .nav-link {
        border: none;
        padding: 1rem 1.5rem;
        color: #6c757d;
        font-weight: 500;
        transition: all 0.3s;
        border-bottom: 3px solid transparent;
    }
    
    .nav-tabs-custom .nav-link:hover {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    
    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
        border-bottom-color: #0d6efd;
    }
    
    .table-borderless tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.03);
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
</style>
@endpush

@push('scripts')
<script>
let currentPartidoId = null;
let currentNuevoEstado = null;

function cambiarEstado(partidoId, nuevoEstado) {
    currentPartidoId = partidoId;
    currentNuevoEstado = nuevoEstado;
    $('#statusModal').modal('show');
}

function eliminarPartido(partidoId) {
    currentPartidoId = partidoId;
    $('#deleteModal').modal('show');
}

$('#confirmStatus').click(function() {
    if (currentPartidoId && currentNuevoEstado) {
        $.ajax({
            url: `/partidos/${currentPartidoId}/cambiar-estado`,
            method: 'POST',
            data: {
                estado: currentNuevoEstado,
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#statusModal').modal('hide');
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showToast('success', 'Estado actualizado correctamente');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Error al actualizar el estado');
                }
            },
            error: function() {
                hideLoading();
                showToast('error', 'Error al actualizar el estado');
            }
        });
    }
});

$('#confirmDelete').click(function() {
    if (currentPartidoId) {
        $.ajax({
            url: `/partidos/${currentPartidoId}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('#deleteModal').modal('hide');
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showToast('success', 'Partido eliminado correctamente');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Error al eliminar el partido');
                }
            },
            error: function() {
                hideLoading();
                showToast('error', 'Error al eliminar el partido');
            }
        });
    }
});

// Live score update function
function actualizarMarcador(partidoId) {
    Swal.fire({
        title: 'Actualizar Marcador',
        html: `
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label">Goles Local</label>
                    <input type="number" id="golesLocal" class="form-control" min="0" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">Goles Visitante</label>
                    <input type="number" id="golesVisitante" class="form-control" min="0" value="0">
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const golesLocal = $('#golesLocal').val();
            const golesVisitante = $('#golesVisitante').val();
            
            if (!golesLocal || !golesVisitante) {
                Swal.showValidationMessage('Por favor ingresa ambos valores');
                return false;
            }
            
            return { golesLocal, golesVisitante };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/partidos/${partidoId}/actualizar-marcador`,
                method: 'POST',
                data: {
                    goles_local: result.value.golesLocal,
                    goles_visitante: result.value.golesVisitante,
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: showLoading,
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showToast('success', 'Marcador actualizado correctamente');
                        location.reload();
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    hideLoading();
                    showToast('error', 'Error al actualizar el marcador');
                }
            });
        }
    });
}

// Utility functions
function showLoading() {
    $('.main-content-soccer').prepend(`
        <div class="loading-overlay">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);
}

function hideLoading() {
    $('.loading-overlay').remove();
}

function showToast(type, message) {
    const toast = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('.toast-container').append(toast);
    $('.toast:last').toast('show');
    setTimeout(() => $('.toast:last').remove(), 5000);
}

// Initialize tooltips
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Create toast container if not exists
    if ($('.toast-container').length === 0) {
        $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999"></div>');
    }
    
    // Auto-refresh for live matches
    setInterval(() => {
        if (window.location.href.includes('estado=Jugando')) {
            location.reload();
        }
    }, 30000);
});
</script>
@endpush