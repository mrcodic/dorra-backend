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
<div class="py-2" dir="ltr">
    <h2 class="mb-3">Scan Job Ticket</h2>

    {{-- Mode Switcher --}}
    <div class="btn-group mb-3" role="group" aria-label="Scanner Mode">
        <button id="btn-mode-hw" type="button" class="btn btn-outline-primary active">
            <i data-feather="type"></i> Hardware Scanner
        </button>
        <button id="btn-mode-cam" type="button" class="btn btn-outline-primary">
            <i data-feather="camera"></i> Camera
        </button>
    </div>

    {{-- Status alert --}}
    <div id="status" class="alert d-none" role="alert"></div>

    <div class="row g-3">
        {{-- Hardware scanner section --}}
        <div class="col-12" id="section-hw">
            <form id="scan" action="{{ route('scan.submit') }}" method="post" class="p-3 border rounded-3">
                @csrf
                <label for="code" class="form-label mb-1">Scan / Enter Job Code</label>
                <div class="input-group">
                    <input type="text" id="code" name="code" class="form-control" placeholder="JT-YYYYMMDD-..."
                        autofocus>
                    <button class="btn btn-primary" type="submit">Submit</button>
                </div>
                <small class="text-muted d-block mt-1">Tip: connect a USB/BT barcode scanner—most act like a keyboard
                    and press Enter automatically.</small>
            </form>
        </div>

        {{-- Camera scanner section --}}
        <div class="col-12 d-none" id="section-cam">
            <div class="d-flex gap-2 flex-wrap align-items-center mb-2">
                <select id="camera-select" class="form-select" style="max-width: 280px;"></select>
                <button id="start-btn" class="btn btn-success" type="button" disabled>Start</button>
                <button id="stop-btn" class="btn btn-outline-secondary" type="button" disabled>Stop</button>
                <button id="torch-btn" class="btn btn-outline-dark" type="button" disabled>Torch</button>
            </div>
            <div id="reader" style="width: 100%; max-width: 520px;"></div>
        </div>

        {{-- Last result (optional) --}}
        <div class="col-12">
            <div class="border rounded-3 p-2">
                <div><strong>Last Code:</strong> <span id="last-code">—</span></div>
                <div><strong>Station:</strong> <span id="last-station">—</span></div>
                <div><strong>Status:</strong> <span id="last-status">—</span></div>
            </div>
        </div>
    </div>

    {{-- Beep sounds (optional) --}}
    <audio id="beep-ok" src="{{ asset('sounds/beep-ok.mp3')  }}" preload="auto"></audio>
    <audio id="beep-ng" src="{{ asset('sounds/beep-ng.mp3')  }}" preload="auto"></audio>
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
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
    handleAjaxFormSubmit("#scan",{
            successMessage: "Scan Submitted Successfully"
        })
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();

            // -------- Mode elements --------
            const btnModeHw   = document.getElementById('btn-mode-hw');
            const btnModeCam  = document.getElementById('btn-mode-cam');
            const secHw       = document.getElementById('section-hw');
            const secCam      = document.getElementById('section-cam');
            const inputCode   = document.getElementById('code');

            // -------- Status / result UI --------
            const status   = document.getElementById('status');
            const lastCodeEl = document.getElementById('last-code');
            const lastStnEl  = document.getElementById('last-station');
            const lastStatusEl  = document.getElementById('last-status');
            const token   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // -------- Camera controls --------
            const startBtn = document.getElementById('start-btn');
            const stopBtn  = document.getElementById('stop-btn');
            const torchBtn = document.getElementById('torch-btn');
            const camSel   = document.getElementById('camera-select');

            let html5QrCode = null;
            let currentCameraId = null;
            let scanning = false;
            let canToggleTorch = false;
            let torchOn = false;

            // Dedupe scans (both modes)
            let lastSent = '';
            let lastSentAt = 0;
            const dedupeWindowMs = 1200;

            function showAlert(msg, type) {
                status.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
                status.classList.add('alert-' + type);
                status.textContent = msg;
            }
            function toastOk(msg) {
                Toastify({
                    text: msg || "Done",
                    duration: 2500,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#28C76F",
                    close: true,
                    stopOnFocus: true
                }).showToast();
            }

            function toastErr(msg) {
                Toastify({
                    text: msg || "Something went wrong",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#EA5455",
                    close: true,
                    stopOnFocus: true
                }).showToast();
            }

            async function postCode(code) {
                const now = Date.now();
                if (code === lastSent && (now - lastSentAt) < dedupeWindowMs) return;
                lastSent = code; lastSentAt = now;

                try {
                    const res = await fetch("{{ route('scan.submit') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ code })
                    });

                    const data = await res.json();
                    if (!res.ok) {
                        // Prefer API message or first validation error
                        const apiMsg =
                            data?.message ||
                            (data?.errors && Object.values(data.errors)[0]?.[0]) ||
                            'Scan failed';
                        throw new Error(apiMsg);
                    }

                    // success UI

                    try { document.getElementById('beep-ok').play(); } catch(e){}
                    showAlert(data.message || 'OK', 'success');
                    toastOk(data.message || 'Scan accepted');

                    lastCodeEl.textContent  = data.code ?? code;
                    lastStnEl.textContent   = data.to_station ?? '—';
                    lastStatusEl.textContent= data.to_status  ?? '—';

                } catch (err) {
                    try { document.getElementById('beep-ng').play(); } catch(e){}
                    showAlert(err.message || 'Error', 'danger');
                    toastErr(err.message || 'Scan rejected');

                    lastMsgEl.textContent = err.message || 'Error';
                    lastCodeEl.textContent = code;
                }
            }

            // ---------- Hardware scanner mode (keyboard wedge) ----------
            // If your scanner sends Enter, the form submits normally.
            // If you want Ajax instead of form submit, uncomment below:
            // document.querySelector('#section-hw form').addEventListener('submit', async (e) => {
            //   e.preventDefault();
            //   const code = inputCode.value.trim();
            //   if (!code) return;
            //   await postCode(code);
            //   inputCode.value = '';
            //   inputCode.focus();
            // });

            // ---------- Camera mode (html5-qrcode) ----------
            async function onScanSuccess(decodedText) {
                console.log("Scanned:", decodedText);
                const code = (decodedText || '').trim();
                if (!code) return;
                inputCode.value = code;
                await postCode(code);
                await stopScanner();
            }

            function onScanFailure(_) { /* ignore noisy callbacks */ }

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
                const config = {
                    fps: 15,
                    qrbox: (vw, vh) => {
                        const size = Math.min(vw, vh) * 0.7;
                        return { width: size, height: size };
                    },
                    rememberLastUsedCamera: true,
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
                    await html5QrCode.start({ deviceId: { exact: currentCameraId } }, config, onScanSuccess, onScanFailure);
                    scanning = true;
                    startBtn.disabled = true;
                    stopBtn.disabled = false;
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
                try {
                    torchOn = !torchOn;
                    await html5QrCode.applyVideoConstraints({ advanced: [{ torch: torchOn }] });
                    torchBtn.classList.toggle('btn-dark', torchOn);
                    torchBtn.classList.toggle('btn-outline-dark', !torchOn);
                    torchBtn.textContent = torchOn ? 'Torch On' : 'Torch';
                } catch (_) {
                    showAlert('Torch not supported on this device.', 'warning');
                }
            }

            camSel.addEventListener('change', async () => {
                currentCameraId = camSel.value;
                if (scanning) { await stopScanner(); await startScanner(); }
            });
            startBtn.addEventListener('click', startScanner);
            stopBtn.addEventListener('click', stopScanner);
            torchBtn.addEventListener('click', toggleTorch);

            // ---------- Mode switch behavior ----------
            async function switchToHw() {
                btnModeHw.classList.add('active');
                btnModeCam.classList.remove('active');
                secHw.classList.remove('d-none');
                secCam.classList.add('d-none');
                // stop camera if running
                await stopScanner();
                // focus the input
                setTimeout(() => inputCode?.focus(), 50);
                showAlert('Hardware scanner mode. Use your USB/BT scanner or type the code.', 'info');
            }

            async function switchToCam() {
                btnModeCam.classList.add('active');
                btnModeHw.classList.remove('active');
                secCam.classList.remove('d-none');
                secHw.classList.add('d-none');
                await listCameras();
                showAlert('Camera mode selected. Pick a camera then press Start.', 'info');
            }

            btnModeHw.addEventListener('click', switchToHw);
            btnModeCam.addEventListener('click', switchToCam);

            // default: HW mode
            switchToHw();
        });
</script>
@endsection
