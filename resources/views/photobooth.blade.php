@extends('layouts.app')

@section('title', 'PhotoBooth')

@section('content')
<div class="photobooth-page">
    <div class="container-photobooth">
        <h1 class="photobooth-title">Bingkis Kaca Photo Booth</h1>
        
        <div class="photobooth-layout">
            <!-- LEFT SIDE: Controls & Camera -->
            <div class="photobooth-left">
                <!-- Control Dropdowns -->
                <div class="control-panel">
                    <!-- Camera Selection -->
                    <div class="control-group">
                        <label>Pilih Kamera:</label>
                        <select id="cameraSelect" class="control-select">
                            <option value="">Loading cameras...</option>
                        </select>
                    </div>

                    <!-- Photo Count -->
                    <div class="control-group">
                        <label>Jumlah Foto:</label>
                        <select id="photoCountSelect" class="control-select">
                            <option value="2">2 Foto</option>
                            <option value="3">3 Foto</option>
                            <option value="4" selected>4 Foto</option>
                        </select>
                    </div>

                    <!-- Timer -->
                    <div class="control-group">
                        <label>Timer:</label>
                        <select id="timerSelect" class="control-select">
                            <option value="0">No Timer</option>
                            <option value="3" selected>3 Detik</option>
                            <option value="5">5 Detik</option>
                            <option value="10">10 Detik</option>
                        </select>
                    </div>
                </div>

                <!-- Camera Area -->
                <div class="camera-wrapper">
                    <div class="camera-container" id="cameraContainer">
                        <video id="cameraVideo" autoplay playsinline muted></video>
                        <canvas id="cameraCanvas" style="display: none;"></canvas>
                        
                        <!-- Frame Overlay -->
                        <img id="frameOverlay" class="frame-overlay" src="" alt="" style="display: none;">
                        
                        <!-- Countdown Overlay -->
                        <div id="countdownOverlay" class="countdown-overlay" style="display: none;">
                            <span id="countdownNumber">3</span>
                        </div>

                        <!-- Flash Effect -->
                        <div id="flashEffect" class="flash-effect" style="display: none;"></div>
                    </div>

                    <!-- Action Button -->
                    <button type="button" id="startPhotoBtn" class="btn-capture">
                        <span class="btn-icon">📷</span>
                        <span class="btn-text">Mulai Foto</span>
                    </button>
                </div>
            </div>

            <!-- RIGHT SIDE: Thumbnails -->
            <div class="photobooth-right">
                <div class="thumbnail-panel">
                    <h3 class="panel-title">Preview Foto</h3>
                    <div id="thumbnailContainer" class="thumbnail-grid">
                        <div class="thumbnail-empty">Foto akan muncul di sini</div>
                    </div>
                    <div class="thumbnail-progress">
                        <span id="progressText">0/4 foto</span>
                        <div class="progress-bar">
                            <div id="progressFill" class="progress-fill"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="review-modal" style="display: none;">
    <div class="review-content">
        <h2 class="review-title">Photo Strip Review</h2>
        
        <div class="review-layout">
            <!-- Left: Strip Preview with Character -->
            <div class="review-left">
                <div class="review-left-content">
                    <div class="character-container">
                        <img src="{{ asset('images/character-left.png') }}" alt="Character" class="character-image" onerror="this.style.display='none'">
                    </div>
                    <div class="strip-preview-wrapper">
                        <div class="strip-preview-container">
                            <canvas id="stripCanvas" class="strip-canvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Frame Selection & Actions with Character -->
            <div class="review-right">
                <div class="review-right-content">
                    <div class="frame-picker">
                        <h3>Pick Your Photo Frame</h3>
                        <div class="color-selector" id="colorSelector">
                            <button class="color-btn active" data-color="brown" style="background: #6B4423;" title="Brown"></button>
                            <button class="color-btn" data-color="cream" style="background: #CBA991;" title="Cream"></button>
                            <button class="color-btn" data-color="white" style="background: #FFFFFF; border: 3px solid #522504;" title="White"></button>
                        </div>
                    </div>

                    <div class="review-actions">
                        <button type="button" id="backBtn" class="btn-review btn-back">
                            Back
                        </button>
                        <button type="button" id="downloadBtn" class="btn-review btn-download">
                            Download
                        </button>
                        <button type="button" id="retakeBtn" class="btn-review btn-retake">
                            Retake
                        </button>
                    </div>

                    <div class="character-right-container">
                        <img src="{{ asset('images/character-right.png') }}" alt="Character" class="character-image-right" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@guest
