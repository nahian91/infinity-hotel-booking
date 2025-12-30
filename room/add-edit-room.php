<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. DATABASE SAVE HANDLER (Settings Style)
 */
$show_success = false;
if (isset($_POST['ihb_save_booking_action']) && current_user_can('edit_posts')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_booking_secure_save')) {
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $guest_name = sanitize_text_field($_POST['guest_name']);
        
        $post_args = [
            'post_title'   => $guest_name,
            'post_status'  => 'publish',
            'post_type'    => 'ihb_bookings', 
        ];

        // Insert or Update Post
        if ($booking_id > 0) {
            $post_args['ID'] = $booking_id;
            wp_update_post($post_args);
        } else {
            $booking_id = wp_insert_post($post_args);
        }

        if ($booking_id) {
            // Save Meta Data
            update_post_meta($booking_id, '_ihb_phone', sanitize_text_field($_POST['phone']));
            update_post_meta($booking_id, '_ihb_address', sanitize_text_field($_POST['address']));
            update_post_meta($booking_id, '_ihb_room_id', intval($_POST['room_id']));
            update_post_meta($booking_id, '_ihb_checkin', sanitize_text_field($_POST['checkin']));
            update_post_meta($booking_id, '_ihb_checkout', sanitize_text_field($_POST['checkout']));
            update_post_meta($booking_id, '_ihb_pay_method', sanitize_text_field($_POST['pay_method']));
            update_post_meta($booking_id, '_ihb_mfs_phone', sanitize_text_field($_POST['mfs_phone']));
            update_post_meta($booking_id, '_ihb_mfs_trx', sanitize_text_field($_POST['mfs_trx']));
            update_post_meta($booking_id, '_ihb_guest_photo', intval($_POST['guest_photo']));
            update_post_meta($booking_id, '_ihb_nid_doc', intval($_POST['nid_doc']));

            $show_success = true;
        }
    }
}

/**
 * 2. DATA PREPARATION (Fetch existing data if editing)
 */
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($booking_id) ? $booking_id : 0);

// Chained Dropdown Data (Rooms)
$rooms = get_posts(['post_type' => 'ihb_rooms', 'numberposts' => -1]); 
$categories = [];
$room_details = [];
foreach($rooms as $r) {
    $type  = get_post_meta($r->ID, '_ihb_type', true) ?: 'Standard';
    $price = get_post_meta($r->ID, '_ihb_price', true) ?: 0;
    $categories[$type][] = ['id' => $r->ID, 'no' => $r->post_title];
    $room_details[$r->ID] = ['price' => $price, 'type' => $type];
}

// Fetch Booking Meta
$meta     = get_post_custom($id);
$phone    = $meta['_ihb_phone'][0] ?? '';
$address  = $meta['_ihb_address'][0] ?? '';
$checkin  = $meta['_ihb_checkin'][0] ?? '';
$checkout = $meta['_ihb_checkout'][0] ?? '';
$room_id  = $meta['_ihb_room_id'][0] ?? '';
$method   = $meta['_ihb_pay_method'][0] ?? 'cash';
$mfs_p    = $meta['_ihb_mfs_phone'][0] ?? '';
$mfs_t    = $meta['_ihb_mfs_trx'][0] ?? '';
$photo_id = $meta['_ihb_guest_photo'][0] ?? '';
$nid_id   = $meta['_ihb_nid_doc'][0] ?? '';

$today = date('Y-m-d');
?>

