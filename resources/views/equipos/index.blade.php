@extends('layouts.app')

@section('title', 'Gestión de Equipos')
@section('icon', 'bi-people-fill')
@section('subtitle', 'Administra todos los equipos del sistema')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('equipos.index') }}">Equipos</a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">Listado</li>
@endsection

@section('header-buttons')
    <a href="{{ route('equipos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nuevo Equipo
    </a>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-white border-bottom">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Listado de Equipos</h5>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end">
                    <!-- Filtros Rápidos -->
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel"></i> Filtros
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="all">
                                    <i class="bi bi-list"></i> Todos los equipos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="active">
                                    <i class="bi bi-check-circle text-success"></i> Solo activos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item filter-status" href="#" data-status="inactive">
                                    <i class="bi bi-x-circle text-danger"></i> Solo inactivos
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Buscador -->
                    <div class="input-group" style="width: 300px;">
                        <input type="text" id="searchInput" class="form-control" 
                               placeholder="Buscar equipos..." aria-label="Buscar">
                        <button class="btn btn-outline-secondary" type="button" id="searchButton">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Estadísticas Rápidas -->
        <div class="row mb-4">
            <div class="col-md-3 col-6">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Total Equipos</h6>
                                <h3 class="mb-0">{{ $equipos->total() }}</h3>
                            </div>
                            <i class="bi bi-people-fill fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Activos</h6>
                                <h3 class="mb-0">{{ $equiposActivos }}</h3>
                            </div>
                            <i class="bi bi-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-warning text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Inactivos</h6>
                                <h3 class="mb-0">{{ $equiposInactivos }}</h3>
                            </div>
                            <i class="bi bi-x-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card bg-info text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Con Estadio</h6>
                                <h3 class="mb-0">{{ $equiposConEstadio }}</h3>
                            </div>
                            <i class="bi bi-house-door fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de Equipos -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="60">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Equipo</th>
                        <th>Ciudad</th>
                        <th>Estadio</th>
                        <th>Fundación</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="equiposTableBody">
                    @forelse($equipos as $equipo)
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input select-item" type="checkbox" 
                                           value="{{ $equipo->id }}">
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        @if($equipo->escudo_url)
                                            <img src="{{ $equipo->escudo_url }}" 
                                                 alt="{{ $equipo->nombre }}" 
                                                 class="rounded-circle border" 
                                                 width="40" height="40"
                                                 onerror="this.src='{{ asset('img/default-team.png') }}'">
                                        @else
                                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-shield text-white"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <a href="{{ route('equipos.show', $equipo) }}" 
                                               class="text-decoration-none">
                                                {{ $equipo->nombre }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            {{ $equipo->abreviacion }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $equipo->ciudad }}
                                </span>
                            </td>
                            <td>
                                @if($equipo->estadio)
                                    <small>{{ $equipo->estadio }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($equipo->fundacion)
                                    <span class="badge bg-info">
                                        {{ $equipo->fundacion }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-status badge-{{ $equipo->activo ? 'success' : 'danger' }}">
                                    {{ $equipo->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('equipos.show', $equipo) }}" 
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip" title="Ver Detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('equipos.edit', $equipo) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-{{ $equipo->activo ? 'warning' : 'success' }} toggle-status"
                                            data-id="{{ $equipo->id }}"
                                            data-bs-toggle="tooltip" 
                                            title="{{ $equipo->activo ? 'Desactivar' : 'Activar' }}">
                                        <i class="bi bi-{{ $equipo->activo ? 'x-circle' : 'check-circle' }}"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="empty-state">
                                    <i class="bi bi-people display-4 text-muted mb-3"></i>
                                    <h5 class="text-muted">No hay equipos registrados</h5>
                                    <p class="text-muted mb-4">Comienza agregando tu primer equipo al sistema.</p>
                                    <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i> Crear Primer Equipo
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        @if($equipos->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Mostrando {{ $equipos->firstItem() }} a {{ $equipos->lastItem() }} de {{ $equipos->total() }} equipos
                </div>
                <nav aria-label="Page navigation">
                    {{ $equipos->links() }}
                </nav>
            </div>
        @endif
    </div>
    
    <!-- Bulk Actions -->
    <div class="card-footer bg-light border-top" id="bulkActions" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span id="selectedCount">0</span> equipos seleccionados
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-success btn-sm" id="bulkActivate">
                    <i class="bi bi-check-circle me-1"></i> Activar
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" id="bulkDeactivate">
                    <i class="bi bi-x-circle me-1"></i> Desactivar
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="bulkDelete">
                    <i class="bi bi-trash me-1"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .empty-state {
        text-align: center;
        padding: 2rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    #bulkActions {
        position: sticky;
        bottom: 0;
        z-index: 1000;
    }
    
    .badge-status {
        transition: all 0.3s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Simple search functionality
        $('#searchInput').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchTerm) > -1);
            });
        });
        
        $('#searchButton').on('click', function() {
            $('#searchInput').trigger('keyup');
        });
        
        // Status filtering
        $('.filter-status').on('click', function(e) {
            e.preventDefault();
            const status = $(this).data('status');
            
            $('tbody tr').each(function() {
                const statusBadge = $(this).find('.badge-status').text().trim();
                if (status === 'all') {
                    $(this).show();
                } else if (status === 'active') {
                    $(this).toggle(statusBadge === 'Activo');
                } else if (status === 'inactive') {
                    $(this).toggle(statusBadge === 'Inactivo');
                }
            });
        });
        
        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.select-item').prop('checked', isChecked);
            updateBulkActions();
        });
        
        // Individual checkbox selection
        $('.select-item').on('change', function() {
            const allChecked = $('.select-item:checked').length === $('.select-item').length;
            $('#selectAll').prop('checked', allChecked);
            updateBulkActions();
        });
        
        // Update bulk actions visibility
        function updateBulkActions() {
            const selectedCount = $('.select-item:checked').length;
            $('#selectedCount').text(selectedCount);
            
            if (selectedCount > 0) {
                $('#bulkActions').slideDown();
            } else {
                $('#bulkActions').slideUp();
            }
        }
        
        // Get selected IDs
        function getSelectedIds() {
            return $('.select-item:checked').map(function() {
                return $(this).val();
            }).get();
        }
        
        // Bulk actions
        $('#bulkActivate').on('click', function() {
            const ids = getSelectedIds();
            if (ids.length > 0 && confirm(`¿Activar ${ids.length} equipo(s)?`)) {
                performBulkAction(ids, 'activate');
            }
        });
        
        $('#bulkDeactivate').on('click', function() {
            const ids = getSelectedIds();
            if (ids.length > 0 && confirm(`¿Desactivar ${ids.length} equipo(s)?`)) {
                performBulkAction(ids, 'deactivate');
            }
        });
        
        $('#bulkDelete').on('click', function() {
            const ids = getSelectedIds();
            if (ids.length > 0 && confirm(`¿Eliminar permanentemente ${ids.length} equipo(s)? Esta acción no se puede deshacer.`)) {
                performBulkAction(ids, 'delete');
            }
        });
        
        // Perform bulk action via AJAX
        function performBulkAction(ids, action) {
            $.ajax({
                url: "{{ route('equipos.bulk-action') }}",
                method: 'POST',
                data: {
                    ids: ids,
                    action: action,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function() {
                    // Show loading indicator
                    $('#bulkActions').html('<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Procesando...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        // Reload after 1 second
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        showToast('error', response.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    let message = 'Error en la operación';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showToast('error', message);
                    location.reload();
                }
            });
        }
        
        // Toggle individual status
        $('.toggle-status').on('click', function() {
            const button = $(this);
            const id = button.data('id');
            const currentStatus = button.find('i').hasClass('bi-check-circle') ? 'inactive' : 'active';
            const newStatusText = currentStatus === 'active' ? 'Desactivar' : 'Activar';
            
            if (confirm(`¿${newStatusText} este equipo?`)) {
                $.ajax({
                    url: "{{ url('equipos') }}/" + id + "/toggle-status",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        button.prop('disabled', true);
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update button appearance
                            const isActive = response.activo;
                            const badge = button.closest('tr').find('.badge-status');
                            
                            // Update badge
                            badge.removeClass('bg-success bg-danger')
                                 .addClass(isActive ? 'bg-success' : 'bg-danger')
                                 .text(isActive ? 'Activo' : 'Inactivo');
                            
                            // Update button
                            button.removeClass('btn-outline-success btn-outline-warning')
                                  .addClass(isActive ? 'btn-outline-warning' : 'btn-outline-success')
                                  .attr('title', isActive ? 'Desactivar' : 'Activar')
                                  .tooltip('dispose').tooltip();
                            
                            // Update icon
                            button.find('i')
                                  .removeClass('bi-check-circle bi-x-circle')
                                  .addClass(isActive ? 'bi-x-circle' : 'bi-check-circle');
                            
                            showToast('success', response.message);
                        }
                    },
                    error: function(xhr) {
                        showToast('error', 'Error al cambiar estado');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            }
        });
        
        // Toast notification function
        function showToast(type, message) {
            // Use Toastr if available, otherwise alert
            if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        }
    });
</script>
@endpush
@endsection