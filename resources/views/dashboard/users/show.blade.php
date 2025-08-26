@extends('layouts/contentLayoutMaster')
@section('title', 'User View - Account')
@section('main-page', 'Users')
@section('sub-page', 'Show User')
@section('main-page-url', route("users.index"))
@section('sub-page-url', route("users.show",$model->id))
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
<section class="app-user-view-account">
    <div class="row">
        <!-- User Sidebar -->
        <div class="col-xl-4 col-lg-5 col-md-5 order-1 order-md-0">
            <!-- User Card -->
            <div class="card">
                <div class="card-body">
                    <div class="user-avatar-section">
                        <span class=" rounded-3 status-label
                                 {{ $model->status == " Active" ? "primary-text-color" : "" }} text-center d-flex
                            justify-content-center align-items-center" style="background-color: {{ $model->status == "
                            Active" ?"#D7EEDD" : "#F0F0F0" }};">
                            {{ $model->status == "Active" ? "Active" : "Blocked" }}</span>
                        <div class="d-flex align-items-center flex-column">
                            <img class="img-fluid rounded-circle mt-3 mb-2"
                                src="{{$model->image?->getUrl() ?? asset('images/avatar.png')}}" height="110"
                                width="110" alt="User avatar" />
                            <div class="user-info text-center">
                                <h4>{{ $model->name }}</h4>
                                <span class=" text-secondary">userId : {{ $model->id }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 my-1">
                        <div class="d-flex align-items-center gap-1">

                            <i data-feather="calendar" class="font-medium-2"></i>
                            <h4 class="mb-0">Joined {{ $model->created_at->format('j M Y') }}</h4>
                        </div>
                        <div class="d-flex align-items-center gap-1">

                        </div>
                    </div>
                    <div class="w-100 d-flex flex-column gap-1">
                        <div class="fw-semibold disabled-field w-100 p-1 text-center">Last
                            Login:{{ $model->last_login_at?->format('j M Y') }}</div>
                        <div class="w-100 rounded-3 border p-1 d-flex justify-content-center align-items-center gap-1">
                            <i data-feather="at-sign" class="font-medium-2"></i><span>{{ $model->email }}</span>
                        </div>
                        <div class="w-100 rounded-3 border p-1 d-flex justify-content-center align-items-center gap-1">
                            <i data-feather="smartphone" class="font-medium-2"></i><span>{{
                                $model->countryCode?->phone_code }} {{ $model->phone_number }}</span>
                        </div>
                    </div>

                    <div class="info-container">
                        <h2 class="text-black my-1">Teams</h2>
                        <div class="card border rounded p-1 my-2">
                            <div class="d-flex justify-content-between align-items-start">

                                @forelse($model->teams as $team)

                                <!-- left: Icon and Info -->
                                <div class="d-flex gap-2 align-items-center">
                                    <div class="">
                                        <i data-feather="users" class="text-primary"></i> <!-- User icon -->
                                    </div>
                                    <div class="text-center flex-grow-1">

                                        <h5>{{ $team->owner->name }}’s Team</h5>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i data-feather="calendar"> </i> Joined {{ $team->created_at->format("j M
                                            Y") }}
                                        </div>
                                    </div>
                                </div>

                                @empty
                                <div class="text-center flex-grow-1">
                                    <p>No teams
                                        yet.</p>
                                </div>

                                @endforelse
                                <!-- Right: Actions Dropdown -->
                                <div class="dropdown d-none">
                                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i data-feather="more-horizontal" class=""></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="#">Show all team members</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#">Remove from team</a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>


                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('users.edit',$model->id) }}" class="btn btn-primary me-1 w-100">
                                Edit User
                            </a>
                            <button class="btn btn-outline-danger me-1 w-100" data-bs-target="#deleteUserModal"
                                data-bs-toggle="modal">
                                Delete User
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /User Card -->

        </div>
        <!--/ User Sidebar -->

        <!-- User Content -->
        <div class="col-xl-8 col-lg-7 col-md-7 order-0 order-md-1">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs border-bottom-0">
                        <li class="nav-item">
                            <a class="nav-link active custom-tab" data-bs-toggle="tab" href="#tab1"
                                style="font-size: 14px;">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab2"
                                style="font-size: 14px;">Reviews</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link custom-tab" data-bs-toggle="tab" href="#tab3" style="font-size: 14px;">
                                Shipping Addresses
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content mt-3">
                        <!-- tab 1 content -->
                        <div class="tab-pane fade show active" id="tab1">
                            <!-- Pills -->
                            <ul class="nav nav-pills mb-3" id="order-status-pills">
                                <li class="nav-item">
                                    <button class="tab-button btn active text-white" data-status="all">All</button>
                                </li>
                                <li class="nav-item">
                                    <button class="tab-button btn" data-status="placed">Placed</button>
                                </li>
                                <li class="nav-item">
                                    <button class="tab-button btn" data-status="canceled">Canceled</button>
                                </li>
                            </ul>

                            <!-- Order Card -->
                            <div class="card border rounded-3 p-1 mb-2">
                                @forelse($model->orders as $order)

                                <!-- Order Header -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fs-16"><span class="fs-5">Order Number:</span> {{
                                        $order->order_number }}</h6>
                                    <span class=" fs-16 rounded-pill px-1 py-50"
                                        style="background-color: #FCF8FC;color:#4E2775">{{ $order->status->label()
                                        }}</span>
                                </div>

                                <!-- Total Price -->
                                <p class="mb-2 fw-bold fs-16"><span class="fs-5">Total Price:</span> {{
                                    $order->total_price }}</p>

                                <!-- Order Items -->
                                <div class="mb-2">
                                    <h3 class="fs-16 text-black">Items</h3>
                                    <!-- Item 1 -->
                                    @foreach($order->orderItems as $item)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-start gap-2">
                                            <img src="{{$item->itemable?->getFirstMediaUrl(Str::plural(Str::lower(class_basename($item->itemable)))) }}"
                                                class="rounded" alt="Item" style="max-width: 55px;" />
                                            <div>
                                                <div class="text-black fss-16">{{ $item->itemable->name }}</div>
                                                <small class="fs-5">Qty: {{ $item->quantity }}</small>
                                            </div>
                                        </div>
                                        <div class="fw-bold text-black">{{ $item->sub_total }}</div>
                                    </div>
                                    @endforeach

                                </div>

                                <!-- Show Order Button -->
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('orders.show',$order->id) }}" class="btn btn-primary mb-1">Show
                                        Order</a>
                                </div>


                                <!-- Divider -->
                                <hr />
                                @empty
                                <div class="text-center flex-grow-1">
                                    <p>No orders
                                        yet.</p>
                                </div>

                                @endforelse
                            </div>

                        </div>
                        <!-- tab 2 content -->
                        <div class="tab-pane fade" id="tab2">
                            <!-- Total Reviews Section -->
                            <div class="">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-small">Total Reviews:</span>
                                    <span class="label-text delete-review-label" id="review-counter">{{
                                        $model->reviews()->count() }} Reviews</span>

                                </div>

                                @forelse($model->reviews as $review)
                                <!-- Single Review -->
                                <div class="review-wrapper" data-review-id="{{ $review->id }}">
                                    <div class="d-flex align-items-center gap-1 mb-2">
                                        <img src="{{$review->product->getMainImageUrl() }}" alt="Avatar"
                                            class="rounded-circle" width="50" height="50">
                                        <div>
                                            <div class="fw-bold text-dark fs-4">{{ $review->review }}</div>
                                        </div>
                                    </div>

                                    @forelse($review->images as $image)
                                    <div class="mb-2">
                                        <img src="{{ $image?->getUrl() }}" alt="Review Image" class="img-fluid rounded"
                                            style="width: 80px;height: 80px">
                                    </div>
                                    @empty
                                    <div class="mb-2 text-muted" style="font-style: italic;">No review
                                        images
                                        available.
                                    </div>
                                    @endforelse
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <div class="rating-stars text-warning" data-rating="{{ $review->rating }}">
                                        </div>
                                        <span class="fs-6">Placed {{ $review->created_at?->format('d/m/Y') }}</span>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end w-100">
                                        <button class="btn btn-outline-danger d-none"
                                            data-bs-target="#deleteReviewModal" data-bs-toggle="modal">
                                            <i data-feather="trash-2"></i> Delete
                                        </button>
                                        <button
                                            class="btn btn-outline-danger delete-btn delete-review  {{$review->comment ? "
                                            d-none" :""}}" data-review-id="{{ $review->id }}">

                                            <i data-feather="trash-2"></i> Delete
                                        </button>

                                        <button class="btn btn-primary reply-btn  {{$review->comment ? " d-none" :""}}"
                                            data-review-id="{{ $review->id }}" data-bs-toggle="modal"
                                            data-bs-target="#modals-slide-in">
                                            Reply
                                        </button>

                                    </div>
                                </div>



                                <!-- Comment Reply -->
                                <div class="reply-block {{$review->comment ? "" :" d-none"}}">
                                    <div class="d-flex justify-content-between align-items-center mb-1 ">
                                        <div class="d-flex align-items-center gap-1 ">
                                            <img src="{{ asset('images/logo-reply.png') }}" alt="Avatar"
                                                class="rounded-circle" width="48" height="48">

                                            <div class="fw-bold text-primary">Reply from Dorra Team</div>
                                        </div>
                                        <div class="text-small">{{ $review->comment_at?->format('d-m-Y') }}</div>
                                    </div>

                                    <div class="mb-2 label-text mx-5 reply-comment">
                                        {{ $review->comment }}
                                    </div>
                                    <div class="mb-2">
                                        <img src="{{ $review->getFirstMediaUrl('review_reply') ?? " -"}}"
                                            alt="Review Image" class="img-fluid rounded reply-image"
                                            style="width: 80px;height: 80px">
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-danger delete-review"
                                            data-review-id="{{ $review->id }}"><i data-feather="trash-2"></i>
                                            Delete
                                            Review
                                        </button>
                                        <button class="btn btn-outline-secondary delete-reply"
                                            data-review-id="{{ $review->id }}">Delete Reply</button>
                                    </div>
                                </div>
                                <!-- Divider -->
                                <hr class="my-2">
                                @empty
                                <!-- No Reviews Yet Message with Inline Styles -->
                                <div
                                    style="padding: 50px; background-color: #f9f9f9; border-radius: 8px; border: 1px dashed #ccc; font-size: 1.2rem; color: #6c757d; margin-top: 20px; text-align: center;">
                                    <p style="margin: 0; font-weight: 500; font-size: 1.1rem;">No reviews
                                        yet.</p>
                                </div>

                                @endforelse

                            </div>

                        </div>
                        <!-- tab 3 content -->
                        <div class="tab-pane fade" id="tab3">
                            @forelse($model->addresses as $address)
                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                                <!-- left: Icon and Info -->
                                <div
                                    class=" border rounded-3 p-1 d-flex gap-1 align-items-center justify-content-start col-12 col-md-6">
                                    <div class=" flex-grow-1">

                                        <h5 class="fs-16 text-black"> {{ $address->label }}</h5>
                                        <div class="d-flex flex-column fs-16 text-dark">
                                            <span>{{ $address->line }}</span>
                                            <span>{{ $address->state->name }}, {{ $address->state->country->name
                                                }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="d-flex gap-1 justify-content-center mt-2 col-12 col-md-6">

                                    <button class="btn bg-white text-danger place-order fs-16 delete-address"
                                        data-url="{{ route('shipping-addresses.destroy', $address->id) }}">
                                        Delete
                                    </button>




                                    <button type="button" class="btn btn-outline-secondary place-order fs-16"
                                        data-bs-toggle="modal" data-bs-target="#editAddressModal-{{ $address->id }}">
                                        Edit
                                    </button>

                                    @include('modals.addresses.edit-address',['address'=>$address , 'countries' =>
                                    $associatedData['countries']])


                                </div>

                            </div>
                            @empty
                            <div class="text-center p-5 my-3 bg-light border border-dashed rounded-3">
                                <p class="mb-0 fs-5 text-muted">No shipping addresses available yet.</p>
                            </div>
                            @endforelse
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button type="button" class="w-100  rounded-3 p-1 bg-white text-dark"
                                        style="border:2px dashed #CED5D4;" data-bs-target="#addNewAddressModal"
                                        data-bs-toggle="modal">
                                        <i data-feather="plus" class="me-25"></i> <span>Add New Address</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('modals.addresses.add-new-address',['countries' => $associatedData['countries'],'modelId'=>
                    $model->id])

                </div>
            </div>
            @include('modals.delete',[
            'id' => 'deleteUserModal',
            'formId' => 'deleteUserForm',
            'title' => 'Delete User',
            ])
            <!--/ User Content -->
        </div>
    </div>
    @include('modals.users.edit-user',['user'=>$model , 'countryCodes' => $associatedData['country_codes']])
    <!-- Modal to add new user starts-->

    <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
        <div class="modal-dialog">
            <form id="replyForm" class="add-new-user modal-content pt-0" method="post" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Reply To Review</h5>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-3">
                        <label for="replyImage" class="form-label">Upload Image</label>
                        <input class="form-control" type="file" id="image" name="image">
                    </div>
                    <div class="mb-3">
                        <label for="replyText" class="form-label">Your Reply</label>
                        <textarea class="form-control" id="replyText" rows="4" name="comment"
                            placeholder="Write your reply here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Reply</button>
                </div>

            </form>
        </div>
    </div>
</section>

@endsection

@section('vendor-script')
{{-- Vendor js files --}}
<script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>
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
<script>
    $(document).ready(function () {
                const hash = window.location.hash;
                if (hash) {
                    const tabTrigger = $('.nav-link[href="' + hash + '"]');
                    if (tabTrigger.length) {
                        tabTrigger.tab('show'); // Activates the tab
                    }
                }
                $(document).on('click', '.delete-address', function (e) {
                    e.preventDefault();
                    url = $(this).data('url');
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE'
                        },
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Toastify({
                                text: "Address deleted successfully!",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "#dc3545",
                                close: true,
                                callback: function () {
                                    window.location.hash = '#tab3';
                                    location.reload()
                                }
                            }).showToast();
                        },
                        error: function (xhr) {
                            let errorMsg = 'Failed to delete address.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            alert(errorMsg);
                        }
                    });
                });

            });



