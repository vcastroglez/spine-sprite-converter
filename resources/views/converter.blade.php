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
    <title>Document</title>
    <style>
        #sizer {
            height: 4829px;
            width: 1970px;
            top: 150px;
            left: 0;
            background: transparent;
        }

        #body {
            height: 100%;
            width: fit-content;
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
        }

        .container {
            margin-top: 200px;
            display: flex;
            flex-direction: column;
            justify-items: center;
            align-items: center;
        }
    </style>
</head>
<body id="body" style="background: transparent">
<div class="container">
    <a href="/" class="againBtn">Export again</a>
    <p>
    <h3>Your sprite download should have started automatically, if not, then check the page permissions.</h3>
    </p>
    <div class="sizer" id="sizer"></div>
    <div id="shower"></div>
</div>
<script type="module" defer>
    const scale = {{$scale}};
    const height = {{$height}};
    const width = {{$width}};
    const frames = {{$frames}};
    const asset_path = "{{$asset_path}}";
    const has_animation = {{$has_animation?'true':'false'}};
    const sizer = document.getElementById('sizer');
    const real_height = {{$real_height}};
    const real_width = {{$real_width}};
    const scaled_height = real_height * scale;
    const scaled_width = real_width * scale;

    if (!has_animation) {
        const img = document.createElement('img');
        img.src = asset_path;
        img.alt = 'Recruit pose';
        img.style.width = `${scaled_width}px`;
        img.style.height = `${scaled_height}px`;

        sizer.appendChild(img);
    } else {
        showAnimation()
    }
    sizer.style.width = `${scaled_width}px`;
    sizer.style.height = `${scaled_height}px`;
    sizer.style.transformOrigin = `top left`

    function showAnimation() {
        const app = new PIXI.Application({
            height: scaled_height,
            width: scaled_width,
            backgroundAlpha: 0,
        });

        let canvas = app.view;

        const sizerSize = sizer.getBoundingClientRect();
        document.getElementById('body').style.width = sizerSize.width;
        document.getElementById('body').style.height = sizerSize.height;

        PIXI.Assets.load(asset_path).then(onAssetsLoaded);

        function onAssetsLoaded(spineAsset) {
            app.stage.eventMode = 'dynamic';
            const spine = new PIXI.spine.Spine(spineAsset.spineData);
            spine.x = scaled_width / 2;
            spine.y = scaled_height / 2;
            spine.scale.set(scale);
            app.stage.addChild(spine);
            spine.state.setAnimation(0, 'Idle', true);
            captureSpineFrames(app, spine, frames).then((frames) => {
                attachImagesToContainer(frames, 'shower')
                createAndDownloadSpriteSheet(frames);
                // Here you can use the frames array, which contains base64 data URLs
                // You can display them, download them, or process them further
            });
            sizer.appendChild(canvas);
        }
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
</script>
</body>
</html>
