const puppeteer = require('puppeteer');
const fs = require('fs');

const inputJsonPath = process.argv[2];
const outputPngPath = process.argv[3];

(async () => {
    const jsonData = fs.readFileSync(inputJsonPath, 'utf8');
    const width = 800;  // You can later make this dynamic
    const height = 600;

    const browser = await puppeteer.launch({
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });
    const page = await browser.newPage();

    await page.setViewport({ width, height });

    // Expose a function for the browser to call when done rendering
    let renderDone;
    const renderPromise = new Promise(resolve => {
        renderDone = resolve;
    });

    await page.exposeFunction('notifyRendered', () => {
        console.log('✅ Fabric canvas reported it finished rendering');
        renderDone();
    });

    await page.setContent(`
        <html>
        <body style="margin:0">
            <canvas id="c" width="${width}" height="${height}"></canvas>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
            <script>
                const canvas = new fabric.Canvas('c', { width: ${width}, height: ${height} });
                const json = ${JSON.stringify(jsonData)};
                canvas.loadFromJSON(JSON.parse(json), () => {
                    canvas.renderAll();
                    window.notifyRendered();
                });
            </script>
        </body>
        </html>
    `);

    // Wait for notifyRendered to be called
    await renderPromise;

    const element = await page.$('canvas');
    await element.screenshot({ path: outputPngPath });

    await browser.close();

    console.log('✅ Rendered PNG saved to', outputPngPath);
})();
