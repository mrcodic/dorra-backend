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
            const $submitBtn = $('#importExcelSubmitBtn');

            const $loading = $('#importExcelLoading');
            const $result  = $('#importExcelResult');
            const $errorBox = $('#importExcelError');
            const $errorMsg = $('#importExcelErrorMsg');

            const $batch = $('#importBatch');
            const $created = $('#importCreated');
            const $skippedCount = $('#importSkippedCount');

            const $skippedBox = $('#importSkippedBox');
            const $skippedList = $('#importSkippedList');

            const $copyBtn = $('#copyImportReportBtn');
            const $copyText = $('#importReportText');

            function resetUI() {
                $loading.addClass('d-none');
                $result.addClass('d-none');
                $errorBox.addClass('d-none');
                $errorMsg.text('');

                $skippedBox.addClass('d-none');
                $skippedList.empty();

                $batch.text('');
                $created.text('');
                $skippedCount.text('');

                $copyText.val('');
            }

            function setLoading(isLoading) {
                if (isLoading) {
                    $loading.removeClass('d-none');
                    $submitBtn.prop('disabled', true).addClass('disabled');
                } else {
                    $loading.addClass('d-none');
                    $submitBtn.prop('disabled', false).removeClass('disabled');
                }
            }

            $form.on('submit', function (e) {
                e.preventDefault();
                resetUI();
                setLoading(true);

                const url = $form.attr('action');
                const fd = new FormData(this);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        setLoading(false);

                        // expected:
                        // { status:200, success:true, message:"...", data:{batch,created,skipped_count,skipped:[]} }
                        const data = res?.data || {};

                        // $batch.text(data.batch ?? '-');
                        $created.text(data.created ?? 0);
                        $skippedCount.text(data.skipped_count ?? 0);

                        // render skipped
                        const skipped = Array.isArray(data.skipped) ? data.skipped : [];
                        if (skipped.length) {
                            $skippedBox.removeClass('d-none');
                            skipped.forEach(item => {
                                $skippedList.append(`<li>${String(item)}</li>`);
                            });
                        }

                        // build copy report
                        const report = [
                            `Import Templates Report`,
                            // `Batch: ${data.batch ?? '-'}`,
                            `Created: ${data.created ?? 0}`,
                            `Skipped: ${data.skipped_count ?? 0}`,
                            skipped.length ? `Skipped list:\n- ${skipped.join('\n- ')}` : `Skipped list: (none)`
                        ].join('\n');

                        $copyText.val(report);

                        $result.removeClass('d-none');
                    },
                    error: function (xhr) {
                        setLoading(false);

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

                        $errorMsg.text(msg);
                        $errorBox.removeClass('d-none');
                    }
                });
            });

            // copy
            $copyBtn.on('click', async function () {
                const text = $copyText.val() || '';
                if (!text) return;

                try {
                    await navigator.clipboard.writeText(text);
                    // optional tiny feedback
                    $copyBtn.text('Copied!');
                    setTimeout(() => $copyBtn.text('Copy Report'), 1200);
                } catch (e) {
                    // fallback
                    $copyText.removeClass('d-none').focus().select();
                    document.execCommand('copy');
                    $copyText.addClass('d-none');
                }
            });

            // reset when modal opens
            $('#importExcelModal').on('shown.bs.modal', function () {
                resetUI();
            });

            // reset files when modal closed (optional)
            $('#importExcelModal').on('hidden.bs.modal', function () {
                resetUI();
                $form[0].reset();
            });
        })();
    </script>
@endpush

