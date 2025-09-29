{{-- resources/views/admin/job_tickets/_modal.blade.php --}}
<div class="modal-header mb-1">
    <h5 class="modal-title">
        <span class="badge bg-dark me-2">{{ $job->code }}</span>
        @if($job->orderItem?->order)
            <a href="{{ route('orders.show', $job->orderItem->order_id) }}" target="_blank">
                Order #{{ $job->orderItem->order->number ?? $job->orderItem->order_id }}
            </a>
        @endif
        @if($job->orderItem)
            <span class="text-muted ms-2">— {{ $job->orderItem->name ?? "Item #{$job->order_item_id}" }}</span>
        @endif
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
</div>

<div class="modal-body">
    {{-- Top row: QR + Code128 + Current State --}}
    <div class="row g-3 align-items-center mb-2">
        <div class="col-md-4 text-center">
            <img src="{{ route('jobtickets.qr', $job) }}" alt="QR" class="img-fluid border rounded p-2">
            <div class="small text-muted mt-1">QR</div>
        </div>
        <div class="col-md-4 text-center">
            <img src="{{ route('jobtickets.code128', $job) }}" alt="Code128" class="img-fluid border rounded p-2">
            <div class="small text-muted mt-1">Code128</div>
        </div>
        <div class="col-md-4">
            <div class="d-flex flex-column gap-2">
                <div>
                    <span class="text-muted me-1">Station:</span>
                    <span class="fw-bold">{{ $job->station?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-muted me-1">Status:</span>
                    <span class="badge bg-primary">{{ $job->status->label() }}</span>
                </div>
                <div>
                    <span class="text-muted me-1">Priority:</span>
                    <span class="badge {{ $job->priority === 2 ? 'bg-danger' : 'bg-secondary' }}">
            {{ \App\Enums\JobTicket\PriorityEnum::from($job->priority)->label() }}
          </span>
                </div>
                @if($job->due_at)
                    <div>
                        <span class="text-muted me-1">Due:</span>
                        <span class="fw-semibold">{{ $job->due_at->format('Y-m-d H:i') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Specs table --}}
    @php $specs = is_array($job->specs) ? $job->specs : (json_decode($job->specs ?? '[]', true) ?? []); @endphp
    @if(!empty($specs))
        <h6 class="mt-2">Specifications</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <tbody>
                @foreach($specs as $k => $v)
                    <tr>
                        <th class="w-25">{{ Str::headline($k) }}</th>
                        <td>
                            @if(is_array($v) || is_object($v))
                                <code class="small">{{ json_encode($v, JSON_UNESCAPED_UNICODE) }}</code>
                            @else
                                {{ $v }}
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Timeline --}}
    <h6 class="mt-3">Timeline</h6>