</script>
<script !src="">
    let currentReviewId = null;

            $(document).on('click', '.reply-btn', function() {
                currentReviewId = $(this).data('review-id');
            });
            $('#replyForm').on('submit', function(e) {
                e.preventDefault();

                if (!currentReviewId) {
                    alert('Review ID not set');
                    return;
                }

                const formData = new FormData(this);

                $.ajax({
                    url: `/api/reviews/${currentReviewId}`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Toastify({
                            text: "Reply sent successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true
                        }).showToast();

                        $('#modals-slide-in').modal('hide');
                        $('#replyForm')[0].reset();

                        // Find the review wrapper
                        const wrapper = $(`.review-wrapper[data-review-id="${currentReviewId}"]`);

                        // Find the reply block directly after this wrapper
                        const replyBlock = wrapper.next('.reply-block');

                        // Show the reply block
                        replyBlock.removeClass('d-none');

                        // Set comment content (optional based on response structure)
                        replyBlock.find('.label-text').text(response.comment || '');
                        replyBlock.find('.text-small').text(response.comment_at || '');
                        replyBlock.find('.reply-image').attr('src', response.data.image || '-');
                        replyBlock.find('.reply-comment').text(response.data.comment || '-');

                        // Hide reply and delete buttons for this review
                        wrapper.find('.reply-btn').addClass('d-none');
                        wrapper.find('.delete-btn').addClass('d-none');

                        // Reset currentReviewId
                        currentReviewId = null;
                    },
                    error: function(xhr) {
                        Toastify({
                            text: "Failed to send reply",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#dc3545",
                            close: true
                        }).showToast();
                    }
                });
            });
