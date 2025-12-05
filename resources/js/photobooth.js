/**
 * BINGKIS KACA - PHOTOBOOTH MODULE
 * Fixed version with proper initialization
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
        photoCountBtn: document.getElementById('photoCountBtn'),
        photoCountText: document.getElementById('photoCountText'),
        photoCountMenu: document.getElementById('photoCountMenu'),
        timerBtn: document.getElementById('timerBtn'),
        timerText: document.getElementById('timerText'),
        timerMenu: document.getElementById('timerMenu'),
        startPhotoBtn: document.getElementById('startPhotoBtn'),
        retakeBtn: document.getElementById('retakeBtn'),
        downloadBtn: document.getElementById('downloadBtn'),
        saveBtn: document.getElementById('saveBtn'),
        thumbnailContainer: document.getElementById('thumbnailContainer'),
    };

    // =========================
    // Helpers
    // =========================

    // Check if all required elements exist
    function checkElements() {
        for (const [key, element] of Object.entries(elements)) {
            if (!element) {
                console.error(`Element not found: ${key}`);
                return false;
            }
        }
        return true;
    }

    // Sleep utility
    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // =========================
    // Initialization
    // =========================

    function init() {
        console.log('Initializing photobooth...');

        if (!checkElements()) {
            console.error('Some elements are missing!');
            return;
        }

        setupEventListeners();
        setupDropdowns();
        setupFrameSelection();
        initializeCamera();
    }

    // =========================
    // Event Listeners
    // =========================

    function setupEventListeners() {
        console.log('Setting up event listeners...');

        if (elements.startPhotoBtn) {
            elements.startPhotoBtn.addEventListener('click', startPhotoSession);
        }

        if (elements.retakeBtn) {
            elements.retakeBtn.addEventListener('click', retakePhotos);
        }

        if (elements.downloadBtn) {
            elements.downloadBtn.addEventListener('click', downloadStrip);
        }

        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', saveStrip);
        }

        if (elements.cameraSelect) {
            elements.cameraSelect.addEventListener('change', switchCamera);
        }
    }

    // =========================
    // Dropdowns (Photo Count & Timer)
    // =========================

    function setupDropdowns() {
        console.log('Setting up dropdowns...');

        // Photo Count Dropdown
        const photoCountItems = elements.photoCountMenu.querySelectorAll('.dropdown-item');
        photoCountItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.stopPropagation();

                const count = parseInt(this.dataset.count, 10);
                state.photoCount = count;
                elements.photoCountText.textContent = `${count} Foto`;

                // Update active state
                photoCountItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Close dropdown
                elements.photoCountMenu.classList.remove('active');

                // Reset captured photos
                resetPhotos();
            });
        });

        // Timer Dropdown
        const timerItems = elements.timerMenu.querySelectorAll('.dropdown-item');
        timerItems.forEach(item => {
            item.addEventListener('click', function (e) {
                e.stopPropagation();

                const timer = parseInt(this.dataset.timer, 10);
                state.timerSeconds = timer;

                if (timer === 0) {
                    elements.timerText.textContent = 'No Timer';
                } else {
                    elements.timerText.textContent = `${timer}S Tertunda`;
                }

                // Update active state
                timerItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');

                // Close dropdown
                elements.timerMenu.classList.remove('active');
            });
        });

        // Dropdown toggle handlers
        if (elements.photoCountBtn) {
            elements.photoCountBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                elements.photoCountMenu.classList.toggle('active');
                elements.timerMenu.classList.remove('active');
            });
        }

        if (elements.timerBtn) {
            elements.timerBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                elements.timerMenu.classList.toggle('active');
                elements.photoCountMenu.classList.remove('active');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function () {
            elements.photoCountMenu.classList.remove('active');
            elements.timerMenu.classList.remove('active');
        });
    }

    // =========================
    // Frame Selection
    // =========================

    function setupFrameSelection() {
        console.log('Setting up frame selection...');

        const frameItems = document.querySelectorAll('.frame-item-compact');

        frameItems.forEach(item => {
            item.addEventListener('click', function () {
                const frameId = this.dataset.frameId;
                const frameUrl = this.dataset.frameUrl;

                console.log('Frame selected:', frameId, frameUrl);

                // Update state
                state.selectedFrame = {
                    id: frameId,
                    url: frameUrl,
                };

                // Update UI
                frameItems.forEach(f => f.classList.remove('active'));
                this.classList.add('active');

                // Show frame overlay on video
                if (frameUrl && elements.frameOverlay) {
                    elements.frameOverlay.src = frameUrl;
                    elements.frameOverlay.style.display = 'block';
                } else if (elements.frameOverlay) {
                    elements.frameOverlay.style.display = 'none';
                }
            });
        });
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

            // Populate camera select dropdown
            if (state.videoDevices.length > 1 && elements.cameraSelect) {
                elements.cameraSelect.style.display = 'block';
                elements.cameraSelect.innerHTML = '<option value="">Select Camera</option>';

                state.videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.textContent = device.label || `Camera ${index + 1}`;
                    elements.cameraSelect.appendChild(option);
                });
            }

            // Preferred device (back camera if available)
            const preferredDevice =
                state.videoDevices.find(d => d.label.toLowerCase().includes('back')) ||
                state.videoDevices[0];

            if (preferredDevice) {
                state.currentDeviceId = preferredDevice.deviceId;

                if (elements.cameraSelect) {
                    elements.cameraSelect.value = preferredDevice.deviceId;
                }
            }

            // Start camera stream
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
                    facingMode: state.currentDeviceId ? undefined : 'user',
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
        elements.startPhotoBtn.textContent = '⏳ Capturing...';

        // Clear thumbnails
        elements.thumbnailContainer.innerHTML = '';

        // Capture photos sequentially
        for (let i = 0; i < state.photoCount; i++) {
            state.currentPhotoIndex = i;
            await capturePhoto();

            // Wait a bit between captures
            if (i < state.photoCount - 1) {
                await sleep(1000);
            }
        }

        // All photos captured
        state.isCapturing = false;
        elements.startPhotoBtn.disabled = false;
        elements.startPhotoBtn.textContent = '📸 Mulai Foto';

        showCompletionButtons();
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

        // Draw frame overlay if selected
        if (state.selectedFrame && state.selectedFrame.url) {
            const frameImg = new Image();
            frameImg.src = state.selectedFrame.url;
            frameImg.crossOrigin = 'anonymous';

            try {
                ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);
            } catch (e) {
                console.warn('Frame overlay drawing skipped', e);
            }
        }

        // Convert to base64
        return canvas.toDataURL('image/png');
    }

    // =========================
    // Thumbnails & Retake
    // =========================

    function updateThumbnails() {
        elements.thumbnailContainer.innerHTML = '';

        state.capturedPhotos.forEach((photo, index) => {
            const thumbnail = document.createElement('div');
            thumbnail.className = 'thumbnail-item';
            thumbnail.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="thumbnail-number">${index + 1}</div>
            `;

            // Click to select for retake
            thumbnail.addEventListener('click', () => {
                if (confirm(`Retake photo ${index + 1}?`)) {
                    retakeSinglePhoto(index);
                }
            });

            elements.thumbnailContainer.appendChild(thumbnail);
        });
    }

    async function retakeSinglePhoto(index) {
        state.currentPhotoIndex = index;

        // Show countdown if timer is set
        if (state.timerSeconds > 0) {
            await showCountdown();
        }

        // Flash effect
        showFlash();

        // Capture from video
        const photoData = captureFromVideo();
        state.capturedPhotos[index] = photoData;

        // Update thumbnails
        updateThumbnails();
    }

    function retakePhotos() {
        if (confirm('Retake all photos?')) {
            resetPhotos();
            hideCompletionButtons();
        }
    }

    function resetPhotos() {
        state.capturedPhotos = [];
        state.currentPhotoIndex = 0;
        elements.thumbnailContainer.innerHTML =
            '<div class="thumbnail-placeholder"><p>📷 Foto akan muncul di sini</p></div>';
    }

    function showCompletionButtons() {
        elements.retakeBtn.style.display = 'block';
        elements.downloadBtn.style.display = 'block';
        elements.saveBtn.style.display = 'block';
    }

    function hideCompletionButtons() {
        elements.retakeBtn.style.display = 'none';
        elements.downloadBtn.style.display = 'none';
        elements.saveBtn.style.display = 'none';
    }

    // =========================
    // Download & Save Strip
    // =========================

    function downloadStrip() {
        console.log('Downloading strip...');

        const stripCanvas = document.createElement('canvas');
        const stripWidth = 800;
        const stripHeight = 600 * state.photoCount;

        stripCanvas.width = stripWidth;
        stripCanvas.height = stripHeight;

        const ctx = stripCanvas.getContext('2d');
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, stripWidth, stripHeight);

        let loadedCount = 0;
        const photos = [];

        state.capturedPhotos.forEach((photoData, index) => {
            const img = new Image();

            img.onload = function () {
                photos[index] = img;
                loadedCount++;

                if (loadedCount === state.capturedPhotos.length) {
                    photos.forEach((image, idx) => {
                        const yPos = idx * 600;
                        ctx.drawImage(image, 0, yPos, stripWidth, 600);
                    });

                    stripCanvas.toBlob(blob => {
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `bingkiskaca_${Date.now()}.png`;
                        a.click();
                        URL.revokeObjectURL(url);
                    });
                }
            };

            img.src = photoData;
        });
    }

    async function saveStrip() {
        try {
            console.log('Saving strip...');

            elements.saveBtn.disabled = true;
            elements.saveBtn.textContent = '💾 Saving...';

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
                alert('Photo strip saved successfully!');

                if (confirm('View in your profile?')) {
                    window.location.href = '/profile';
                } else {
                    resetPhotos();
                    hideCompletionButtons();
                }
            } else {
                throw new Error(result.error || 'Failed to save strip');
            }
        } catch (error) {
            console.error('Error saving strip:', error);
            alert('Error saving photo strip: ' + error.message);
        } finally {
            elements.saveBtn.disabled = false;
            elements.saveBtn.textContent = '💾 Simpan';
        }
    }

    // =========================
    // DOM Ready
    // =========================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
