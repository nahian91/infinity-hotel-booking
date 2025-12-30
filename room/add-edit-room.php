<?php
/**
 * INFINITY HOTEL - COMPLETE ROOM EDITOR (Integrated & Fixed)
 * Features: Image Sync, Global Settings Integration, Facility Toggle
 */
if (!defined('ABSPATH')) exit;

// --- 1. DATA SAVING LOGIC ---
$show_success = false;
if (isset($_POST['ihb_save_room_action']) && current_user_can('edit_posts')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_room_save')) {
        
        $room_id  = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
        $room_no  = isset($_POST['room_no']) ? sanitize_text_field($_POST['room_no']) : ''; 
        $room_img = isset($_POST['room_image']) ? intval($_POST['room_image']) : 0;
        
        $post_args = [
            'post_title'   => 'Room ' . $room_no,
            'post_status'  => 'publish',
            'post_type'    => 'ihb_rooms',
            'post_content' => isset($_POST['room_desc']) ? wp_kses_post($_POST['room_desc']) : ''
        ];

        // Create or Update Post
        if ($room_id > 0) {
            $post_args['ID'] = $room_id;
            wp_update_post($post_args);
        } else {
            $room_id = wp_insert_post($post_args);
        }

        if ($room_id) {
            // Update Meta Fields
            update_post_meta($room_id, '_ihb_type', sanitize_text_field($_POST['room_type']));
            update_post_meta($room_id, '_ihb_price', sanitize_text_field($_POST['price']));
            
            // CRITICAL FIX: Save as Featured Image so it shows in all views
            update_post_meta($room_id, '_ihb_room_image', $room_img);
            if ($room_img > 0) {
                set_post_thumbnail($room_id, $room_img);
            } else {
                delete_post_thumbnail($room_id);
            }

            // Save Amenities
            $features = isset($_POST['room_features']) ? (array)$_POST['room_features'] : [];
            update_post_meta($room_id, '_ihb_features', $features);
            
            $show_success = true;
            // Update local ID if we just created a new post
            $id = $room_id; 
        }
    }
}

// --- 2. DATA RETRIEVAL ---
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($id) ? $id : 0);
$post_obj         = get_post($id);
$current_no       = $post_obj ? str_replace('Room ', '', $post_obj->post_title) : '';
$current_desc     = $post_obj ? $post_obj->post_content : '';
$current_type     = get_post_meta($id, '_ihb_type', true);
$current_price    = get_post_meta($id, '_ihb_price', true);
$current_features = get_post_meta($id, '_ihb_features', true) ?: [];

// Get correct Image ID (Featured Image or Custom Meta)
$current_img_id   = get_post_thumbnail_id($id) ?: get_post_meta($id, '_ihb_room_image', true);
$current_img_url  = $current_img_id ? wp_get_attachment_url($current_img_id) : '';

// --- 3. GLOBAL SETTINGS LOAD ---
$saved_types   = get_option('ihb_room_types', ['Standard', 'Deluxe', 'Suite']);
$saved_numbers = get_option('ihb_room_numbers', ['101', '102', '103']);
$enabled_facs  = get_option('ihb_enabled_facilities', ['wifi', 'ac', 'tv', 'bar', 'safe', 'service']);

