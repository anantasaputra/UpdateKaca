@extends('admin.layouts.app')

@section('title', 'Frames Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-image me-2"></i> Frames Management</h1>
        <p class="text-muted mb-0">Manage photo booth frames</p>
    </div>
    <a href="{{ route('admin.frames.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Frame
    </a>
</div>

{{-- Statistics Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 opacity-75">Total Frames</h6>
                <h3 class="card-title mb-0">{{ $frames->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 opacity-75">Default Frames</h6>
                <h3 class="card-title mb-0">{{ $frames->where('is_default', true)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 opacity-75">Active Frames</h6>
                <h3 class="card-title mb-0">{{ $frames->where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <h6 class="card-subtitle mb-2 opacity-75">Custom Frames</h6>
                <h3 class="card-title mb-0">{{ $frames->where('is_default', false)->count() }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Frames Grid -->
<div class="row g-4">
    @forelse($frames as $frame)
    <div class="col-md-4 col-lg-3">
        <div class="card frame-card border-0 shadow-sm h-100 {{ $frame->is_default ? 'border-warning' : '' }}" 
             style="{{ $frame->is_default ? 'border: 2px solid #ffc107 !important;' : '' }}">
            
            {{-- Default Frame Badge --}}
            @if($frame->is_default)
            <div class="default-badge">
                <i class="bi bi-star-fill"></i> Default
            </div>
            @endif
            
            <div class="frame-preview">
                @if($frame->image_path && Storage::disk('public')->exists($frame->image_path))
                    <img src="{{ Storage::url($frame->image_path) }}" alt="{{ $frame->name }}" class="frame-image">
                @else
                    <div class="frame-placeholder">
                        <i class="bi bi-image fs-1 text-muted"></i>
                        <p class="text-muted small mt-2">No Image</p>
                    </div>
                @endif
                
                <div class="frame-badge" style="background: {{ $frame->color_code == 'brown' ? '#6B4423' : ($frame->color_code == 'cream' ? '#CBA991' : '#fff') }}; color: {{ $frame->color_code == 'white' ? '#000' : '#fff' }};">
                    {{ $frame->photo_count }} Photos
                </div>
            </div>
            
            <div class="card-body">
                <h5 class="card-title mb-2">{{ $frame->name }}</h5>
                <p class="text-muted small mb-2">{{ Str::limit($frame->description, 50) ?: '-' }}</p>
                
                <div class="frame-meta mb-3">
                    <span class="badge bg-secondary me-1">
                        <i class="bi bi-palette"></i> {{ ucfirst($frame->color_code) }}
                    </span>
                    <span class="badge bg-info me-1">
                        <i class="bi bi-graph-up"></i> {{ $frame->photo_strips_count ?? 0 }} uses
                    </span>
                    @if($frame->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
                
                <div class="d-flex gap-2">
                    {{-- Edit Button --}}
                    <a href="{{ route('admin.frames.edit', $frame) }}" 
                       class="btn btn-sm btn-outline-primary flex-fill"
                       title="{{ $frame->is_default ? 'Edit name & description only' : 'Edit frame' }}">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    
                    {{-- Toggle Active Button --}}
                    <form action="{{ route('admin.frames.toggle', $frame) }}" method="POST" class="flex-fill">
                        @csrf
                        <button type="submit" 
                                class="btn btn-sm {{ $frame->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} w-100"
                                title="{{ $frame->is_active ? 'Deactivate' : 'Activate' }}">
                            <i class="bi bi-{{ $frame->is_active ? 'eye-slash' : 'eye' }}"></i>
                        </button>
                    </form>
                    
                    {{-- FIXED: Delete Button (Hanya untuk Custom Frame yang tidak digunakan) --}}
                    @if(!$frame->is_default && ($frame->photo_strips_count ?? 0) == 0)
                        <form action="{{ route('admin.frames.destroy', $frame) }}" 
                              method="POST" 
                              onsubmit="return confirmDelete('{{ addslashes($frame->name) }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-sm btn-outline-danger"
                                    title="Delete frame">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    @else
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger"
                                title="{{ $frame->is_default ? 'Default frames cannot be deleted' : 'Frame is in use and cannot be deleted' }}"
                                disabled>
                            <i class="bi bi-lock-fill"></i>
                        </button>
                    @endif
                </div>
                
                {{-- Usage Warning --}}
                @if(($frame->photo_strips_count ?? 0) > 0)
                <small class="text-warning d-block mt-2">
                    <i class="bi bi-exclamation-triangle"></i> Used in {{ $frame->photo_strips_count }} photo strips
                </small>
                @endif
                
                {{-- Default Frame Info --}}
                @if($frame->is_default)
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-shield-lock-fill"></i> Protected system frame
                </small>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle me-2"></i>
            No frames found. <a href="{{ route('admin.frames.create') }}" class="alert-link">Add your first frame</a>
        </div>
    </div>
    @endforelse
</div>

{{-- Pagination --}}
@if($frames->hasPages())
<div class="mt-4 d-flex justify-content-center">
    {{ $frames->links() }}
</div>
@endif

{{-- Info Box --}}
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i> Catatan Penting</h6>
    <hr>
    <ul class="mb-0 small">
        <li>Frame <strong>Default</strong> tidak dapat dihapus (protected system frame)</li>
        <li>Frame <strong>Custom</strong> yang sudah digunakan tidak dapat dihapus</li>
        <li>Frame <strong>Custom</strong> yang belum digunakan dapat dihapus dengan aman</li>
        <li>Icon <i class="bi bi-lock-fill"></i> menunjukkan frame tidak dapat dihapus</li>
    </ul>
</div>
@endsection

@push('styles')
<style>
.frame-card {
    transition: transform 0.3s, box-shadow 0.3s;
    position: relative;
    overflow: visible;
}

.frame-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}

.default-badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: linear-gradient(135deg, #ffc107, #ff9800);
    color: #000;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.frame-preview {
    position: relative;
    height: 300px;
    background: #f8f9fa;
    overflow: hidden;
    border-radius: 0.375rem 0.375rem 0 0;
}

.frame-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.frame-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
}

.frame-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.frame-meta .badge {
    font-size: 0.75rem;
}

.btn-outline-danger:disabled,
.btn-outline-danger.disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.card-body {
    padding: 1rem;
}

.card-title {
    font-size: 1rem;
    font-weight: 600;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(frameName) {
    return confirm('Hapus Frame: "' + frameName + '"?\n\nTindakan ini tidak dapat dibatalkan!\n\nKlik OK untuk melanjutkan.');
}
</script>
@endpush
