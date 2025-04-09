// ajax setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});


var dt_user = dt_user_table.DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: baseUrl + 'user-list',
        dataSrc: function (json) {
            ids = 0;
            return json.data;
        }
    },

    return data ? $('<table class="table"/><tbody />').append(data) : false;
});
