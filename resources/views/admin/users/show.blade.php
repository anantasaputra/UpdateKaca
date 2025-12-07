@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-person me-2"></i> {{ $user->name }}</h1>
        <p class="text-muted">User details and activity</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row g-4">
    <!-- User Info Card -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                <div class="d-flex gap-2 justify-content-center mb-3">
                    @if($user->is_admin)
                        <span class="badge bg-danger">Admin</span>
                    @else
                        <span class="badge bg-info">User</span>
                    @endif
                    
                    @if($user->is_blocked)
                        <span class="badge bg-secondary">Blocked</span>
                    @else
                        <span class="badge bg-success">Active</span>
                    @endif
                </div>

                @if(!$user->is_admin)
                <div class="d-grid gap-2">
                    @if($user->is_blocked)
                        <form action="{{ route('admin.users.unblock', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-unlock"></i> Unblock User
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.users.block', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-lock"></i> Block User
                            </button>
                        </form>
                    @endif
                    
                    <form action="{{ route('admin.users.destroy', $user) }}" 
                          method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete User
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Strips:</td>
                        <td class="text-end"><strong>{{ $stats['total_strips'] ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Saved Strips:</td>
                        <td class="text-end"><strong>{{ $stats['saved_strips'] ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Photos:</td>
                        <td class="text-end"><strong>{{ $stats['total_photos'] ?? 0 }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Joined:</td>
                        <td class="text-end"><strong>{{ $user->created_at->format('M d, Y') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- User Activity -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-images me-2"></i> Recent Photo Strips</h5>
            </div>
            <div class="card-body p-0">
                <div class="row g-3 p-3">
                    @forelse($user->photoStrips as $strip)
                    <div class="col-md-4">
                        <div class="card">
                            @if($strip->final_image_path && Storage::disk('public')->exists($strip->final_image_path))
                                <img src="{{ Storage::url($strip->final_image_path) }}" 
                                     class="card-img-top" 
                                     style="height: 150px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 150px;">
                                    <i class="bi bi-image text-muted"></i>
                                </div>
                            @endif
                            <div class="card-body p-2">
                                <small class="text-muted">
                                    {{ $strip->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4">
                        <i class="bi bi-images text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No photo strips yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-circle {
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
</style>
@endpush
