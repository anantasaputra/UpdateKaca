/**
 * BINGKIS KACA - COMPLETE PHOTOBOOTH SYSTEM
 * With Camera Selection, Timer, Multi-Photo, and Review Modal
 */

(function () {
    'use strict';

    console.log('Complete Photobooth System Initialized');

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
        // Controls
        cameraSelect: document.getElementById('cameraSelect'),
        photoCountSelect: document.getElementById('photoCountSelect'),
        timerSelect: document.getElementById('timerSelect'),

        // Camera
        video: document.getElementById('cameraVideo'),
        canvas: document.getElementById('cameraCanvas'),
        frameOverlay: document.getElementById('frameOverlay'),
        countdownOverlay: document.getElementById('countdownOverlay'),
        countdownNumber: document.getElementById('countdownNumber'),
        flashEffect: document.getElementById('flashEffect'),

        // Actions
        startPhotoBtn: document.getElementById('startPhotoBtn'),

        // Thumbnails / Progress
        thumbnailContainer: document.getElementById('thumbnailContainer'),
        progressText: document.getElementById('progressText'),
        progressFill: document.getElementById('progressFill'),

        // Review Modal
        reviewModal: document.getElementById('reviewModal'),
        stripCanvas: document.getElementById('stripCanvas'),
        frameOptions: document.getElementById('frameOptions'),
        downloadBtn: document.getElementById('downloadBtn'),
        retakeBtn: document.getElementById('retakeBtn'),
        saveBtn: document.getElementById('saveBtn'), // optional if guest
    };

    // =========================
    // Initialization
    // =========================

    function init() {
        console.log('Initializing...');

        if (!checkElements()) {
            console.error('Some elements missing!');
            return;
        }

        setupEventListeners();
        initializeCamera();
        updateProgress();
    }

    function checkElements() {
        for (const [key, element] of Object.entries(elements)) {
            // saveBtn boleh tidak ada untuk guest user
            if (!element && key !== 'saveBtn') {
                console.error(`Missing element: ${key}`);
                return false;
            }
        }
        return true;
    }

    // =========================
    // Event Listeners
    // =========================

    function setupEventListeners() {
        // Controls
        if (elements.photoCountSelect) {
            elements.photoCountSelect.addEventListener('change', e => {
                state.photoCount = parseInt(e.target.value, 10);
                updateProgress();
            });
        }

        if (elements.timerSelect) {
            elements.timerSelect.addEventListener('change', e => {
                state.timerSeconds = parseInt(e.target.value, 10);
            });
        }

        if (elements.cameraSelect) {
            elements.cameraSelect.addEventListener('change', e => {
                state.currentDeviceId = e.target.value;
                startCamera();
            });
        }

        // Actions
        if (elements.startPhotoBtn) {
            elements.startPhotoBtn.addEventListener('click', startPhotoSession);
        }

        // Review Modal Actions
        if (elements.downloadBtn) {
            elements.downloadBtn.addEventListener('click', downloadStrip);
        }

        if (elements.retakeBtn) {
            elements.retakeBtn.addEventListener('click', retakeAll);
        }

        if (elements.saveBtn) {
            elements.saveBtn.addEventListener('click', saveStrip);
        }

        // Frame Selection in Review
        if (elements.frameOptions) {
            const frameOptions = elements.frameOptions.querySelectorAll('.frame-option');

            frameOptions.forEach(option => {
                option.addEventListener('click', function () {
                    frameOptions.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');

                    state.selectedFrame = {
                        id: this.dataset.frameId,
                        url: this.dataset.frameUrl,
                    };

                    // Redraw strip with new frame
                    drawPhotoStrip();
                });
            });
        }
    }

    // =========================
    // Camera Handling
    // =========================

    async function initializeCamera() {
        try {
            console.log('Requesting camera access...');

            // Request permission first
            await navigator.mediaDevices.getUserMedia({ video: true });

            // Get devices
            const devices = await navigator.mediaDevices.enumerateDevices();
            state.videoDevices = devices.filter(d => d.kind === 'videoinput');

            console.log(`Found ${state.videoDevices.length} cameras`);

            // Populate select
            if (elements.cameraSelect) {
                elements.cameraSelect.innerHTML = '';
                state.videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.textContent = device.label || `Camera ${index + 1}`;
                    elements.cameraSelect.appendChild(option);
                });
            }

            // Start with first camera
            if (state.videoDevices.length > 0) {
                state.currentDeviceId = state.videoDevices[0].deviceId;

                if (elements.cameraSelect) {
                    elements.cameraSelect.value = state.currentDeviceId;
                }

                await startCamera();
            }
        } catch (error) {
            console.error('Camera error:', error);
            alert('Cannot access camera. Please allow camera permission.');
        }
    }

    async function startCamera() {
        try {
            if (state.stream) {
                state.stream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: {
                    deviceId: state.currentDeviceId ? { exact: state.currentDeviceId } : undefined,
                    width: { ideal: 1280 },
                    height: { ideal: 960 },
                },
                audio: false,
            };

            state.stream = await navigator.mediaDevices.getUserMedia(constraints);

            if (elements.video) {
                elements.video.srcObject = state.stream;
            }

            console.log('Camera started');
        } catch (error) {
            console.error('Error starting camera:', error);
        }
    }

    // =========================
    // Capture Flow
    // =========================

    async function startPhotoSession() {
        if (state.isCapturing) return;

        state.isCapturing = true;
        state.capturedPhotos = [];
        state.currentPhotoIndex = 0;

        if (elements.startPhotoBtn) {
            elements.startPhotoBtn.disabled = true;

            const btnTextSpan = elements.startPhotoBtn.querySelector('.btn-text');
            if (btnTextSpan) {
                btnTextSpan.textContent = 'Capturing...';
            } else {
                elements.startPhotoBtn.textContent = 'Capturing...';
            }
        }

        // Clear thumbnails
        if (elements.thumbnailContainer) {
            elements.thumbnailContainer.innerHTML = '';
        }
        updateProgress();

        // Capture photos
        for (let i = 0; i < state.photoCount; i++) {
            state.currentPhotoIndex = i;
            await capturePhoto();

            if (i < state.photoCount - 1) {
                await sleep(1000);
            }
        }

        // All photos captured
        state.isCapturing = false;

        if (elements.startPhotoBtn) {
            elements.startPhotoBtn.disabled = false;

            const btnTextSpan = elements.startPhotoBtn.querySelector('.btn-text');
            if (btnTextSpan) {
                btnTextSpan.textContent = 'Mulai Foto';
            } else {
                elements.startPhotoBtn.textContent = 'Mulai Foto';
            }
        }

        // Show review modal
        showReviewModal();
    }

    async function capturePhoto() {
        // Countdown
        if (state.timerSeconds > 0) {
            await showCountdown();
        }

        // Flash
        if (elements.flashEffect) {
            elements.flashEffect.style.display = 'block';
            setTimeout(() => {
                elements.flashEffect.style.display = 'none';
            }, 300);
        }

        // Capture
        const photoData = captureFromVideo();
        state.capturedPhotos.push(photoData);

        // Update UI
        addThumbnail(photoData, state.currentPhotoIndex);
        updateProgress();
    }

    function showCountdown() {
        return new Promise(resolve => {
            let count = state.timerSeconds;

            if (!elements.countdownOverlay || !elements.countdownNumber) {
                resolve();
                return;
            }

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

    function captureFromVideo() {
        if (!elements.video || !elements.canvas) return null;

        elements.canvas.width = elements.video.videoWidth || 1280;
        elements.canvas.height = elements.video.videoHeight || 960;

        const ctx = elements.canvas.getContext('2d');
        ctx.drawImage(elements.video, 0, 0, elements.canvas.width, elements.canvas.height);

        return elements.canvas.toDataURL('image/png');
    }

    function addThumbnail(photoData, index) {
        if (!elements.thumbnailContainer) return;
        if (!photoData) return;

        const div = document.createElement('div');
        div.className = 'thumbnail-item';
        div.innerHTML = `
            <img src="${photoData}" alt="Photo ${index + 1}">
            <div class="thumbnail-badge">${index + 1}</div>
        `;
        elements.thumbnailContainer.appendChild(div);
    }

    function updateProgress() {
        const current = state.capturedPhotos.length;
        const total = state.photoCount;
        const percentage = total > 0 ? (current / total) * 100 : 0;

        if (elements.progressText) {
            elements.progressText.textContent = `${current}/${total} foto`;
        }

        if (elements.progressFill) {
            elements.progressFill.style.width = `${percentage}%`;
        }
    }

    // =========================
    // Review Modal & Strip
    // =========================

    function showReviewModal() {
        if (!elements.reviewModal) return;
        elements.reviewModal.style.display = 'flex';
        drawPhotoStrip();
    }

    function drawPhotoStrip() {
        if (!elements.stripCanvas || state.capturedPhotos.length === 0) return;

        const photoWidth = 400;
        const photoHeight = 300;
        const stripWidth = photoWidth;
        const stripHeight = photoHeight * state.photoCount;

        elements.stripCanvas.width = stripWidth;
        elements.stripCanvas.height = stripHeight;

        const ctx = elements.stripCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, stripWidth, stripHeight);

        let loadedCount = 0;
        const images = [];

        state.capturedPhotos.forEach((photoData, index) => {
            const img = new Image();

            img.onload = function () {
                images[index] = img;
                loadedCount++;

                if (loadedCount === state.capturedPhotos.length) {
                    // Draw all photos
                    images.forEach((image, idx) => {
                        const yPos = idx * photoHeight;
                        ctx.drawImage(image, 0, yPos, photoWidth, photoHeight);
                    });

                    // Draw frame if selected
                    if (state.selectedFrame && state.selectedFrame.url) {
                        const frameImg = new Image();
                        frameImg.crossOrigin = 'anonymous';

                        frameImg.onload = function () {
                            for (let i = 0; i < state.photoCount; i++) {
                                const yPos = i * photoHeight;
                                ctx.drawImage(frameImg, 0, yPos, photoWidth, photoHeight);
                            }
                        };

                        frameImg.src = state.selectedFrame.url;
                    }
                }
            };

            img.src = photoData;
        });
    }

    function downloadStrip() {
        if (!elements.stripCanvas) return;

        elements.stripCanvas.toBlob(blob => {
            if (!blob) return;
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `bingkiskaca_${Date.now()}.png`;
            a.click();
            URL.revokeObjectURL(url);
        });
    }

    function retakeAll() {
        if (!confirm('Retake all photos?')) return;

        if (elements.reviewModal) {
            elements.reviewModal.style.display = 'none';
        }

        state.capturedPhotos = [];
        updateProgress();
    }

    // =========================
    // Save Strip to Server
    // =========================

    async function saveStrip() {
        if (!elements.saveBtn) return;

        try {
            elements.saveBtn.disabled = true;
            elements.saveBtn.innerHTML = '<span>⏳</span> Saving...';

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
                alert('Saved successfully!');
                if (confirm('View in profile?')) {
                    window.location.href = '/profile';
                } else {
                    if (elements.reviewModal) {
                        elements.reviewModal.style.display = 'none';
                    }
                    state.capturedPhotos = [];
                    updateProgress();
                }
            } else {
                throw new Error(result.error || 'Save failed');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            elements.saveBtn.disabled = false;
            elements.saveBtn.innerHTML = '<span>💾</span> Simpan';
        }
    }

    // =========================
    // Utilities
    // =========================

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // =========================
    // DOM Ready
    // =========================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // =========================
    // Global functions for modals
    // =========================

    window.closeLoginModal = function () {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };
})();
