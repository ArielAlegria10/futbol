@extends('layouts.app')

@section('title', 'Gestión de Temporadas')
@section('icon', 'bi-calendar-week')
@section('subtitle', 'Administra todas las temporadas del torneo')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('temporadas.index') }}">Temporadas</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Listado</li>
@endsection

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('temporadas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nueva Temporada
        </a>
        <a href="{{ route('temporadas.historial') }}" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-clock-history me-1"></i> Historial
        </a>
        <a href="{{ route('temporadas.actual') }}" class="btn btn-outline-success ms-2">
            <i class="bi bi-play-circle me-1"></i> Actual
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Estadísticas Rápidas -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3 col-6 mb-3">
                <div class="card bg-primary text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Total Temporadas</h6>
                                <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-calendar-week fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card bg-success text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">En Curso</h6>
                                <h3 class="mb-0">{{ $stats['en_curso'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-play-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card bg-warning text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Programadas</h6>
                                <h3 class="mb-0">{{ $stats['programadas'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card bg-info text-white shadow-sm">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Finalizadas</h6>
                                <h3 class="mb-0">{{ $stats['finalizadas'] ?? 0 }}</h3>
                            </div>
                            <i class="bi bi-check-circle fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenido Principal -->
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        Listado de Temporadas
                    </h5>
                    
                    <div class="d-flex gap-2">
                        <!-- Filtros Rápidos -->
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel me-1"></i> Estado
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="all">
                                        <i class="bi bi-list me-2"></i> Todas las temporadas
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="En Curso">
                                        <i class="bi bi-play-circle text-success me-2"></i> En Curso
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="Programada">
                                        <i class="bi bi-clock text-warning me-2"></i> Programadas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="Finalizada">
                                        <i class="bi bi-check-circle text-info me-2"></i> Finalizadas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="Cancelada">
                                        <i class="bi bi-x-circle text-danger me-2"></i> Canceladas
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-status" href="#" data-status="Archivada">
                                        <i class="bi bi-archive text-secondary me-2"></i> Archivadas
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Buscador -->
                        <div class="input-group" style="width: 300px;">
                            <input type="text" id="searchInput" class="form-control" 
                                   placeholder="Buscar temporadas...">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                @if($temporadas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="temporadasTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" width="50">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                        </div>
                                    </th>
                                    <th>Temporada</th>
                                    <th>Periodo</th>
                                    <th class="text-center">Equipos</th>
                                    <th class="text-center">Partidos</th>
                                    <th class="text-center">Progreso</th>
                                    <th>Estado</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($temporadas as $temporada)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="form-check">
                                                <input class="form-check-input select-item" type="checkbox" 
                                                       value="{{ $temporada->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="bg-{{ $temporada->estado_color ?? 'secondary' }} text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="bi bi-calendar-week"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('temporadas.show', $temporada) }}" 
                                                           class="text-decoration-none">
                                                            {{ $temporada->nombre }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar3 me-1"></i>{{ $temporada->anio }}
                                                        @if($temporada->descripcion)
                                                            <span class="ms-2">
                                                                <i class="bi bi-chat-left-text me-1"></i>{{ Str::limit($temporada->descripcion, 30) }}
                                                            </span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-plus me-1"></i>
                                                    {{ $temporada->fecha_inicio->format('d/m/Y') }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-minus me-1"></i>
                                                    {{ $temporada->fecha_fin->format('d/m/Y') }}
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock-history me-1"></i>
                                                    {{ $temporada->dias_transcurridos ?? 0 }} días
                                                </small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill">
                                                <i class="bi bi-people me-1"></i>
                                                {{ $temporada->equipos_count ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-info rounded-pill mb-1">
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $temporada->partidos_count ?? 0 }}
                                                </span>
                                                @if(($temporada->partidos_count ?? 0) > 0)
                                                    <small class="text-muted">
                                                        {{ $temporada->partidos_jugados_count ?? 0 }} jugados
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar progress-bar-striped 
                                                         {{ $temporada->estado === 'En Curso' ? 'progress-bar-animated' : '' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $temporada->progreso ?? 0 }}%;"
                                                     aria-valuenow="{{ $temporada->progreso ?? 0 }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-1">
                                                <small class="text-muted">{{ $temporada->progreso ?? 0 }}%</small>
                                                <small class="text-muted">{{ $temporada->dias_restantes ?? 0 }} días restantes</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $temporada->estado_color ?? 'secondary' }} rounded-pill">
                                                <i class="bi bi-{{ $temporada->estado_icon ?? 'calendar-week' }} me-1"></i>
                                                {{ $temporada->estado }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('temporadas.show', $temporada) }}" 
                                                   class="btn btn-outline-info"
                                                   data-bs-toggle="tooltip" title="Ver Detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('temporadas.clasificacion', $temporada) }}" 
                                                   class="btn btn-outline-success"
                                                   data-bs-toggle="tooltip" title="Clasificación">
                                                    <i class="bi bi-trophy"></i>
                                                </a>
                                                <a href="{{ route('temporadas.estadisticas', $temporada) }}" 
                                                   class="btn btn-outline-warning"
                                                   data-bs-toggle="tooltip" title="Estadísticas">
                                                    <i class="bi bi-bar-chart"></i>
                                                </a>
                                                <a href="{{ route('temporadas.edit', $temporada) }}" 
                                                   class="btn btn-outline-primary"
                                                   data-bs-toggle="tooltip" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-outline-{{ $temporada->estado === 'Archivada' ? 'secondary' : 'danger' }}"
                                                        data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('temporadas.jornadas', $temporada) }}">
                                                            <i class="bi bi-calendar-range me-2"></i> Ver Jornadas
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
                                                        <button class="dropdown-item text-danger" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deleteModal"
                                                                data-id="{{ $temporada->id }}"
                                                                data-nombre="{{ $temporada->nombre }}">
                                                            <i class="bi bi-trash me-2"></i> Eliminar
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Acciones por Lotes -->
                    <div class="card-footer bg-light border-top py-3" id="bulkActions" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span id="selectedCount">0</span> temporadas seleccionadas
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-success btn-sm" id="bulkActivate">
                                    <i class="bi bi-play-circle me-1"></i> Iniciar
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" id="bulkFinalize">
                                    <i class="bi bi-check-circle me-1"></i> Finalizar
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkArchive">
                                    <i class="bi bi-archive me-1"></i> Archivar
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDelete">
                                    <i class="bi bi-trash me-1"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Paginación -->
                    @if($temporadas->hasPages())
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Mostrando {{ $temporadas->firstItem() }} a {{ $temporadas->lastItem() }} 
                                    de {{ $temporadas->total() }} temporadas
                                </div>
                                <div>
                                    {{ $temporadas->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                    
                @else
                    <!-- Estado Vacío -->
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="bi bi-calendar-week display-1 text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">No hay temporadas registradas</h4>
                            <p class="text-muted mb-4">Comienza creando la primera temporada del torneo.</p>
                            <a href="{{ route('temporadas.create') }}" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle me-2"></i> Crear Primera Temporada
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Cambiar Estado -->
<div class="modal fade" id="estadoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    Cambiar Estado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="estadoForm">
                    @csrf
                    <input type="hidden" id="temporadaId" name="temporada_id">
                    
                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevoEstado" name="estado" required>
                            <option value="">Seleccionar estado</option>
                            <option value="Programada">Programada</option>
                            <option value="En Curso">En Curso</option>
                            <option value="Finalizada">Finalizada</option>
                            <option value="Cancelada">Cancelada</option>
                            <option value="Archivada">Archivada</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario" class="form-label">Comentario (Opcional)</label>
                        <textarea class="form-control" id="comentario" name="comentario" 
                                  rows="3" placeholder="Motivo del cambio de estado..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmEstado">
                    <i class="bi bi-check-circle me-1"></i> Cambiar Estado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
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
                        ¿Está seguro de eliminar la temporada 
                        "<strong id="deleteTemporadaNombre"></strong>"?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle me-2"></i> Impacto de la Eliminación
                    </h6>
                    <ul class="mb-0 ps-3">
                        <li>Todas las jornadas serán eliminadas</li>
                        <li>Todos los partidos serán eliminados</li>
                        <li>La clasificación será eliminada</li>
                        <li>Las estadísticas asociadas se perderán</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar Temporada
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transition: background-color 0.2s ease;
    }
    
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .badge-pill {
        padding: 0.5em 1em;
    }
    
    #bulkActions {
        position: sticky;
        bottom: 0;
        z-index: 100;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }
    
    .btn-group-sm .dropdown-toggle::after {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTable
        const table = $('#temporadasTable').DataTable({
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [0, 7] },
                { searchable: false, targets: [0, 4, 5, 6, 7] }
            ],
            drawCallback: function() {
                // Re-inicializar tooltips después de cada redibujado
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
        
        // Búsqueda
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        $('#searchButton').on('click', function() {
            table.search($('#searchInput').val()).draw();
        });
        
        // Filtros por estado
        $('.filter-status').on('click', function(e) {
            e.preventDefault();
            const status = $(this).data('status');
            
            if (status === 'all') {
                table.column(6).search('').draw();
            } else {
                table.column(6).search('^' + status + '$', true, false).draw();
            }
        });
        
        // Selección múltiple
        $('#selectAll').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.select-item').prop('checked', isChecked);
            updateBulkActions();
        });
        
        $('.select-item').on('change', function() {
            const allChecked = $('.select-item:checked').length === $('.select-item').length;
            $('#selectAll').prop('checked', allChecked);
            updateBulkActions();
        });
        
        function updateBulkActions() {
            const selectedCount = $('.select-item:checked').length;
            $('#selectedCount').text(selectedCount);
            
            if (selectedCount > 0) {
                $('#bulkActions').slideDown();
            } else {
                $('#bulkActions').slideUp();
            }
        }
        
        // Acciones por lotes
        $('#bulkActivate').on('click', function() {
            bulkAction('En Curso');
        });
        
        $('#bulkFinalize').on('click', function() {
            bulkAction('Finalizada');
        });
        
        $('#bulkArchive').on('click', function() {
            bulkAction('Archivada');
        });
        
        $('#bulkDelete').on('click', function() {
            if (confirm('¿Está seguro de eliminar las temporadas seleccionadas?')) {
                bulkAction('delete');
            }
        });
        
        function bulkAction(action) {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            
            showLoading();
            
            $.ajax({
                url: "{{ route('temporadas.bulk-action') }}",
                method: 'POST',
                data: {
                    ids: ids,
                    action: action,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    toastr.error('Error en la operación');
                }
            });
        }
        
        function getSelectedIds() {
            return $('.select-item:checked').map(function() {
                return $(this).val();
            }).get();
        }
        
        // Cambiar estado individual
        $('.cambiar-estado').on('click', function() {
            const temporadaId = $(this).data('id');
            const estadoActual = $(this).data('estado');
            
            $('#temporadaId').val(temporadaId);
            $('#nuevoEstado').val('');
            
            const modal = new bootstrap.Modal(document.getElementById('estadoModal'));
            modal.show();
        });
        
        $('#confirmEstado').on('click', function() {
            const formData = $('#estadoForm').serialize();
            
            $.ajax({
                url: "{{ route('temporadas.cambiar-estado', '') }}/" + $('#temporadaId').val(),
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#estadoModal').modal('hide');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function(field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Error al cambiar estado');
                    }
                }
            });
        });
        
        // Eliminación individual
        $('#deleteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const temporadaId = button.data('id');
            const temporadaNombre = button.data('nombre');
            
            $('#deleteTemporadaNombre').text(temporadaNombre);
            $('#deleteForm').attr('action', `/temporadas/${temporadaId}`);
        });
        
        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Loading overlay functions
        function showLoading() {
            $('body').append(`
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
    });
</script>
@endpush
@endsection