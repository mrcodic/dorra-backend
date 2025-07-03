@php
$configData = Helper::applClasses();
@endphp

<div
  class="main-menu menu-fixed {{ $configData['theme'] === 'dark' || $configData['theme'] === 'semi-dark' ? 'menu-dark' : 'menu-light' }} menu-accordion menu-shadow"
  data-scroll-to-active="true">
  <div class="navbar-header" >
    <ul class="nav navbar-nav flex-row" >
      <li class="nav-item mx-auto" >
        <a class="navbar-brand" href="{{ url('/') }}">
          <img id="logo" src="{{ asset('images/dorra-logo.svg') }}" alt="logo" style="width: 92px; " />
        </a>
      </li>
    </ul>
  </div>
  <div class="shadow-bottom"></div>
  <div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
      {{-- Foreach menu item starts --}}
      @if (isset($menuData[0]))
      @foreach ($menuData[0]->menu as $menu)
      @if (isset($menu->navheader))
      <li class="navigation-header">
        <span>{{ __('locale.' . $menu->navheader) }}</span>
        <i data-feather="more-horizontal"></i>
      </li>
      @else
      {{-- Add Custom Class with nav-item --}}
      @php
      $custom_classes = '';
      if (isset($menu->classlist)) {
      $custom_classes = $menu->classlist;
      }
      @endphp

      @php
      $isActive = '';
      if (isset($menu->url)) {
      $cleanUrl = ltrim(parse_url($menu->url, PHP_URL_PATH), '/'); // safer
      $isActive = Request::is($cleanUrl) || Request::is($cleanUrl . '/*') ? 'active' : '';
      }
      @endphp


      <li class="nav-item {{ $custom_classes }} {{ $isActive }}">


          @if (!empty($menu->modalTarget))
              <a href="javascript:void(0)"
                 data-bs-toggle="modal"
                 data-bs-target="{{ $menu->modalTarget }}"
                 class="d-flex align-items-center gap-1">
                  @else
                      <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0)' }}"
                         class="d-flex align-items-center gap-1"
                         target="{{ isset($menu->newTab) ? '_blank' : '_self' }}">
                          @endif

          <!-- <i data-feather="{{ $menu->icon }}"></i> -->
        <img src="{{ asset('images/sidebar-icons/' . $menu->icon) }}" width="20" height="20" alt="{{ $menu->name }} icon" />

          <span class="menu-title text-truncate">{{ __('locale.' . $menu->name) }}</span>
          @if (isset($menu->badge))
          <?php $badgeClasses = 'badge rounded-pill badge-light-primary ms-auto me-1'; ?>
          <span
            class="{{ isset($menu->badgeClass) ? $menu->badgeClass : $badgeClasses }}">{{ $menu->badge }}</span>
          @endif
        </a>
        @if (isset($menu->submenu))
        @include('panels/submenu', ['menu' => $menu->submenu])
        @endif
      </li>
      @endif
      @endforeach
      @endif
      {{-- Foreach menu item ends --}}
    </ul>
  </div>
</div>
<!-- END: Main Menu-->
