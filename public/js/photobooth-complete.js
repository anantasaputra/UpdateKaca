// PhotoBooth Application with Custom PNG Frames + Dynamic DB Frames
// ✅ UPDATED: Fixed frame loading issues + improved error handling

class PhotoBoothApp {
    constructor() {
        this.video = document.getElementById('cameraVideo');
        this.canvas = document.getElementById('cameraCanvas');
        this.ctx = this.canvas.getContext('2d');
        this.photos = [];
        this.currentPhotoCount = 4;
        this.currentCamera = null;
        this.timerDuration = 3;
        this.isCapturing = false;
        
        // NEW: Dynamic frame system from database
        this.useDynamicFrames = window.hasFramesInDB || false;
        this.selectedFrameId = null;
        this.selectedFramePath = null;
            // ✅ NEW: Strip mode (single or double)
        this.stripMode = 'single'; // default

        
        // ✅ NEW: Backend frames data
        this.backendFrames = {};
        
        // OLD: Fallback color system
        this.selectedColor = 'brown';
        this.frameType = 'frame4';
        
        // ✅ Strip management
        this.currentStripId = null;
        this.currentStripUrl = null;
        this.retakingIndex = null;
        this.isStripSaved = false;

        // Frame configurations
        this.frameConfigs = this.getFrameConfigs();

        console.log('PhotoBoothApp initialized:', {
            useDynamicFrames: this.useDynamicFrames,
            framesByCount: window.framesByCount,
            availableFrames: window.availableFrames
        });

        // ✅ Load frames from backend
        this.loadFramesFromBackend();

        this.init();
    }
    // ✅ NEW: sync pilihan frame dari DOM ke state
    updateSelectedFrameFromActive() {
        if (!this.useDynamicFrames) return;

        const active = document.querySelector('.frame-option.active');
        if (active) {
            this.selectedFrameId = active.dataset.frameId || null;
            this.selectedFramePath = active.dataset.framePath || null;
            console.log('Synced selected frame from DOM:', {
                id: this.selectedFrameId,
                path: this.selectedFramePath
            });
        }
    }
    /**
     * ✅ NEW: Load frames from backend
     */
    loadFramesFromBackend() {
        if (window.availableFrames && window.availableFrames.length > 0) {
            console.log('Loading frames from backend:', window.availableFrames);
            
            // Store frames grouped by photo count
            window.availableFrames.forEach(frame => {
                if (!this.backendFrames[frame.photo_count]) {
                    this.backendFrames[frame.photo_count] = [];
                }
                this.backendFrames[frame.photo_count].push(frame);
            });
            
            console.log('Frames grouped by count:', this.backendFrames);
        } else {
            console.warn('No frames loaded from backend, using fallback');
            this.backendFrames = {};
        }
    }

    /**
     * ✅ NEW: Get frame path with priority system
     */
        getFramePath() {
            // 1) Selalu utamakan frame yang sedang dipilih di UI
            if (this.useDynamicFrames && this.selectedFramePath) {
                console.log('Using selected frame path:', this.selectedFramePath);
                return this.selectedFramePath;
            }

            // 2) Kalau belum ada yang dipilih tapi data backend ada, pilih yang cocok dengan mode & photo_count
            if (this.backendFrames && this.backendFrames[this.currentPhotoCount]) {
                const frames = this.backendFrames[this.currentPhotoCount];

                // Cocokkan juga dengan stripMode
                const targetMode = this.stripMode === 'double' ? true : false;
                const matchingFrame = frames.find(f => f.is_double_strip === targetMode);

                if (matchingFrame) {
                    console.log('Using matching backend frame:', matchingFrame.image_path);
                    return matchingFrame.image_path;
                }

                // Fallback: pertama untuk photo_count ini
                console.log('Using first backend frame for this count:', frames[0].image_path);
                return frames[0].image_path;
            }

            // 3) Terakhir banget: sistem lama
            const fallbackPath = `/storage/frames/4R_${this.selectedColor}${this.currentPhotoCount}.png`;
            console.warn('Using legacy fallback frame:', fallbackPath);
            return fallbackPath;
        }


