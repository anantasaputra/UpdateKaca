@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="profile-page">
    <div class="container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <div class="profile-info">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Member since:</strong> {{ $user->created_at->format('d M Y') }}</p>
                <a href="{{ route('profile.edit') }}" class="btn-secondary">Edit Profile</a>
            </div>
        </div>

        <div class="profile-content">
            <h2>My Photo Strips ({{ $photoStrips->total() }})</h2>

            @if($photoStrips->count() > 0)
                <div class="strips-grid">
                    @foreach($photoStrips as $photoStrip)
                    <div class="strip-card">
                        <div class="strip-image">
                            <img src="{{ $photoStrip->image_url }}" alt="Photo Strip #{{ $photoStrip->id }}">
                        </div>
                        <div class="strip-info">
                            <p><strong>Date:</strong> {{ $photoStrip->created_at->format('d M Y, H:i') }}</p>
                            @if($photoStrip->frame)
                                <p><strong>Frame:</strong> {{ $photoStrip->frame->name }}</p>
                            @endif
                            <p><strong>Photos:</strong> {{ $photoStrip->photo_count }}</p>
                        </div>
                        <div class="strip-actions">
                            <a href="{{ route('photobooth.download', $photoStrip->id) }}" class="btn-small btn-primary">Download</a>
                            <form method="POST" action="{{ route('profile.strips.delete', $photoStrip->id) }}" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-small btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $photoStrips->links() }}
                </div>
            @else
                <div class="empty-state">
                    <p>You haven't created any photo strips yet.</p>
                    <a href="{{ route('photobooth') }}" class="btn-primary">Create Your First Strip!</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection