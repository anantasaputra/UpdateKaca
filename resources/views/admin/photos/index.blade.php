@extends('layouts.app')

@section('title', 'Manage Photos')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Photo Moderation</h1>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">Dashboard</a>
            <a href="{{ route('admin.frames.index') }}" class="admin-nav-link">Frames</a>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-link">Categories</a>
            <a href="{{ route('admin.photos.index') }}" class="admin-nav-link active">Photos</a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link">Users</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="filter-tabs">
            <a href="{{ route('admin.photos.index', ['status' => 'pending']) }}" 
               class="filter-tab {{ $status === 'pending' ? 'active' : '' }}">
                Pending
            </a>
            <a href="{{ route('admin.photos.index', ['status' => 'approved']) }}" 
               class="filter-tab {{ $status === 'approved' ? 'active' : '' }}">
                Approved
            </a>
            <a href="{{ route('admin.photos.index', ['status' => 'rejected']) }}" 
               class="filter-tab {{ $status === 'rejected' ? 'active' : '' }}">
                Rejected
            </a>
            <a href="{{ route('admin.photos.index', ['status' => 'all']) }}" 
               class="filter-tab {{ $status === 'all' ? 'active' : '' }}">
                All
            </a>
        </div>

        <div class="photos-grid">
            @foreach($photos as $photo)
            <div class="photo-moderation-card">
                <div class="photo-preview">
                    <img src="{{ $photo->image_url }}" alt="Photo #{{ $photo->id }}">
                </div>
                <div class="photo-info">
                    <p><strong>ID:</strong> #{{ $photo->id }}</p>
                    <p><strong>User:</strong> {{ $photo->user ? $photo->user->name : 'Guest' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $photo->status === 'approved' ? 'success' : ($photo->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($photo->status) }}
                        </span>
                    </p>
                    <p><strong>Uploaded:</strong> {{ $photo->created_at->diffForHumans() }}</p>
                    <p><strong>IP:</strong> {{ $photo->ip_address }}</p>
                </div>
                <div class="photo-actions">
                    @if($photo->status === 'pending')
                        <form method="POST" action="{{ route('admin.photos.approve', $photo) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-small btn-success">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.photos.reject', $photo) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-small btn-warning">Reject</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.photos.destroy', $photo) }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-small btn-danger" onclick="return confirm('Delete this photo?')">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if($photos->count() === 0)
            <div class="empty-state">
                <p>No photos found with status: {{ $status }}</p>
            </div>
        @endif

        <div class="pagination">
            {{ $photos->links() }}
        </div>
    </div>
</div>
@endsection