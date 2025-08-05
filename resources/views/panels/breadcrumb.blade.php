<div class="content-header row">
    <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
            <div class="col-12 d-flex align-items-center">
                {{-- Main Page Link --}}
                <h2 class="bread-crumb-title float-start mb-0">
                    <a href="@yield('main-page-url')" class="text-decoration-none text-body">
                        @yield('main-page')
                    </a>
                </h2>

                @hasSection('sub-page')
                    <i data-feather="chevron-right" class="mx-1 fs-1"></i>

                    {{-- Sub Page Link --}}
                    <h2 class="bread-crumb-title float-start mb-0">
                        <a href="@yield('sub-page-url')" class="text-decoration-none text-body">
                            @yield('sub-page')
                        </a>
                    </h2>
                @endif
            </div>
        </div>
    </div>
</div>