<style>
    /* Reuse your Inventory Styles */
    .ihb-view-container { display: grid; grid-template-columns: 1fr 380px; gap: 30px; margin-top: 25px; }
    .ihb-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .ihb-label-sm { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .ihb-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; }
    
    /* TOAST STYLES */
    .ihb-toast-notification {
        position: fixed; top: 50px; right: 30px; background: #ffffff; 
        border-left: 4px solid #22c55e; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-radius: 8px; display: flex; align-items: center; padding: 16px 24px; z-index: 9999;
    }
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .preview-box { height: 150px; border: 2px dashed #e2e8f0; border-radius: 12px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: pointer; }
    .preview-box img { width: 100%; height: 100%; object-fit: cover; }
    #mfs-details-box { display: none; background: #fffcf0; padding: 20px; border-radius: 10px; border: 1px solid #fde68a; margin-top: 15px; }
</style>

<?php if ($show_success): ?>
<div id="ihb-success-toast" class="ihb-toast-notification">
    <div style="margin-right:15px; color:#22c55e;"><span class="dashicons dashicons-saved"></span></div>
    <div><strong>Success!</strong><br><small>Reservation saved. Redirecting...</small></div>
</div>
<script>
    setTimeout(() => { window.location.href = "admin.php?page=infinity-hotel&tab=all_bookings"; }, 2000);
</script>
<?php endif; ?>

<form id="ihb-main-booking-form" method="POST">
    <?php wp_nonce_field('ihb_booking_secure_save', 'ihb_nonce'); ?>
    <input type="hidden" name="booking_id" value="<?= $id ?>">
    <input type="hidden" name="ihb_save_booking_action" value="1">

    <div class="ihb-header">
        <div>
            <h2><?= $id ? 'Update Reservation' : 'New Reservation' ?></h2>
        </div>
        <button type="submit" class="ihb-btn-gold" id="save-btn">Save Reservation</button>
    </div>

    <div class="ihb-view-container">
        <div class="ihb-main-content">
            <div class="ihb-card">
                <label class="ihb-label-sm">Guest Name</label>
                <input type="text" name="guest_name" class="ihb-input" value="<?= $id ? get_the_title($id) : '' ?>" required>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:20px;">
                    <div>
                        <label class="ihb-label-sm">Phone</label>
                        <input type="text" name="phone" class="ihb-input" value="<?= $phone ?>">
                    </div>
                    <div>
                        <label class="ihb-label-sm">Address</label>
                        <input type="text" name="address" class="ihb-input" value="<?= $address ?>">
                    </div>
                </div>
            </div>

            <div class="ihb-card">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="ihb-label-sm">Category</label>
                        <select id="sel-cat" class="ihb-input">
                            <option value="">-- Select --</option>
                            <?php foreach(array_keys($categories) as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="ihb-label-sm">Room Number</label>
                        <select name="room_id" id="sel-room" class="ihb-input" required disabled></select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:20px;">
                    <div>
                        <label class="ihb-label-sm">Check-In</label>
                        <input type="date" name="checkin" id="cin" class="ihb-input" value="<?= $checkin ?>" required>
                    </div>
                    <div>
                        <label class="ihb-label-sm">Check-Out</label>
                        <input type="date" name="checkout" id="cout" class="ihb-input" value="<?= $checkout ?>" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="ihb-sidebar-content">
            <div class="ihb-card">
                <label class="ihb-label-sm">Total Billing</label>
                <div style="font-size: 28px; font-weight: 800; color: var(--gold);">৳<span id="lb-total">0.00</span></div>
            </div>

            <div class="ihb-card">
                <label class="ihb-label-sm">Documents</label>
                <div class="preview-box" id="btn-photo" style="margin-bottom:10px;">
                    <input type="hidden" name="guest_photo" id="val-photo" value="<?= $photo_id ?>">
                    <img src="<?= wp_get_attachment_url($photo_id) ?>" id="img-photo" style="<?= $photo_id ? '' : 'display:none' ?>">
                    <span id="p-photo" style="<?= $photo_id ? 'display:none' : '' ?>">Guest Photo</span>
                </div>
                <div class="preview-box" id="btn-nid">
                    <input type="hidden" name="nid_doc" id="val-nid" value="<?= $nid_id ?>">
                    <img src="<?= wp_get_attachment_url($nid_id) ?>" id="img-nid" style="<?= $nid_id ? '' : 'display:none' ?>">
                    <span id="p-nid" style="<?= $nid_id ? 'display:none' : '' ?>">NID Document</span>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const roomCats = <?= json_encode($categories) ?>;
const roomMeta = <?= json_encode($room_details) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('sel-cat');
    const roomSelect = document.getElementById('sel-room');
    
    // 1. Chained Dropdowns
    typeSelect.addEventListener('change', function() {
        const cat = this.value;
        roomSelect.innerHTML = '<option value="">-- Room --</option>';
        if (cat && roomCats[cat]) {
            roomCats[cat].forEach(r => {
                let opt = document.createElement('option');
                opt.value = r.id; opt.textContent = 'Room ' + r.no;
                roomSelect.appendChild(opt);
            });
            roomSelect.disabled = false;
        } else { roomSelect.disabled = true; }
    });

    // 2. WP Media (Same logic as Inventory)
    function setupMedia(btnId, inputId, imgId, pId) {
        document.getElementById(btnId).onclick = function(e) {
            e.preventDefault();
            const frame = wp.media({ multiple: false });
            frame.on('select', function() {
                const att = frame.state().get('selection').first().toJSON();
                document.getElementById(inputId).value = att.id;
                document.getElementById(imgId).src = att.url;
                document.getElementById(imgId).style.display = 'block';
                document.getElementById(pId).style.display = 'none';
            });
            frame.open();
        };
    }
    setupMedia('btn-photo', 'val-photo', 'img-photo', 'p-photo');
    setupMedia('btn-nid', 'val-nid', 'img-nid', 'p-nid');

    // 3. Loading State (Syncing...)
    document.getElementById('ihb-main-booking-form').addEventListener('submit', function() {
        const btn = document.getElementById('save-btn');
        btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Syncing...';
        btn.style.opacity = '0.7';
    });
});
</script>