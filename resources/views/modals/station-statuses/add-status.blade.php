<div class="modal modal-slide-in new-user-modal fade" id="addStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="add-new-user modal-content pt-0">
            {{-- ✅ Update action to your real route --}}
            <form id="addStationStatusForm" method="POST" action="{{ route('station-statuses.store') }}">
                @csrf

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>

                <div class="modal-header mb-1">
                    <h5 class="modal-title">Add New Station Status</h5>
                </div>

                <div class="modal-body flex-grow-1">

                    {{-- Name (EN/AR) --}}

                        <div class="col-md-12">
                            <label class="form-label label-text">Name </label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Printing Started" required>
                        </div>


                    {{-- Station (required) --}}
                    <div class="mb-2">
                        <label class="form-label label-text">Station</label>
                        {{-- Static options version (uncomment if you pass $stations) --}}

                        <select class="form-select" name="station_id" required>
                          <option value="">— Select Station —</option>
                          @foreach ($associatedData['stations'] ?? [] as $station)
                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                          @endforeach
                        </select>

                    </div>

                    {{-- Parent Status (optional) --}}
                    <div class="mb-2">
                        <label class="form-label label-text">Parent Status (optional)</label>
                        {{-- Static options version (uncomment if you pass $statuses) --}}

                        <select class="form-select" name="parent_id">
                          <option value="">— None —</option>
                          @foreach ($associatedData['statuses'] ?? [] as $s)
                            <option value="{{ $s->id }}">{{ $s->getTranslation('name', app()->getLocale()) }}</option>
                          @endforeach
                        </select>

                    </div>

                    {{-- Job Ticket (optional) --}}
                    <div class="mb-2">
                        <label class="form-label label-text">Job Ticket (optional)</label>
                        {{-- Static options version (uncomment if you pass $jobTickets) --}}
                        <select class="form-select" name="job_ticket_id">
                          <option value="">— None —</option>
                          @foreach ($associatedData['job_tickets'] ?? [] as $jt)
                            <option value="{{ $jt->id }}">{{ $jt->code }}</option>
                          @endforeach
                        </select>

                    </div>

                </div>

                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fs-5 saveChangesButton" id="SaveChangesButton">
                        <span class="btn-text">Save</span>
                        <span id="saveLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
