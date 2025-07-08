const puppeteer = require('puppeteer');
const fs = require('fs');

const inputJsonPath = process.argv[2];
const outputPngPath = process.argv[3];

if (!inputJsonPath || !outputPngPath) {
    console.error('Usage: node renderFabric.js <input.json> <output.png>');
    process.exit(1);
}

(async () => {
    const rawJson = fs.readFileSync(inputJsonPath, 'utf8');
    let parsedJson;

    try {
        parsedJson = JSON.parse(rawJson);
    } catch (err) {
        console.error('❌ Invalid JSON:', err);
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        args: ['--no-sandbox', '--disable-setuid-sandbox'],
    });

    const page = await browser.newPage();
    const width = 1000;
    const height = 1000;

    await page.setViewport({ width, height });

    let renderDone;
    const renderPromise = new Promise(resolve => {
        renderDone = resolve;
    });

    await page.exposeFunction('notifyRendered', () => {
        renderDone();
    });

    const safeJsonString = JSON.stringify(parsedJson);

    await page.setContent(`
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body, html {
                    margin: 0;
                    padding: 0;
                    background: transparent;
                }
                canvas {
                    display: block;
                }
            </style>
        </head>
        <body>
            <canvas id="c" width="${width}" height="${height}"></canvas>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
            <script>
                const canvas = new fabric.Canvas('c');
                const json = ${safeJsonString};
                canvas.loadFromJSON(json, () => {
                    canvas.renderAll();
                    const waitForImages = () => {
                        const objects = canvas.getObjects();
                        const loading = objects.filter(obj =>
                            obj.type === 'image' && (!obj._element || !obj._element.complete)
                        );
                        if (loading.length > 0) {
                            setTimeout(waitForImages, 200);
                        } else {
                            canvas.renderAll();
                            setTimeout(() => window.notifyRendered(), 300);
                        }
                    };
                    waitForImages();
                });
            </script>
        </body>
        </html>
    `);

    await renderPromise;

    const element = await page.$('canvas');
    await element.screenshot({ path: outputPngPath, omitBackground: true });

    await browser.close();
    console.log('✅ PNG saved to', outputPngPath);
})();

