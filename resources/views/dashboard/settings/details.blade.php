@extends('layouts/contentLayoutMaster')

@section('title', 'Settings-Details')
@section('main-page', 'Details')

@section('vendor-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">
<link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')) }}">


<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-style')
    <style>
        .tab-section { display: none; }
        .tab-section.active { display: block; }
        .profile-tab.active { background: var(--bs-primary); color: #fff; }
    </style>

    {{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<div class="card d-flex flex-column flex-md-row">
    {{-- Left Side: Vertical Tabs --}}
    <div class="nav d-flex flex-row flex-md-column nav-pills px-1 py-2 gap-2" id="v-pills-tab" role="tablist"
        aria-orientation="vertical">
        <button class="btn profile-tab active" data-target="tab1">Profile</button>
        <button class="btn profile-tab" data-target="tab2">Social Media Platforms</button>
        <button class="btn profile-tab" data-target="tab4">Order Format</button>
    </div>

    {{-- Right Side: Tab Content --}}
    <div class="tab-content flex-grow-1 p-2" id="v-pills-tabContent">
        <!-- Profile Section -->
        <div id="tab1" class="tab-section">
            <h4 class="mb-2">Profile</h4>
            <form class="profileSettingsForm" method="post" action="{{ route('settings-edit-details') }}">
                @csrf
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter phone number"
                               value="{{ setting('phone') }}">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label for="store_email" class="form-label">Store Contact Email Address</label>
                        <input type="email" id="store_email" class="form-control" placeholder="Enter store email" name="store_email"
                               value="{{ setting('store_email') }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2">
                    <button class="btn btn-outline-secondary me-1" type="reset">Discard Changes</button>
                    <button class="btn btn-primary" id="saveProfileBtn" type="submit">Save</button>
                </div>
            </form>
        </div>



        <!-- Social Media Platforms -->
        <div id="tab2" class="tab-section">
            <h4 class="mb-2">Social Media Platforms</h4>
            <div class="social-repeater">
                <form action="{{ route("social-links") }}" method="post" id="socialForm">
                    @csrf
                <div data-repeater-list="socials">

                    @forelse($socialLinks as $socialLink)

                        <div data-repeater-item>

                            <input type="hidden" name="id" value="{{ $socialLink->id }}">
                            <!-- Social Media Input Group -->
                            <div id="social-media-group" class="row social-input-row">
                                <div class="col-12 col-lg-6">
                                    <label for="platform">Platform</label>
                                    <select class="form-select" name="platform">
                                        <option value="" disabled selected>Select Platform</option>
                                        @foreach(\App\Enums\Setting\SocialEnum::cases() as $socialEnum)
                                            <option value="{{ $socialEnum->value }}">{{ $socialEnum->label() }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-12 col-lg-6 d-flex gap-1">
                                    <div class="col-8">
                                        <label for="social_url">URL</label>
                                        <div class="input-group">
{{--                                            <span class="input-group-text">https://</span>--}}
                                            <input type="text" name="url" class="form-control" placeholder="yourpage.com" value="{{ $socialLink->url }}" />
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger delete-social" data-repeater-delete>Delete</button>


                                    </div>
                                </div>
                            </div>


                        </div>
                    @empty
                        <div data-repeater-item>

                                @csrf

                            <!-- Social Media Input Group -->
                            <div id="social-media-group" class="row social-input-row">
                                <div class="col-12 col-lg-6">
                                    <label for="platform">Platform</label>
                                    <select class="form-select" name="platform">
                                        <option value="" disabled selected>Select Platform</option>
                                        @foreach(\App\Enums\Setting\SocialEnum::cases() as $socialEnum)
                                            <option value="{{ $socialEnum->value }}">{{ $socialEnum->label() }}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <div class="col-12 col-lg-6 d-flex gap-1">
                                    <div class="col-8">
                                        <label for="social_url">URL</label>
                                        <div class="input-group">
{{--                                            <span class="input-group-text">https://</span>--}}
                                            <input type="text" name="url" class="form-control" placeholder="yourpage.com" />
                                        </div>
                                    </div>
                                    <div class="col-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger delete-social" data-repeater-delete>Delete</button>


                                    </div>
                                </div>
                            </div>


                        </div>
                    @endforelse


                </div>
                    <button type="button" class="btn btn-outline-primary mb-2 mt-2" id="add-social" data-repeater-create>+ Add Social Media</button>

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
                </form>
            </div>

        </div>


        <!-- Order Format -->
        <div id="tab4" class="tab-section">
            <form class="profileSettingsForm" method="post" action="{{ route('settings-edit-details') }}">
                @csrf
            <h4 class="mb-2">Order Format</h4>
            <div class="row mb-2">
                <div class="col-md-6">
                    <label for="prefix" class="form-label">Prefix</label>
                    <input type="text" name="order_format" id="prefix" value="{{ setting('order_format') }}" class="form-control" placeholder="e.g. ORD-" />
                </div>
            </div>
            <div class="d-flex justify-content-end mt-2">
                <button class="btn btn-outline-secondary me-1" type="reset">Discard Changes</button>
                <button class="btn btn-primary" type="submit" >Save</button>
            </div>
            </form>
        </div>


    </div>


</div>


@endsection

@section('vendor-script')

    <script src="{{ asset(mix('vendors/js/forms/repeater/jquery.repeater.min.js')) }}"></script>


    <!-- 2) Your init code (AFTER the plugin) -->
    <script>
        handleAjaxFormSubmit("#socialForm",
            {
                successMessage: "Request completed successfully",
                onSuccess: function () {
                    location.reload()
                },
                resetForm: false,
            })
        handleAjaxFormSubmit(".profileSettingsForm",
            {
                successMessage: "Request completed successfully",
                onSuccess: function () {
                    location.reload()
                },
                resetForm: false,
            }

        )
        $(function () {
            function recalc($wrap) {
                const $items = $wrap.find('[data-repeater-item]:visible');

            }

            $('.social-repeater').each(function () {
                const $wrap = $(this);

                $wrap.repeater({
                    // keep one row on load; set true if you want it empty initially
                    initEmpty: false,
                    show: function () {

                        $(this).slideDown(150, function () {
                            recalc($wrap);
                        });
                        if (window.feather) feather.replace();
                    },

                    hide: function (deleteElement) {
                        // run animation, then actually delete, then recalc
                        $(this).slideUp(150, () => {
                            deleteElement();
                            recalc($wrap);
                        });
                    },

                    ready: function () {
                        // called after init
                        recalc($wrap);
                    }
                });

                // safety: if ready isn't fired for any reason
                recalc($wrap);
            });
        });
    </script>

    {{-- Vendor js files --}}
<script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
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
<script src="{{ asset(mix('vendors/js/forms/validation/jquery.validate.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/cleave.min.js')) }}"></script>
<script src="{{ asset(mix('vendors/js/forms/cleave/addons/cleave-phone.us.js')) }}"></script>
@endsection

@section('page-script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
    const productsDataUrl = "{{ route('products.data') }}";
    const productsCreateUrl = "{{ route('products.create') }}";
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons  = Array.from(document.querySelectorAll('.profile-tab'));
        const sections = Array.from(document.querySelectorAll('#v-pills-tabContent > div[id^="tab"]'));

        const show = (id) => {
            sections.forEach(s => s.classList.toggle('active', s.id === id));
            buttons.forEach(b => b.classList.toggle('active', b.dataset.target === id));
            // keep it in URL (so refresh/deep-link works)
            history.replaceState(null, '', '#' + id);
        };

        // initial tab: hash if valid, else first button target
        const hashId = (location.hash || '').replace('#','');
        const initial = sections.some(s => s.id === hashId)
            ? hashId
            : (buttons[0] && buttons[0].dataset.target);

        if (initial) show(initial);

        buttons.forEach(btn => {
            btn.addEventListener('click', () => show(btn.dataset.target));
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.profile-tab');
        const sections = ['tab1', 'tab2', 'tab3', 'tab4'].map(id => document.getElementById(id));

        // Button click - scroll and set active class
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                document.getElementById(targetId).scrollIntoView({
                    behavior: 'smooth'
                });

                // Remove active from all and add to clicked
                buttons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Optional: Scrollspy - update active button based on scroll
        window.addEventListener('scroll', function() {
            let fromTop = window.scrollY + 100;

            sections.forEach((section, index) => {
                if (section.offsetTop <= fromTop &&
                    section.offsetTop + section.offsetHeight > fromTop) {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    buttons[index].classList.add('active');
                }
            });
        });
    });
</script>


<script>
    document.querySelector('.add-social')?.addEventListener('click', function() {
        const group = document.querySelector('.social-input-row');
        const clone = group.cloneNode(true);
        clone.querySelector('input').value = '';
        document.getElementById('social-media-group').appendChild(clone);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const addButton = document.getElementById('add-social');
        const socialGroup = document.getElementById('social-media-group');

        // Add Social Media
        addButton.addEventListener('click', function() {
            const rows = socialGroup.querySelectorAll('.social-input-row');
            const clone = rows[0].cloneNode(true);
            clone.querySelector('input').value = '';
            clone.querySelector('select').selectedIndex = 0;

            // Ensure delete button exists
            const deleteBtn = clone.querySelector('.delete-social');
            if (!deleteBtn) {
                const deleteCol = document.createElement('div');
                deleteCol.className = "col-md-2 d-flex align-items-end";
                deleteCol.innerHTML = `<button type="button" class="btn btn-danger delete-social">Delete</button>`;
                clone.appendChild(deleteCol);
            }

            socialGroup.appendChild(clone);
        });

        // Delegate Delete Buttons
        socialGroup.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-social')) {
                const rows = socialGroup.querySelectorAll('.social-input-row');
                if (rows.length > 1) {
                    e.target.closest('.social-input-row').remove();
                }
            }
        });
    });
</script>
{{-- Page js files --}}
<script src="{{ asset('js/scripts/pages/app-product-list.js') }}?v={{ time() }}"></script>
@endsection
