@extends('layouts.app')

@section('title', 'Editar Equipo')
@section('icon', 'bi-pencil-square')
@section('subtitle', 'Modificar información del equipo registrado')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('equipos.index') }}" class="text-decoration-none">
            <i class="bi bi-people me-1"></i> Equipos
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('equipos.show', $equipo->id) }}" class="text-decoration-none">
            {{ $equipo->nombre }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-pencil me-1"></i> Editar
    </li>
@endsection

@section('header-buttons')
    <div class="btn-group">
        <a href="{{ route('equipos.show', $equipo->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-eye me-1"></i> Ver Detalles
        </a>
        <a href="{{ route('equipos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        @if($equipo->escudo_url)
                            <div class="me-3">
                                <img src="{{ $equipo->escudo_url }}" 
                                     alt="Escudo {{ $equipo->nombre }}"
                                     class="rounded-circle border"
                                     style="width: 50px; height: 50px; object-fit: cover;"
                                     onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"50\" height=\"50\" fill=\"%236c757d\" class=\"bi bi-shield\" viewBox=\"0 0 16 16\"><path d=\"M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56\"/></svg>'">
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-0 fw-semibold">
                                <i class="bi bi-pencil-square text-primary me-2"></i>
                                Editar Equipo
                            </h5>
                            <p class="text-muted mb-0 small">{{ $equipo->nombre }}</p>
                        </div>
                    </div>
                    <div class="badge bg-{{ $equipo->activo ? 'success' : 'danger' }} bg-opacity-10 text-{{ $equipo->activo ? 'success' : 'danger' }} px-3 py-2">
                        <i class="bi bi-circle-fill me-1 small"></i>
                        {{ $equipo->activo ? 'Activo' : 'Inactivo' }}
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <form id="equipoForm" action="{{ route('equipos.update', $equipo->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    
                    <!-- Información Básica -->
                    <div class="mb-5">
                        <h6 class="border-bottom pb-2 mb-4 fw-semibold text-muted">
                            <i class="bi bi-info-circle me-1"></i> Información Básica
                        </h6>
                        
                        <div class="row">
                            <!-- Nombre del Equipo -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-medium required">
                                    <i class="bi bi-tag me-1"></i> Nombre del Equipo
                                </label>
                                <input type="text" 
                                       class="form-control @error('nombre') is-invalid @enderror" 
                                       id="nombre" 
                                       name="nombre" 
                                       value="{{ old('nombre', $equipo->nombre) }}" 
                                       placeholder="Ej: Real Madrid CF"
                                       required
                                       autofocus>
                                @error('nombre')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">Nombre completo y oficial del equipo</small>
                                @enderror
                            </div>
                            
                            <!-- Abreviación -->
                            <div class="col-md-6 mb-3">
                                <label for="abreviacion" class="form-label fw-medium">
                                    <i class="bi bi-type me-1"></i> Abreviación
                                </label>
                                <input type="text" 
                                       class="form-control @error('abreviacion') is-invalid @enderror" 
                                       id="abreviacion" 
                                       name="abreviacion" 
                                       value="{{ old('abreviacion', $equipo->abreviacion) }}" 
                                       placeholder="Ej: RMA"
                                       maxlength="10">
                                @error('abreviacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">Máximo 10 caracteres</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Ciudad -->
                            <div class="col-md-6 mb-3">
                                <label for="ciudad" class="form-label fw-medium required">
                                    <i class="bi bi-geo-alt me-1"></i> Ciudad
                                </label>
                                <input type="text" 
                                       class="form-control @error('ciudad') is-invalid @enderror" 
                                       id="ciudad" 
                                       name="ciudad" 
                                       value="{{ old('ciudad', $equipo->ciudad) }}" 
                                       placeholder="Ej: Madrid"
                                       required>
                                @error('ciudad')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Estadio -->
                            <div class="col-md-6 mb-3">
                                <label for="estadio" class="form-label fw-medium">
                                    <i class="bi bi-house-door me-1"></i> Estadio / Sede
                                </label>
                                <input type="text" 
                                       class="form-control @error('estadio') is-invalid @enderror" 
                                       id="estadio" 
                                       name="estadio" 
                                       value="{{ old('estadio', $equipo->estadio) }}" 
                                       placeholder="Ej: Santiago Bernabéu">
                                @error('estadio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detalles Adicionales -->
                    <div class="mb-5">
                        <h6 class="border-bottom pb-2 mb-4 fw-semibold text-muted">
                            <i class="bi bi-gear me-1"></i> Detalles Adicionales
                        </h6>
                        
                        <div class="row">
                            <!-- Año de Fundación -->
                            <div class="col-md-4 mb-3">
                                <label for="fundacion" class="form-label fw-medium">
                                    <i class="bi bi-calendar-event me-1"></i> Año de Fundación
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('fundacion') is-invalid @enderror" 
                                           id="fundacion" 
                                           name="fundacion" 
                                           value="{{ old('fundacion', $equipo->fundacion) }}" 
                                           min="1800" 
                                           max="{{ date('Y') + 1 }}"
                                           placeholder="1902">
                                    <span class="input-group-text">año</span>
                                </div>
                                @error('fundacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Capacidad del Estadio -->
                            <div class="col-md-4 mb-3">
                                <label for="capacidad_estadio" class="form-label fw-medium">
                                    <i class="bi bi-people me-1"></i> Capacidad
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('capacidad_estadio') is-invalid @enderror" 
                                           id="capacidad_estadio" 
                                           name="capacidad_estadio" 
                                           value="{{ old('capacidad_estadio', $equipo->capacidad_estadio) }}" 
                                           min="0"
                                           step="100"
                                           placeholder="81044">
                                    <span class="input-group-text">espect.</span>
                                </div>
                                @error('capacidad_estadio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- URL del Escudo -->
                            <div class="col-md-4 mb-3">
                                <label for="escudo_url" class="form-label fw-medium">
                                    <i class="bi bi-image me-1"></i> URL del Escudo
                                </label>
                                <input type="url" 
                                       class="form-control @error('escudo_url') is-invalid @enderror" 
                                       id="escudo_url" 
                                       name="escudo_url" 
                                       value="{{ old('escudo_url', $equipo->escudo_url) }}" 
                                       placeholder="https://ejemplo.com/escudo.png"
                                       pattern="https?://.*">
                                @error('escudo_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">URL de la imagen del escudo</small>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Presidente -->
                            <div class="col-md-6 mb-3">
                                <label for="presidente" class="form-label fw-medium">
                                    <i class="bi bi-person-badge me-1"></i> Presidente
                                </label>
                                <input type="text" 
                                       class="form-control @error('presidente') is-invalid @enderror" 
                                       id="presidente" 
                                       name="presidente" 
                                       value="{{ old('presidente', $equipo->presidente) }}" 
                                       placeholder="Nombre completo del presidente">
                                @error('presidente')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- Entrenador -->
                            <div class="col-md-6 mb-3">
                                <label for="entrenador" class="form-label fw-medium">
                                    <i class="bi bi-person-video3 me-1"></i> Director Técnico
                                </label>
                                <input type="text" 
                                       class="form-control @error('entrenador') is-invalid @enderror" 
                                       id="entrenador" 
                                       name="entrenador" 
                                       value="{{ old('entrenador', $equipo->entrenador) }}" 
                                       placeholder="Nombre completo del entrenador">
                                @error('entrenador')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colores del Equipo -->
                    <div class="mb-5">
                        <h6 class="border-bottom pb-2 mb-4 fw-semibold text-muted">
                            <i class="bi bi-palette me-1"></i> Colores Representativos
                        </h6>
                        
                        <div class="row g-3" id="colores-container">
                            @php
                                $colores = old('colores', $equipo->colores ?? ['#1E40AF', '#FFFFFF', '#DC2626']);
                            @endphp
                            
                            @foreach($colores as $index => $color)
                                <div class="col-md-4 color-input-group" data-index="{{ $index }}">
                                    <label class="form-label fw-medium small">
                                        Color {{ $index == 0 ? 'Primario' : ($index == 1 ? 'Secundario' : 'Terciario') }}
                                        @if($index >= 3)
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-color ms-2" onclick="removeColor(this)">
                                                <i class="bi bi-trash small"></i>
                                            </button>
                                        @endif
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text p-0 border-0">
                                            <input type="color" 
                                                   class="form-control form-control-color p-1 border-0 color-picker"
                                                   value="{{ $color }}"
                                                   title="Seleccionar color">
                                        </span>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('colores.'.$index) is-invalid @enderror color-input" 
                                               name="colores[]" 
                                               value="{{ $color }}"
                                               placeholder="#RRGGBB"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7">
                                    </div>
                                    @error('colores.'.$index)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addColor()">
                                <i class="bi bi-plus-circle me-1"></i> Añadir Color
                            </button>
                            <small class="form-text text-muted ms-2">Formato hexadecimal (ej: #FF0000)</small>
                        </div>
                    </div>
                    
                    <!-- Estado del Equipo -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-4 fw-semibold text-muted">
                            <i class="bi bi-toggle-on me-1"></i> Estado del Equipo
                        </h6>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="activo" 
                                   name="activo" 
                                   value="1" 
                                   {{ old('activo', $equipo->activo) ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="activo">
                                Equipo Activo
                            </label>
                            <div class="form-text text-muted">
                                Los equipos inactivos no participarán en nuevas temporadas ni torneos
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="border-top pt-4 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('equipos.show', $equipo->id) }}" class="btn btn-outline-secondary me-2">
                                    <i class="bi bi-eye me-1"></i> Ver Detalles
                                </a>
                                <a href="{{ route('equipos.index') }}" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                            </div>
                            
                            <div class="btn-group">
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Restablecer
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                    <i class="bi bi-check-circle me-1"></i> Actualizar Equipo
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Panel Lateral -->
    <div class="col-lg-4">
        <!-- Información del Equipo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-square me-2"></i> Información Actual
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        @if($equipo->escudo_url)
                            <img src="{{ $equipo->escudo_url }}" 
                                 alt="Escudo {{ $equipo->nombre }}"
                                 class="rounded-circle border shadow-sm"
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100\" height=\"100\" fill=\"%236c757d\" class=\"bi bi-shield\" viewBox=\"0 0 16 16\"><path d=\"M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56\"/></svg>'">
                        @else
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border" 
                                 style="width: 100px; height: 100px;">
                                <i class="bi bi-shield text-secondary" style="font-size: 2.5rem;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="fw-bold mb-1">{{ $equipo->nombre }}</h5>
                    @if($equipo->abreviacion)
                        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2">
                            {{ $equipo->abreviacion }}
                        </div>
                    @endif
                </div>
                
                <div class="list-group list-group-flush small">
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted">
                            <i class="bi bi-geo-alt me-2"></i> Ciudad
                        </span>
                        <span class="fw-medium">{{ $equipo->ciudad }}</span>
                    </div>
                    @if($equipo->estadio)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted">
                            <i class="bi bi-house-door me-2"></i> Estadio
                        </span>
                        <span class="fw-medium">{{ $equipo->estadio }}</span>
                    </div>
                    @endif
                    @if($equipo->fundacion)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted">
                            <i class="bi bi-calendar-event me-2"></i> Fundación
                        </span>
                        <span class="fw-medium">{{ $equipo->fundacion }}</span>
                    </div>
                    @endif
                    @if($equipo->presidente)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted">
                            <i class="bi bi-person-badge me-2"></i> Presidente
                        </span>
                        <span class="fw-medium">{{ $equipo->presidente }}</span>
                    </div>
                    @endif
                    @if($equipo->entrenador)
                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                        <span class="text-muted">
                            <i class="bi bi-person-video3 me-2"></i> Entrenador
                        </span>
                        <span class="fw-medium">{{ $equipo->entrenador }}</span>
                    </div>
                    @endif
                </div>
                
                <!-- Colores Actuales -->
                @if($equipo->colores && count($equipo->colores) > 0)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-semibold mb-3">Colores Actuales</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($equipo->colores as $color)
                            <div class="color-sample rounded" 
                                 style="background-color: {{ $color }}; width: 40px; height: 40px;"
                                 title="{{ $color }}"></div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Vista Previa -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-eye me-2"></i> Vista Previa
                </h6>
            </div>
            <div class="card-body p-4">
                <div id="previewSection" class="text-center">
                    <div id="previewEscudo" class="mb-3">
                        @if($equipo->escudo_url)
                            <img src="{{ $equipo->escudo_url }}" 
                                 class="rounded-circle border shadow-sm"
                                 style="width: 80px; height: 80px; object-fit: cover;"
                                 onerror="this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"80\" height=\"80\" fill=\"%236c757d\" class=\"bi bi-shield\" viewBox=\"0 0 16 16\"><path d=\"M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56\"/></svg>'">
                        @else
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border" 
                                 style="width: 80px; height: 80px;">
                                <i class="bi bi-shield text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        @endif
                    </div>
                    <h6 id="previewNombre" class="fw-bold mb-1">{{ $equipo->nombre }}</h6>
                    <p id="previewAbreviacion" class="text-muted small mb-2">{{ $equipo->abreviacion ?? 'ABR' }}</p>
                </div>
                
                <div class="mt-3 pt-3 border-top">
                    <h6 class="fw-semibold mb-2">Colores Previsualizados</h6>
                    <div id="previewColores" class="d-flex gap-2">
                        @if($equipo->colores && count($equipo->colores) > 0)
                            @foreach($equipo->colores as $color)
                                <div class="color-sample rounded" 
                                     style="background-color: {{ $color }}; width: 30px; height: 30px;"></div>
                            @endforeach
                        @else
                            <div class="color-sample rounded" style="background-color: #1E40AF; width: 30px; height: 30px;"></div>
                            <div class="color-sample rounded" style="background-color: #FFFFFF; width: 30px; height: 30px; border: 1px solid #dee2e6;"></div>
                            <div class="color-sample rounded" style="background-color: #DC2626; width: 30px; height: 30px;"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .required:after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }
    
    .form-control-color {
        width: 40px;
        height: 40px;
        border: none;
        cursor: pointer;
        padding: 2px;
    }
    
    .color-sample {
        transition: transform 0.2s;
    }
    
    .color-sample:hover {
        transform: scale(1.1);
    }
    
    .btn-remove-color {
        padding: 0.1rem 0.3rem;
        font-size: 0.7rem;
    }
    
    .color-input-group {
        transition: all 0.3s ease;
    }
    
    .color-input-group.removing {
        opacity: 0;
        transform: translateX(-20px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos principales
        const form = document.getElementById('equipoForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Elementos de preview
        const previewNombre = document.getElementById('previewNombre');
        const previewAbreviacion = document.getElementById('previewAbreviacion');
        const previewEscudo = document.getElementById('previewEscudo');
        const previewColores = document.getElementById('previewColores');
        
        // Inicializar sincronización de colores
        initializeColorSync();
        
        // Preview en tiempo real - Nombre
        document.getElementById('nombre').addEventListener('input', function() {
            previewNombre.textContent = this.value || 'Nombre del Equipo';
        });
        
        // Preview en tiempo real - Abreviación
        document.getElementById('abreviacion').addEventListener('input', function() {
            const abbr = this.value.toUpperCase() || '';
            previewAbreviacion.textContent = abbr || 'ABR';
        });
        
        // Preview en tiempo real - Escudo
        document.getElementById('escudo_url').addEventListener('input', function() {
            const url = this.value.trim();
            
            if (url && isValidUrl(url)) {
                previewEscudo.innerHTML = `
                    <img src="${url}" 
                         class="rounded-circle border shadow-sm"
                         style="width: 80px; height: 80px; object-fit: cover;"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"80\" height=\"80\" fill=\"%236c757d\" class=\"bi bi-shield\" viewBox=\"0 0 16 16\"><path d=\"M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56\"/></svg>'"
                         alt="Escudo">
                `;
            } else {
                previewEscudo.innerHTML = `
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-shield text-secondary" style="font-size: 2rem;"></i>
                    </div>
                `;
            }
        });
        
        // Validación del formulario
        form.addEventListener('submit', function(e) {
            // Validar campos requeridos
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            // Validar colores
            const colorInputs = form.querySelectorAll('.color-input');
            colorInputs.forEach(input => {
                const value = input.value.trim();
                if (value && !value.match(/^#[0-9A-Fa-f]{6}$/)) {
                    input.classList.add('is-invalid');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Por favor, complete correctamente todos los campos requeridos.', 'error');
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Actualizando...';
            }
        });
        
        // Limpiar validación al editar
        form.addEventListener('input', function(e) {
            if (e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
            }
        });
        
        // Helper functions
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        function showToast(message, type = 'info') {
            if (typeof toastr !== 'undefined') {
                toastr[type === 'error' ? 'error' : 'info'](message);
            } else {
                alert(message);
            }
        }
        
        // Funciones para manejar colores
        window.addColor = function() {
            const container = document.getElementById('colores-container');
            const count = container.querySelectorAll('.color-input-group').length;
            const colorNames = ['Cuaternario', 'Quinario', 'Senario', 'Septenario', 'Octonario'];
            const name = colorNames[count - 3] || `Color ${count + 1}`;
            
            const newGroup = document.createElement('div');
            newGroup.className = 'col-md-4 color-input-group';
            newGroup.setAttribute('data-index', count);
            newGroup.innerHTML = `
                <label class="form-label fw-medium small">
                    ${name}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-color ms-2" onclick="removeColor(this)">
                        <i class="bi bi-trash small"></i>
                    </button>
                </label>
                <div class="input-group">
                    <span class="input-group-text p-0 border-0">
                        <input type="color" class="form-control form-control-color p-1 border-0 color-picker" value="#6B7280">
                    </span>
                    <input type="text" class="form-control text-uppercase color-input" name="colores[]" value="#6B7280" placeholder="#RRGGBB" pattern="^#[0-9A-Fa-f]{6}$" maxlength="7">
                </div>
            `;
            
            container.appendChild(newGroup);
            initializeColorSyncForElement(newGroup);
            updateColorPreview();
        };
        
        window.removeColor = function(button) {
            const group = button.closest('.color-input-group');
            group.classList.add('removing');
            
            setTimeout(() => {
                group.remove();
                updateColorPreview();
            }, 300);
        };
        
        function initializeColorSync() {
            document.querySelectorAll('.color-input-group').forEach(group => {
                initializeColorSyncForElement(group);
            });
        }
        
        function initializeColorSyncForElement(group) {
            const colorPicker = group.querySelector('.color-picker');
            const colorInput = group.querySelector('.color-input');
            
            if (colorPicker && colorInput) {
                // Sincronizar color picker con input text
                colorPicker.addEventListener('input', function() {
                    colorInput.value = this.value.toUpperCase();
                    updateColorPreview();
                });
                
                // Sincronizar input text con color picker
                colorInput.addEventListener('input', function() {
                    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                        colorPicker.value = this.value;
                        updateColorPreview();
                    }
                });
            }
        }
        
        function updateColorPreview() {
            const colorInputs = document.querySelectorAll('.color-input');
            const colors = Array.from(colorInputs)
                .map(input => input.value.trim())
                .filter(color => color.match(/^#[0-9A-Fa-f]{6}$/));
            
            previewColores.innerHTML = '';
            
            colors.forEach(color => {
                const sample = document.createElement('div');
                sample.className = 'color-sample rounded';
                sample.style = `background-color: ${color}; width: 30px; height: 30px;`;
                sample.title = color;
                previewColores.appendChild(sample);
            });
        }
        
        // Auto-capitalize abbreviation
        document.getElementById('abreviacion').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Auto-capitalize color inputs
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('color-input')) {
                e.target.value = e.target.value.toUpperCase();
            }
        });
    });
</script>
@endpush
@endsection