<!-- Login Prompt Modal -->
<div id="loginModal" class="login-prompt-modal" style="display: none;">
    <div class="login-prompt-content">
        <button class="modal-close" onclick="closeLoginModal()">&times;</button>
        <h2>Login Required</h2>
        <p>Silakan login untuk menyimpan photo strip ke profil Anda.</p>
        <div class="login-actions">
            <a href="{{ route('login') }}" class="btn-primary">Login</a>
            <a href="{{ route('register') }}" class="btn-secondary">Sign Up</a>
        </div>
    </div>
</div>
@endguest
@endsection

@push('styles')
<style>
/* Photobooth Styles */
.photobooth-page {
    background: linear-gradient(135deg, #CBA991 0%, #9D6B46 100%);
    min-height: calc(100vh - 120px);
    padding: 2rem 0;
}

.container-photobooth {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.photobooth-title {
    text-align: center;
    color: #522504;
    font-size: 2.5rem;
    margin-bottom: 2rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.photobooth-layout {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 2rem;
}

/* Left Side */
.photobooth-left {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.control-panel {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.control-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.control-group label {
    font-weight: 600;
    color: #522504;
    font-size: 0.9rem;
}

.control-select {
    padding: 0.75rem;
    border: 2px solid #9D6B46;
    border-radius: 8px;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.control-select:hover {
    border-color: #522504;
}

.control-select:focus {
    outline: none;
    border-color: #522504;
    box-shadow: 0 0 0 3px rgba(82, 37, 4, 0.1);
}

.camera-wrapper {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.camera-container {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #000;
    border-radius: 15px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}

#cameraVideo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.frame-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 10;
}

.countdown-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 20;
}

#countdownNumber {
    font-size: 10rem;
    font-weight: 700;
    color: white;
    animation: countdownPulse 1s ease-in-out;
}

@keyframes countdownPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.3); opacity: 0.7; }
}

.flash-effect {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    z-index: 30;
    animation: flash 0.3s ease-out;
}

@keyframes flash {
    0% { opacity: 0; }
    50% { opacity: 1; }
    100% { opacity: 0; }
}

.btn-capture {
    width: 100%;
    padding: 1.25rem;
    background: linear-gradient(135deg, #522504 0%, #9D6B46 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-family: 'Poppins', sans-serif;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    box-shadow: 0 4px 12px rgba(82, 37, 4, 0.3);
    transition: all 0.3s ease;
}

.btn-capture:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(82, 37, 4, 0.4);
}

.btn-capture:active {
    transform: translateY(0);
}

.btn-capture:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-icon {
    font-size: 1.5rem;
}

/* Right Side */
.photobooth-right {
    display: flex;
    flex-direction: column;
}

.thumbnail-panel {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.panel-title {
    color: #522504;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #CBA991;
}

.thumbnail-grid {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    overflow-y: auto;
    margin-bottom: 1rem;
}

.thumbnail-empty {
    text-align: center;
    color: #999;
    padding: 2rem 1rem;
    border: 2px dashed #ddd;
    border-radius: 10px;
}

.thumbnail-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail-item:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.thumbnail-item img {
    width: 100%;
    height: auto;
    display: block;
}

.thumbnail-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: #522504;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

.thumbnail-progress {
    padding-top: 1rem;
    border-top: 2px solid #f0f0f0;
}

#progressText {
    display: block;
    text-align: center;
    color: #522504;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #522504 0%, #9D6B46 100%);
    width: 0%;
    transition: width 0.3s ease;
}

/* Review Modal */
.review-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.review-content {
    background: #CBA991;
    border-radius: 20px;
    padding: 2.5rem;
    max-width: 1000px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.review-title {
    text-align: center;
    color: #522504;
    font-size: 2rem;
    margin-bottom: 2rem;
    font-weight: 700;
}

.review-layout {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 2.5rem;
    align-items: stretch;
}

/* Left Side - Canvas Preview */
.review-left {
    display: flex;
    align-items: flex-end;
    justify-content: center;
}

.review-left-content {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
}

.character-container {
    display: flex;
    align-items: flex-end;
    padding-bottom: 15px;
}

.character-image {
    width: 90px;
    height: auto;
    object-fit: contain;
}

.strip-preview-wrapper {
    display: flex;
    align-items: center;
}

.strip-preview-container {
    background: transparent;
    padding: 0;
    border-radius: 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}

.strip-canvas {
    display: block;
    max-height: 600px;
    max-width: 100%;
    height: auto;
    width: auto;
    border-radius: 8px;
}

/* Right Side - Controls */
.review-right {
    display: flex;
    flex-direction: column;
    min-width: 280px;
}

.review-right-content {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 1.5rem;
    height: 100%;
}

.frame-picker {
    background: white;
    border-radius: 15px;
    padding: 1.75rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.frame-picker h3 {
    color: #522504;
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
    text-align: center;
    font-weight: 600;
}

.color-selector {
    display: flex;
    justify-content: center;
    gap: 1.2rem;
}

.color-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
}

.color-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.35);
}

