@extends('admin.layouts.app')

@section('title', 'Photo Strip #' . $photoStrip->id)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-image me-2"></i> Photo Strip #{{ $photoStrip->id }}</h1>
        <p class="text-muted">View photo strip details</p>
    </div>
    <a href="{{ route('admin.photo-strips.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

{{-- Success/Error Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Photo Strip Image -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-image me-2"></i> Photo Strip</h5>
            </div>
            <div class="card-body text-center">
                @if($photoStrip->final_image_path && Storage::disk('public')->exists($photoStrip->final_image_path))
                    <img src="{{ Storage::url($photoStrip->final_image_path) }}" 
                         class="img-fluid rounded shadow-sm" 
                         alt="Photo Strip #{{ $photoStrip->id }}"
                         style="max-height: 800px;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                         style="height: 600px;">
                        <div class="text-center">
                            <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                            <p class="text-muted mt-3 mb-1">Image not found</p>
                            <small class="text-danger">{{ $photoStrip->final_image_path ?? 'No path stored' }}</small>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-white">
                <div class="d-flex gap-2 justify-content-center">
                    @if($photoStrip->final_image_path && Storage::disk('public')->exists($photoStrip->final_image_path))
                        <a href="{{ route('photobooth.download', $photoStrip->id) }}" 
                           class="btn btn-primary">
                            <i class="bi bi-download"></i> Download
                        </a>
                    @endif
                    
                    {{-- FIXED: Delete Button dengan ID yang benar dan konfirmasi yang lebih baik --}}
                    <form id="deletePhotoStripForm" 
                          action="{{ route('admin.photo-strips.destroy', $photoStrip->id) }}" 
                          method="POST" 
                          class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" 
                                onclick="confirmDeletePhotoStrip()" 
                                class="btn btn-danger">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Details -->
    <div class="col-lg-4">
        <!-- User Info -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i> User Information</h5>
            </div>
            <div class="card-body">
                @if($photoStrip->user)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-circle me-3">
                            {{ strtoupper(substr($photoStrip->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $photoStrip->user->name }}</h6>
                            <small class="text-muted">{{ $photoStrip->user->email }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.users.show', $photoStrip->user) }}" 
                       class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye"></i> View Profile
                    </a>
                @else
                    <div class="alert alert-warning mb-0">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-person-x me-2"></i>
                            <strong>Guest User</strong>
                        </div>
                        <p class="mb-0 small">Pengguna tidak login saat membuat photo strip ini.</p>
                        @if($photoStrip->guest_session_id)
                            <hr class="my-2">
                            <small class="text-muted">
                                <i class="bi bi-fingerprint me-1"></i>
                                Session: <code>{{ substr($photoStrip->guest_session_id, 0, 20) }}...</code>
                            </small>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Strip Details -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i> Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">ID:</td>
                        <td><strong>#{{ $photoStrip->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Photo Count:</td>
                        <td><span class="badge bg-info">{{ $photoStrip->photo_count }} photos</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($photoStrip->is_saved)
                                <span class="badge bg-success">
                                    <i class="bi bi-bookmark-fill"></i> Saved
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-bookmark"></i> Not Saved
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Frame:</td>
                        <td>
                            @if($photoStrip->frame)
                                <div class="d-flex align-items-center">
                                    @if($photoStrip->frame->image_path && Storage::disk('public')->exists($photoStrip->frame->image_path))
                                        <img src="{{ Storage::url($photoStrip->frame->image_path) }}" 
                                             alt="{{ $photoStrip->frame->name }}"
                                             class="me-2 rounded"
                                             style="width: 30px; height: 30px; object-fit: cover;">
                                    @endif
                                    <span>{{ $photoStrip->frame->name }}</span>
                                </div>
                            @else
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">IP Address:</td>
                        <td><code class="small">{{ $photoStrip->ip_address ?? 'N/A' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Created:</td>
                        <td>
                            <div>{{ $photoStrip->created_at->format('d M Y, H:i') }}</div>
                            <small class="text-muted">({{ $photoStrip->created_at->diffForHumans() }})</small>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Updated:</td>
                        <td>
                            <div>{{ $photoStrip->updated_at->format('d M Y, H:i') }}</div>
                            <small class="text-muted">({{ $photoStrip->updated_at->diffForHumans() }})</small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- File Info (Technical Details) -->
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-file-earmark-code me-2"></i> File Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block mb-1">File Path:</small>
                    <code class="small d-block text-break">{{ $photoStrip->final_image_path ?? 'N/A' }}</code>
                </div>
                <div>
                    <small class="text-muted d-block mb-1">File Status:</small>
                    @if($photoStrip->final_image_path && Storage::disk('public')->exists($photoStrip->final_image_path))
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> File exists
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle"></i> File not found
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.3rem;
    flex-shrink: 0;
}

.card {
    transition: box-shadow 0.2s;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
</style>
@endpush

@push('scripts')
<script>
function confirmDeletePhotoStrip() {
    // Menggunakan confirm dialog native browser yang lebih reliable
    const confirmed = confirm(
        'KONFIRMASI HAPUS\n\n' +
        'Apakah Anda yakin ingin menghapus photo strip ini?\n\n' +
        'Tindakan ini akan:\n' +
        '• Menghapus file gambar dari server\n' +
        '• Menghapus record dari database\n' +
        '• TIDAK DAPAT dibatalkan!\n\n' +
        'Klik OK untuk melanjutkan atau Cancel untuk membatalkan.'
    );
    
    if (confirmed) {
        console.log('Delete confirmed, submitting form...');
        document.getElementById('deletePhotoStripForm').submit();
    } else {
        console.log('Delete cancelled by user');
    }
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
@endpush
