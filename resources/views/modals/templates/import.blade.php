<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Import Templates (Excel)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
            </div>

            <!-- ✅ ID added -->
            <form id="importExcelForm" action="{{ route('product-templates.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Excel file</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted d-block mt-1">Allowed: .xlsx, .xls, .csv</small>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Images Zip File</label>
                        <input type="file" name="images" class="form-control" accept=".zip" required>
                        <small class="text-muted d-block mt-1">Allowed: .zip</small>
                    </div>

                    <!-- ✅ Loading -->
                    <div id="importExcelLoading" class="d-none mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="text-muted">Uploading & importing…</span>
                        </div>
                    </div>

                    <!-- ✅ Result -->
                    <div id="importExcelResult" class="d-none mt-2">
                        <div class="alert alert-success mb-1">
{{--                            <div><b>Batch:</b> <span id="importBatch"></span></div>--}}
                            <div><b>Created:</b> <span id="importCreated"></span></div>
                            <div><b>Skipped:</b> <span id="importSkippedCount"></span></div>
                        </div>

                        <div id="importSkippedBox" class="d-none">
                            <div class="alert alert-warning">
                                <b>Skipped rows:</b>
                                <ul id="importSkippedList" class="mb-0"></ul>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm" id="copyImportReportBtn">
                            Copy Report
                        </button>

                        <textarea id="importReportText" class="d-none"></textarea>
                    </div>

                    <!-- ✅ Error -->
                    <div id="importExcelError" class="d-none mt-2">
                        <div class="alert alert-danger mb-0" id="importExcelErrorMsg"></div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>

                    <!-- ✅ ID added -->
                    <button type="submit" class="btn btn-primary" id="importExcelSubmitBtn">
                        <i data-feather="check"></i>
                        <span class="btn-text">Upload</span>
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>

@push("scripts")
    <script>
        (function () {
            const $form = $('#importExcelForm');
            const $btn  = $('#importExcelSubmitBtn');
            const originalHtml = $btn.html();

            function setLoading(loading) {
                if (loading) {
                    $btn.prop('disabled', true).addClass('disabled');
                    $btn.html(`
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Uploading...
        `);
                } else {
                    $btn.prop('disabled', false).removeClass('disabled');
                    $btn.html(originalHtml);
                }
            }

            $form.on('submit', function (e) {
                e.preventDefault();

                const url = $form.attr('action');
                const fd  = new FormData(this);

                setLoading(true);

                $.ajax({
                    url,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,

                    success: function (res) {
                        setLoading(false);

                        Toastify({
                            text: "Process run on Background!",
                            duration: 2000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true,
                        }).showToast();

                        // Bootstrap 5 (لو عندك BS5)
                        const modalEl = document.getElementById('importExcelModal');
                        const instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        instance.hide();

                        // اختياري: reload بعد ما المودال يقفل
                        setTimeout(() => location.reload(), 300);
                    },

                    error: function (xhr) {
                        setLoading(false);

                        let msg = 'Import failed.';
                        try {
                            const json = xhr.responseJSON;
                            msg = json?.message || msg;

                            if (json?.errors) {
                                const firstKey = Object.keys(json.errors)[0];
                                if (firstKey && json.errors[firstKey]?.[0]) {
                                    msg = json.errors[firstKey][0];
                                }
                            }
                        } catch {}

                        Toastify({
                            text: msg,
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545", // ✅ احمر للخطأ
                            close: true,
                        }).showToast();
                    }
                });
            });
        })();
    </script>

@endpush

