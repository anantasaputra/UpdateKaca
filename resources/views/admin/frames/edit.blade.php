@extends('layouts.app')

@section('title', 'Edit Frame')

@section('content')
<div class="admin-page">
    <div class="container">
        <h1>Edit Frame</h1>

        {{-- Error Messages --}}
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Current Frame Preview --}}
        <div class="current-frame-preview">
            <h3>Current Frame:</h3>
            <img src="{{ $frame->image_url }}" alt="{{ $frame->name }}" style="max-width: 200px;">
        </div>

        <form method="POST" action="{{ route('admin.frames.update', $frame) }}" enctype="multipart/form-data" class="admin-form">
            @csrf
            @method('PUT')

            {{-- Frame Name --}}
            <div class="form-group">
                <label for="name">Frame Name *</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $frame->name) }}" 
                    required
                >
            </div>

            {{-- Category --}}
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option 
                            value="{{ $category->id }}" 
                            {{ old('category_id', $frame->category_id) == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Frame Image Upload --}}
            <div class="form-group">
                <label for="frame_image">Frame Image (PNG with transparency)</label>
                <input 
                    type="file" 
                    id="frame_image" 
                    name="frame_image" 
                    accept="image/png"
                >
                <small>Leave empty to keep current image. Max size: 10MB.</small>
            </div>

            {{-- Active Checkbox --}}
            <div class="form-group">
                <label>
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $frame->is_active) ? 'checked' : '' }}
                    >
                    Active
                </label>
            </div>

            {{-- Actions --}}
            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Frame</button>
                <a href="{{ route('admin.frames.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
