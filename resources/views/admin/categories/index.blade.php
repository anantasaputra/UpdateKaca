@extends('layouts.app')

@section('title', 'Manage Categories')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Manage Categories</h1>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">Dashboard</a>
            <a href="{{ route('admin.frames.index') }}" class="admin-nav-link">Frames</a>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-link active">Categories</a>
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
            <a href="{{ route('admin.categories.create') }}" class="btn-primary">Add New Category</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Frames Count</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->slug }}</td>
                    <td>{{ Str::limit($category->description, 50) }}</td>
                    <td>{{ $category->frames_count }}</td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="table-actions">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-small btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-small btn-danger" onclick="return confirm('Delete this category?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection