<?php
/**
 * Plugin Name: Custom Image Editor
 * Description: A custom image editor allowing users to upload, edit, and download images.
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
        wp_enqueue_script('toastr-js', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js', array(), '2.1.4', true);
        wp_enqueue_style('toastr-css', 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css', array(), '2.1.4');

        wp_enqueue_script('bootstrap-js',plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js', array('jquery'), '4.5.2', true);
        wp_enqueue_script('fabric-js', plugin_dir_url(__FILE__) .'assets/js/fabric.min.js', array(), '5.3.1', true);
        wp_enqueue_style('bootstrap-css', plugin_dir_url(__FILE__) .'assets/css/bootstrap.min.css', array(), '4.5.2');
        wp_enqueue_style('fontawesome-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4');
        wp_enqueue_script('editor-js', plugins_url('app.js', __FILE__));
        wp_enqueue_style('editor-css', plugins_url('style.css', __FILE__));
    }

    public function render_editor() {
        ob_start();
        ?>
        <div id="image-editor" class="container">
            <div class="row mb-3">
                <div class="col">
                    <label for="imageUpload">Upload an Image</label>
                    <input type="file" id="imageUpload" class="form-control-file" accept="image/*">
                </div>
            </div>
            <div class="row mb-3" id="editor-controls">
                <div class="col">
                    <button id="addText" class="btn btn-primary"><i class="fas fa-font"></i> Add Text</button>
                    <button id="undoAction" class="btn btn-danger"><i class="fas fa-undo"></i> Undo</button>
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
                        <option value="Arial" style="font-family: Arial, sans-serif;">Arial</option>
                        <option value="Courier New" style="font-family: 'Courier New', Courier, monospace;">Courier New</option>
                        <option value="Georgia" style="font-family: Georgia, serif;">Georgia</option>
                        <option selected value="Times New Roman" style="font-family: 'Times New Roman', Times, serif;">Times New Roman</option>
                        <option value="Verdana" style="font-family: Verdana, Geneva, sans-serif;">Verdana</option>
                        <option value="Comic Sans MS" style="font-family: 'Comic Sans MS', cursive;">Comic Sans MS</option>
                        <option value="Impact" style="font-family: Impact, Charcoal, sans-serif;">Impact</option>
                        <option value="Tahoma" style="font-family: Tahoma, Geneva, sans-serif;">Tahoma</option>
                        <option value="Trebuchet MS" style="font-family: 'Trebuchet MS', Helvetica, sans-serif;">Trebuchet MS</option>
                        <option value="Brush Script MT" style="font-family: 'Brush Script MT', cursive;">Brush Script MT</option>
                        <option value="Garamond" style="font-family: Garamond, serif;">Garamond</option>
                        <option value="Palatino" style="font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, serif;">Palatino</option>
                        <option value="Lucida Handwriting" style="font-family: 'Lucida Handwriting', 'Lucida Sans', 'Arial', sans-serif;">Lucida Handwriting</option>
                        <option value="Copperplate" style="font-family: Copperplate, 'Copperplate Gothic Bold', fantasy;">Copperplate</option>
                        <option value="Futura" style="font-family: Futura, 'Trebuchet MS', Arial, sans-serif;">Futura</option>
                        <option value="Gill Sans" style="font-family: 'Gill Sans', 'Gill Sans MT', Calibri, sans-serif;">Gill Sans</option>
                        <option value="Helvetica" style="font-family: Helvetica, Arial, sans-serif;">Helvetica</option>
                        <option value="Rockwell" style="font-family: Rockwell, 'Courier Bold', serif;">Rockwell</option>
                        <option value="Segoe Script" style="font-family: 'Segoe Script', 'Segoe UI', Arial, sans-serif;">Segoe Script</option>
                        <option value="Perpetua" style="font-family: Perpetua, 'Palatino Linotype', 'Book Antiqua', serif;">Perpetua</option>
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
                    <label>Background:</label>
                    <input type="color" id="textBackgroundColor" class="form-control" value="#FFFFFF">
                    <button id="transparentBackground" class="btn btn-secondary mt-2">Transparent</button>
                </div>
            </div>
            <div class="row">
                <div class="col" id="canvas-container">
                    <canvas id="imageCanvas"></canvas>
                </div>
            </div>
            <div class="row mt-3" id="editor-controls">
                <div class="col">
                    <button id="downloadImage" class="btn btn-success"><i class="fas fa-download"></i> Download</button>
                    <button class="btn btn-info share-btn" data-social="copy-link"><i class="fas fa-copy"></i> Copy Link</button>
                </div>
            </div>
        </div>
       
        <?php
        return ob_get_clean();
    }
}

new CustomImageEditor();
