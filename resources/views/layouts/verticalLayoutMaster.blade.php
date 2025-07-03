<body class="vertical-layout vertical-menu-modern {{ $configData['verticalMenuNavbarType'] }} {{ $configData['blankPageClass'] }} {{ $configData['bodyClass'] }} {{ $configData['sidebarClass']}} {{ $configData['footerType'] }} {{$configData['contentLayout']}}"
data-open="click"
data-menu="vertical-menu-modern"
data-col="{{$configData['showMenu'] ? $configData['contentLayout'] : '1-column' }}"
data-framework="laravel"
data-asset-path="{{ asset('/')}}">
  <!-- BEGIN: Header-->
  @include('panels.navbar')
  <!-- END: Header-->

  <!-- BEGIN: Main Menu-->
  @if((isset($configData['showMenu']) && $configData['showMenu'] === true))
  @include('panels.sidebar')
  @endif
  <!-- END: Main Menu-->
  <div class="modal new-user-modal fade" id="templateModal">
      <div class="modal-dialog modal-dialog-centered">
          <div class="add-new-user modal-content pt-0 px-1">

              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
              <div class="modal-header mb-1 border-0 p-0">
                  <h5 class="modal-title fs-4">Select Product to add template</h5>

              </div>
              <form action="{{ route("check.product.type") }}" method="post">
                  @csrf
                  <div class="modal-body flex-grow-1 d-flex flex-column gap-2">
                      <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                          <input
                              class="form-check-input me-2"
                              type="radio"
                              name="product_type"
                              id="codeTshirt"
                              value="T-shirt"
                          />
                          <label class="form-check-label mb-0 flex-grow-1" for="codeTshirt">T-shirt</label>
                      </div>
                      <div class="form-check option-box rounded border py-1 px-3 d-flex align-items-center">
                          <input
                              class="form-check-input me-2"
                              type="radio"
                              name="product_type"
                              id="codeOther"
                              value="other"
                          />
                          <label class="form-check-label mb-0 flex-grow-1" for="codeOther">Other</label>
                      </div>
                  </div>
                  <div class="modal-footer border-top-0 d-flex justify-content-end gap-2">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit"
                              class="btn btn-primary ">
                          Next
                      </button>
              </form>





              </div>

          </div>
      </div>
  </div>

  <!-- BEGIN: Content-->
  <div class="app-content content {{ $configData['pageClass'] }}">
    <!-- BEGIN: Header-->
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>

    @if(($configData['contentLayout']!=='default') && isset($configData['contentLayout']))
    <div class="content-area-wrapper {{ $configData['layoutWidth'] === 'boxed' ? 'container-xxl p-0' : '' }}">
      <div class="{{ $configData['sidebarPositionClass'] }}">
        <div class="sidebar">
          {{-- Include Sidebar Content --}}
          @yield('content-sidebar')
        </div>
      </div>
      <div class="{{ $configData['contentsidebarClass'] }}">
        <div class="content-wrapper">
          <div class="content-body">
            {{-- Include Page Content --}}
            @yield('content')
          </div>
        </div>
      </div>
    </div>
    @else
    <div class="content-wrapper {{ $configData['layoutWidth'] === 'boxed' ? 'container-xxl p-0' : '' }}">
      {{-- Include Breadcrumb --}}
      @if($configData['pageHeader'] === true && isset($configData['pageHeader']))
      @include('panels.breadcrumb')
      @endif

      <div class="content-body">
        {{-- Include Page Content --}}
        @yield('content')
      </div>
    </div>
    @endif

  </div>
  <!-- End: Content-->

  <div class="sidenav-overlay"></div>
  <div class="drag-target"></div>

  {{-- include footer --}}
  @include('panels/footer')

  {{-- include default scripts --}}
  @include('panels/scripts')

  <script type="text/javascript">
      $(window).on('load', function() {
          if (typeof feather !== 'undefined') {
              feather.replace({
                  width: 14, // Set the width of icons
                  height: 14 // Set the height of icons
              });
          } else {
              console.error("Feather icons are not loaded correctly.");
          }
      });
  </script>

</body>
</html>
