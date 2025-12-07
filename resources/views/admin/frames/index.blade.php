@extends('admin.layouts.app')

@section('title', 'Frames Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-palette me-2"></i> Frames Management</h1>
        <p class="text-muted">Manage photo booth frames</p>
    </div>
    <a href="{{ route('admin.frames.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Frame
    </a>
</div>

<!-- Frames Grid -->
<div class="row g-4">
    @forelse($frames as $frame)
    <div class="col-md-4">
        <div class="card h-100">
            <div class="position-relative">
                @if($frame->image_path && Storage::disk('public')->exists($frame->image_path))
                    <img src="{{ Storage::url($frame->image_path) }}" 
                         class="card-img-top" 
                         alt="{{ $frame->name }}"
                         style="height: 250px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" 
                         style="height: 250px;">
                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                    </div>
                @endif
                
                <div class="position-absolute top-0 end-0 m-2">
                    @if($frame->is_active)
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-x-circle"></i> Inactive</span>
                    @endif
                </div>
            </div>
            
            <div class="card-body">
                <h5 class="card-title">{{ $frame->name }}</h5>
                <p class="card-text text-muted small">{{ Str::limit($frame->description, 100) }}</p>
                
                @if($frame->category)
                    <span class="badge bg-info">{{ $frame->category->name }}</span>
                @endif
            </div>
            
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.frames.edit', $frame) }}" 
                       class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    
                    <form action="{{ route('admin.frames.toggle', $frame) }}" 
                          method="POST" class="d-inline">
                        @csrf
                        <button type="submit" 
                                class="btn btn-sm {{ $frame->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                            <i class="bi bi-{{ $frame->is_active ? 'x' : 'check' }}-circle"></i>
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.frames.destroy', $frame) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-palette text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No frames found</h4>
                <p class="text-muted">Start by adding your first frame.</p>
                <a href="{{ route('admin.frames.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Frame
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($frames->hasPages())
<div class="mt-4">
    {{ $frames->links() }}
</div>
@endif
@endsection
