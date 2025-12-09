@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-speedometer2 me-2"></i> Dashboard</h1>
    <p class="text-muted">Selamat datang, {{ auth()->user()->name }}</p>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-people fs-1 text-primary"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_users']) }}</h3>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #28a745;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-images fs-1 text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_strips']) }}</h3>
                    <small class="text-muted">Photo Strips</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #17a2b8;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-camera fs-1 text-info"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_photos']) }}</h3>
                    <small class="text-muted">Total Photos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card" style="border-left-color: #ffc107;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="bi bi-palette fs-1 text-warning"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h3 class="mb-0">{{ number_format($stats['total_frames']) }}</h3>
                    <small class="text-muted">Total Frames</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ✅ BARU: Frame Usage Statistics --}}
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i> Statistik Penggunaan Frame</h5>
    </div>
    <div class="card-body">
        {{-- Frame Stats Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 bg-primary bg-opacity-10 rounded">
                    <div class="text-primary fw-bold">Total Frame</div>
                    <h3 class="mb-0 text-primary">{{ $frameStatsSummary['total_frames'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-success bg-opacity-10 rounded">
                    <div class="text-success fw-bold">Frame Aktif</div>
                    <h3 class="mb-0 text-success">{{ $frameStatsSummary['active_frames'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-warning bg-opacity-10 rounded">
                    <div class="text-warning fw-bold">Frame Custom</div>
                    <h3 class="mb-0 text-warning">{{ $frameStatsSummary['custom_frames'] }}</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-danger bg-opacity-10 rounded">
                    <div class="text-danger fw-bold">Tidak Digunakan</div>
                    <h3 class="mb-0 text-danger">{{ $frameStatsSummary['unused_frames'] }}</h3>
                </div>
            </div>
        </div>

        {{-- Top 5 Most Used Frames --}}
        <div class="mb-4">
            <h6 class="fw-bold mb-3">Top 5 Frame Paling Populer</h6>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="50">Rank</th>
                            <th>Frame</th>
                            <th>Tipe</th>
                            <th>Warna</th>
                            <th class="text-center">Photos</th>
                            <th class="text-center">Digunakan</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topFrames as $index => $frame)
                            <tr>
                                <td class="text-center">
                                    <span class="fs-5">
                                        @if($index === 0) <strong>1</strong>
                                        @elseif($index === 1) <strong>2</strong>
                                        @elseif($index === 2) <strong>3</strong>
                                        @else <strong>{{ $index + 1 }}</strong>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($frame->image_path && Storage::disk('public')->exists($frame->image_path))
                                            <img src="{{ Storage::url($frame->image_path) }}" 
                                                 alt="{{ $frame->name }}"
                                                 class="me-2 rounded"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $frame->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($frame->is_default)
                                        <span class="badge bg-primary">Default</span>
                                    @else
                                        <span class="badge bg-secondary">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background-color: {{ $frame->color_code == 'brown' ? '#6B4423' : ($frame->color_code == 'cream' ? '#CBA991' : '#e9ecef') }}; color: {{ $frame->color_code == 'white' ? '#000' : '#fff' }};">
                                        {{ ucfirst($frame->color_code) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $frame->photo_count }}</span>
                                </td>
                                <td class="text-center">
                                    <strong class="text-success">{{ $frame->usage_count }}</strong>
                                    <small class="text-muted">kali</small>
                                </td>
                                <td class="text-center">
                                    @if($frame->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    Belum ada data frame yang digunakan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Unused Frames --}}
        @if($unusedFrames->count() > 0)
            <div class="alert alert-warning">
                <h6 class="alert-heading">Frame yang Belum Pernah Digunakan ({{ $unusedFrames->count() }})</h6>
                <hr>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($unusedFrames as $frame)
                        <span class="badge bg-light text-dark border">
                            {{ $frame->name }}
                            @if(!$frame->is_active)
                                <span class="text-danger">(Nonaktif)</span>
                            @endif
                            @if(!$frame->is_default)
                                <span class="text-secondary">(Custom)</span>
                            @endif
                        </span>
                    @endforeach
                </div>
                <hr>
                <p class="mb-0 small">
                    <strong>Saran:</strong> Pertimbangkan untuk menonaktifkan atau menghapus frame yang tidak pernah digunakan untuk merapikan koleksi frame.
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4">
    <!-- Recent Photo Strips -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-images me-2"></i> Recent Photo Strips</h5>
                <a href="{{ route('admin.photo-strips.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Photos</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentStrips as $strip)
                            <tr>
                                <td><strong>#{{ $strip->id }}</strong></td>
                                <td>
                                    @if($strip->user)
                                        <a href="{{ route('admin.users.show', $strip->user) }}">
                                            {{ $strip->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">Guest</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $strip->photo_count }} photos</span></td>
                                <td>{{ $strip->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.photo-strips.show', $strip) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No photo strips yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people me-2"></i> Recent Users</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($recentUsers as $user)
                    <a href="{{ route('admin.users.show', $user) }}" 
                       class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Admin</span>
                            @endif
                        </div>
                    </a>
                    @empty
                    <div class="list-group-item text-center text-muted">
                        No users yet
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
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    display: flex;
    align-items-center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.stat-card {
    border-left: 4px solid #007bff;
    padding: 1.5rem;
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
@endpush
