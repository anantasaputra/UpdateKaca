@extends('admin.layouts.app')

@section('title', 'Photos Management')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-camera me-2"></i> Photos Management</h1>
    <p class="text-muted">Manage and moderate user photos</p>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.photos.index') }}">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by user name..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.photos.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Photos Grid -->
<div class="row g-4">
    @forelse($photos as $photo)
    <div class="col-md-3">
        <div class="card h-100">
            <div class="position-relative">
                @if($photo->file_path && Storage::disk('public')->exists($photo->file_path))
                    <img src="{{ Storage::url($photo->file_path) }}" 
                         class="card-img-top" 
                         alt="Photo #{{ $photo->id }}"
                         style="height: 200px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                @endif
                
                <div class="position-absolute top-0 end-0 m-2">
                    @if($photo->is_approved)
                        <span class="badge bg-success"><i class="bi bi-check"></i></span>
                    @else
                        <span class="badge bg-warning"><i class="bi bi-clock"></i></span>
                    @endif
                </div>
            </div>
            
            <div class="card-body p-2">
                <small class="text-muted">
                    @if($photo->user)
                        <i class="bi bi-person"></i> {{ $photo->user->name }}
                    @else
                        Guest
                    @endif
                </small>
            </div>
            
            <div class="card-footer bg-white border-top-0 p-2">
                <div class="d-flex gap-1">
                    @if(!$photo->is_approved)
                        <form action="{{ route('admin.photos.approve', $photo) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success w-100" title="Approve">
                                <i class="bi bi-check"></i>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.photos.reject', $photo) }}" method="POST" class="flex-fill">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning w-100" title="Reject">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.photos.destroy', $photo) }}" 
                          method="POST" 
                          onsubmit="return confirm('Delete this photo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
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
                <i class="bi bi-camera text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">No photos found</h4>
                <p class="text-muted">Photos will appear here when users upload them.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($photos->hasPages())
<div class="mt-4">
    {{ $photos->links() }}
</div>
@endif
@endsection
