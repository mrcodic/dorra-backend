
    $.ajaxSetup({
    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
});

    const dt_orders = $(".order-list-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
    url: ordersDataUrl,
    type: "GET",
    data: function (d) {
    d.search_value = $("#search-order-form").val() || "";
    d.created_at   = $(".filter-date").val()      || ""; // asc|desc
    d.status       = $(".filter-status").val()    || ""; // enum value/text
    return d;
},
},
    columns: [
{
    data: null,
    orderable: false,
    defaultContent: "",
    render: (row)=> `<input type="checkbox" name="ids[]" class="category-checkbox" value="${row.id}">`,
},
{ data: "order_number" },
{ data: "user_name" },
{ data: "items" },
{ data: "total_price" },
{
    data: "status",
    render: function (data) {
    let icon = "/images/defaultIcon.svg";
    let label = data;
    switch (data) {
    case "Pending":   icon="/images/pendingIcon.svg";   label="Pending"; break;
    case "Confirmed": icon="/images/confirmedIcon.svg"; label="Confirmed"; break;
    case "Prepared":  icon="/images/preparingIcon.svg"; label="Prepared"; break;
    case "Shipped":   icon="/images/deliveryIcon.svg";  label="Out for delivery"; break;
    case "Delivered": icon="/images/deliveryIcon.svg";  label="Delivered"; break;
    case "Refunded":  icon="/images/refundedIcon.svg";  label="Refunded"; break;
}
    return `
          <div class="d-flex align-items-center gap-1" style="background:#FCF8FC;border-radius:12px;padding:4px 8px">
            <img src="${icon}" alt="${label}" style="width:20px;height:20px">
            <span>${label}</span>
          </div>`;
}
},
{ data: "added_date" },
{
    data: "id",
    orderable: false,
    render: function (id, type, row) {
    return `
          <div class="d-flex gap-1">
            <a href="/orders/${id}"><i data-feather="eye"></i></a>
            <a href="/orders/${id}/edit"><i data-feather="edit"></i></a>
            <a href="#" class="text-danger open-delete-order-modal"
               data-id="${id}"
               data-name="${row.order_number}"
               data-action="/orders/${id}"
               data-bs-toggle="modal"
               data-bs-target="#deleteOrderModal">
               <i data-feather="trash-2"></i>
            </a>
          </div>`;
},
},
    ],
    order: [[1, "asc"]],
    dom:
    '<"d-flex align-items-center header-actions mx-2 row mt-75"' +
    '<"col-12 d-flex flex-wrap align-items-center justify-content-between">' +
    ">t" +
    '<"d-flex mx-2 row mb-1"' +
    '<"col-sm-12 col-md-6"i>' +
    '<"col-sm-12 col-md-6"p>' +
    ">",
    buttons: [],
    drawCallback: function(){ feather.replace(); },
    language: {
    sLengthMenu: "Show _MENU_",
    search: "",
    searchPlaceholder: "Search..",
    paginate: { previous: "&nbsp;", next: "&nbsp;" },
},
});

    /* ---------- helpers like jobs ---------- */
    function setActiveStatusCard(val){
    $(".status-card").removeClass("selected");
    if (val) $(`.status-card[data-status="${val}"]`).addClass("selected");
}
    function resetBulkSelection(){
    $("#bulk-delete-container").hide();
    $(".category-checkbox").prop("checked", false);
    $("#select-all-checkbox").prop("checked", false);
}
    function updateBulkDeleteVisibility(){
    const count = $(".category-checkbox:checked").length;
    if (count>0){
    // unify the counter id
    $("#selected-count, #selected-count-text").text(count);
    $("#bulk-delete-container").show();
}else{
    $("#bulk-delete-container").hide();
}
}

    /* ---------- filters (jobs-style) ---------- */
    // debounce search
    let searchTimeout;
    $("#search-order-form").on("keyup", function(){
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(()=> dt_orders.draw(), 300);
});
    $("#clear-search").on("click", function(){
    $("#search-order-form").val("");
    dt_orders.search("").draw();
});

    // dropdowns → draw
    $(".filter-date").on("change", function(){ dt_orders.draw(); });
    $(".filter-status").on("change", function(){
    setActiveStatusCard($(this).val() || "");
    dt_orders.draw();
});

    // cards → dropdown + draw
    $(document).on("click", ".status-card", function(){
    const status = $(this).data("status") || "";
    $(".filter-status").val(status).trigger("change");
});

    // sync selected card on first load (if select has value)
    $(document).ready(function(){
    setActiveStatusCard($(".filter-status").val() || "");
});

    /* ---------- checkbox selection (jobs-style) ---------- */
    $(document).on("change", "#select-all-checkbox", function(){
    $(".category-checkbox").prop("checked", $(this).prop("checked"));
    updateBulkDeleteVisibility();
});
    $(document).on("change", ".category-checkbox", function(){
    const all = $(".category-checkbox").length;
    const checked = $(".category-checkbox:checked").length;
    $("#select-all-checkbox").prop("checked", all===checked);
    updateBulkDeleteVisibility();
});

    // show modal for single delete
    $(document).on("click", ".open-delete-order-modal", function(){
    const orderId = $(this).data("id");
    $("#deleteOrderForm").data("id", orderId);
    $("#deleteOrderModal").modal("show");
});

    // single delete submit
    $(document).on("submit", "#deleteOrderForm", function(e){
    e.preventDefault();
    const id = $(this).data("id");
    $.ajax({
    url: `/orders/${id}`,
    method: "DELETE",
    success: function(){
    $("#deleteOrderModal").modal("hide");
    Toastify({ text:"Order deleted successfully!", duration:2000, gravity:"top", position:"right", backgroundColor:"#28C76F", close:true }).showToast();
    $(".order-list-table").DataTable().ajax.reload(null, false);
    resetBulkSelection();
},
    error: function(){
    $("#deleteOrderModal").modal("hide");
    Toastify({ text:"Something Went Wrong!", duration:2000, gravity:"top", position:"right", backgroundColor:"#EA5455", close:true }).showToast();
    $(".order-list-table").DataTable().ajax.reload(null, false);
}
});
});

    /* ---------- bulk delete (orders, not invoices) ---------- */
    $(document).on("click", "#bulk-delete-btn", function(e){
    e.preventDefault();
    const ids = $(".category-checkbox:checked").map(function(){ return $(this).val(); }).get();
    if (ids.length===0){
    Toastify({ text:"Please select at least one order to delete!", duration:2000, gravity:"top", position:"right", backgroundColor:"#EA5455", close:true }).showToast();
    return;
}
    $("#deleteOrdersModal").modal("show");
});
    $(document).on("click", "#confirm-bulk-delete", function(){
    const ids = $(".category-checkbox:checked").map(function(){ return $(this).val(); }).get();
    if (ids.length>0) bulkDeleteOrders(ids);
});

    function bulkDeleteOrders(ids){
    $.ajax({
        url: "orders/bulk-delete",
        method: "POST",
        data: { ids, _token: $('meta[name="csrf-token"]').attr("content") },
        success: function(){
            $("#deleteOrdersModal").modal("hide");
            Toastify({ text:"Selected orders deleted successfully!", duration:2000, gravity:"top", position:"right", backgroundColor:"#28a745", close:true }).showToast();
            resetBulkSelection();
            $(".order-list-table").DataTable().ajax.reload(null, false);
        },
        error: function(){
            $("#deleteOrdersModal").modal("hide");
            Toastify({ text:"Something Went Wrong!", duration:2000, gravity:"top", position:"right", backgroundColor:"#EA5455", close:true }).showToast();
            resetBulkSelection();
            $(".order-list-table").DataTable().ajax.reload(null, false);
        }
    });
}

