<?php
/**
 * INFINITY HOTEL - COMPLETE ROOM EDITOR (Integrated with Settings)
 * Version: 2.2 - Fixed Data Loading from Settings
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

        if ($room_id > 0) {
            $post_args['ID'] = $room_id;
            wp_update_post($post_args);
        } else {
            $room_id = wp_insert_post($post_args);
        }

        if ($room_id) {
            update_post_meta($room_id, '_ihb_type', sanitize_text_field($_POST['room_type']));
            update_post_meta($room_id, '_ihb_price', sanitize_text_field($_POST['price']));
            update_post_meta($room_id, '_ihb_room_image', $room_img);
            $features = isset($_POST['room_features']) ? (array)$_POST['room_features'] : [];
            update_post_meta($room_id, '_ihb_features', $features);
            $show_success = true;
        }
    }
}

// --- 2. DATA RETRIEVAL (Current Room Being Edited) ---
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$post_obj         = get_post($id);
$current_no       = $post_obj ? str_replace('Room ', '', $post_obj->post_title) : '';
$current_desc     = $post_obj ? $post_obj->post_content : '';
$current_type     = get_post_meta($id, '_ihb_type', true);
$current_price    = get_post_meta($id, '_ihb_price', true);
$current_features = get_post_meta($id, '_ihb_features', true) ?: [];
$current_img_id   = get_post_meta($id, '_ihb_room_image', true);
$current_img_url  = wp_get_attachment_url($current_img_id);

// --- 3. DATA LOAD FROM SETTINGS (THE FIX) ---
// These keys MUST match your settings page update_option('KEY', $val)
$saved_types   = get_option('ihb_room_types', []);
$saved_numbers = get_option('ihb_room_numbers', []);
$enabled_facs  = get_option('ihb_enabled_facilities', []);

// Fallback defaults if settings are empty so the dropdowns aren't blank
if(empty($saved_types))   $saved_types   = ['Standard', 'Deluxe', 'Suite'];
if(empty($saved_numbers)) $saved_numbers = ['101', '102', '103', '104', '105'];

$facilities_master = [
    'wifi' => ['label' => 'High-Speed WiFi', 'path' => 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    'ac' => ['label' => 'Climate Control', 'path' => 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    'tv' => ['label' => 'Entertainment TV', 'path' => 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    'bar' => ['label' => 'Mini Bar', 'path' => 'M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2H7M7,4V10H17V4H7M7,12V20H17V12H7M13,14V18H15V14H13Z'],
    'safe' => ['label' => 'Personal Safe', 'path' => 'M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.66,7 15,8.34 15,10C15,11.66 13.66,13 12,13C10.34,13 9,11.66 9,10C9,8.34 10.34,7 12,7Z'],
    'service' => ['label' => 'Room Service', 'path' => 'M11,9H13V11H11V9M11,13H13V17H11V13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z']
];
?>

<style>
    :root { --gold: #c19b76; --dark: #1e293b; --slate: #64748b; --border: #e2e8f0; --bg: #f8fafc; }

    .ihb-header-modern {
        display: flex; justify-content: space-between; align-items: center;
        padding: 24px 30px; background: #fff; border-radius: 16px;
        border: 1px solid var(--border); margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .ihb-header-icon {
        width: 46px; height: 46px; background: rgba(193, 155, 118, 0.1);
        color: var(--gold); border-radius: 12px; display: flex; align-items: center; justify-content: center;
    }
    .ihb-header-text h2 { margin: 0; font-size: 20px; font-weight: 800; color: var(--dark); letter-spacing: -0.3px; }
    .ihb-header-text p { margin: 3px 0 0; color: var(--gold); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; }

    .ihb-btn-gold {
        background: var(--gold); color: #fff; border: none; padding: 12px 25px;
        border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;
        display: flex; align-items: center; gap: 10px; transition: 0.3s;
        box-shadow: 0 4px 12px rgba(193, 155, 118, 0.25);
    }
    .ihb-btn-gold:hover { background: #a88562; transform: translateY(-1px); }

    .ihb-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 25px; }
    .ihb-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 25px; }
    .ihb-card h3 { margin: 0 0 20px; font-size: 15px; font-weight: 800; color: var(--dark); border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }

    .ihb-group { margin-bottom: 20px; }
    .ihb-label { font-size: 11px; font-weight: 700; color: var(--slate); margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.6px; }
    .ihb-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; background: #fff; transition: 0.2s; box-sizing: border-box; }
    .ihb-input:focus { border-color: var(--gold); outline: none; box-shadow: 0 0 0 3px rgba(193, 155, 118, 0.1); }
    select.ihb-input { appearance: none; background: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 12px center/16px; }

    .ihb-amenity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .ihb-amenity-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9; }
    .ihb-f-label { flex: 1; font-size: 13px; font-weight: 600; color: var(--dark); }

    .ihb-switch { position: relative; display: inline-block; width: 34px; height: 18px; }
    .ihb-switch input { opacity: 0; width: 0; height: 0; }
    .ihb-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .4s; border-radius: 20px; }
    .ihb-slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .ihb-slider { background-color: var(--gold); }
    input:checked + .ihb-slider:before { transform: translateX(16px); }

    .ihb-media-box { width: 100%; height: 180px; border: 2px dashed var(--border); border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #f8fafc; overflow: hidden; transition: 0.3s; }
    .ihb-media-box img { width: 100%; height: 100%; object-fit: cover; }
    .spin { animation: ihb-spin 1s linear infinite; }
    @keyframes ihb-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<div style="padding: 10px; max-width: 1100px;">
    <form method="POST">
        <?php wp_nonce_field('ihb_room_save', 'ihb_nonce'); ?>
        <input type="hidden" name="room_id" value="<?= $id ?>">
        <input type="hidden" name="ihb_save_room_action" value="1">

        <div class="ihb-header-modern">
            <div style="display:flex; align-items:center; gap:18px;">
                <div class="ihb-header-icon"><span class="dashicons dashicons-admin-home"></span></div>
                <div class="ihb-header-text">
                    <h2>Inventory Manager</h2>
                    <p>Configure Property Details & Features</p>
                </div>
            </div>
            <button type="submit" class="ihb-btn-gold" id="save-btn">
                <span class="dashicons dashicons-saved"></span> Save Inventory
            </button>
        </div>

        <?php if($show_success): ?>
            <div style="background:#dcfce7; color:#166534; padding:15px; border-radius:12px; margin-bottom:25px; font-weight:700; border-left: 4px solid #22c55e;">
                ✓ Room details saved successfully!
            </div>
        <?php endif; ?>

        <div class="ihb-grid">
            <div class="ihb-col">
                <div class="ihb-card">
                    <h3>Primary Identity</h3>
                    
                    <div class="ihb-group">
                        <label class="ihb-label">Room Number</label>
                        <select name="room_no" class="ihb-input" required>
                            <option value="">Select Room No...</option>
                            <?php 
                            // Loop through the numbers saved in your global settings
                            foreach($saved_numbers as $num): 
                                $num = trim($num); // Clean whitespace
                                if(empty($num)) continue;
                            ?>
                                <option value="<?= esc_attr($num) ?>" <?= selected($current_no, $num) ?>>Room <?= esc_html($num) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ihb-group">
                        <label class="ihb-label">Room Type</label>
                        <select name="room_type" class="ihb-input" required>
                            <option value="">Select Category...</option>
                            <?php 
                            // Loop through the types saved in your global settings
                            foreach($saved_types as $type): 
                                $type = trim($type); // Clean whitespace
                                if(empty($type)) continue;
                            ?>
                                <option value="<?= esc_attr($type) ?>" <?= selected($current_type, $type) ?>><?= esc_html($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ihb-group" style="margin-bottom:0;">
                        <label class="ihb-label">Nightly Price (৳)</label>
                        <input type="number" name="price" class="ihb-input" value="<?= esc_attr($current_price) ?>" placeholder="5000">
                    </div>
                </div>

                <div class="ihb-card">
                    <h3>Inventory Photo</h3>
                    <div class="ihb-media-box" id="media-trigger">
                        <input type="hidden" name="room_image" id="room-img-id" value="<?= $current_img_id ? $current_img_id : 0 ?>">
                        <?php if($current_img_url): ?>
                            <img src="<?= $current_img_url ?>" id="preview-img">
                        <?php else: ?>
                            <div style="text-align:center;">
                                <span class="dashicons dashicons-camera" style="font-size:36px; color:#cbd5e1;"></span>
                                <p style="color:var(--slate); font-size:10px; font-weight:800; margin-top:10px;">UPLOAD IMAGE</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="ihb-col">
                <div class="ihb-card">
                    <h3>Room Description</h3>
                    <div class="ihb-group" style="margin-bottom:0;">
                        <label class="ihb-label">Property Overview</label>
                        <textarea name="room_desc" class="ihb-input" style="min-height:110px; resize:vertical; line-height:1.6;" placeholder="Detail the layout..."><?= esc_textarea($current_desc) ?></textarea>
                    </div>
                </div>

                <div class="ihb-card">
                    <h3>Available Amenities</h3>
                    <div class="ihb-amenity-grid">
                        <?php 
                        foreach($facilities_master as $slug => $data): 
                            // Only show if the facility is enabled in your Global Settings
                            if (!empty($enabled_facs) && !in_array($slug, $enabled_facs)) continue;
                            
                            $checked = in_array($slug, $current_features) ? 'checked' : '';
                        ?>
                        <div class="ihb-amenity-item">
                            <div style="width:20px; height:20px; fill:var(--gold);"><svg viewBox="0 0 24 24"><path d="<?= $data['path'] ?>"/></svg></div>
                            <div class="ihb-f-label"><?= $data['label'] ?></div>
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
    trigger.onclick = function() {
        const frame = wp.media({ title: 'Select Room Image', multiple: false });
        frame.on('select', function() {
            const att = frame.state().get('selection').first().toJSON();
            document.getElementById('room-img-id').value = att.id;
            trigger.innerHTML = `<img src="${att.url}" style="width:100%; height:100%; object-fit:cover;">`;
        });
        frame.open();
    };

    document.querySelector('form').onsubmit = function() {
        const btn = document.getElementById('save-btn');
        btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Saving...';
    };
});
</script>