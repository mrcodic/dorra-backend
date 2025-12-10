// ------------------------------------------------------------
// TEMPLATE SYSTEM
// ------------------------------------------------------------

let currentTemplateId = null;

// Load templates when a product is selected
window.loadTemplates = function (productId) {
    $("#templatesCardsWrapper").addClass("d-none");
    $("#templatesCardsContainer").html("");
    $("#templatesHiddenContainer").html("");
    $("#selectedTemplateId").val("");

    if (!productId) return;

    axios.get(`/templates/by-product/${productId}`)
        .then(res => renderTemplates(res.data))
        .catch(err => console.error("Error loading templates", err));
};

// Render cards + hidden templates
function renderTemplates(templates) {
    if (!templates.length) return;

    $("#templatesCardsWrapper").removeClass("d-none");

    templates.forEach(template => {
        let cardHtml = `
            <div class="card template-card rounded-3 shadow-sm cursor-pointer m-1"
                 style="width:150px;border: 1px solid #24B094;"
                 data-id="${template.id}">
                <img src="${template.front_image}" class="card-img-top" alt="template-preview">
                <div class="card-body p-1">
                    <h6 class="card-title text-center">${template.name}</h6>
                </div>
            </div>
        `;

        let hiddenHtml = `
            <input type="hidden" id="template_json_${template.id}"
                   value='${JSON.stringify(template)}'>
        `;

        $("#templatesCardsContainer").append(cardHtml);
        $("#templatesHiddenContainer").append(hiddenHtml);
    });
}

// Select a template card
$(document).on("click", ".template-card", function () {
    $(".template-card").removeClass("border-primary");
    $(this).addClass("border-primary");

    currentTemplateId = $(this).data("id");
    $("#selectedTemplateId").val(currentTemplateId);

    let templateData = JSON.parse($("#template_json_" + currentTemplateId).val());
    window.initializeTemplateEditors(templateData);
});
