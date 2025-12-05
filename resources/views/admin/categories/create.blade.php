@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Create New Category</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.store') }}" class="admin-form">
            @csrf

            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                <small>Examples: Cute, Minimalis, Formal, Couple</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Category</button>
                <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection