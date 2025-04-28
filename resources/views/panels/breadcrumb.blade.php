<div class="content-header row">
  <div class="content-header-left col-md-9 col-12 mb-2">
    <div class="row breadcrumbs-top">
      <div class="col-12 d-flex align-items-center">
        <h2 class="bread-crumb-title float-start mb-0">@yield('main-page')</h2>

        @hasSection('sub-page')
          <i data-feather="chevron-right" class="mx-1 fs-1"></i>
          <h2 class="bread-crumb-title float-start mb-0">@yield('sub-page')</h2>
        @endif

      </div>
    </div>
  </div>
</div>
