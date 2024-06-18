<?php
/**
 * Plugin Name: Custom Image Editor
 * Description: A custom image editor allowing users to upload, edit, crop, and download images.
 * Version: 1.0
 * Author: Masum Billah 
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CustomImageEditor {

    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('custom_image_editor', array($this, 'render_editor'));
    }

    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('bootstrap-js',plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
        wp_enqueue_script('fabric-js', plugin_dir_url(__FILE__) . 'assets/js/fabric.min.js', array(), time(), true);
        wp_enqueue_script('cropper-js', plugin_dir_url(__FILE__) . 'assets/js/cropper.min.js', array(), time(), true);
        wp_enqueue_script('file-saver-js', plugin_dir_url(__FILE__) . 'assets/js/FileSaver.min.js', array(), time(), true);
        wp_enqueue_style('bootstrap-css', plugin_dir_url(__FILE__) .'assets/css/bootstrap.min.css', array(), time(), 'all');
        wp_enqueue_style('fontawesome-css', plugin_dir_url(__FILE__) . 'assets/css/all.min.css', array(), time(), 'all');
        wp_enqueue_style('cropper-css', plugin_dir_url(__FILE__) . 'assets/css/cropper.min.css', array(), time(), 'all');
        wp_enqueue_style('editor-css', plugins_url('style.css', __FILE__));
    }

    public function render_editor() {
        ob_start();
        ?>
        <div id="image-editor" class="container">
            <div class="row mb-3">
                <div class="col">
                    <input type="file" id="imageUpload" class="form-control-file" accept="image/*">
                </div>
            </div>
            <div class="row mb-3" id="editor-controls">
                <div class="col">
                    <button id="addText" class="btn btn-primary"><i class="fas fa-font"></i> Add Text</button>
                    <button id="cropImage" class="btn btn-warning"><i class="fas fa-crop-alt"></i> Crop</button>
                    <button id="undoAction" class="btn btn-danger"><i class="fas fa-undo"></i> Undo</button>
                    <button id="downloadImage" class="btn btn-success"><i class="fas fa-download"></i> Download</button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label>Text Size:</label>
                    <input type="number" id="textSize" class="form-control" value="20">
                </div>
                <div class="col">
                    <label>Font Style:</label>
                    <select id="fontStyle" class="form-control">
                        <!-- Add more options as needed -->
                        <option value="Arial">Arial</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Comic Sans MS">Comic Sans MS</option>
                        <option value="Impact">Impact</option>
                        <option value="Tahoma">Tahoma</option>
                        <option value="Trebuchet MS">Trebuchet MS</option>
                        <option value="Brush Script MT">Brush Script MT</option>
                        <option value="Garamond">Garamond</option>
                        <option value="Palatino">Palatino</option>
                        <option value="Lucida Handwriting">Lucida Handwriting</option>
                        <option value="Copperplate">Copperplate</option>
                        <option value="Futura">Futura</option>
                        <option value="Gill Sans">Gill Sans</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Rockwell">Rockwell</option>
                        <option value="Segoe Script">Segoe Script</option>
                        <option value="Perpetua">Perpetua</option>
                    </select>
                </div>
                <div class="col">
                    <label>Font Weight:</label>
                    <select id="fontWeight" class="form-control">
                        <option value="normal">Normal</option>
                        <option value="bold">Bold</option>
                        <option value="bolder">Bolder</option>
                        <option value="lighter">Lighter</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="300">300</option>
                        <option value="400">400</option>
                        <option value="500">500</option>
                        <option value="600">600</option>
                        <option value="700">700</option>
                        <option value="800">800</option>
                        <option value="900">900</option>
                    </select>
                </div>
                <div class="col">
                    <label>Font Color:</label>
                    <input type="color" id="fontColor" class="form-control" value="#000000">
                </div>
                <div class="col">
                    <label>Text Background:</label>
                    <input type="color" id="textBackgroundColor" class="form-control" value="#FFFFFF">
                    <button id="transparentBackground" class="btn btn-secondary mt-2">Transparent</button>
                </div>
            </div>
            <div class="row">
                <div class="col" id="canvas-container">
                    <canvas id="imageCanvas"></canvas>
                </div>
                <div class="col" id="cropper-container" style="display:none;">
                    <img id="cropperImage">
                    <button id="cropButton" class="btn btn-primary">Crop</button>
                </div>
            </div>
        </div>
        <script>
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
                    canvas.toBlob(function(blob) {
                        saveAs(blob, 'edited-image.png');
                    });
                });

                $('#undoAction').on('click', function() {
                    if (stateHistory.length > 1) {
                        stateHistory.pop();
                        const previousState = stateHistory[stateHistory.length - 1];
                        canvas.loadFromJSON(previousState, canvas.renderAll.bind(canvas));
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}

new CustomImageEditor();
