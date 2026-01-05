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
                        Upload
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
            $form.on('submit', function (e) {
                e.preventDefault();
                const url = $form.attr('action');
                const fd = new FormData(this);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        Toastify({
                            text: "Process run on Background!",
                            duration: 1000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true,
                        }).showToast();
                        $("#importExcelModal").modal("hide");
                        location.reload()

                    },
                    error: function (xhr) {

                        // try to show validation/server error message
                        let msg = 'Import failed.';
                        try {
                            const json = xhr.responseJSON;
                            msg = json?.message || msg;

                            // Laravel validation errors
                            if (json?.errors) {
                                const firstKey = Object.keys(json.errors)[0];
                                if (firstKey && json.errors[firstKey]?.[0]) {
                                    msg = json.errors[firstKey][0];
                                }
                            }
                        } catch {}
                        Toastify({
                            text: `${msg}`,
                            duration: 1000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true,
                        }).showToast();

                    }
                });
            });



        })();
    </script>
@endpush

