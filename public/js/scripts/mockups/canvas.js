// ------------------------------------------------------------
// FABRIC CANVAS HANDLING
// ------------------------------------------------------------

let canvasFront = null;
let canvasBack = null;
let canvasNone = null;

window.initializeCanvases = function () {
    canvasFront = new fabric.Canvas("mockupCanvasFront");
    canvasBack = new fabric.Canvas("mockupCanvasBack");
    canvasNone = new fabric.Canvas("mockupCanvasNone");

    [canvasFront, canvasBack, canvasNone].forEach(c => {
        c.freeDrawingBrush = new fabric.PencilBrush(c);
        c.preserveObjectStacking = true;
    });
};

// When a template is selected → load placement info
window.initializeTemplateEditors = function (template) {
    $("#editorFrontWrapper").toggleClass("d-none", !template.has_front);
    $("#editorBackWrapper").toggleClass("d-none", !template.has_back);
    $("#editorNoneWrapper").toggleClass("d-none", template.has_front || template.has_back);

    loadTemplateImageIntoCanvas(canvasFront, template.front_design);
    loadTemplateImageIntoCanvas(canvasBack, template.back_design);
};

// Load design into canvas
function loadTemplateImageIntoCanvas(canvas, url) {
    if (!url || !canvas) return;

    canvas.clear();

    fabric.Image.fromURL(url, img => {
        img.set({
            left: 50,
            top: 50,
            scaleX: 0.4,
            scaleY: 0.4,
            hasRotatingPoint: true,
            cornerStyle: "circle",
            transparentCorners: false
        });
        canvas.add(img).setActiveObject(img);
        canvas.renderAll();
    });
}

// Save placement as percentages
window.getCanvasPlacement = function (canvas) {
    const obj = canvas.getActiveObject();
    if (!obj) return null;

    return {
        left: obj.left / canvas.width,
        top: obj.top / canvas.height,
        scaleX: obj.scaleX,
        scaleY: obj.scaleY
    };
};
