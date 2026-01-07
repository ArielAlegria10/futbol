{{-- resources/views/partials/notifications.blade.php --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>¡Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle-fill me-2"></i>
        <strong>¡Advertencia!</strong> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Información:</strong> {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Por favor corrige los siguientes errores:</strong>
        <ul class="mb-0 mt-2 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Toastr Notifications (si usas Toastr) --}}
@if(session('toastr'))
    <script>
        $(document).ready(function() {
            @if(session('toastr.type') == 'success')
                toastr.success("{{ session('toastr.message') }}", "{{ session('toastr.title', '¡Éxito!') }}");
            @elseif(session('toastr.type') == 'error')
                toastr.error("{{ session('toastr.message') }}", "{{ session('toastr.title', '¡Error!') }}");
            @elseif(session('toastr.type') == 'warning')
                toastr.warning("{{ session('toastr.message') }}", "{{ session('toastr.title', '¡Advertencia!') }}");
            @elseif(session('toastr.type') == 'info')
                toastr.info("{{ session('toastr.message') }}", "{{ session('toastr.title', 'Información') }}");
            @endif
        });
    </script>
@endif