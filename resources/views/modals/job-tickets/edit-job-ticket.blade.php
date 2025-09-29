<div class="modal modal-slide-in new-user-modal fade" id="editJobModal">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            <form id="editJobForm" method="POST" enctype="multipart/form-data" action="">
                @csrf
                @method("PUT")
                <input type="hidden" id="edit-job-id" name="id">

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title fs-3">Edit Job</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    <!-- NEW: Station / Status / Priority -->
                    <div class="row mb-1">
                        <div class="col-md-4">
                            <label class="form-label label-text">Station</label>
                            <select class="form-select" id="edit-station-id" name="station_id">
                                <!-- Prefer server-rendered options; fallback is JS (see below) -->
                                    @foreach($stations as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach

                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">Due Date</label>
                            <input type="date" name="" id="">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label label-text">Priority</label>
                            <select class="form-select" id="edit-priority" name="priority">
                                @foreach(\App\Enums\JobTicket\PriorityEnum::cases() as $p)
                                    <option value="{{ $p->value }}">{{ $p->label() }}</option>


                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary fs-5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5" id="editSaveChangesButton">
                        <span class="btn-text">Save Changes</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
