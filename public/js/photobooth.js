/**
 * BINGKIS KACA - PHOTOBOOTH MODULE
 * Complete working version
 */

(function () {
    'use strict';

    console.log('Photobooth script loaded');

    // =========================
    // State Management
    // =========================
    const state = {
        stream: null,
        selectedFrame: null,
        photoCount: 4,
        timerSeconds: 3,
        capturedPhotos: [],
        isCapturing: false,
        currentPhotoIndex: 0,
        videoDevices: [],
        currentDeviceId: null,
    };

    // =========================
    // DOM Elements
    // =========================
    const elements = {
        video: document.getElementById('cameraVideo'),
        canvas: document.getElementById('cameraCanvas'),
        frameOverlay: document.getElementById('frameOverlay'),
        countdownOverlay: document.getElementById('countdownOverlay'),
        countdownNumber: document.getElementById('countdownNumber'),
        flashEffect: document.getElementById('flashEffect'),
        cameraSelect: document.getElementById('cameraSelect'),
        photoCountSelect: document.getElementById('photoCountSelect'),
        timerSelect: document.getElementById('timerSelect'),
        startPhotoBtn: document.getElementById('startPhotoBtn'),
        thumbnailContainer: document.getElementById('thumbnailContainer'),
        progressText: document.getElementById('progressText'),
        progressFill: document.getElementById('progressFill'),
        reviewModal: document.getElementById('reviewModal'),
        stripCanvas: document.getElementById('stripCanvas'),
        frameOptions: document.getElementById('frameOptions'),
        downloadBtn: document.getElementById('downloadBtn'),
        retakeBtn: document.getElementById('retakeBtn'),
        saveBtn: document.getElementById('saveBtn'),
    };

    // =========================
    // Helpers
    // =========================
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // =========================
    // Initialization
    // =========================
    function init() {
        console.log('Initializing photobooth...');
        
        setupEventListeners();
        initializeCamera();
    }

    // =========================
    // Event Listeners
    // =========================
    function setupEventListeners() {
        console.log('Setting up event listeners...');

        // Camera select
        if (elements.cameraSelect) {
            elements.cameraSelect.addEventListener('change', switchCamera);
        }

        // Photo count
        if (elements.photoCountSelect) {
            elements.photoCountSelect.addEventListener('change', function() {
                state.photoCount = parseInt(this.value, 10);
                resetPhotos();
                updateProgressDisplay();
            });
        }

        // Timer
        if (elements.timerSelect) {
            elements.timerSelect.addEventListener('change', function() {
                state.timerSeconds = parseInt(this.value, 10);
            });
        }

        // Start photo button
        if (elements.startPhotoBtn) {
            elements.startPhotoBtn.addEventListener('click', startPhotoSession);
        }

        // Review buttons
        if (elements.downloadBtn) {
            elements.downloadBtn.addEventListener('click', downloadStrip);
        }

        if (elements.retakeBtn) {
            elements.retakeBtn.addEventListener('click', retakePhotos);
        }

        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', saveStrip);
        }

        // Frame selection
        if (elements.frameOptions) {
            const frameItems = elements.frameOptions.querySelectorAll('.frame-option');
            frameItems.forEach(item => {
                item.addEventListener('click', function() {
                    const frameId = this.dataset.frameId;
                    const frameUrl = this.dataset.frameUrl;
                    
                    // Update UI
                    frameItems.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update state
                    state.selectedFrame = {
                        id: frameId,
                        url: frameUrl
                    };
                    
                    // Re-render strip with new frame
                    if (state.capturedPhotos.length > 0) {
                        renderStripCanvas();
                    }
                });
            });
        }
    }

    // =========================
    // Camera Handling
    // =========================
    async function initializeCamera() {
        console.log('Initializing camera...');

        try {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Camera API not supported');
            }

            // Get available video devices
            const devices = await navigator.mediaDevices.enumerateDevices();
            state.videoDevices = devices.filter(device => device.kind === 'videoinput');

            console.log('Video devices found:', state.videoDevices.length);

            // Populate camera select
            if (state.videoDevices.length > 0 && elements.cameraSelect) {
                elements.cameraSelect.innerHTML = '';
                
                state.videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.textContent = device.label || `Kamera ${index + 1}`;
                    elements.cameraSelect.appendChild(option);
                });

                // Select first camera
                state.currentDeviceId = state.videoDevices[0].deviceId;
                elements.cameraSelect.value = state.currentDeviceId;
            }

            // Start camera
            await startCamera();
        } catch (error) {
            console.error('Error initializing camera:', error);
            handleCameraError(error);
        }
    }

    async function startCamera() {
        try {
            console.log('Starting camera...');

            // Stop existing stream
            if (state.stream) {
                state.stream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 960 },
                },
                audio: false,
            };

            if (state.currentDeviceId) {
                constraints.video.deviceId = { exact: state.currentDeviceId };
            }

            // Get media stream
            state.stream = await navigator.mediaDevices.getUserMedia(constraints);

            if (elements.video) {
                elements.video.srcObject = state.stream;
                console.log('Camera started successfully');
            }
        } catch (error) {
            console.error('Error starting camera:', error);
            handleCameraError(error);
        }
    }

    async function switchCamera(e) {
        state.currentDeviceId = e.target.value;
        await startCamera();
    }

    function handleCameraError(error) {
        let message = 'Unable to access camera. ';

        if (error.name === 'NotAllowedError') {
            message += 'Please grant camera permissions.';
        } else if (error.name === 'NotFoundError') {
            message += 'No camera found on this device.';
        } else {
            message += 'Please check your camera settings.';
        }

        alert(message);
        console.error('Camera error:', error);
    }

    // =========================
    // Capture Flow
    // =========================
    async function startPhotoSession() {
        if (state.isCapturing) return;

        console.log('Starting photo session...');

        state.isCapturing = true;
        state.currentPhotoIndex = 0;
        state.capturedPhotos = [];

        elements.startPhotoBtn.disabled = true;
        elements.startPhotoBtn.querySelector('.btn-text').textContent = 'Sedang Mengambil...';

        // Clear thumbnails
        resetPhotos();

        // Capture photos sequentially
        for (let i = 0; i < state.photoCount; i++) {
            state.currentPhotoIndex = i;
            await capturePhoto();
            updateProgressDisplay();

            // Wait between captures
            if (i < state.photoCount - 1) {
                await sleep(1000);
            }
        }

        // All photos captured
        state.isCapturing = false;
        elements.startPhotoBtn.disabled = false;
        elements.startPhotoBtn.querySelector('.btn-text').textContent = 'Mulai Foto';

        // Show review modal
        showReviewModal();
    }

    async function capturePhoto() {
        console.log('Capturing photo', state.currentPhotoIndex + 1);

        // Show countdown if timer is set
        if (state.timerSeconds > 0) {
            await showCountdown();
        }

        // Flash effect
        showFlash();

        // Capture from video
        const photoData = captureFromVideo();
        state.capturedPhotos.push(photoData);

        // Update thumbnails
        updateThumbnails();
    }

    function showCountdown() {
        return new Promise(resolve => {
            let count = state.timerSeconds;

            elements.countdownOverlay.style.display = 'flex';
            elements.countdownNumber.textContent = count;

            const interval = setInterval(() => {
                count--;

                if (count > 0) {
                    elements.countdownNumber.textContent = count;
                } else {
                    clearInterval(interval);
                    elements.countdownOverlay.style.display = 'none';
                    resolve();
                }
            }, 1000);
        });
    }

    function showFlash() {
        elements.flashEffect.style.display = 'block';
        setTimeout(() => {
            elements.flashEffect.style.display = 'none';
        }, 300);
    }

    function captureFromVideo() {
        const video = elements.video;
        const canvas = elements.canvas;

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 960;

        const ctx = canvas.getContext('2d');

        // Draw video frame
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert to base64
        return canvas.toDataURL('image/png');
    }

    // =========================
    // Thumbnails
    // =========================
    function updateThumbnails() {
        elements.thumbnailContainer.innerHTML = '';

        if (state.capturedPhotos.length === 0) {
            elements.thumbnailContainer.innerHTML = '<div class="thumbnail-empty">📷 Foto akan muncul di sini</div>';
            return;
        }

        state.capturedPhotos.forEach((photo, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'thumbnail-item';
            thumbnail.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="thumbnail-badge">${index + 1}</div>
            `;
            elements.thumbnailContainer.appendChild(thumbnail);
        });
    }

    function updateProgressDisplay() {
        const progress = (state.capturedPhotos.length / state.photoCount) * 100;
        elements.progressText.textContent = `${state.capturedPhotos.length}/${state.photoCount} foto`;
        elements.progressFill.style.width = `${progress}%`;
    }

    function resetPhotos() {
        state.capturedPhotos = [];
        state.currentPhotoIndex = 0;
        updateThumbnails();
        updateProgressDisplay();
    }

    // =========================
    // Review Modal
    // =========================
    function showReviewModal() {
        elements.reviewModal.style.display = 'flex';
        renderStripCanvas();
    }

    function hideReviewModal() {
        elements.reviewModal.style.display = 'none';
    }

    function renderStripCanvas() {
        const canvas = elements.stripCanvas;
        const photoWidth = 400;
        const photoHeight = 300;
        const stripWidth = photoWidth;
        const stripHeight = photoHeight * state.photoCount;

        canvas.width = stripWidth;
        canvas.height = stripHeight;

        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, stripWidth, stripHeight);

        let loadedCount = 0;
        const photos = [];

        state.capturedPhotos.forEach((photoData, index) => {
            const img = new Image();
            img.onload = function() {
                photos[index] = img;
                loadedCount++;

                if (loadedCount === state.capturedPhotos.length) {
                    // Draw all photos
                    photos.forEach((image, idx) => {
                        const yPos = idx * photoHeight;
                        ctx.drawImage(image, 0, yPos, stripWidth, photoHeight);
                    });

                    // Draw frame overlay if selected
                    if (state.selectedFrame && state.selectedFrame.url) {
                        const frameImg = new Image();
                        frameImg.crossOrigin = 'anonymous';
                        frameImg.onload = function() {
                            for (let idx = 0; idx < state.photoCount; idx++) {
                                const yPos = idx * photoHeight;
                                ctx.drawImage(frameImg, 0, yPos, stripWidth, photoHeight);
                            }
                        };
                        frameImg.src = state.selectedFrame.url;
                    }
                }
            };
            img.src = photoData;
        });
    }

    // =========================
    // Actions
    // =========================
    function downloadStrip() {
        console.log('Downloading strip...');

        elements.stripCanvas.toBlob(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `bingkiskaca_${Date.now()}.png`;
            a.click();
            URL.revokeObjectURL(url);
        });
    }

    function retakePhotos() {
        hideReviewModal();
        resetPhotos();
    }

    async function saveStrip() {
        try {
            console.log('Saving strip...');

            elements.saveBtn.disabled = true;
            elements.saveBtn.innerHTML = '<span>💾</span> Menyimpan...';

            const data = {
                photos: state.capturedPhotos,
                frame_id: state.selectedFrame ? state.selectedFrame.id : null,
                photo_count: state.photoCount,
            };

            const response = await fetch('/photobooth/compose', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                alert('Photo strip berhasil disimpan!');

                if (confirm('Lihat di profil Anda?')) {
                    window.location.href = '/profile';
                } else {
                    hideReviewModal();
                    resetPhotos();
                }
            } else {
                throw new Error(result.error || 'Failed to save strip');
            }
        } catch (error) {
            console.error('Error saving strip:', error);
            alert('Error saving photo strip: ' + error.message);
        } finally {
            elements.saveBtn.disabled = false;
            elements.saveBtn.innerHTML = '<span>💾</span> Simpan';
        }
    }

    window.closeLoginModal = function() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // =========================
    // Initialize on DOM Ready
    // =========================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();