.color-btn.active {
    border-color: #522504;
    box-shadow: 0 0 0 5px rgba(82, 37, 4, 0.3);
    transform: scale(1.15);
}

.review-actions {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.btn-review {
    padding: 1rem 1.5rem;
    border: none;
    border-radius: 50px;
    font-family: 'Poppins', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-back {
    background: #6c757d;
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(108, 117, 125, 0.4);
}

.btn-download {
    background: #522504;
    color: white;
    box-shadow: 0 4px 12px rgba(82, 37, 4, 0.3);
}

.btn-download:hover {
    background: #6b2f05;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(82, 37, 4, 0.4);
}

.btn-retake {
    background: white;
    color: #522504;
    border: 2px solid #522504;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-retake:hover {
    background: #522504;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(82, 37, 4, 0.3);
}

.character-right-container {
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
    margin-top: auto;
}

.character-image-right {
    width: 110px;
    height: auto;
    object-fit: contain;
}

/* Login Modal */
.login-prompt-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-prompt-content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    position: relative;
}

.modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.login-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.login-actions .btn-primary, 
.login-actions .btn-secondary {
    flex: 1;
    padding: 0.75rem;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.login-actions .btn-primary {
    background: #522504;
    color: white;
}

.login-actions .btn-secondary {
    background: white;
    color: #522504;
    border: 2px solid #522504;
}

/* Responsive */
@media (max-width: 1024px) {
    .photobooth-layout {
        grid-template-columns: 1fr;
    }
    
    .control-panel {
        grid-template-columns: 1fr;
    }

    .review-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .review-left-content {
        justify-content: center;
    }

    .strip-canvas {
        max-height: 500px;
    }

    .character-image,
    .character-image-right {
        width: 70px;
    }

    .review-right {
        min-width: auto;
    }
}

@media (max-width: 768px) {
    .review-content {
        padding: 1.5rem;
    }

    .review-title {
        font-size: 1.5rem;
    }

    .color-btn {
        width: 50px;
        height: 50px;
    }

    .strip-canvas {
        max-height: 400px;
    }

    .character-image,
    .character-image-right {
        width: 60px;
    }
}
</style>
@endpush

@push('scripts')
<script>
window.csrfToken = '{{ csrf_token() }}';
window.framesData = {
    frame2: {
        brown: '{{ asset("images/frames/frame2_brown.png") }}',
        cream: '{{ asset("images/frames/frame2_cream.png") }}',
        white: '{{ asset("images/frames/frame2_white.png") }}'
    },
    frame3: {
        brown: '{{ asset("images/frames/frame3_brown.png") }}',
        cream: '{{ asset("images/frames/frame3_cream.png") }}',
        white: '{{ asset("images/frames/frame3_white.png") }}'
    },
    frame4: {
        brown: '{{ asset("images/frames/frame4_brown.png") }}',
        cream: '{{ asset("images/frames/frame4_cream.png") }}',
        white: '{{ asset("images/frames/frame4_white.png") }}'
    }
};
</script>
<script src="{{ asset('js/photobooth-complete.js') }}"></script>
@endpush
