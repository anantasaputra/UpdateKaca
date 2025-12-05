@extends('layouts.app')

@section('title', 'Manage Frames')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Manage Frames</h1>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">Dashboard</a>
            <a href="{{ route('admin.frames.index') }}" class="admin-nav-link active">Frames</a>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-link">Categories</a>
            <a href="{{ route('admin.photos.index') }}" class="admin-nav-link">Photos</a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link">Users</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="admin-actions">
            <a href="{{ route('admin.frames.create') }}" class="btn-primary">Add New Frame</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Usage</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($frames as $frame)
                <tr>
                    <td>
                        <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" class="table-thumbnail">
                    </td>
                    <td>{{ $frame->name }}</td>
                    <td>{{ $frame->category->name }}</td>
                    <td>
                        <span class="badge {{ $frame->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $frame->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ $frame->usage_count }}</td>
                    <td>{{ $frame->created_at->format('d M Y') }}</td>
                    <td class="table-actions">
                        <a href="{{ route('admin.frames.edit', $frame) }}" class="btn-small btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('admin.frames.destroy', $frame) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-small btn-danger" onclick="return confirm('Delete this frame?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination">
            {{ $frames->links() }}
        </div>
    </div>
</div>
@endsection