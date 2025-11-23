<div class="modal new-user-modal fade" id="templateEditorModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="add-new-user modal-content pt-0 px-1">

            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

            <div class="modal-header mb-1 border-0 p-0">
                <h5 class="modal-title fs-4">Select option to add template</h5>
            </div>

            {{-- أهم تعديل هنا: action --}}
            <form id="templateEditorForm"
                  method="GET"
                  action="{{ route('product-templates.create') }}">


                <div class="modal-body flex-grow-1 d-flex flex-column gap-2">

                    {{-- With Editor --}}
                    <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                        <input
                            class="form-check-input me-2"
                            type="radio"
                            name="q"              {{-- اسم البراميتر في ال URL --}}
                            id="withEditor"
                            value="with"
                            required
                        />
                        <label class="form-check-label mb-0 flex-grow-1" for="withEditor">
                            With Editor
                        </label>
                    </div>

                    {{-- Without Editor --}}
                    <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                        <input
                            class="form-check-input me-2"
                            type="radio"
                            name="q"
                            id="withoutEditor"
                            value="without"
                        />
                        <label class="form-check-label mb-0 flex-grow-1" for="withoutEditor">
                            Without Editor
                        </label>
                    </div>

                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Next</button>
                </div>
            </form>

        </div>
    </div>
</div>
