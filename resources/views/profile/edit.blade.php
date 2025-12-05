@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="profile-edit-page">
    <div class="container">
        <h1>Edit Profile</h1>

        @if (session('status') === 'profile-updated')
            <div class="alert alert-success">
                Profile updated successfully!
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Profile</button>
                <a href="{{ route('profile.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>

        <hr>

        <h2>Delete Account</h2>
        <form method="POST" action="{{ route('profile.destroy') }}" class="delete-form">
            @csrf
            @method('DELETE')

            <p class="warning-text">Once your account is deleted, all of your resources and data will be permanently deleted.</p>

            <div class="form-group">
                <label for="password">Confirm with Password</label>
                <input type="password" id="password" name="password" required>
                @error('password', 'userDeletion')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</button>
        </form>
    </div>
</div>
@endsection