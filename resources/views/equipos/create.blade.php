@extends('layouts.app')

@section('title', 'Crear Nuevo Equipo')
@section('icon', 'bi-plus-circle')
@section('subtitle', 'Registra un nuevo equipo en el sistema')

@section('breadcrumb-items')
    <li class="breadcrumb-item">
        <a href="{{ route('equipos.index') }}" class="text-decoration-none">
            <i class="bi bi-people me-1"></i> Equipos
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
        <i class="bi bi-plus-circle me-1"></i> Nuevo Equipo
    </li>
@endsection

@section('header-buttons')
    <a href="{{ route('equipos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Volver al Listado
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-clipboard-plus text-primary me-2"></i>
                    Información del Equipo
                </h5>
            </div>
            <div class="card-body p-4">
                <form id="equipoForm" action="{{ route('equipos.store') }}" method="POST" novalidate>
                    @csrf
                    
                    <!-- Información Básica -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold text-muted">
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
                                       value="{{ old('nombre') }}" 
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
                                       value="{{ old('abreviacion') }}" 
                                       placeholder="Ej: RMA"
                                       maxlength="10"
                                       data-auto-generate="true">
                                @error('abreviacion')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">Máximo 10 caracteres (se auto-genera si se deja vacío)</small>
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
                                       value="{{ old('ciudad') }}" 
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
                                       value="{{ old('estadio') }}" 
                                       placeholder="Ej: Santiago Bernabéu">
                                @error('estadio')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detalles Adicionales -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold text-muted">
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
                                           value="{{ old('fundacion') }}" 
                                           min="1800" 
                                           max="{{ date('Y') }}"
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
                                           value="{{ old('capacidad_estadio') }}" 
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
                                       value="{{ old('escudo_url') }}" 
                                       placeholder="https://ejemplo.com/escudo.png"
                                       pattern="https://.*">
                                @error('escudo_url')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <small class="form-text text-muted">Solo URLs HTTPS permitidas</small>
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
                                       value="{{ old('presidente') }}" 
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
                                       value="{{ old('entrenador') }}" 
                                       placeholder="Nombre completo del entrenador">
                                @error('entrenador')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colores del Equipo -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold text-muted">
                            <i class="bi bi-palette me-1"></i> Colores Representativos
                        </h6>
                        
                        <div class="row g-3">
                            @php
                                $coloresDefault = ['#1E40AF', '#FFFFFF', '#DC2626'];
                                $coloresOld = old('colores', []);
                            @endphp
                            
                            @for($i = 0; $i < 3; $i++)
                                <div class="col-md-4">
                                    <label class="form-label fw-medium small">
                                        Color {{ $i == 0 ? 'Primario' : ($i == 1 ? 'Secundario' : 'Terciario') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text p-0 border-0">
                                            <input type="color" 
                                                   class="form-control form-control-color p-1 border-0" 
                                                   data-color-index="{{ $i }}"
                                                   value="{{ $coloresOld[$i] ?? $coloresDefault[$i] }}"
                                                   title="Seleccionar color {{ $i + 1 }}">
                                        </span>
                                        <input type="text" 
                                               class="form-control text-uppercase @error('colores.'.$i) is-invalid @enderror" 
                                               name="colores[]" 
                                               value="{{ $coloresOld[$i] ?? $coloresDefault[$i] }}"
                                               placeholder="#RRGGBB"
                                               pattern="^#[0-9A-Fa-f]{6}$"
                                               maxlength="7">
                                    </div>
                                    @error('colores.'.$i)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                        <small class="form-text text-muted mt-2">Formato hexadecimal (ej: #FF0000)</small>
                    </div>
                    
                    <!-- Estado -->
                    <div class="mb-4">
                        <h6 class="border-bottom pb-2 mb-3 fw-semibold text-muted">
                            <i class="bi bi-toggle-on me-1"></i> Estado del Equipo
                        </h6>
                        
                        <div class="form-check form-switch">
                            <input type="hidden" name="activo" value="0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   role="switch" 
                                   id="activo" 
                                   name="activo" 
                                   value="1" 
                                   {{ old('activo', true) ? 'checked' : '' }}>
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
                            <a href="{{ route('equipos.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                            
                            <div class="btn-group">
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Restablecer
                                </button>
                                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                                    <i class="bi bi-check-circle me-1"></i> Guardar Equipo
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
        <!-- Vista Previa -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-eye me-2"></i> Vista Previa
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div id="escudoPreview" class="mb-3">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border" 
                             style="width: 120px; height: 120px;">
                            <i class="bi bi-shield text-secondary" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    
                    <h4 id="previewNombre" class="fw-bold mb-1">Nombre del Equipo</h4>
                    <div class="badge bg-primary bg-opacity-10 text-primary fs-6 mb-3 px-3 py-2" id="previewAbreviacion">
                        ABR
                    </div>
                    
                    <div class="text-start">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-geo-alt text-muted me-2"></i>
                            <span id="previewCiudad" class="text-muted">Ciudad</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-house-door text-muted me-2"></i>
                            <span id="previewEstadio" class="text-muted">Estadio</span>
                        </div>
                    </div>
                </div>
                
                <!-- Muestra de Colores -->
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-semibold mb-3">Colores del Equipo</h6>
                    <div class="d-flex gap-2">
                        <div class="color-sample" data-color-index="0" 
                             style="background-color: #1E40AF;"></div>
                        <div class="color-sample" data-color-index="1" 
                             style="background-color: #FFFFFF; border: 1px solid #dee2e6;"></div>
                        <div class="color-sample" data-color-index="2" 
                             style="background-color: #DC2626;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información de Ayuda -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-info-circle me-2"></i> Guía Rápida
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-light border mb-3">
                    <div class="d-flex">
                        <i class="bi bi-lightbulb text-warning fs-5 me-2"></i>
                        <div>
                            <h6 class="fw-semibold mb-1">Consejos</h6>
                            <ul class="mb-0 ps-3 small">
                                <li>Verifica la unicidad del nombre y abreviatura</li>
                                <li>Usa HTTPS para imágenes externas</li>
                                <li>Los colores deben ser en formato hexadecimal</li>
                                <li>Revisa el año de fundación históricamente</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-light border">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle text-danger fs-5 me-2"></i>
                        <div>
                            <h6 class="fw-semibold mb-1">Campos Requeridos</h6>
                            <p class="mb-0 small">
                                Solo los campos marcados con <span class="text-danger fw-bold">*</span> son obligatorios. 
                                Los demás son opcionales pero recomendados.
                            </p>
                        </div>
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
        width: 40px;
        height: 40px;
        border-radius: 6px;
        transition: transform 0.2s;
    }
    
    .color-sample:hover {
        transform: scale(1.1);
    }
    
    #previewAbreviacion {
        letter-spacing: 1px;
        font-family: 'Courier New', monospace;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
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
        const previewCiudad = document.getElementById('previewCiudad');
        const previewEstadio = document.getElementById('previewEstadio');
        const escudoPreview = document.getElementById('escudoPreview');
        
        // Sincronización de colores entre inputs color y text
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const index = colorInput.dataset.colorIndex;
            const textInput = document.querySelector(`input[name="colores[]"][value="${colorInput.value}"]`);
            const colorSample = document.querySelector(`.color-sample[data-color-index="${index}"]`);
            
            if (textInput) {
                // Sincronizar color picker con input text
                colorInput.addEventListener('input', function() {
                    textInput.value = this.value.toUpperCase();
                    if (colorSample) {
                        colorSample.style.backgroundColor = this.value;
                    }
                });
                
                // Sincronizar input text con color picker
                textInput.addEventListener('input', function() {
                    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                        colorInput.value = this.value;
                        if (colorSample) {
                            colorSample.style.backgroundColor = this.value;
                        }
                    }
                });
                
                // Actualizar muestra de color inicial
                if (colorSample) {
                    colorSample.style.backgroundColor = colorInput.value;
                }
            }
        });
        
        // Preview en tiempo real
        document.getElementById('nombre').addEventListener('input', function() {
            previewNombre.textContent = this.value || 'Nombre del Equipo';
        });
        
        document.getElementById('abreviacion').addEventListener('input', function() {
            const abbr = this.value.toUpperCase() || 'ABR';
            previewAbreviacion.textContent = abbr;
            previewAbreviacion.title = abbr;
        });
        
        document.getElementById('ciudad').addEventListener('input', function() {
            previewCiudad.textContent = this.value || 'Ciudad';
        });
        
        document.getElementById('estadio').addEventListener('input', function() {
            previewEstadio.textContent = this.value || 'Estadio';
        });
        
        // Auto-generación de abreviatura
        document.getElementById('nombre').addEventListener('blur', function() {
            const abbrInput = document.getElementById('abreviacion');
            if (abbrInput.dataset.autoGenerate === 'true' && !abbrInput.value.trim()) {
                const nombre = this.value.trim();
                if (nombre) {
                    let abbr = '';
                    const palabras = nombre.split(' ').filter(p => p.length > 0);
                    
                    if (palabras.length >= 2) {
                        // Tomar primeras letras de las primeras 3 palabras
                        for (let i = 0; i < Math.min(3, palabras.length); i++) {
                            abbr += palabras[i].charAt(0).toUpperCase();
                        }
                    } else if (nombre.length >= 3) {
                        // Tomar primeras 3 letras
                        abbr = nombre.substring(0, 3).toUpperCase();
                    } else {
                        // Si el nombre es muy corto
                        abbr = nombre.toUpperCase();
                    }
                    
                    abbrInput.value = abbr;
                    previewAbreviacion.textContent = abbr;
                }
            }
        });
        
        // Preview de escudo
        document.getElementById('escudo_url').addEventListener('input', function() {
            const url = this.value.trim();
            
            if (url && isValidUrl(url)) {
                escudoPreview.innerHTML = `
                    <img src="${url}" 
                         class="rounded-circle border shadow-sm" 
                         style="width: 120px; height: 120px; object-fit: cover;"
                         onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"120\" height=\"120\" fill=\"%236c757d\" class=\"bi bi-shield\" viewBox=\"0 0 16 16\"><path d=\"M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56\"/></svg>'"
                         alt="Escudo del equipo">
                `;
            } else {
                escudoPreview.innerHTML = `
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center border" 
                         style="width: 120px; height: 120px;">
                        <i class="bi bi-shield text-secondary" style="font-size: 3rem;"></i>
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
            const colorInputs = form.querySelectorAll('input[name="colores[]"]');
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
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Guardando...';
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
                const url = new URL(string);
                return url.protocol === 'https:';
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
    });
</script>
@endpush
@endsection