@extends('layouts/contentLayoutMaster')

@section('title', 'Scan Job')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/animate/animate.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection
@section('content')
        <div class="container py-4" dir="ltr">
            <h2 class="mb-3">Scan Job Ticket (Camera)</h2>

            <div class="row g-3">
                <div class="col-md-6">
                    {{-- Scanner viewport --}}
                    <div id="reader" style="width: 100%; max-width: 520px;"></div>
                    <div class="d-flex gap-2 mt-2 align-items-center">
                        <select id="camera-select" class="form-select w-auto"></select>
                        <button id="start-btn" class="btn btn-primary">Start</button>
                        <button id="stop-btn" class="btn btn-secondary" disabled>Stop</button>
                        <button id="torch-btn" class="btn btn-outline-dark" disabled>Torch</button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Tip: Use HTTPS and allow camera permission. For rear camera on phones, choose “Back” camera.
                    </small>
                </div>

                <div class="col-md-6">
                    {{-- Status & last results --}}
                    <div id="status" class="alert d-none" role="alert"></div>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Last Scan</h5>
                            <div><strong>Code:</strong> <span id="last-code">—</span></div>
                            <div><strong>Message:</strong> <span id="last-msg">—</span></div>
                            <div><strong>Scans Used:</strong> <span id="last-count">—</span></div>
                            <div><strong>Station:</strong> <span id="last-station">—</span></div>
                        </div>
                    </div>
                    {{-- Optional audio cues --}}
                    <audio id="beep-ok"  src="/sounds/success.mp3" preload="auto"></audio>
                    <audio id="beep-ng"  src="/sounds/error.mp3"   preload="auto"></audio>
                </div>
            </div>
        </div>


@endsection


@section('vendor-script')
    {{-- Vendor js files --}}
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
    {{-- data table --}}
    <script src="{{ asset(mix('vendors/js/extensions/moment.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/jszip.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/pdfmake.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/vfs_fonts.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.print.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.rowGroup.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/sweetalert2.all.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/extensions/polyfill.min.js')) }}"></script>
@endsection

@section('page-script')
    {{-- html5-qrcode library (CDN) --}}
    <script src="https://unpkg.com/html5-qrcode" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const token   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const status  = document.getElementById('status');
            const lastCodeEl = document.getElementById('last-code');
            const lastMsgEl  = document.getElementById('last-msg');
            const lastCntEl  = document.getElementById('last-count');
            const lastStnEl  = document.getElementById('last-station');

            const startBtn = document.getElementById('start-btn');
            const stopBtn  = document.getElementById('stop-btn');
            const torchBtn = document.getElementById('torch-btn');
            const camSel   = document.getElementById('camera-select');

            let html5QrCode = null;
            let currentCameraId = null;
            let scanning = false;
            let canToggleTorch = false;
            let torchOn = false;

            // Avoid duplicate posts for the same code within N ms
            let lastSent = '';
            let lastSentAt = 0;
            const dedupeWindowMs = 1500;

            function showAlert(msg, type) {
                status.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
                status.classList.add('alert-' + type);
                status.textContent = msg;
            }

            async function postCode(code) {
                // dedupe
                const now = Date.now();
                if (code === lastSent && (now - lastSentAt) < dedupeWindowMs) return;
                lastSent = code; lastSentAt = now;

                try {
                    const res = await fetch("", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Scan failed');

                    // success UI
                    try { document.getElementById('beep-ok').play(); } catch(e){}
                    showAlert(data.message || 'OK', 'success');
                    lastCodeEl.textContent = data.code ?? code;
                    lastMsgEl.textContent  = data.message ?? 'OK';
                    lastCntEl.textContent  = data.scan_count ?? '—';
                    lastStnEl.textContent  = (data.from_station ?? '—') + ' ➜ ' + (data.to_station ?? '—');
                } catch (err) {
                    try { document.getElementById('beep-ng').play(); } catch(e){}
                    showAlert(err.message || 'Error', 'danger');
                    lastMsgEl.textContent = err.message || 'Error';
                    lastCodeEl.textContent = code;
                }
            }

            function onScanSuccess(decodedText, decodedResult) {
                // Some scanners / formats may include whitespace or \n
                const code = (decodedText || '').trim();
                if (!code) return;
                postCode(code);
            }

            function onScanFailure(error) {
                // called frequently; keep silent to avoid console spam
                // console.debug(error);
            }

            async function listCameras() {
                try {
                    const devices = await Html5Qrcode.getCameras();
                    camSel.innerHTML = '';
                    devices.forEach((d, i) => {
                        const opt = document.createElement('option');
                        opt.value = d.id;
                        opt.textContent = d.label || `Camera ${i+1}`;
                        camSel.appendChild(opt);
                    });
                    // Prefer back camera if found
                    const back = devices.find(d => /back|rear|environment/i.test(d.label));
                    camSel.value = back ? back.id : (devices[0]?.id || '');
                    currentCameraId = camSel.value || null;
                    startBtn.disabled = !currentCameraId;
                } catch (e) {
                    showAlert('No cameras found or permission denied. Use HTTPS and allow camera.', 'warning');
                }
            }

            async function startScanner() {
                if (scanning || !currentCameraId) return;
                if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");

                // Build config
                const config = {
                    fps: 15,
                    qrbox: (viewfinderWidth, viewfinderHeight) => {
                        const size = Math.min(viewfinderWidth, viewfinderHeight) * 0.7;
                        return { width: size, height: size };
                    },
                    rememberLastUsedCamera: true,
                    // Try to read many 1D formats:
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.ITF
                    ]
                };

                try {
                    await html5QrCode.start(
                        { deviceId: { exact: currentCameraId } },
                        config,
                        onScanSuccess,
                        onScanFailure
                    );
                    scanning = true;
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
                    // Torch availability (not guaranteed)
                    canToggleTorch = await html5QrCode.applyVideoConstraints({ advanced: [{ torch: false }] })
                        .then(()=>true).catch(()=>false);
                    torchBtn.disabled = !canToggleTorch;
                    showAlert('Camera started. Aim at the barcode.', 'info');
                } catch (e) {
                    showAlert('Failed to start camera: ' + (e?.message || e), 'danger');
                }
            }

            async function stopScanner() {
                if (!scanning || !html5QrCode) return;
                try {
                    await html5QrCode.stop();
                    await html5QrCode.clear();
                    scanning = false;
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                    torchBtn.disabled = true;
                    showAlert('Camera stopped.', 'warning');
                } catch (e) {
                    showAlert('Failed to stop camera.', 'danger');
                }
            }

            async function toggleTorch() {
                if (!canToggleTorch || !html5QrCode) return;
                torchOn = !torchOn;
                try {
                    await html5QrCode.applyVideoConstraints({ advanced: [{ torch: torchOn }] });
                    torchBtn.classList.toggle('btn-dark', torchOn);
                    torchBtn.classList.toggle('btn-outline-dark', !torchOn);
                    torchBtn.textContent = torchOn ? 'Torch On' : 'Torch';
                } catch (e) {
                    showAlert('Torch not supported on this device.', 'warning');
                }
            }

            camSel.addEventListener('change', async (e) => {
                currentCameraId = camSel.value;
                if (scanning) { // restart with the new camera
                    await stopScanner();
                    await startScanner();
                }
            });

            startBtn.addEventListener('click', startScanner);
            stopBtn.addEventListener('click', stopScanner);
            torchBtn.addEventListener('click', toggleTorch);

            listCameras();
        });
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
@endsection
