// ------------------------------------------------------------
// FILE UPLOAD HANDLING
// ------------------------------------------------------------

// Show upload blocks when type is checked
$(".type-checkbox").on("change", function () {
    const typeName = $(this).data("type-name");

    if (this.checked) {
        appendUploadBlock(typeName);
    } else {
        $("#upload_block_" + typeName).remove();
    }
});

// Append upload block
function appendUploadBlock(typeName) {
    if ($("#upload_block_" + typeName).length) return;

    const html = `
        <div id="upload_block_${typeName}" class="border rounded p-2 mb-2">
            <label class="mb-1 fw-bold text-capitalize">${typeName} Images</label>

            <input type="file" name="mockups[${typeName}][base]" class="form-control mb-2"
                   accept="image/*">

            <input type="file" name="mockups[${typeName}][mask]" class="form-control"
                   accept="image/*">
        </div>
    `;

    $("#fileInputsContainer").append(html);
}

// Main mockup image (featured)
$("#templateImageInput").on("change", function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => $("#preview-main-mockup").attr("src", e.target.result);
    reader.readAsDataURL(file);
});
