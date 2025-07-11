{{-- For submenu --}}
<ul class="menu-content">
  @if (isset($menu))
    @foreach ($menu as $submenu)
      @php
        $submenuUrl = isset($submenu->url) ? ltrim(parse_url($submenu->url, PHP_URL_PATH), '/') : '';
        $isSubmenuActive = Request::is($submenuUrl) || Request::is($submenuUrl . '/*');
      @endphp

      <li class="{{ $isSubmenuActive ? 'active' : '' }}">
        <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}" class="d-flex align-items-center"
          target="{{ isset($submenu->newTab) && $submenu->newTab === true ? '_blank' : '_self' }}">
          @if (isset($submenu->icon))
            <i data-feather="{{ $submenu->icon }}" style="visibility: hidden;" ></i>
          @endif
          <span class="menu-item text-truncate">{{ __('locale.' . $submenu->name) }}</span>
        </a>
        @if (isset($submenu->submenu))
          @include('panels/submenu', ['menu' => $submenu->submenu])
        @endif
      </li>
    @endforeach
  @endif
</ul>