$facilities_master = [
    'wifi' => ['label' => 'High-Speed WiFi', 'path' => 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    'ac'   => ['label' => 'Climate Control', 'path' => 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    'tv'   => ['label' => 'Entertainment TV', 'path' => 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    'bar'  => ['label' => 'Mini Bar', 'path' => 'M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2H7M7,4V10H17V4H7M7,12V20H17V12H7M13,14V18H15V14H13Z'],
    'safe' => ['label' => 'Personal Safe', 'path' => 'M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.66,7 15,8.34 15,10C15,11.66 13.66,13 12,13C10.34,13 9,11.66 9,10C9,8.34 10.34,7 12,7Z'],
    'service' => ['label' => 'Room Service', 'path' => 'M11,9H13V11H11V9M11,13H13V17H11V13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z']
];
?>

<style>
    :root { --gold: #c19b76; --dark: #1e293b; --slate: #64748b; --border: #e2e8f0; --bg: #f8fafc; }
    
    .ihb-wrapper { padding: 20px; max-width: 1100px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
    
    /* Header Section */
    .ihb-header-modern {
        display: flex; justify-content: space-between; align-items: center;
        padding: 24px 30px; background: #fff; border-radius: 16px;
        border: 1px solid var(--border); margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .ihb-header-text h2 { margin: 0; font-size: 22px; font-weight: 800; color: var(--dark); }
    .ihb-header-text p { margin: 4px 0 0; color: var(--gold); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

    /* Layout */
    .ihb-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 25px; }
    .ihb-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 25px; position: relative; }
    .ihb-card h3 { margin: 0 0 20px; font-size: 16px; font-weight: 800; color: var(--dark); border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }

    /* Form Elements */
    .ihb-group { margin-bottom: 20px; }
    .ihb-label { font-size: 11px; font-weight: 700; color: var(--slate); margin-bottom: 8px; display: block; text-transform: uppercase; }
    .ihb-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; box-sizing: border-box; }
    .ihb-input:focus { border-color: var(--gold); outline: none; box-shadow: 0 0 0 3px rgba(193, 155, 118, 0.1); }

    /* Image Box */
    .ihb-media-container { position: relative; width: 100%; height: 220px; border: 2px dashed var(--border); border-radius: 12px; overflow: hidden; background: #f8fafc; cursor: pointer; transition: 0.3s; }
    .ihb-media-container:hover { border-color: var(--gold); }
    .ihb-media-container img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ihb-media-placeholder { height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--slate); }
    
    .ihb-remove-img { position: absolute; top: 10px; right: 10px; background: rgba(220, 38, 38, 0.9); color: #fff; border: none; padding: 5px 10px; border-radius: 6px; font-size: 10px; cursor: pointer; display: none; z-index: 10; }
    .has-image .ihb-remove-img { display: block; }

    /* Amenities */
    .ihb-amenity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .ihb-amenity-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9; }
    .ihb-switch { position: relative; display: inline-block; width: 34px; height: 18px; }
    .ihb-switch input { opacity: 0; width: 0; height: 0; }
    .ihb-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 20px; }
    .ihb-slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .ihb-slider { background-color: var(--gold); }
    input:checked + .ihb-slider:before { transform: translateX(16px); }

    /* Buttons */
    .ihb-btn-gold { background: var(--gold); color: #fff; border: none; padding: 12px 25px; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: 0.3s; }
    .ihb-btn-gold:hover { background: #a88562; transform: translateY(-1px); }
    .spin { animation: ihb-spin 1s linear infinite; }
    @keyframes ihb-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<div class="ihb-wrapper">
    <form method="POST" id="ihb-room-form">
        <?php wp_nonce_field('ihb_room_save', 'ihb_nonce'); ?>
        <input type="hidden" name="room_id" value="<?= $id ?>">
        <input type="hidden" name="ihb_save_room_action" value="1">

        <div class="ihb-header-modern">
            <div class="ihb-header-text">
                <h2>Room Inventory</h2>
                <p>Management & Configuration</p>
            </div>
            <button type="submit" class="ihb-btn-gold" id="save-btn">
                <span class="dashicons dashicons-saved"></span> Save Room Data
            </button>
        </div>

        <?php if($show_success): ?>
            <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:12px; margin-bottom:25px; font-weight:700; border-left: 5px solid #22c55e;">
                ✓ Inventory updated successfully!
            </div>
        <?php endif; ?>

        <div class="ihb-grid">
            <div class="ihb-col">
                <div class="ihb-card">
                    <h3>Room Identity</h3>
                    <div class="ihb-group">
                        <label class="ihb-label">Room Number</label>
                        <select name="room_no" class="ihb-input" required>
                            <option value="">Select No...</option>
                            <?php foreach($saved_numbers as $num): $num = trim($num); if(!$num) continue; ?>
                                <option value="<?= esc_attr($num) ?>" <?= selected($current_no, $num) ?>>Room <?= esc_html($num) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ihb-group">
                        <label class="ihb-label">Category</label>
                        <select name="room_type" class="ihb-input" required>
                            <option value="">Select Type...</option>
                            <?php foreach($saved_types as $type): $type = trim($type); if(!$type) continue; ?>
                                <option value="<?= esc_attr($type) ?>" <?= selected($current_type, $type) ?>><?= esc_html($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ihb-group">
                        <label class="ihb-label">Price Per Night (৳)</label>
                        <input type="number" name="price" class="ihb-input" value="<?= esc_attr($current_price) ?>" placeholder="e.g. 4500">
                    </div>
                </div>

                <div class="ihb-card">
                    <h3>Room Media</h3>
                    <div class="ihb-media-container <?= $current_img_url ? 'has-image' : '' ?>" id="media-trigger">
                        <button type="button" class="ihb-remove-img" id="remove-img-btn">Remove</button>
                        <input type="hidden" name="room_image" id="room-img-id" value="<?= $current_img_id ?: 0 ?>">
                        <div id="image-preview-wrap" style="height:100%;">
                            <?php if($current_img_url): ?>
                                <img src="<?= $current_img_url ?>" alt="Preview">
                            <?php else: ?>
                                <div class="ihb-media-placeholder">
                                    <span class="dashicons dashicons-camera" style="font-size:40px;"></span>
                                    <p style="font-weight:700; font-size:10px; margin-top:10px;">ADD ROOM PHOTO</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ihb-col">
                <div class="ihb-card">
                    <h3>Full Description</h3>
                    <textarea name="room_desc" class="ihb-input" style="min-height:150px; line-height:1.6;" placeholder="Describe the room experience..."><?= esc_textarea($current_desc) ?></textarea>
                </div>

                <div class="ihb-card">
                    <h3>Included Amenities</h3>
                    <div class="ihb-amenity-grid">
                        <?php 
                        foreach($facilities_master as $slug => $f): 
                            if (!empty($enabled_facs) && !in_array($slug, $enabled_facs)) continue;
                            $checked = in_array($slug, $current_features) ? 'checked' : '';
                        ?>
                        <div class="ihb-amenity-item">
                            <svg style="width:20px; height:20px; fill:var(--gold);" viewBox="0 0 24 24"><path d="<?= $f['path'] ?>"/></svg>
                            <div style="flex:1; font-size:13px; font-weight:600;"><?= $f['label'] ?></div>
                            <label class="ihb-switch">
                                <input type="checkbox" name="room_features[]" value="<?= $slug ?>" <?= $checked ?>>
                                <span class="ihb-slider"></span>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('media-trigger');
    const preview = document.getElementById('image-preview-wrap');
    const inputId = document.getElementById('room-img-id');
    const removeBtn = document.getElementById('remove-img-btn');

    // Handle Upload
    trigger.onclick = function(e) {
        if(e.target === removeBtn) return; // Don't trigger upload if clicking remove

        const frame = wp.media({ title: 'Select Room Image', multiple: false });
        frame.on('select', function() {
            const att = frame.state().get('selection').first().toJSON();
            inputId.value = att.id;
            preview.innerHTML = `<img src="${att.url}" style="width:100%; height:100%; object-fit:cover;">`;
            trigger.classList.add('has-image');
        });
        frame.open();
    };

    // Handle Remove
    removeBtn.onclick = function(e) {
        e.stopPropagation();
        inputId.value = 0;
        preview.innerHTML = `
            <div class="ihb-media-placeholder">
                <span class="dashicons dashicons-camera" style="font-size:40px;"></span>
                <p style="font-weight:700; font-size:10px; margin-top:10px;">ADD ROOM PHOTO</p>
            </div>`;
        trigger.classList.remove('has-image');
    };

    // Loading State
    document.getElementById('ihb-room-form').onsubmit = function() {
        const btn = document.getElementById('save-btn');
        btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Saving...';
        btn.style.opacity = '0.7';
    };
});
</script>