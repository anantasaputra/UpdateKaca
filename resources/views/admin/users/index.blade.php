@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Manage Users</h1>

        <div class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link">Dashboard</a>
            <a href="{{ route('admin.frames.index') }}" class="admin-nav-link">Frames</a>
            <a href="{{ route('admin.categories.index') }}" class="admin-nav-link">Categories</a>
            <a href="{{ route('admin.photos.index') }}" class="admin-nav-link">Photos</a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link active">Users</a>
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

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Photo Strips</th>
                    <th>Photos</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>#{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->photo_strips_count }}</td>
                    <td>{{ $user->photos_count }}</td>
                    <td>
                        <span class="badge {{ $user->is_blocked ? 'badge-danger' : 'badge-success' }}">
                            {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td class="table-actions">
                        <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-small {{ $user->is_blocked ? 'btn-success' : 'btn-warning' }}">
                                {{ $user->is_blocked ? 'Unblock' : 'Block' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-small btn-danger" onclick="return confirm('Delete this user and all their data?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection