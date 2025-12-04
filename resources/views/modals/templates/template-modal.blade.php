{{-- Templates Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Remaining Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="templates-modal-container" class="row g-2">
                    {{-- AJAX templates will be injected here --}}
                </div>

                <div id="templates-modal-pagination" class="mt-3 text-center">
                    {{-- Load more / pagination will be injected here --}}
                </div>
            </div>
        </div>
    </div>
</div>
