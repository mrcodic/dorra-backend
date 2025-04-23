@extends('layouts/contentLayoutMaster')

@section('title', '')

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
    <link rel="stylesheet" href="../../assets//vendor/fonts/iconify-icons.css" />
@endsection

@section('page-style')
    {{-- Page Css files --}}
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/extensions/ext-component-sweet-alerts.css')) }}">
    <link rel="stylesheet" href="../../assets//vendor/fonts/iconify-icons.css" />

@endsection

@section('content')
    <div class="row">
        <div class="col-md-4 bg-white p-3 rounded">
            <p class="fs-2 fw-bold text-black">Product Name</p>

            <!-- Main Preview Image -->
            <p class="label-text">Product Image (main)</p>
            <img id="mainPreview" src="{{ asset('images/banner/banner-1.jpg') }}" alt="Preview" class="img-fluid mb-2"
                 style="max-height: 300px;" />

            <p class="label-text">Product Images</p>

            <!-- Custom Slider -->
            <div class="position-relative mb-3">
                <!-- Left Arrow -->
                <button
                    class="btn btn-outline-secondary p-0 position-absolute top-50 start-0 translate-middle-y zindex-sticky"
                    onclick="moveSlide(-1)">
                    <i data-feather="chevron-left"></i>
                </button>

                <!-- Visible Thumbnails (4 at a time) -->
                <div class="d-flex overflow-hidden" style="width: 260px; margin: 0 auto;">
                    <div id="sliderTrack" class="d-flex transition" style="gap: 0.5rem;">
                        @foreach (['banner/banner-2.jpg', 'banner/banner-1.jpg', 'banner/banner-2.jpg', 'banner/banner-3.jpg', 'banner/banner-2.jpg', 'banner/banner-2.jpg', 'banner/banner-1.jpg'] as $img)
                            <img src="{{ asset("images/$img") }}" class="img-thumbnail thumb"
                                 style="width: 60px; height: 60px; flex: 0 0 auto; cursor: pointer;"
                                 onclick="updatePreview(this)">
                        @endforeach
                    </div>
                </div>

                <!-- Right Arrow -->
                <button
                    class="btn btn-outline-secondary p-0 position-absolute top-50 end-0 translate-middle-y zindex-sticky"
                    onclick="moveSlide(1)">
                    <i data-feather="chevron-right"></i>
                </button>
            </div>

            <!-- Info Section -->

            <p class="mb-1 fw-bold  label-text">Rate</p>
            <div class="d-flex justify-content-start align-items-center gap-1 disabled-field">
                <img src="{{ asset('images/star-rate.svg') }}" alt="Star" width="18" />
                <span class=" fw-bold">4.5</span>

            </div>


            <!-- Meta Fields -->
            <div class="my-3 d-flex justify-content-between">
                <div class="d-flex flex-column ">
                    <span class="mb-1 fw-bold  label-text">Added Date:</span>
                    <span class="fw-semibold disabled-field">2024-04-22</span>
                </div>
                <div class="d-flex flex-column  justify-content-between">
                    <span class="mb-1 fw-bold  label-text">Purchase Times:</span>
                    <span class="fw-semibold disabled-field">243</span>
                </div>
            </div>

            <!-- Edit Button -->
            <div class="text-end">
                <button class="btn btn-primary">Edit</button>
            </div>
        </div>




        <!-- Right Section -->
        <div class="col-md-8 ">
            <div class="">
                <div class="">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-2" id="tabContent">
                        <li class="nav-item">
                            <a class="nav-link active " id="tab1-tab" data-bs-toggle="tab" href="#tab1">Product Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab2-tab" data-bs-toggle="tab" href="#tab2">Reviews</a>
                        </li>
                    </ul>

                    <!-- Tab Contents -->
                    <div class="tab-content bg-white p-3">
                        <div class="tab-pane fade show active" id="tab1">
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Product Name In English</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Product Name In Arabic</span>
                                    <span class="fw-semibold disabled-field">243</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Product Description In English</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Product Description In Arabic</span>
                                    <span class="fw-semibold disabled-field">243</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Category</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Subcategory</span>
                                    <span class="fw-semibold disabled-field">243</span>
                                </div>
                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-100">
                                    <span class="mb-1 fw-bold label-text">Tags</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>

                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-100">
                                    <span class="mb-1 fw-bold label-text">Quantity & Price</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>

                            </div>
                            <div class="my-3 d-flex justify-content-between gap-2">
                                <div class="d-flex flex-column w-50">
                                    <span class="mb-1 fw-bold label-text">Quantity</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>
                                <div class="d-flex flex-column  justify-content-between w-50">
                                    <span class="mb-1 fw-bold label-text">Original Price (EGP) (Per Item)</span>
                                    <span class="fw-semibold disabled-field">243</span>
                                </div>
                            </div>
                            <p class="label-text">Product Specs</p>
                            <div class="border rounded p-1">
                                <div class="d-flex flex-column w-100">
                                    <span class="mb-1 fw-bold label-text">Name</span>
                                    <span class="fw-semibold disabled-field">2024-04-22</span>
                                </div>
                                <div class="my-3 d-flex justify-content-between gap-2">
                                    <div class="d-flex flex-column w-100">
                                        <span class="mb-1 fw-bold label-text">Value</span>
                                        <span class="fw-semibold disabled-field">2024-04-22</span>
                                    </div>
                                    <div class="d-flex flex-column  justify-content-between w-100  ">
                                        <span class="mb-1 fw-bold label-text">Price (EGP) (Optional)</span>
                                        <span class="fw-semibold disabled-field">243</span>
                                    </div>
                                    <div class="d-flex flex-column  justify-content-between w-100">
                                        <span class="mb-1 fw-bold label-text">Photo</span>
                                        <span class="fw-semibold disabled-field">243</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab2">
                            <!-- Total Reviews Section -->
                            <div class="">
                                <div class="d-flex justify-content-between mb-2"><span class="text-small">Total Reviews:</span><span class="label-text">421 Reviews</span></div>

                                <!-- Single Review -->
                                <div class="">
                                    <div class="d-flex align-items-center gap-1 mb-2">
                                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Avatar" class="rounded-circle" width="50" height="50">
                                        <div>
                                            <div class="fw-bold text-dark fs-4">John Doe</div>
                                            <div class="text-small">2024-04-22</div>
                                        </div>
                                    </div>
                                    <div class="mb-2 label-text">
                                        This product is really great, highly recommend!
                                    </div>
                                    <div class="mb-2">
                                        <img src="{{ asset('images/banner/banner-1.jpg') }}" alt="Review Image" class="img-fluid rounded">
                                    </div>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <div class="rating-stars text-warning" data-rating="4.1"></div>
                                        <span class="fs-6">Placed 27/09/2024</span>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end w-100">

                                        <button class="btn btn-outline-danger"><i data-feather="trash-2"></i> Delete</button>
                                        <button class="btn btn-primary ">Reply</button>
                                    </div>
                                </div>

                                <!-- Repeat .border div for each review -->
                            </div>

                        </div>
                    </div>
                </div>
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
        window.onload = () => {
            updatePreview(thumbs[0]);
        };
    </script>
    <script>

    </script>





@endsection