</script>

<script !src="">
    $(document).on('click', '.delete-review', function(e) {
                e.preventDefault();

                const button = $(this);
                const reviewId = button.data('review-id');

                if (!reviewId) return;

                $.ajax({
                    url: '/api/reviews/' + reviewId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Toastify({
                            text: "Review deleted successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455",
                            close: true
                        }).showToast();

                        const reviewWrapper = $(`.review-wrapper[data-review-id="${reviewId}"]`);
                        const replyBlock = reviewWrapper.next('.reply-block');

                        reviewWrapper.slideUp(300, function() {
                            $(this).remove();
                        });

                        replyBlock.slideUp(300, function() {
                            $(this).remove();
                        });

                        const counter = $('#review-counter');
                        let currentCount = parseInt(counter.text()) || 0;
                        counter.text(`${Math.max(currentCount - 1, 0)} Reviews`);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseJSON);
                        Toastify({
                            text: "Failed to delete review",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ffc107",
                            close: true
                        }).showToast();
                    }
                });
            });

            $(document).on('click', '.delete-reply', function(e) {
                e.preventDefault();

                const button = $(this);
                const reviewId = button.data('review-id');

                if (!reviewId) return;


                $.ajax({
                    url: '/api/reviews/' + reviewId + '/reply',
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log(response)
                        Toastify({
                            text: "Reply deleted successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28a745",
                            close: true
                        }).showToast();

                        $('#modals-slide-in').modal('hide');
                        $('#replyForm')[0].reset();

                        const reviewId = button.data('review-id');

                        // Get the review wrapper
                        const wrapper = $(`.review-wrapper[data-review-id="${reviewId}"]`);

                        // Hide the reply-block following this wrapper
                        const replyBlock = wrapper.next('.reply-block');
                        replyBlock.addClass('d-none');
                        replyBlock.find('.reply-image').attr('src', response.data || '-');


                        // Show the reply and delete buttons inside the wrapper
                        wrapper.find('.reply-btn').removeClass('d-none');
                        wrapper.find('.delete-btn').removeClass('d-none');
                    },
                    error: function(xhr) {
                        console.error(xhr.responseJSON);
                        Toastify({
                            text: "Failed to delete reply",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#ffc107",
                            close: true
                        }).showToast();
                    }
                });
            });
</script>

{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
<script>
    $(document).on("submit", "#deleteUserForm", function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('users.destroy',$model->id) }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: "DELETE",
                    success: function(res) {

                        $("#deleteUserModal").modal("hide");

                        Toastify({
                            text: "User deleted successfully!",
                            duration: 2000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#28C76F",
                            close: true,
                            callback: function() {
                                window.location.href = "/users";
                            }
                        }).showToast();


                    },
                    error: function() {

                        $("#deleteUserModal").modal("hide");
                        Toastify({
                            text: "Something Went Wrong!",
                            duration: 2000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#EA5455", // red
                            close: true,
                        }).showToast();

                    },
                });
            });
</script>
@endsection
