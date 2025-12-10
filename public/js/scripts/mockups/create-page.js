// ------------------------------------------------------------
// CREATE PAGE CONTROLLER
// ------------------------------------------------------------

$(document).ready(function () {

    // Initialize canvases
    window.initializeCanvases();

    // Load templates when a product changes
    $("#productsSelect").on("change", function () {
        const productId = $(this).val();
        window.loadTemplates(productId);
    });

    // Form submission
    $("#addMockupForm").on("submit", function (e) {
        e.preventDefault();

        $(".saveLoader").removeClass("d-none");
        $(".btn-text").addClass("d-none");

        // Extract placement
        const placement = {
            front: window.getCanvasPlacement(canvasFront),
            back: window.getCanvasPlacement(canvasBack),
            none: window.getCanvasPlacement(canvasNone),
        };

        // Append placement to form
        $("<input>", {
            type: "hidden",
            name: "placement",
            value: JSON.stringify(placement)
        }).appendTo(this);

        this.submit();
    });
});
