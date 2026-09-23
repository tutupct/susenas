<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Validasi Susenas') }}</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">

            <a class="navbar-brand fw-bold" href="{{ url('/') }}">
                Validasi Susenas
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">

                    <li class="nav-item">
                        <a href="{{ route('petugas.index') }}"
                            class="nav-link {{ request()->routeIs('petugas.*') ? 'active' : '' }}">
                            Petugas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('reports.index') }}"
                            class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            Report
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-sm btn-outline-light">
                                Logout
                            </button>
                        </form>
                    </li>

                </ul>
            </div>

        </div>
    </nav>

    <main class="container py-4">
        <x-alert />
        @yield('content')
    </main>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Alpine Sort Plugin --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/sort@3.17.3/dist/cdn.min.js"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.17.3/dist/cdn.min.js"></script>

    {{-- Photo capture & annotation --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const photoInputs = document.querySelectorAll('input[type="file"][name="foto"]');

            if (!photoInputs.length) {
                return;
            }

            const modal = document.createElement('div');
            modal.className = 'position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none align-items-center justify-content-center p-3';
            modal.style.zIndex = '2000';
            modal.innerHTML = `
                <div class="bg-white rounded-3 shadow w-100" style="max-width:900px;max-height:95vh;overflow-y:auto;">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <strong>Edit Foto</strong>
                        <button type="button" class="btn-close" data-photo-editor-cancel aria-label="Tutup"></button>
                    </div>
                    <div class="p-3">
                        <div class="bg-dark rounded overflow-hidden">
                            <canvas data-photo-editor-canvas class="w-100 d-block" style="touch-action:none;max-height:65vh;object-fit:contain;"></canvas>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                            <label class="d-flex align-items-center gap-2 mb-0">
                                <span class="small">Warna</span>
                                <input type="color" value="#ff0000" data-photo-editor-color class="form-control form-control-color" title="Warna coretan">
                            </label>

                            <label class="d-flex align-items-center gap-2 mb-0 flex-grow-1">
                                <span class="small">Ukuran</span>
                                <input type="range" min="2" max="20" step="1" value="6" data-photo-editor-width class="form-range">
                            </label>

                            <button type="button" class="btn btn-sm btn-outline-secondary" data-photo-editor-undo>Undo</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-photo-editor-clear>Hapus Coretan</button>
                        </div>

                        <div class="form-text mt-2">
                            Coret langsung di atas foto menggunakan jari, mouse, atau stylus.
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn btn-light" data-photo-editor-cancel>Batal</button>
                            <button type="button" class="btn btn-primary" data-photo-editor-apply>Gunakan Foto</button>
                        </div>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            const canvas = modal.querySelector('[data-photo-editor-canvas]');
            const ctx = canvas.getContext('2d');
            const colorInput = modal.querySelector('[data-photo-editor-color]');
            const widthInput = modal.querySelector('[data-photo-editor-width]');
            const undoButton = modal.querySelector('[data-photo-editor-undo]');
            const clearButton = modal.querySelector('[data-photo-editor-clear]');
            const applyButton = modal.querySelector('[data-photo-editor-apply]');

            let activeInput = null;
            let image = null;
            let strokes = [];
            let currentStroke = null;
            let drawing = false;

            const showModal = () => {
                modal.classList.remove('d-none');
                modal.classList.add('d-flex');
            };

            const hideModal = () => {
                modal.classList.add('d-none');
                modal.classList.remove('d-flex');
                drawing = false;
                currentStroke = null;
            };

            const updateButtons = () => {
                undoButton.disabled = strokes.length === 0;
                clearButton.disabled = strokes.length === 0;
            };

            const render = () => {
                if (!image) {
                    return;
                }

                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(image, 0, 0, canvas.width, canvas.height);

                strokes.forEach(stroke => {
                    drawStroke(stroke);
                });

                updateButtons();
            };

            const drawStroke = stroke => {
                if (!stroke.points.length) {
                    return;
                }

                ctx.save();
                ctx.strokeStyle = stroke.color;
                ctx.fillStyle = stroke.color;
                ctx.lineWidth = stroke.width;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';

                if (stroke.points.length === 1) {
                    const point = stroke.points[0];
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, stroke.width / 2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                    return;
                }

                ctx.beginPath();
                ctx.moveTo(stroke.points[0].x, stroke.points[0].y);

                for (let i = 1; i < stroke.points.length; i++) {
                    ctx.lineTo(stroke.points[i].x, stroke.points[i].y);
                }

                ctx.stroke();
                ctx.restore();
            };

            const getPoint = event => {
                const rect = canvas.getBoundingClientRect();

                return {
                    x: (event.clientX - rect.left) * (canvas.width / rect.width),
                    y: (event.clientY - rect.top) * (canvas.height / rect.height),
                };
            };

            const openEditor = (input, file) => {
                activeInput = input;

                const url = URL.createObjectURL(file);
                const nextImage = new Image();

                nextImage.onload = () => {
                    URL.revokeObjectURL(url);

                    image = nextImage;
                    strokes = [];
                    currentStroke = null;
                    drawing = false;

                    const maxDimension = 1920;
                    const scale = Math.min(
                        1,
                        maxDimension / Math.max(image.naturalWidth, image.naturalHeight)
                    );

                    canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
                    canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));

                    render();
                    showModal();
                };

                nextImage.onerror = () => {
                    URL.revokeObjectURL(url);
                    alert('Foto tidak dapat dibaca.');
                };

                nextImage.src = url;
            };

            const setFile = file => {
                if (!activeInput || !file) {
                    return;
                }

                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                activeInput.files = dataTransfer.files;

                activeInput.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const validateAndOpen = (input, file) => {
                if (!file) {
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if (!allowedTypes.includes(file.type)) {
                    input.value = '';
                    alert('Format foto harus JPG, JPEG, atau WEBP.');
                    return;
                }

                if (file.size > 5 * 1024 * 1024) {
                    input.value = '';
                    alert('Ukuran foto maksimal 5 MB.');
                    return;
                }

                openEditor(input, file);
            };

            photoInputs.forEach(input => {
                const wrapper = document.createElement('div');
                wrapper.className = 'd-flex flex-wrap gap-2 align-items-center';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);

                input.classList.add('d-none');

                const fileButton = document.createElement('button');
                fileButton.type = 'button';
                fileButton.className = 'btn btn-outline-secondary';
                fileButton.textContent = 'Pilih File';
                fileButton.addEventListener('click', () => input.click());

                const cameraInput = document.createElement('input');
                cameraInput.type = 'file';
                cameraInput.accept = 'image/*';
                cameraInput.capture = 'environment';
                cameraInput.className = 'd-none';

                const cameraButton = document.createElement('button');
                cameraButton.type = 'button';
                cameraButton.className = 'btn btn-outline-primary';
                cameraButton.textContent = 'Ambil Foto';
                cameraButton.addEventListener('click', () => cameraInput.click());

                cameraInput.addEventListener('change', event => {
                    validateAndOpen(input, event.target.files?.[0]);
                    cameraInput.value = '';
                });

                wrapper.insertBefore(fileButton, input);
                wrapper.insertBefore(cameraButton, input);
                wrapper.appendChild(cameraInput);

                input.addEventListener('change', event => {
                    validateAndOpen(input, event.target.files?.[0]);
                });
            });

            canvas.addEventListener('pointerdown', event => {
                if (!image) {
                    return;
                }

                canvas.setPointerCapture?.(event.pointerId);
                const rect = canvas.getBoundingClientRect();

                drawing = true;
                currentStroke = {
                    color: colorInput.value,
                    width: Number(widthInput.value) * (canvas.width / rect.width),
                    points: [getPoint(event)],
                };

                drawStroke(currentStroke);
            });

            canvas.addEventListener('pointermove', event => {
                if (!drawing || !currentStroke) {
                    return;
                }

                const point = getPoint(event);
                const points = currentStroke.points;
                const previous = points[points.length - 1];

                points.push(point);

                ctx.save();
                ctx.strokeStyle = currentStroke.color;
                ctx.lineWidth = currentStroke.width;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.beginPath();
                ctx.moveTo(previous.x, previous.y);
                ctx.lineTo(point.x, point.y);
                ctx.stroke();
                ctx.restore();
            });

            const stopDrawing = event => {
                if (!drawing) {
                    return;
                }

                if (event?.pointerId !== undefined) {
                    canvas.releasePointerCapture?.(event.pointerId);
                }

                drawing = false;

                if (currentStroke) {
                    strokes.push(currentStroke);
                }

                currentStroke = null;
                updateButtons();
            };

            canvas.addEventListener('pointerup', stopDrawing);
            canvas.addEventListener('pointercancel', stopDrawing);
            canvas.addEventListener('pointerleave', stopDrawing);

            undoButton.addEventListener('click', () => {
                strokes.pop();
                render();
            });

            clearButton.addEventListener('click', () => {
                strokes = [];
                render();
            });

            modal.querySelectorAll('[data-photo-editor-cancel]').forEach(button => {
                button.addEventListener('click', hideModal);
            });

            applyButton.addEventListener('click', () => {
                if (!activeInput || !canvas.width || !canvas.height) {
                    return;
                }

                canvas.toBlob(blob => {
                    if (!blob) {
                        alert('Gagal memproses foto.');
                        return;
                    }

                    const file = new File(
                        [blob],
                        `temuan-${Date.now()}.jpg`,
                        { type: 'image/jpeg' }
                    );

                    setFile(file);
                    hideModal();
                }, 'image/jpeg', 0.95);
            });
        });
    </script>

    @stack('scripts')

</body>

</html>
