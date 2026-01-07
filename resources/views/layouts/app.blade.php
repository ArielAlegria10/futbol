<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema de gestión de fútbol profesional - Administración de equipos, jugadores, partidos y estadísticas">
    <meta name="author" content="Futbol Pro System">
    <meta name="keywords" content="fútbol, equipos, jugadores, partidos, estadísticas, liga, campeonato">
    
    <title>@yield('title', 'Dashboard') | GESTION DE FUTBOL</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" crossorigin="anonymous">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" crossorigin="anonymous">
    
    <!-- Estilos principales -->
    <style>
        :root {
            --primary-color: #1e40af;
            --primary-dark: #1e3a8a;
            --secondary-color: #dc2626;
            --success-color: #059669;
            --info-color: #0ea5e9;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --grass-color: #10b981;
            --dark-color: #0f172a;
            --light-color: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-lg: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar-soccer {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            box-shadow: var(--shadow-lg);
            height: 72px;
            padding: 0 1rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            border-bottom: 3px solid var(--secondary-color);
        }

        .navbar-brand-soccer {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.75rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0;
        }

        .navbar-brand-soccer:hover {
            color: white;
            opacity: 0.9;
        }

        /* Main Content */
        .main-content-soccer {
            margin-top: 72px;
            flex: 1 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 72px - 200px);
        }

        @media (max-width: 768px) {
            .main-content-soccer {
                padding: 1rem;
            }
        }

        /* Footer */
        .footer-soccer {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1e293b 100%);
            color: white;
            padding: 2rem 0;
            margin-top: auto;
        }

        /* Glass Effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-lg);
        }

        /* Card Hover */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg) !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: var(--radius);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: var(--radius);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Loading Animation */
        .spinner {
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid currentColor;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Badge Soccer */
        .badge-soccer {
            background: linear-gradient(135deg, var(--secondary-color), var(--warning-color));
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Match Card */
        .match-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .match-card:hover {
            border-color: var(--primary-color);
            box-shadow: var(--shadow-md);
        }

        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            border: 2px solid transparent;
            background-clip: padding-box;
            position: relative;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: var(--radius-lg);
            z-index: -1;
        }

        /* Gradient Text */
        .text-gradient {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Live Indicator */
        .live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--secondary-color);
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(220, 38, 38, 0);
            }
        }

        /* Table Soccer */
        .table-soccer {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-soccer thead {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .table-soccer tbody tr:nth-child(even) {
            background-color: var(--gray-100);
        }

        .table-soccer tbody tr:hover {
            background-color: var(--gray-200);
        }

        /* Position Colors */
        .position-1 {
            background-color: rgba(255, 215, 0, 0.1) !important;
            border-left: 4px solid gold;
        }

        .position-2 {
            background-color: rgba(192, 192, 192, 0.1) !important;
            border-left: 4px solid silver;
        }

        .position-3 {
            background-color: rgba(205, 127, 50, 0.1) !important;
            border-left: 4px solid #cd7f32;
        }

        /* Button Soccer */
        .btn-soccer {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-soccer:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            color: white;
        }

        .btn-soccer-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-soccer-outline:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
    
    <!-- Estilos personalizados adicionales -->
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-soccer">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="bi bi-trophy-fill me-2"></i>
                <span class="navbar-brand-soccer">FUTBOL</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarSoccer" aria-controls="navbarSoccer"
                    aria-label="Toggle navigation">
                <i class="bi bi-grid-3x3-gap text-white fs-4"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSoccer">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <!-- Dashboard -->
                    <li class="nav-item mx-1">
                        <a class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ request()->routeIs('dashboard') ? 'bg-primary bg-opacity-25' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>INICIO</span>
                        </a>
                    </li>
                    
                    <!-- Equipos -->
                    @php
                        $equipoCount = class_exists('App\\Models\\Equipo') ? App\Models\Equipo::count() : 0;
                    @endphp
                    <li class="nav-item dropdown mx-1">
                        <a class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 dropdown-toggle {{ request()->routeIs('equipos.*') ? 'bg-primary bg-opacity-25' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people-fill"></i>
                            <span>Equipos</span>
                            @if($equipoCount > 0)
                                <span class="badge bg-secondary ms-2">{{ $equipoCount }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('equipos.index') }}"><i class="bi bi-list-columns"></i> Listar Equipos</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('equipos.create') }}"><i class="bi bi-plus-circle"></i> Nuevo Equipo</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="bi bi-diagram-3"></i> Plantillas</a></li>
                        </ul>
                    </li>
                    
                    <!-- Temporadas -->
                    <li class="nav-item mx-1">
                        <a class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ request()->routeIs('temporadas.*') ? 'bg-primary bg-opacity-25' : '' }}"
                           href="{{ route('temporadas.index') }}">
                            <i class="bi bi-calendar-range"></i>
                            <span>Temporadas</span>
                        </a>
                    </li>
                    
                    <!-- Partidos -->
                    <li class="nav-item dropdown mx-1">
                        <a class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 dropdown-toggle {{ request()->routeIs('partidos.*') ? 'bg-primary bg-opacity-25' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar-event"></i>
                            <span>Partidos</span>
                            <span class="live-indicator"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('partidos.index') }}"><i class="bi bi-calendar3"></i> Calendario</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('partidos.create') }}"><i class="bi bi-plus-lg"></i> Programar Partido</a></li>
                        </ul>
                    </li>
                    
                    <!-- Clasificación -->
                    <li class="nav-item mx-1">
                        <a class="nav-link text-white d-flex align-items-center gap-2 px-3 py-2 rounded-3 {{ request()->routeIs('clasificacion.*') ? 'bg-primary bg-opacity-25' : '' }}"
                           href="{{ route('clasificacion.index') }}">
                            <i class="bi bi-trophy"></i>
                            <span>Clasificación</span>
                        </a>
                    </li>
                </ul>
                
                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-outline-light d-flex align-items-center gap-2 px-3 py-2 rounded-pill border-2 dropdown-toggle"
                            type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-md-inline">Administrador</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                        <li><h6 class="dropdown-header">Director Técnico</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><i class="bi bi-gear"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger w-100">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content-soccer">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="glass-effect p-4 mb-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h1 class="h2 mb-2 text-gradient">
                            <i class="bi @yield('icon', 'bi-speedometer2') me-2"></i>
                            @yield('title', 'Dashboard')
                        </h1>
                        @hasSection('subtitle')
                            <p class="text-muted mb-0 lead">@yield('subtitle')</p>
                        @else
                            <p class="text-muted mb-0">
                                <span class="badge-soccer me-2">EN DIRECTO</span>
                                Actualizado: {{ now()->format('d/m/Y H:i:s') }}
                            </p>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @yield('header-actions')
                        <button class="btn btn-outline-primary d-flex align-items-center gap-2" id="refreshData">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span class="d-none d-md-inline">Actualizar</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill fs-5"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>
                            <p class="mb-1">Por favor corrige los siguientes errores:</p>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <!-- Content -->
            <div class="content-area">
                @yield('content')
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer-soccer">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary rounded-circle p-3">
                            <i class="bi bi-trophy-fill text-white fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Futbol Pro System</h5>
                            <small class="text-white-50">Gestión Profesional de Fútbol</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="text-center">
                        <div class="d-flex justify-content-center gap-3 mb-2">
                            <a href="#" class="text-white-50"><i class="bi bi-twitter fs-5"></i></a>
                            <a href="#" class="text-white-50"><i class="bi bi-facebook fs-5"></i></a>
                            <a href="#" class="text-white-50"><i class="bi bi-instagram fs-5"></i></a>
                        </div>
                        <small class="text-white-50">© {{ date('Y') }} Sistema Oficial</small>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="mb-2">
                        <span class="text-white-50">
                            <i class="bi bi-clock me-2"></i>
                            <span id="server-time">--:--:--</span>
                        </span>
                    </div>
                    <div>
                        <span class="text-white-50">
                            v1.0.0
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button id="backToTop" 
            class="btn btn-primary rounded-circle position-fixed border-0 shadow-lg"
            style="bottom: 2rem; right: 2rem; width: 56px; height: 56px; display: none; z-index: 1000;"
            aria-label="Volver arriba">
        <i class="bi bi-arrow-up fs-5"></i>
    </button>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" crossorigin="anonymous"></script>
    
    <!-- Soccer System Script -->
    <script>
        // Configurar Toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000",
            "extendedTimeOut": "2000"
        };

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Actualizar hora del servidor
            function updateServerTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                document.getElementById('server-time').textContent = timeString;
            }
            
            // Actualizar cada segundo
            setInterval(updateServerTime, 1000);
            updateServerTime();
            
            // Botón volver arriba
            const backToTopButton = document.getElementById('backToTop');
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            });
            
            backToTopButton.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
            
            // Botón de refrescar
            const refreshButton = document.getElementById('refreshData');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    icon.classList.add('spinner');
                    
                    // Simular actualización
                    setTimeout(function() {
                        icon.classList.remove('spinner');
                        toastr.success('Datos actualizados correctamente');
                    }, 1000);
                });
            }
            
            // Auto-ocultar alertas
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Hover effects para cards
            document.querySelectorAll('.card-hover').forEach(function(card) {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = 'var(--shadow-lg)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '';
                });
            });
            
            // CSRF token para AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Live matches simulation
            function updateLiveMatches() {
                // Aquí iría la lógica para actualizar partidos en vivo
                console.log('Actualizando partidos en vivo...');
            }
            
            // Actualizar cada 30 segundos
            setInterval(updateLiveMatches, 30000);
            
            // Notificación de actualización automática
            setTimeout(function() {
                toastr.info('Los datos se actualizan automáticamente cada 30 segundos', 'Actualización en vivo', {
                    timeOut: 3000
                });
            }, 10000);
        });
        
        // Mostrar notificación genérica
        function showNotification(message, type = 'info') {
            switch(type) {
                case 'success':
                    toastr.success(message);
                    break;
                case 'error':
                    toastr.error(message);
                    break;
                case 'warning':
                    toastr.warning(message);
                    break;
                default:
                    toastr.info(message);
            }
        }
        
        // Formatear fecha
        function formatDate(date) {
            return new Date(date).toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
        
        // Formatear hora
        function formatTime(date) {
            return new Date(date).toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Capitalizar texto
        function capitalize(text) {
            return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
        }
    </script>
    
    <!-- Scripts adicionales -->
    @stack('scripts')
</body>
</html>