    // Frame configurations
        getFrameConfigs() {
            // ✅ Single mode configs (existing)
            const singleConfigs = {
                2: {
                    frameSize: { width: 1200, height: 1800 },
                    photoAreas: [
                        { x: 70, y: 60, width: 1050, height: 735 },
                        { x: 70, y: 805, width: 1050, height: 735 }
                    ]
                },
                3: {
                    frameSize: { width: 1200, height: 1800 },
                    photoAreas: [
                        { x: 70, y: 60, width: 520, height: 765 },
                        { x: 610, y: 60, width: 520, height: 765 },
                        { x: 70, y: 805, width: 1050, height: 735 }
                    ]
                },
                4: {
                    frameSize: { width: 1200, height: 1800 },
                    photoAreas: [
                        { x: 70, y: 60, width: 520, height: 765 },
                        { x: 610, y: 60, width: 520, height: 765 },
                        { x: 70, y: 805, width: 520, height: 765 },
                        { x: 610, y: 805, width: 520, height: 765 }
                    ]
                }
            };

            // ✅ NEW: Double mode configs
            // ✅ UPDATED: Double mode configs (berdasarkan frame asli)
            const doubleConfigs = {
                2: {
                    frameSize: { width: 1205, height: 1795 },
                    photoAreas: [
                        // Kolom KIRI
                        { x: 45, y: 120, width: 485, height: 650 },   // Foto 1 (kiri atas)
                        { x: 45, y: 820, width: 485, height: 650 },   // Foto 2 (kiri bawah)
                        // Kolom KANAN (duplicate)
                        { x: 635, y: 120, width: 485, height: 650 },  // Foto 1 duplicate (kanan atas)
                        { x: 635, y: 820, width: 485, height: 650 },  // Foto 2 duplicate (kanan bawah)
                    ]
                },
                3: {
                    frameSize: { width: 1205, height: 1795 },
                    photoAreas: [
                        // Kolom KIRI (3 foto)
                        { x: 85, y: 120, width: 485, height: 420 },   // Foto 1 (kiri atas)
                        { x: 85, y: 585, width: 485, height: 420 },   // Foto 2 (kiri tengah)
                        { x: 85, y: 1050, width: 485, height: 420 },  // Foto 3 (kiri bawah)
                        // Kolom KANAN (duplicate)
                        { x: 635, y: 120, width: 485, height: 420 },  // Foto 1 duplicate
                        { x: 635, y: 585, width: 485, height: 420 },  // Foto 2 duplicate
                        { x: 635, y: 1050, width: 485, height: 420 }, // Foto 3 duplicate
                    ]
                },
                4: {
                    frameSize: { width: 1205, height: 1795 },
                    photoAreas: [
                        // Kolom KIRI (4 foto)
                        { x: 85, y: 110, width: 485, height: 310 },    // Foto 1 (kiri atas)
                        { x: 85, y: 470, width: 485, height: 310 },   // Foto 2
                        { x: 85, y: 820, width: 485, height: 310 },   // Foto 3
                        { x: 85, y: 1170, width: 485, height: 310 },  // Foto 4 (kiri bawah)
                        // Kolom KANAN (duplicate)
                        { x: 635, y: 110, width: 485, height: 310 },   // Foto 1 duplicate
                        { x: 635, y: 475, width: 485, height: 310 },  // Foto 2 duplicate
                        { x: 635, y: 820, width: 485, height: 310 },  // Foto 3 duplicate
                        { x: 635, y: 1170, width: 485, height: 310 }, // Foto 4 duplicate
                    ]
                }
            };

            // ✅ Return config berdasarkan strip mode
            return this.stripMode === 'double' ? doubleConfigs : singleConfigs;
        }


    init() {
        this.setupCameraList();
        this.setupEventListeners();
        //     if (this.useDynamicFrames) {
        //     console.log('Applying initial frame filter...');
        //     setTimeout(() => {
        //         this.filterFramesByMode(this.stripMode);
        //     }, 500); // Delay untuk pastikan DOM sudah ready
        // }
    }

    async setupCameraList() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            const select = document.getElementById('cameraSelect');
            select.innerHTML = '';
            
