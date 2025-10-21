@extends('layouts/contentLayoutMaster')

@section('title', 'Roles')
@section('main-page', 'Roles')

@section('vendor-style')
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
<link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
@endsection

@section('content')
<div class="bg-white p-2" style="min-height: 770px;">
  <!-- Header actions -->
  <div class="d-flex gap-1 align-items-center flex-wrap mb-2">
    <div class="col-12 col-md-8">

        <form id="searchForm" action="{{ url()->current() }}" method="get" style="display:none">
            <input type="text" name="search" class="form-control" placeholder="Search here">
        </form>

        <input type="text" id="searchInput" class="form-control" placeholder="Search here">

    </div>
    <a class="btn btn-outline-primary col-12 col-md-3" href="{{ route("roles.create") }}">
      <i data-feather="plus"></i> Add New Role
    </a>
  </div>

  <!-- Role cards -->
    <div id="rolesGrid" class="row">
        @foreach($associatedData['roles'] as $role)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @foreach($role->users as $user)
                                    <li data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ $user->name ?? 'User' }}" class="avatar avatar-sm pull-up">
                                        <img class="rounded-circle"
                                             src="{{ $user->getFirstMediaUrl('admins') ?: asset('images/default-user.png') }}"
                                             alt="Avatar" />
                                    </li>
                                @endforeach
                            </ul>
                            <span>{{ $role->users_count }} Users</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end mt-1 pt-25">
                            <div class="role-heading">
                                <h4 class="">{{ is_array($role->name) ? ($role->name[app()->getLocale()] ?? reset($role->name)) : $role->name }}</h4>
                                <a href="{{ route('roles.edit',$role->id) }}" class="role-edit-modal">Edit Role</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection

@section('vendor-script')
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
<script src="{{ asset(mix('js/scripts/pages/modal-add-role.js')) }}"></script>
<script src="{{ asset(mix('js/scripts/pages/app-access-roles.js')) }}"></script>
<script>
    (function () {
        let t, xhr;

        const $input   = $('#searchInput');
        const $grid    = $('#rolesGrid');
        const editBase = "{{ url('/roles') }}";                 // -> /roles/{id}/edit
        const DEFAULT_AVATAR = "{{ asset('images/default-user.png') }}";

        // escape text
        function esc(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
            }[s]));
        }
        // pick avatar url
        function avatarUrl(u) {
            return (u && u.image && u.image.url) ? u.image.url : DEFAULT_AVATAR;
        }

        function renderRolesFromApi(payload) {
            const ok   = payload && payload.success !== false && (payload.status === undefined || payload.status === 200);
            const list = ok ? (payload.data || []) : [];

            if (!list.length) {
                $grid.html('<div class="col-12 text-center text-muted py-5">No roles found.</div>');
                return;
            }

            const html = list.map(r => {
                const avatars = (r.users || []).map(u => `
          <li data-bs-toggle="tooltip" data-bs-placement="top"
              title="${esc([u.first_name, u.last_name].filter(Boolean).join(' ') || u.email || 'User')}"
              class="avatar avatar-sm pull-up">
            <img class="rounded-circle" src="${esc(avatarUrl(u))}" alt="Avatar" />
          </li>
        `).join('');

                return `
          <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card">
              <div class="card-body">

                <div class="d-flex justify-content-between">
                  <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                    ${avatars}
                  </ul>
                  <span>${r.users_count ?? 0} Users</span>
                </div>

                <div class="d-flex justify-content-between align-items-end mt-1 pt-25">
                  <div class="role-heading">
                    <h4 class="">${esc(r.name)}</h4>
                    <a href="${editBase}/${encodeURIComponent(r.id)}/edit" class="role-edit-modal">Edit Role</a>
                  </div>
                </div>

              </div>
            </div>
          </div>`;
            }).join('');

            $grid.html(html);

            // Re-init icons/tooltips
            if (window.feather) window.feather.replace();
            $('[data-bs-toggle="tooltip"]').each(function () {
                if (!this._tooltip) this._tooltip = new bootstrap.Tooltip(this);
            });
        }

        function setLoading() {
            $grid.html('<div class="col-12 text-center py-5">Loading...</div>');
        }

        $input.on('input', function () {
            clearTimeout(t);
            const q = $(this).val();

            t = setTimeout(() => {
                if (xhr && xhr.readyState !== 4) xhr.abort();

                const urlObj = new URL(window.location.href);
                if (q) urlObj.searchParams.set('search', q);
                else   urlObj.searchParams.delete('search');
                history.replaceState({}, '', urlObj);

                setLoading();

                xhr = $.ajax({
                    url: "{{ request()->url() }}",
                    method: "GET",
                    dataType: "json",
                    data: { search: q },
                    success: renderRolesFromApi,
                    error: function (xhr) {
                        if (xhr.statusText === 'abort') return;
                        console.error('Search failed', xhr.responseText);
                        $grid.html('<div class="col-12 text-center text-danger py-5">Search failed.</div>');
                    }
                });
            }, 300);
        });
    })();
</script>




@endsection
