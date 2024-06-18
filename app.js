
jQuery(document).ready(function($) {
    const canvas = new fabric.Canvas('imageCanvas');
    let cropper;
    const stateHistory = [];

    const saveState = () => {
        stateHistory.push(JSON.stringify(canvas));
    };

    const removeObject = (object) => {
        canvas.remove(object);
        saveState();
    };

    $('#imageUpload').on('change', function(e) {
        const reader = new FileReader();
        reader.onload = function(event) {
            fabric.Image.fromURL(event.target.result, function(img) {
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height
                });
                saveState();
            });
        }
        reader.readAsDataURL(e.target.files[0]);
    });

    $('#addText').on('click', function() {
        const textSize = $('#textSize').val();
        const fontStyle = $('#fontStyle').val();
        const fontWeight = $('#fontWeight').val();
        const fontColor = $('#fontColor').val();
        const textBackgroundColor = $('#textBackgroundColor').val();

        const text = new fabric.IText('Sample Text', {
            left: 50,
            top: 50,
            fontFamily: fontStyle,
            fontWeight: fontWeight,
            fill: fontColor,
            fontSize: parseInt(textSize),
            backgroundColor: textBackgroundColor
        });

        canvas.add(text);
        canvas.setActiveObject(text);
        saveState();
    });

    $('#textSize, #fontStyle, #fontWeight, #fontColor, #textBackgroundColor').on('input change', function() {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set({
                fontSize: parseInt($('#textSize').val()),
                fontFamily: $('#fontStyle').val(),
                fontWeight: $('#fontWeight').val(),
                fill: $('#fontColor').val(),
                backgroundColor: $('#textBackgroundColor').val()
            });
            canvas.renderAll();
            saveState();
        }
    });

    $('#transparentBackground').on('click', function() {
        const activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'i-text') {
            activeObject.set('backgroundColor', 'transparent');
            canvas.renderAll();
            saveState();
        }
    });

    $('#cropImage').on('click', function() {
        const dataURL = canvas.toDataURL();
        $('#cropperImage').attr('src', dataURL);
        $('#canvas-container').hide();
        $('#cropper-container').show();
        cropper = new Cropper(document.getElementById('cropperImage'), {
            aspectRatio: NaN
        });
    });

    $('#cropButton').on('click', function() {
        const croppedCanvas = cropper.getCroppedCanvas();
        const croppedImage = croppedCanvas.toDataURL();
        fabric.Image.fromURL(croppedImage, function(img) {
            canvas.clear();
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: canvas.width / img.width,
                scaleY: canvas.height / img.height
            });
            saveState();
        });
        $('#canvas-container').show();
        $('#cropper-container').hide();
        cropper.destroy();
    });

    $('#downloadImage').on('click', function() {
        var dataURL = canvas.toDataURL('image/png'); // Other options: 'image/jpeg' for JPEG format

        // Create a temporary anchor element
        var link = document.createElement('a');
        link.download = 'canvas-image.png'; // Set the file name
        link.href = dataURL;

        // Trigger the download
        document.body.appendChild(link); // Append the anchor element to the DOM
        link.click(); // Programmatically click the download link

        // Clean up
        document.body.removeChild(link);
                        });

    $('#undoAction').on('click', function() {
        if (stateHistory.length > 1) {
            stateHistory.pop();
            const previousState = stateHistory[stateHistory.length - 1];
            canvas.loadFromJSON(previousState, canvas.renderAll.bind(canvas));
        }
    });
     // Social media sharing buttons
     $('.share-btn').on('click', function() {
        const social = $(this).data('social');
        const dataURL = canvas.toDataURL('image/png'); // Generate the image data URL

        switch (social) {
            case 'copy-link':
                const tempInput = document.createElement('input');
                tempInput.value = dataURL;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
               // Use Toastr for notification
                toastr.success('Image link copied to clipboard!', 'Success', {
                    timeOut: 3000, // 1 second
                    progressBar: true,
                    closeButton: true
                });
                break;
            default:
                break;
        }
    });
});