            videoDevices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || `Camera ${index + 1}`;
                select.appendChild(option);
            });

            if (videoDevices.length > 0) {
                this.currentCamera = videoDevices[0].deviceId;
                this.startCamera();
            }
        } catch (error) {
            console.error('Error getting cameras:', error);
        }
    }

    async startCamera() {
        try {
            if (this.video.srcObject) {
                this.video.srcObject.getTracks().forEach(track => track.stop());
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: this.currentCamera ? { exact: this.currentCamera } : undefined,
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            });

            this.video.srcObject = stream;
        } catch (error) {
            console.error('Error starting camera:', error);
            alert('Tidak dapat mengakses kamera. Pastikan Anda memberikan izin akses kamera.');
        }
    }

    setupEventListeners() {
        // Camera selection
        document.getElementById('cameraSelect').addEventListener('change', (e) => {
            this.currentCamera = e.target.value;
            this.startCamera();
        });
        // Timer selection
        const timerSelect = document.getElementById('timerSelect');
        if (timerSelect) {
            timerSelect.addEventListener('change', (e) => {
                this.timerDuration = parseInt(e.target.value);
                console.log('Timer changed to:', this.timerDuration);
            });
        }
        // Photo count selection
      // Saat ganti jumlah foto
        document.getElementById('photoCountSelect').addEventListener('change', (e) => {
            this.currentPhotoCount = parseInt(e.target.value);
            this.frameType = `frame${this.currentPhotoCount}`;
            this.resetStrip();

            if (this.useDynamicFrames) {
                document.querySelectorAll('.frame-group').forEach(group => {
                    const groupCount = parseInt(group.dataset.photoCount);
                    group.style.display = groupCount === this.currentPhotoCount ? 'block' : 'none';
                });

                this.selectedFrameId = null;
                this.selectedFramePath = null;
                document.querySelectorAll('.frame-option').forEach(opt => opt.classList.remove('active'));

                this.filterFramesByMode(this.stripMode);
            }

            this.updateProgress();
        });

        // Saat ganti mode strip
        document.getElementById('stripModeSelect').addEventListener('change', (e) => {
            this.stripMode = e.target.value;
            this.resetStrip();

            if (this.useDynamicFrames) {
                document.querySelectorAll('.frame-group').forEach(group => {
                    const groupCount = parseInt(group.dataset.photoCount);
                    group.style.display = groupCount === this.currentPhotoCount ? 'block' : 'none';
                });

                this.selectedFrameId = null;
                this.selectedFramePath = null;
                document.querySelectorAll('.frame-option').forEach(opt => opt.classList.remove('active'));

                this.filterFramesByMode(this.stripMode);
            }
        });



        // Start photo button
        document.getElementById('startPhotoBtn').addEventListener('click', () => {
            this.startPhotoSession();
        });

        // Review modal buttons
        document.getElementById('backBtn').addEventListener('click', () => {
            this.closeReviewModal();
        });

        document.getElementById('downloadBtn').addEventListener('click', () => {
            this.downloadPhotoStrip();
        });

        document.getElementById('retakeBtn').addEventListener('click', () => {
            this.openRetakeSelection();
        });

        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveStripToProfile();
            });
        }

        if (this.useDynamicFrames) {
            this.setupDynamicFrameSelection();
        } else {
            this.setupColorSelection();
        }
    }

    /**
     * ✅ Reset strip state
     */
    resetStrip() {
        this.photos = [];
        this.currentStripId = null;
        this.currentStripUrl = null;
        this.isStripSaved = false;
        this.retakingIndex = null;
        this.updateThumbnails();
        this.updateProgress();
        
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.style.display = 'none';
        }
        
        console.log('Strip state reset');
    }

    /**
     * ✅ UPDATED: Setup dynamic frame selection
     */
    setupDynamicFrameSelection() {
        console.log('Setting up dynamic frame selection...');
        const frameOptions = document.querySelectorAll('.frame-option');
        
        console.log(`Found ${frameOptions.length} frame options`);
        
        frameOptions.forEach((option, index) => {
            option.addEventListener('click', async () => {
                console.log(`\nFrame ${index + 1} clicked`);
                
                document.querySelectorAll('.frame-option').forEach(opt => opt.classList.remove('active'));
                option.classList.add('active');
                
                this.selectedFrameId = option.dataset.frameId;
                this.selectedFramePath = option.dataset.framePath;
                this.selectedColor = option.dataset.color || 'brown';
                
                console.log('Frame selected:', {
                    id: this.selectedFrameId,
                    path: this.selectedFramePath,
                    color: this.selectedColor,
                    photoCount: option.dataset.photoCount
                });
                
                // ✅ Test frame loading
                await this.testFrameLoad(this.selectedFramePath);
                
                // Update preview if photos exist
                if (this.photos.length > 0) {
                    console.log('Updating preview with new frame...');
                    await this.updateStripPreview();
                    
                    if (this.currentStripId && this.photos.length === this.currentPhotoCount) {
                        console.log('Re-composing strip with new frame...');
                        await this.composeAndUpdateStrip();
                    }
                }
            });
        });
        
        // const firstFrame = document.querySelector('.frame-option');
        // if (firstFrame) {
        //     console.log('Auto-selecting first frame...');
        //     firstFrame.click();
        // } else {
        //     console.warn('No frame options found!');
        // }
    }
    /**
     * ✅ NEW: Filter frames berdasarkan strip mode
     */
        filterFramesByMode(mode) {
            console.log('Filtering frames by mode:', mode);
            
            if (!this.useDynamicFrames) {
                console.log('Dynamic frames not used, skipping filter');
                return;
            }
            
            const frameOptions = document.querySelectorAll('.frame-option');
            let firstVisible = null;
            let visibleCount = 0;

            frameOptions.forEach(frame => {
                const rawMode = frame.getAttribute('data-strip-mode');
                const frameMode = rawMode || 'single'; // fallback kalau null
                const photoCount = frame.getAttribute('data-photo-count');
                const selectedPhotoCount = this.currentPhotoCount.toString();

                // Debug bantuan
                console.log('Frame check:', {
                    title: frame.title,
                    frameMode,
                    photoCount,
                    selectedMode: mode,
                    selectedPhotoCount
                });

                if (frameMode === mode && photoCount === selectedPhotoCount) {
                    frame.style.display = 'block';
                    visibleCount++;
                    if (!firstVisible) {
                        firstVisible = frame;
                    }
                } else {
                    frame.style.display = 'none';
                    frame.classList.remove('active');
                }
            });

            // Kalau tidak ada satupun yang cocok, jangan kosongkan UI:
            if (visibleCount === 0) {
                console.warn('No frames matched filter, showing all for this photoCount');
                frameOptions.forEach(frame => {
                    const photoCount = frame.getAttribute('data-photo-count');
                    const selectedPhotoCount = this.currentPhotoCount.toString();
                    if (photoCount === selectedPhotoCount) {
                        frame.style.display = 'block';
                        if (!firstVisible) {
                            firstVisible = frame;
                        }
                    }
                });
            }

            if (firstVisible) {
                console.log('Auto-selecting first visible frame:', firstVisible.title);
                firstVisible.classList.add('active');

                // Set state supaya getFramePath selalu pakai ini
                this.selectedFrameId = firstVisible.dataset.frameId || null;
                this.selectedFramePath = firstVisible.dataset.framePath || null;

                // Panggil click supaya preview ikut update
                firstVisible.click();
            }
        }

    /**
     * ✅ NEW: Test frame loading
     */
    async testFrameLoad(framePath) {
        try {
            console.log(`Testing frame load: ${framePath}`);
            
            const testImg = new Image();
            testImg.crossOrigin = 'anonymous';
            
            return new Promise((resolve, reject) => {
                const timeout = setTimeout(() => {
                    console.error('Frame load timeout');
                    reject(new Error('Timeout'));
                }, 5000);
                
                testImg.onload = () => {
                    clearTimeout(timeout);
                    console.log('Frame loaded successfully!');
                    resolve(true);
                };
                
                testImg.onerror = (error) => {
                    clearTimeout(timeout);
                    console.error('Frame load failed!');
                    console.error('Path:', framePath);
                    
                    this.showFrameError(framePath, error);
                    reject(error);
                };
                
                testImg.src = framePath;
            });
        } catch (error) {
            console.error('Frame test error:', error);
        }
    }

    /**
     * ✅ NEW: Show user-friendly frame error
     */
    showFrameError(framePath, error) {
        const errorMsg = `Frame tidak dapat dimuat!\n\n` +
                        `Path: ${framePath}\n\n` +
                        `Kemungkinan penyebab:\n` +
                        `1. File tidak ada di storage/app/public/frames/\n` +
                        `2. Symlink belum dibuat (php artisan storage:link)\n` +
                        `3. Nama file salah (case-sensitive)\n` +
                        `4. File permission tidak OK\n\n` +
                        `Cek browser console (F12) untuk detail.`;
        
        alert(errorMsg);
    }

    setupColorSelection() {
        const colorButtons = document.querySelectorAll('.color-btn');
        colorButtons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                colorButtons.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.selectedColor = e.target.dataset.color;
                
                await this.updateStripPreview();
                
                if (this.photos.length > 0 && this.currentStripId) {
                    await this.composeAndUpdateStrip();
                }
            });
        });
    }

    async startPhotoSession() {
        if (this.isCapturing) return;

        if (this.retakingIndex === null) {
            this.resetStrip();
        }

        this.isCapturing = true;
        this.updateThumbnails();
        this.updateProgress();

        const btn = document.getElementById('startPhotoBtn');
        btn.disabled = true;

        // Handle retake
        if (this.retakingIndex !== null) {
            await this.capturePhoto(this.retakingIndex + 1);
            
            this.isCapturing = false;
            btn.disabled = false;
            
            this.updateThumbnails();
            this.updateProgress();
            this.closeRetakeModal();
            this.showReviewModal();
            
            await this.composeAndUpdateStrip();
            
            this.retakingIndex = null;
            btn.querySelector('.btn-text').textContent = 'Mulai Foto';
            return;
        }

        // Capture all photos
        for (let i = 0; i < this.currentPhotoCount; i++) {
            await this.capturePhoto(i + 1);
            await this.wait(1000);
        }

        this.isCapturing = false;
        btn.disabled = false;
        
        this.showReviewModal();
        
        // ✅ Save strip once
        await this.composeAndSaveStrip();
    }

    async capturePhoto(photoNumber) {
        if (this.timerDuration > 0) {
            await this.showCountdown();
        }

        const flash = document.getElementById('flashEffect');
        flash.style.display = 'block';
        setTimeout(() => {
            flash.style.display = 'none';
        }, 300);

        this.canvas.width = this.video.videoWidth;
        this.canvas.height = this.video.videoHeight;
        this.ctx.drawImage(this.video, 0, 0);
        
        const photoData = this.canvas.toDataURL('image/png');
        
        if (this.retakingIndex !== null) {
            this.photos[this.retakingIndex] = photoData;
        } else {
            this.photos.push(photoData);
        }

        this.updateThumbnails();
        this.updateProgress();
        
        console.log(`📷 Photo ${photoNumber} captured (${this.photos.length}/${this.currentPhotoCount})`);
    }

    async showCountdown() {
        const overlay = document.getElementById('countdownOverlay');
        const number = document.getElementById('countdownNumber');
        
        overlay.style.display = 'flex';

        for (let i = this.timerDuration; i > 0; i--) {
            number.textContent = i;
            number.style.animation = 'none';
            setTimeout(() => {
                number.style.animation = 'countdownPulse 1s ease-in-out';
            }, 10);
            await this.wait(1000);
        }

        overlay.style.display = 'none';
    }

    updateThumbnails() {
        const container = document.getElementById('thumbnailContainer');
        container.innerHTML = '';

        if (this.photos.length === 0) {
            container.innerHTML = '<div class="thumbnail-empty">Foto akan muncul di sini</div>';
            return;
        }

        this.photos.forEach((photo, index) => {
            const div = document.createElement('div');
            div.className = 'thumbnail-item';
            div.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="thumbnail-badge">${index + 1}</div>
            `;
            container.appendChild(div);
        });
    }

    updateProgress() {
        const text = document.getElementById('progressText');
        const fill = document.getElementById('progressFill');
        
        text.textContent = `${this.photos.length}/${this.currentPhotoCount} foto`;
        fill.style.width = `${(this.photos.length / this.currentPhotoCount) * 100}%`;
    }

    openRetakeSelection() {
        const retakeModal = document.getElementById('retakeModal');
        const retakePhotoGrid = document.getElementById('retakePhotoGrid');
        
        retakePhotoGrid.innerHTML = '';
        
        this.photos.forEach((photo, index) => {
            const item = document.createElement('div');
            item.className = 'retake-photo-item';
            item.innerHTML = `
                <img src="${photo}" alt="Photo ${index + 1}">
                <div class="retake-photo-label">Foto ${index + 1}</div>
                <button class="retake-photo-button" data-index="${index}">
                    Ambil Ulang
                </button>
            `;
            
            const btn = item.querySelector('.retake-photo-button');
            btn.addEventListener('click', () => {
                this.retakePhoto(index);
            });
            
            retakePhotoGrid.appendChild(item);
        });
        
        retakeModal.style.display = 'flex';
    }

    retakePhoto(index) {
        this.retakingIndex = index;
        this.closeRetakeModal();
        this.closeReviewModal();
        
        const btn = document.getElementById('startPhotoBtn');
        btn.querySelector('.btn-text').textContent = `Ambil Ulang Foto ${index + 1}`;
        
        document.querySelector('.camera-container').scrollIntoView({ behavior: 'smooth' });
        
        setTimeout(() => {
            this.startPhotoSession();
        }, 1000);
    }

    closeRetakeModal() {
        const retakeModal = document.getElementById('retakeModal');
        if (retakeModal) {
            retakeModal.style.display = 'none';
        }
    }

    /**
     * ✅ Compose and SAVE strip (once only)
     */
    async composeAndSaveStrip() {
        if (this.isStripSaved) {
            console.log('Strip already saved, skipping...');
            return;
        }
  

        if (this.photos.length !== this.currentPhotoCount) {
            console.log(`Photos incomplete: ${this.photos.length}/${this.currentPhotoCount}`);
            return;
        }

        console.log('\nCreating and saving final strip...');

        try {
            const downloadBtn = document.getElementById('downloadBtn');
            const originalDownloadText = downloadBtn ? downloadBtn.innerHTML : '';
            
            if (downloadBtn) {
                downloadBtn.disabled = true;
                downloadBtn.innerHTML = 'Processing...';
            }
            this.updateSelectedFrameFromActive(); 
            const finalCanvas = await this.createFinalCanvasWithCustomFrame();
            const finalImageData = finalCanvas.toDataURL('image/png');
            
            await this.sendToServer(finalImageData, downloadBtn, originalDownloadText);
            
            this.isStripSaved = true;
            console.log('Strip saved successfully!');

        } catch (error) {
            console.error('Error composing strip:', error);
            alert('Terjadi kesalahan: ' + error.message);
            
            const downloadBtn = document.getElementById('downloadBtn');
            if (downloadBtn) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = 'Download';
            }
        }
    }

    /**
     * ✅ Update existing strip (no new save)
     */
    async composeAndUpdateStrip() {
        if (!this.currentStripId) {
            console.log('No strip ID, saving new strip...');
            await this.composeAndSaveStrip();
            return;
        }
        

        console.log('\nUpdating existing strip...');

        try {
            this.updateSelectedFrameFromActive(); 
            const finalCanvas = await this.createFinalCanvasWithCustomFrame();
            const finalImageData = finalCanvas.toDataURL('image/png');
            
            const response = await fetch(`/photobooth/update-strip/${this.currentStripId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    image: finalImageData,
                    frame_id: this.useDynamicFrames ? this.selectedFrameId : null,
                    photo_count: this.currentPhotoCount,
                    is_double_strip: this.stripMode === 'double' ,
                    
                })
            });

            const result = await response.json();
            
            if (result.success) {
                console.log('Strip updated successfully!');
                this.currentStripUrl = result.strip_url;
            }

        } catch (error) {
            console.error('Error updating strip:', error);
        }
    }

    async sendToServer(finalImageData, downloadBtn, originalDownloadText) {
        console.log('Sending strip to server...');
        
        const response = await fetch('/photobooth/compose', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                image: finalImageData,
                frame_id: this.useDynamicFrames ? this.selectedFrameId : null,
                photo_count: this.currentPhotoCount,
                is_double_strip: this.stripMode === 'double' ,
            })
        });

        const result = await response.json();
        
        if (result.success) {
            this.currentStripId = result.strip_id;
            this.currentStripUrl = result.strip_url;
            
            console.log('✅ Strip created:', {
                id: this.currentStripId,
                url: this.currentStripUrl
            });
            
            this.showSaveButton();
            
            if (downloadBtn) {
                downloadBtn.disabled = false;
                downloadBtn.innerHTML = originalDownloadText || 'Download';
            }
        } else {
            throw new Error(result.error || 'Unknown error');
        }
    }

    /**
     * ✅ UPDATED: Create final canvas with improved frame loading
     */
    async createFinalCanvasWithCustomFrame() {
        console.log('\nCreating final canvas...');
        
        const tempCanvas = document.createElement('canvas');
        const ctx = tempCanvas.getContext('2d');

        const config = this.frameConfigs[this.currentPhotoCount];
        
        if (!config) {
            throw new Error(`Config not found for ${this.currentPhotoCount} photos`);
        }

        tempCanvas.width = config.frameSize.width;
        tempCanvas.height = config.frameSize.height;

        // White background
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);

        // Draw photos
        console.log(`Drawing ${this.photos.length} photos in ${this.stripMode} mode...`);

        if (this.stripMode === 'double') {
            // ✅ DOUBLE MODE: Draw foto di kiri, lalu duplicate di kanan
            for (let i = 0; i < this.photos.length; i++) {
                try {
                    const photoImg = await this.loadImage(this.photos[i]);
                    
                    // Draw foto KIRI
                    const leftArea = config.photoAreas[i];
                    if (leftArea) {
                        this.drawImageCover(ctx, photoImg, leftArea.x, leftArea.y, leftArea.width, leftArea.height);
                        console.log(`Photo ${i + 1} (LEFT) drawn`);
                    }
                    
                    // Draw foto KANAN (duplicate)
                    const rightArea = config.photoAreas[this.photos.length + i];
                    if (rightArea) {
                        this.drawImageCover(ctx, photoImg, rightArea.x, rightArea.y, rightArea.width, rightArea.height);
                        console.log(`Photo ${i + 1} (RIGHT) drawn`);
                    }
                } catch (error) {
                    console.error(`Failed to draw photo ${i}:`, error);
                }
            }
        } else {
            // ✅ SINGLE MODE: Draw foto normal (existing code)
            for (let i = 0; i < this.photos.length; i++) {
                try {
                    const photoImg = await this.loadImage(this.photos[i]);
                    const area = config.photoAreas[i];
                    
                    if (!area) {
                        console.warn(`Photo area ${i} not found`);
                        continue;
                    }
                    
                    this.drawImageCover(ctx, photoImg, area.x, area.y, area.width, area.height);
                    console.log(`Photo ${i + 1} drawn`);
                } catch (error) {
                    console.error(`Failed to draw photo ${i}:`, error);
                }
            }
        }


        // ✅ Get frame path
        const framePath = this.getFramePath();
        console.log(`Loading frame: ${framePath}`);
        
        try {
            const frameImg = await this.loadImage(framePath);
            console.log('Frame loaded');
            
            ctx.drawImage(frameImg, 0, 0, tempCanvas.width, tempCanvas.height);
            console.log('Frame drawn');
            
        } catch (error) {
            console.error('Frame load error:', error);
            this.showFrameError(framePath, error);
            throw error;
        }

        console.log('Final canvas complete!');
        return tempCanvas;
    }

    drawImageCover(ctx, img, x, y, width, height) {
        const imgRatio = img.width / img.height;
        const canvasRatio = width / height;
        
        let drawWidth, drawHeight, offsetX, offsetY;
        
        if (imgRatio > canvasRatio) {
            drawHeight = height;
            drawWidth = img.width * (height / img.height);
            offsetX = (drawWidth - width) / 2;
            offsetY = 0;
        } else {
            drawWidth = width;
            drawHeight = img.height * (width / img.width);
            offsetX = 0;
            offsetY = (drawHeight - height) / 2;
        }
        
        ctx.save();
        ctx.beginPath();
        ctx.rect(x, y, width, height);
        ctx.clip();
        ctx.drawImage(img, x - offsetX, y - offsetY, drawWidth, drawHeight);
        ctx.restore();
    }

    showSaveButton() {
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn && window.isAuthenticated && this.currentStripId) {
            saveBtn.style.display = 'block';
            saveBtn.disabled = false;
        }
    }

    async saveStripToProfile() {
        if (!this.currentStripId) {
            alert('Strip ID tidak ditemukan!');
            return;
        }

        if (!window.isAuthenticated) {
            alert('Anda harus login terlebih dahulu!');
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;

        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Menyimpan...';

        try {
            const response = await fetch(`/photobooth/save/${this.currentStripId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });

            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.href = '/profile';
            } else {
                alert(result.message);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error saving strip:', error);
            alert('Terjadi kesalahan saat menyimpan.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    showReviewModal() {
        this.updateStripPreview();
        document.getElementById('reviewModal').style.display = 'flex';
    }

    closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

        async updateStripPreview() {
        const canvas = document.getElementById('stripCanvas');
        const ctx = canvas.getContext('2d');

        // ✅ Get config based on current strip mode
        const config = this.getFrameConfigs()[this.currentPhotoCount];
        
        if (!config) return;

        const scale = 0.35;
        canvas.width = config.frameSize.width * scale;
        canvas.height = config.frameSize.height * scale;

        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // ✅ Draw photos based on mode
        if (this.stripMode === 'double') {
            // DOUBLE MODE
            for (let i = 0; i < this.photos.length; i++) {
                const photoImg = await this.loadImage(this.photos[i]);
                
                // Foto KIRI
                const leftArea = config.photoAreas[i];
                if (leftArea) {
                    const scaledLeft = {
                        x: leftArea.x * scale,
                        y: leftArea.y * scale,
                        width: leftArea.width * scale,
                        height: leftArea.height * scale
                    };
                    this.drawImageCover(ctx, photoImg, scaledLeft.x, scaledLeft.y, scaledLeft.width, scaledLeft.height);
                }
                
                // Foto KANAN (duplicate)
                const rightArea = config.photoAreas[this.photos.length + i];
                if (rightArea) {
                    const scaledRight = {
                        x: rightArea.x * scale,
                        y: rightArea.y * scale,
                        width: rightArea.width * scale,
                        height: rightArea.height * scale
                    };
                    this.drawImageCover(ctx, photoImg, scaledRight.x, scaledRight.y, scaledRight.width, scaledRight.height);
                }
            }
        } else {
            // SINGLE MODE (existing)
            for (let i = 0; i < this.photos.length; i++) {
                const photoImg = await this.loadImage(this.photos[i]);
                const area = config.photoAreas[i];
                
                if (!area) continue;
                
                const scaledArea = {
                    x: area.x * scale,
                    y: area.y * scale,
                    width: area.width * scale,
                    height: area.height * scale
                };
                
                this.drawImageCover(ctx, photoImg, scaledArea.x, scaledArea.y, scaledArea.width, scaledArea.height);
            }
        }

        const framePath = this.getFramePath();
        
        try {
            const frameImg = await this.loadImage(framePath);
            ctx.drawImage(frameImg, 0, 0, canvas.width, canvas.height);
        } catch (error) {
            console.error('Error loading frame for preview:', error);
        }
    }


    async downloadPhotoStrip() {
        if (this.currentStripId) {
            window.location.href = `/photobooth/download/${this.currentStripId}`;
        } else {
            alert('Photo strip belum siap. Silakan tunggu...');
        }
    }

    /**
     * ✅ UPDATED: Load image with better error handling
     */
    loadImage(src) {
        return new Promise((resolve, reject) => {
            console.log(`Loading: ${src}`);
            
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            const timeout = setTimeout(() => {
                console.error(`Timeout: ${src}`);
                reject(new Error('Timeout: ' + src));
            }, 10000);
            
            img.onload = () => {
                clearTimeout(timeout);
                console.log(`Loaded: ${src}`);
                resolve(img);
            };
            
            img.onerror = (event) => {
                clearTimeout(timeout);
                console.error(`Failed: ${src}`);
                console.error('Error:', event);
                
                // ✅ Retry without cache buster
                if (src.includes('?v=')) {
                    const cleanSrc = src.split('?')[0];
                    console.log(`Retry: ${cleanSrc}`);
                    
                    const retryImg = new Image();
                    retryImg.crossOrigin = 'anonymous';
                    
                    retryImg.onload = () => {
                        console.log(`Retry success: ${cleanSrc}`);
                        resolve(retryImg);
                    };
                    
                    retryImg.onerror = () => {
                        console.error(`Retry failed: ${cleanSrc}`);
                        
                        // Last try without CORS
                        const finalImg = new Image();
                        finalImg.onload = () => {
                            console.log(`Loaded (no CORS): ${cleanSrc}`);
                            resolve(finalImg);
                        };
                        finalImg.onerror = () => {
                            console.error(`All attempts failed`);
                            reject(new Error('Failed: ' + src));
                        };
                        finalImg.src = cleanSrc;
                    };
                    
                    retryImg.src = cleanSrc;
                } else {
                    reject(new Error('Failed: ' + src));
                }
            };
            
            // ✅ Handle different URL types
            if (src.startsWith('http://') || src.startsWith('https://')) {
                img.src = src; // Full URL from backend
            } else if (src.includes('/storage/frames/')) {
                img.src = src + '?v=' + Date.now();
            } else {
                img.src = src;
            }
        });
    }

    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing PhotoBoothApp...');
    window.photoboothApp = new PhotoBoothApp();
    console.log('PhotoBoothApp initialized');
});

// Global helpers
function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function closeRetakeModal() {
    const modal = document.getElementById('retakeModal');
    if (modal) {
        modal.style.display = 'none';
    }
}
