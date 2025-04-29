@extends('layouts/contentLayoutMaster')

@section('title', 'Show Product')
@section('main-page', 'Products')
@section('sub-page', 'Show Product')

@section('vendor-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/animate/animate.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/extensions/sweetalert2.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('vendors/fonts/fontawesome.css') }}">
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4 bg-white p-3 rounded">
            <p class="fs-2 fw-bold text-black">Product Name</p>

            <!-- Main Preview Image -->
            <p class="label-text">Product Image (main)</p>
            <div class="w-100 d-flex justify-content-center">
                <img id="mainPreview" src="{{$model->getMainImageUrl() }}" alt="Preview" class="img-fluid mb-2"
                     style="height: 256px;width: 256px"/>
            </div>
            @if($model->getMedia('product_extra_images')->isNotEmpty())
                <p class="label-text">Product Images</p>

                <!-- Custom Slider -->
                <div class="position-relative mb-3">
                    <!-- Left Arrow -->
                    <button
                        class="btn btn-outline-secondary  position-absolute top-50 start-0 translate-middle-y zindex-sticky"
                        style="padding: 5px;"
                        onclick="moveSlide(-1)">
                        <i data-feather="chevron-left"></i>
                    </button>

                    <!-- Visible Thumbnails (4 at a time) -->
                    <div class="d-flex overflow-hidden" style="width: 260px; margin: 0 auto;">
                        <div id="sliderTrack" class="d-flex transition" style="gap: 0.5rem;">

                            @foreach ($model->getAllProductImages() as $media)
                                <img src="{{ $media->getUrl() }}" class="img-thumbnail thumb"
                                     style="width: 60px; height: 60px; flex: 0 0 auto; cursor: pointer;"
                                     onclick="updatePreview(this)">
                            @endforeach
                        </div>
                    </div>

                    <!-- Right Arrow -->
                    <button
                        class="btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y zindex-sticky"
                        style="padding: 5px;"
                        onclick="moveSlide(1)">
                        <i data-feather="chevron-right"></i>
                    </button>
                </div>
            @endif
            <!-- Info Section -->

            <p class="mb-1 fw-bold  label-text">Rate</p>
            <div class="d-flex justify-content-start align-items-center gap-1 disabled-field">
                <img src="{{ asset('images/star-rate.svg') }}" alt="Star" width="18"/>
                <span class=" fw-bold">{{ $model->rating ?? 0 }}</span>

            </div>


            <!-- Meta Fields -->
            <div class="my-3 d-flex justify-content-between">
                <div class="d-flex flex-column ">
                    <span class="mb-1 fw-bold  label-text">Added Date:</span>
                    <span class="fw-semibold disabled-field">{{ $model->created_at->format('Y-m-d') }}</span>
                </div>
                <div class="d-flex flex-column  justify-content-between">
                    <span class="mb-1 fw-bold  label-text">Purchase Times:</span>
                    <span class="fw-semibold disabled-field">243</span>
                </div>
            </div>

            <!-- Edit Button -->
            <div class="text-end">
                <a href="{{ route("products.edit",$model->id) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>


        <!-- Right Section -->
        <div class="col-md-8 py-1">
            <div class="">
                <div class="">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="tabContent">
                        <li class="nav-item">
                            <a class="tab-button active " id="tab1-tab" data-bs-toggle="tab" href="#tab1">Product
                                Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="tab-button" id="tab2-tab" data-bs-toggle="tab" href="#tab2">Reviews</a>
                        </li>
                    </ul>

                    <!-- Tab Contents -->
                    <div class="tab-content bg-white p-3">
                        <div class="tab-pane fade show active" id="tab1">
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Product Name In English</span>
                                    <span
                                        class="fw-semibold disabled-field">{{ $model->getTranslation('name','en') }}</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Product Name In Arabic</span>
                                    <span
                                        class="fw-semibold disabled-field">{{ $model->getTranslation('name','ar') }}</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Product Description In English</span>
                                    <span
                                        class="fw-semibold disabled-field">{{ $model->getTranslation('description','en') }}</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Product Description In Arabic</span>
                                    <span
                                        class="fw-semibold disabled-field">{{ $model->getTranslation('description','ar') }}</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Category</span>
                                    <span class="fw-semibold disabled-field">{{ $model->category?->name }}</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Subcategory</span>
                                    <span
                                        class="fw-semibold disabled-field">{{ $model->subCategory?->name ?? "N/A" }}</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-100">
                                    <span class="mb-1 fw-bold label-text">Tags</span>
                                    <span class="fw-semibold disabled-field d-flex gap-1 ">
                                @forelse($model->tags as $tag)
                                            <span class="fw-semibold bg-white rounded-pill"
                                                  style="padding: 5px 10px;font-size: 12px">{{ $tag->name }}</span>
                                        @empty
                                            -
                                        @endforelse
                                </span>
                                </div>

                            </div>
                            <div class="my-3 d-flex flex-column gap-2">
                                <div class="d-flex flex-column w-100">
                                    <span class="mb-1 fw-bold label-text">Quantity & Price</span>
                                    <span
                                        class="fw-semibold disabled-field"> {{ $model->has_custom_prices ? "Quantity Added Manually" : "Default Quantity"}}</span>
                                </div>
                                @if($model->has_custom_prices)
                                    @foreach($model->prices as  $product)
                                        <div class="my-3 d-flex justify-content-between gap-2">
                                            <div class="d-flex flex-column w-50">
                                                <span class="mb-1 fw-bold label-text">Quantity</span>
                                                <span class="fw-semibold disabled-field">{{ $product->quantity }}</span>
                                            </div>
                                            <div class="d-flex flex-column  justify-content-between w-50">
                                                <span
                                                    class="mb-1 fw-bold label-text">Original Price (EGP) (Per Item)</span>
                                                <span class="fw-semibold disabled-field">{{ $product->price  }}</span>
                                            </div>
                                        </div>
                                    @endforeach

                                @else
                                    <div class="d-flex flex-column  justify-content-between w-100">
                                        <span class="mb-1 fw-bold label-text">Original Price (EGP) (Per Item)</span>
                                        <span class="fw-semibold disabled-field">
                                    {{ $model->base_price }}
                                </span>
                                    </div>
                                @endif

                            </div>


                            <p class="label-text">Product Specs</p>
                            @foreach($model->specifications as $specification)
                                <div class="border rounded p-1">
                                    <div class="d-flex flex-column w-100">
                                        <span class="mb-1 fw-bold label-text">Name</span>
                                        <span class="fw-semibold disabled-field">{{ $specification->name }}</span>
                                    </div>
                                    @foreach($specification->options as $option) @endforeach
                                    <div class="my-3 d-flex justify-content-between gap-2">
                                        <div class="d-flex flex-column w-100">
                                            <span class="mb-1 fw-bold label-text">Value</span>
                                            <span class="fw-semibold disabled-field">{{ $option->value }}</span>
                                        </div>
                                        <div class="d-flex flex-column  justify-content-between w-100  ">
                                            <span class="mb-1 fw-bold label-text">Price (EGP) (Optional)</span>
                                            <span class="fw-semibold disabled-field">{{ $option->price }}</span>
                                        </div>
                                        <div class="d-flex flex-column  justify-content-between w-100">
                                            <span class="mb-1 fw-bold label-text">Photo</span>
                                            <span class="fw-semibold disabled-field">
                                                @if ($option->getFirstMediaUrl())
                                                    <img src="{{ $option->getFirstMediaUrl() }}" width="32px"
                                                         height="32px"/>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="tab-pane fade" id="tab2">
                            <!-- Total Reviews Section -->
                            <div class="">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-small">Total Reviews:</span>
                                    <span class="label-text">421 Reviews</span>
                                </div>

                                <!-- Single Review -->
                                <div class="">
                                    <div class="d-flex align-items-center gap-1 mb-2">
                                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Avatar"
                                             class="rounded-circle" width="50" height="50">
                                        <div>
                                            <div class="fw-bold text-dark fs-4">John Doe</div>
                                            <div class="text-small">2024-04-22</div>
                                        </div>
                                    </div>
                                    <div class="mb-2 label-text">
                                        This product is really great, highly recommend!
                                    </div>
                                    <div class="mb-2">
                                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Review Image"
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <div class="rating-stars text-warning" data-rating="4.1"></div>
                                        <span class="fs-6">Placed 27/09/2024</span>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end w-100">
                                        <button class="btn btn-outline-danger"><i data-feather="trash-2"></i> Delete
                                        </button>
                                        <button class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#modals-slide-in">
                                            Reply
                                        </button>
                                    </div>
                                </div>

                                <!-- Divider -->
                                <hr class="my-2">

                                <!-- Comment Reply -->
                                <div class="">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="d-flex align-items-center gap-1 ">
                                            <img src="{{ asset('images/logo-reply.png') }}" alt="Avatar"
                                                 class="rounded-circle" width="48" height="48">

                                            <div class="fw-bold text-primary">Reply from Dorra Team</div>


                                        </div>
                                        <div class="text-small">2024-04-23</div>
                                    </div>

                                    <div class="mb-2 label-text mx-5">
                                        Thank you for your kind words! We're happy you loved the product.
                                    </div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button class="btn btn-outline-danger"><i data-feather="trash-2"></i> Delete
                                            Comment
                                        </button>
                                        <button class="btn btn-outline-secondary">Delete Reply</button>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal to add new user starts-->

        <div class="modal modal-slide-in new-user-modal fade" id="modals-slide-in">
            <div class="modal-dialog">
                <form id="replyForm" class="add-new-user modal-content pt-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                    </div>
                    <div class="modal-body flex-grow-1">
                        <div class="mb-3">
                            <label for="replyImage" class="form-label">Upload Image</label>
                            <input class="form-control" type="file" id="replyImage" name="replyImage">
                        </div>
                        <div class="mb-3">
                            <label for="replyText" class="form-label">Your Reply</label>
                            <textarea class="form-control" id="replyText" rows="4" name="replyText"
                                      placeholder="Write your reply here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reply</button>
                    </div>

                </form>
            </div>
        </div>


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

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    {{-- Page js files --}}
    <script src="{{ asset('js/scripts/pages/modal-edit-user.js') }}?v={{ time() }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view-account.js')) }}"></script>
    <script src="{{ asset(mix('js/scripts/pages/app-user-view.js')) }}"></script>
    <script src="{{ asset('js/scripts/ui/star-rate.js') }}?v={{ time() }}"></script>
    <script>
        const sliderTrack = document.getElementById('sliderTrack');
        const thumbWidth = 65;
        const maxVisible = 4;
        let currentIndex = 0;

        const thumbs = document.querySelectorAll('.thumb');

        function updatePreview(img) {
            // Set preview image
            document.getElementById('mainPreview').src = img.src;

            // Reset all borders
            thumbs.forEach(t => t.classList.remove('border-success', 'border-3'));

            // Add green border to selected
            img.classList.add('border-success', 'border-3');
        }

        function moveSlide(direction) {
            const totalThumbs = thumbs.length;
            const maxIndex = totalThumbs - maxVisible;

            currentIndex += direction;
            if (currentIndex < 0) currentIndex = 0;
            if (currentIndex > maxIndex) currentIndex = maxIndex;

            sliderTrack.style.transform = `translateX(-${currentIndex * thumbWidth}px)`;
        }

        // Initialize first image as selected

    </script>
    <script>
        document.getElementById('replyForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const image = document.getElementById('replyImage').files[0];
            const replyText = document.getElementById('replyText').value;

            console.log("Reply:", {
                image,
                replyText
            });

            // Hide modal after submission
            const modal = bootstrap.Modal.getInstance(document.getElementById('modals-slide-in'));
            modal.hide();

            // Reset form
            this.reset();
        });
    </script>

@endsection
