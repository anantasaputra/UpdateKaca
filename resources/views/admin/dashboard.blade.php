@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Admin Dashboard</h1>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link active">Dashboard</a>
            <a href="{{ route('admin.frames.index') }}" class="admin-nav-link">Frames</a>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-link">Categories</a>
            <a href="{{ route('admin.photos.index') }}" class="admin-nav-link">Photos</a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link">Users</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number">{{ $stats['total_users'] }}</p>
            </div>
            <div class="stat-card">
                <h3>Total Frames</h3>
                <p class="stat-number">{{ $stats['total_frames'] }}</p>
            </div>
            <div class="stat-card">
                <h3>Total Categories</h3>
                <p class="stat-number">{{ $stats['total_categories'] }}</p>
            </div>
            <div class="stat-card">
                <h3>Total Strips</h3>
                <p class="stat-number">{{ $stats['total_strips'] }}</p>
            </div>
            <div class="stat-card alert">
                <h3>Pending Photos</h3>
                <p class="stat-number">{{ $stats['pending_photos'] }}</p>
            </div>
        </div>

        <div class="recent-activity">
            <h2>Recent Photo Strips</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Frame</th>
                        <th>Photos</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['recent_strips'] as $strip)
                    <tr>
                        <td>#{{ $strip->id }}</td>
                        <td>{{ $strip->user ? $strip->user->name : 'Guest' }}</td>
                        <td>{{ $strip->frame ? $strip->frame->name : 'No Frame' }}</td>
                        <td>{{ $strip->photo_count }}</td>
                        <td>{{ $strip->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ $strip->image_url }}" target="_blank" class="btn-small">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection