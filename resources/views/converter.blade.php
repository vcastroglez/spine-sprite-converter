<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pixi.js/7.2.2/pixi.min.js"
            integrity="sha512-1NTvJOmsulWy01PqMyKBzkHmfkfZ7U1GUklB//Uqy3JRewFG+MzaFXfLF5tPrW8eELRX/Z6qzq1xsFb+rVG7Jw=="
            crossorigin="anonymous" referrerpolicy="no-referrer" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/pixi-spine@4.0.4/dist/pixi-spine.min.js" defer></script>
    <title>Spine -> Sprite exporter</title>
    <style>
        #sizer {
            height: 4829px;
            width: 1970px;
            left: 0;
            background: transparent;
        }

        #body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            background: transparent;
        }

        .againBtn {
            display: flex;
            border: 1px solid teal;
            padding: 15px;
            text-decoration: none;
            flex-shrink: 1;
            cursor: pointer;
        }

        .container {
            margin-top: 50px;
            display: flex;
            flex-direction: column;
            justify-items: center;
            align-items: center;
            align-content: center;
        }

        .row {
            margin-top: 50px;
            flex-direction: row;
            gap: 20px;
            width: 100%;
            justify-content: center;
        }

        .flex {
            display: flex;
            justify-content: center;
            gap: 3px;
        }

        .flex > input {
            width: 90px;
        }

        canvas {
            border: 1px solid lime;
        }

        #frames{
            width: 30px;
        }
    </style>
</head>
<body id="body" style="background: transparent">
<div class="container row">
    <div class="flex">
        With:
        <button class="btn-size width l">-</button>
        <input type="number" id="real_width" name="real_width" value="275" min="0"/>
        <button class="btn-size width p">+</button>
    </div>
    <div class="flex">
        Height:<span id="height-span"></span>
        <button class="btn-size height l">-</button>
        <input type="number" id="real_height" name="real_height" value="421" min="0"/>
        <button class="btn-size height p">+</button>
    </div>
</div>
<div class="container">
    <span id="position-span"></span>
    <div class="sizer" id="sizer"></div>
    <div class="container row">
        <a href="/" class="againBtn ignore" id="againBtn">Export another</a>
        <label for="frames">Frames:
            <input type="number" id="frames" name="frames" value="20" min="0"/>
        </label>
        <a id="exportSprite" class="againBtn">Export sprite</a>
        <a id="center-spine" class="againBtn">Center</a>
    </div>
    <h3>You can move the spine and change the size of the canvas before exporting</h3>
    <div id="shower"></div>
</div>
<script type="module" defer>
    let width = 275;
    let height = 421;
    const frames = 20;
    const asset_path = "{{$asset_path}}";
    const sizer = document.getElementById('sizer');
    const heightSpan = document.getElementById('height-span');
    const positionSpan = document.getElementById('position-span');


    let app = {};
    let canvas = null
    let savedSpineAsset = {}
    let spine = {}

    let initialPosition = {x: width * 0.5, y: height}
    let spineInitial = {x: 0, y: 0}
    let offset = {x: 0, y: 0}
    let isPressed = false;

    const centerSpine = () => {
        initialPosition = {x: width * 0.5, y: height}
        showAnimation()
    }

    const updateSpanText = () => {
        document.getElementById('real_width').value = canvas.width;
        document.getElementById('real_height').value = canvas.height;
        positionSpan.innerHTML = `${JSON.stringify({x: spine.x, y: spine.y})}`
    }

    const moveSpine = (event, type = 'down') => {
        const x = event.x;
        const y = event.y;

        if (type === 'down') {
            isPressed = true;
            initialPosition.x = x;
            initialPosition.y = y;
            spineInitial.x = spine.x;
            spineInitial.y = spine.y;
        }
        if (type === 'up') {
            isPressed = false;
            initialPosition = {x: spineInitial.x - offset.x, y: spineInitial.y - offset.y}
        }

        if (isPressed) {
            offset.x = (initialPosition.x - x);
            offset.y = (initialPosition.y - y);
            spine.x = spineInitial.x - offset.x;
            spine.y = spineInitial.y - offset.y;
            updateSpanText()
        }
    }

    const createCanvasListeners = () => {
        canvas.addEventListener('mousedown', function (event) {
            moveSpine(event, 'down');
        });

        canvas.addEventListener('mouseup', function (event) {
            moveSpine(event, 'up');
        });

        canvas.addEventListener('mousemove', function (event) {
            moveSpine(event, 'move');
        });
    }

    const showAnimation = () => {
        if (canvas) canvas.remove();
        sizer.style.width = `${width}px`;
        sizer.style.height = `${height}px`;
        app = new PIXI.Application({
            height: height,
            width: width,
            backgroundAlpha: 0,
        });

        canvas = app.view;
        createCanvasListeners()
        PIXI.Assets.load(asset_path).then(onAssetsLoaded);

        function onAssetsLoaded(spineAsset) {
            savedSpineAsset = spineAsset
            app.stage.eventMode = 'dynamic';
            spine = new PIXI.spine.Spine(spineAsset.spineData);
            spine.x = initialPosition.x;
            spine.y = initialPosition.y;
            app.stage.addChild(spine);
            spine.state.setAnimation(0, 'Idle', true);
            sizer.appendChild(canvas);
            updateSpanText()
        }

        updateSpanText()
    }

    const resizeCanvas = (event) => {
        let p = event.target.matches('.p')
        let h = event.target.matches('.height');

        let value = 5 * (p ? 1 : -1);
        if (h) {
            height += value;
        } else {
            width += value;
        }

        showAnimation()
        updateSpanText()
    }

    const exportSprite = () => {
        captureSpineFrames(app, spine, frames).then((frames) => {
            createAndDownloadSpriteSheet(frames);
        });
    }

    function createAndDownloadSpriteSheet(images, fileName = 'sprite_sheet.png') {
        // Create a canvas large enough to hold all images
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');

        // Assuming all images are the same size, get dimensions from the first image
        const img = new Image();
        img.onload = () => {
            const frameWidth = img.width;
            const frameHeight = img.height;
            const framesPerRow = Math.ceil(Math.sqrt(images.length)); // Square-ish layout

            canvas.width = frameWidth * framesPerRow;
            canvas.height = frameHeight * Math.ceil(images.length / framesPerRow);

            // Load and draw each image onto the canvas
            let loadedImages = 0;
            images.forEach((imageData, index) => {
                const img = new Image();
                img.onload = () => {
                    const row = Math.floor(index / framesPerRow);
                    const col = index % framesPerRow;
                    ctx.drawImage(img, col * frameWidth, row * frameHeight);

                    loadedImages++;
                    if (loadedImages === images.length) {
                        // All images have been drawn, now download the sprite sheet
                        const link = document.createElement('a');
                        link.className = 'ignore';
                        link.href = canvas.toDataURL('image/png');
                        link.download = fileName;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                };
                img.src = imageData;
            });
        };
        img.src = images[0]; // Load the first image to get dimensions
    }

    function captureSpineFrames(app, spine, numFrames = 6) {
        return new Promise((resolve) => {
            const frames = [];
            const duration = spine.state.tracks[0].animation.duration;
            const interval = duration / (numFrames - 1);

            function captureFrame(index) {
                // Set the spine animation to the current time
                spine.state.tracks[0].trackTime = index * interval;

                // Update the spine
                spine.update(0);

                // Render the current frame
                app.render();

                // Create a new canvas and copy the content
                const frameCanvas = document.createElement('canvas');
                frameCanvas.width = app.view.width;
                frameCanvas.height = app.view.height;
                frameCanvas.getContext('2d').drawImage(app.view, 0, 0);

                // Convert the canvas to base64 and store it
                frames.push(frameCanvas.toDataURL());

                if (index < numFrames - 1) {
                    // Capture the next frame on the next animation frame
                    requestAnimationFrame(() => captureFrame(index + 1));
                } else {
                    // Reset the animation
                    spine.state.tracks[0].trackTime = 0;
                    spine.update(0);
                    app.render();
                    resolve(frames);
                }
            }

            // Start capturing frames
            captureFrame(0);
        });
    }

    const attachImagesToContainer = (base64Array, containerId) => {
        // Get the container element
        const container = document.getElementById(containerId);

        // Iterate through the array of base64 data URLs
        base64Array.forEach(base64String => {
            // Create a new image element
            const img = document.createElement('img');

            // Set the src attribute to the base64 data URL
            img.src = base64String;

            // Optionally, you can add additional attributes or styles
            img.alt = 'Generated Image';
            img.style.margin = '5px';
            img.style.opacity = 0;

            // Append the image to the container
            container.appendChild(img);
        });
    }

    document.getElementById('real_width').addEventListener('change', (event) => {
        width = parseInt(event.target.value);
        showAnimation()
    })

    document.getElementById('real_height').addEventListener('change', (event) => {
        height = parseInt(event.target.value);
        showAnimation()
    })

    document.getElementById('exportSprite').addEventListener('click', exportSprite);
    document.getElementById('center-spine').addEventListener('click', centerSpine);
    document.querySelectorAll('.btn-size').forEach(element => {
        element.addEventListener('click', (event) => {
            resizeCanvas(event)
        })
    })
    showAnimation()
</script>
</body>
</html>
