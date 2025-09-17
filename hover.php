<?php
/*
Plugin Name: Liquid Hover Effect Manager
Description: Admin can add multiple liquid hover effect items with unique shortcodes, each with its own aspect ratio. Uses media library for thumbnails.
Version: 3.4
Author: Hassan
*/

if (!defined('ABSPATH')) exit;

/* ------------------------------
 * FRONTEND ASSETS
 * ------------------------------ */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('liquid-hover-style', plugin_dir_url(__FILE__) . 'style.css');
    wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r122/three.min.js', [], null, true);
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/gsap.min.js', [], null, true);
    wp_enqueue_script('hover-effect', 'https://unpkg.com/hover-effect.1.1.0/dist/hover-effect.umd.js', ['three-js','gsap'], null, true);
    wp_enqueue_script('liquid-hover-script', plugin_dir_url(__FILE__) . 'script.js', ['hover-effect'], '3.4', true);
});

/* ------------------------------
 * ADMIN MENU + ASSETS
 * ------------------------------ */
add_action('admin_menu', function () {
    add_menu_page(
        'Liquid Hover Manager',
        'Liquid Hover',
        'manage_options',
        'liquid-hover-manager',
        'liquid_hover_settings_page',
        'dashicons-images-alt2',
        20
    );
});

add_action('admin_enqueue_scripts', function($hook){
    if ($hook === 'toplevel_page_liquid-hover-manager') {
        wp_enqueue_media(); // WP Media uploader
        wp_enqueue_style('liquid-hover-admin', plugin_dir_url(__FILE__) . 'admin.css');
        wp_enqueue_script('liquid-hover-admin', plugin_dir_url(__FILE__) . 'admin.js', ['jquery'], '3.4', true);
    }
});

add_action('admin_init', function () {
    register_setting('liquid_hover_group', 'liquid_hover_items');
});

/* ------------------------------
 * ADMIN PAGE
 * ------------------------------ */
function liquid_hover_settings_page() {
    $items = get_option('liquid_hover_items', []); ?>
    <div class="wrap">
        <h1>Liquid Hover Effect Manager</h1>
        <form method="post" action="options.php">
            <?php settings_fields('liquid_hover_group'); ?>
            <table class="form-table widefat" id="liquid-hover-table">
                <thead>
                    <tr>
                        <th>Image 1</th>
                        <th>Image 2</th>
                        <th>Aspect Ratio</th>
                        <th>Shortcode</th>
                        <th>Remove</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(!empty($items)): ?>
                    <?php foreach($items as $index => $item): 
                        $img1_id = $item['image1'] ?? '';
                        $img2_id = $item['image2'] ?? '';
                        $img1_url = $img1_id ? wp_get_attachment_thumb_url($img1_id) : '';
                        $img2_url = $img2_id ? wp_get_attachment_thumb_url($img2_id) : '';
                        ?>
                        <tr>
                            <td>
                                <div class="media-preview">
                                    <?php if($img1_url): ?>
                                        <img src="<?php echo esc_url($img1_url); ?>" class="thumb" />
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="liquid_hover_items[<?php echo $index; ?>][image1]" value="<?php echo esc_attr($img1_id); ?>" />
                                <button class="button select-media">Select Image</button>
                            </td>
                            <td>
                                <div class="media-preview">
                                    <?php if($img2_url): ?>
                                        <img src="<?php echo esc_url($img2_url); ?>" class="thumb" />
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="liquid_hover_items[<?php echo $index; ?>][image2]" value="<?php echo esc_attr($img2_id); ?>" />
                                <button class="button select-media">Select Image</button>
                            </td>
                            <td>
                                <select name="liquid_hover_items[<?php echo $index; ?>][aspect_ratio]">
                                    <?php 
                                    // ✅ No auto, default 1/1
                                    $ratios = ['16/9','4/3','1/1','21/9'];
                                    $current = $item['aspect_ratio'] ?? '1/1';
                                    foreach($ratios as $ratio){
                                        echo '<option value="'.$ratio.'" '.selected($current,$ratio,false).'>'.$ratio.'</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><code>[liquid_hover id="<?php echo $index; ?>"]</code></td>
                            <td><button type="button" class="button remove-row">X</button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <p><button type="button" class="button button-primary" id="add-row">+ Add Item</button></p>
            <?php submit_button(); ?>
        </form>
    </div>
<?php }

/* ------------------------------
 * SHORTCODE
 * ------------------------------ */
add_shortcode('liquid_hover', function ($atts) {
    $atts = shortcode_atts(['id' => ''], $atts, 'liquid_hover');
    $items = get_option('liquid_hover_items', []);
    $id = intval($atts['id']);
    if (!isset($items[$id])) return '';

    $image1_id = $items[$id]['image1'] ?? '';
    $image2_id = $items[$id]['image2'] ?? '';
    $image1 = $image1_id ? wp_get_attachment_image_url($image1_id, 'large') : '';
    $image2 = $image2_id ? wp_get_attachment_image_url($image2_id, 'large') : '';
    $aspect = $items[$id]['aspect_ratio'] ?? '1/1'; // ✅ Default is 1/1

    $displacement = 'https://i.postimg.cc/28jsM5QJ/4.png';

    ob_start(); ?>
    <section class="liquid-hover-section">
        <div class="container-mn">
            <div class="img1 img"
                data-image1="<?php echo esc_url($image1); ?>"
                data-image2="<?php echo esc_url($image2); ?>"
                data-displacement="<?php echo esc_url($displacement); ?>"
                data-aspect="<?php echo esc_attr($aspect); ?>">
            </div>
        </div>
    </section>
    <?php
    return ob_get_clean();
});
