<ul class="menu-content  main-menu">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            @php
                $submenuPath = ltrim(parse_url($submenu->url, PHP_URL_PATH), '/');
                parse_str(parse_url($submenu->url, PHP_URL_QUERY) ?? '', $submenuQuery);

                $isSubmenuActive =
                    request()->path() === $submenuPath &&
                    request()->get('product_without_category_id') == ($submenuQuery['product_without_category_id'] ?? null);
            @endphp
            <li class="{{ $isSubmenuActive ? 'active' : '' }}">

                @if (!empty($submenu->modalTarget ?? ''))
                    <a href="javascript:void(0)"
                       data-bs-toggle="modal"
                       data-bs-target="{{ $submenu->modalTarget }}"
                       class="d-flex align-items-center">
                        @else
                            <a href="{{ isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)' }}"
                               class="d-flex align-items-center"
                               target="{{ isset($submenu->newTab) && $submenu->newTab === true ? '_blank' : '_self' }}">
                                @endif

                                @if (isset($submenu->icon))
                                    <i data-feather="{{ $submenu->icon }}" style="visibility: hidden;"></i>
                                @endif

                                {{-- ✅ لو dynamic (من DB) اعرضه مباشرة، لو static استخدم locale --}}
                                <span class="menu-item text-truncate">
            {{ isset($submenu->dynamic) && $submenu->dynamic
                ? $submenu->name
                : __('locale.' . $submenu->name) }}
          </span>

                            </a>

                    @if (isset($submenu->submenu))
                        @include('panels/submenu', ['menu' => $submenu->submenu])
                    @endif
            </li>
        @endforeach
    @endif
</ul>
