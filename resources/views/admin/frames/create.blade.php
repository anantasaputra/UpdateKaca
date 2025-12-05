@extends('layouts.app')

@section('title', 'Create Frame')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Create New Frame</h1>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.frames.store') }}" enctype="multipart/form-data" class="admin-form">
            @csrf

            <div class="form-group">
                <label for="name">Frame Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="frame_image">Frame Image (PNG with transparency) *</label>
                <input type="file" id="frame_image" name="frame_image" accept="image/png" required>
                <small>Max size: 10MB. Only PNG format with transparency supported.</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Active
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Create Frame</button>
                <a href="{{ route('admin.frames.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection