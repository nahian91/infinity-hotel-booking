<?php
/**
 * INFINITY HOTEL - ULTIMATE BOOKING ENGINE (v5.0)
 * Features: Bottom Add Button, Live Guest Counter, Sidebar Date-to-Bill Flow,
 * Full Address, Gender, Email, and Media Uploaders.
 */

if (!defined('ABSPATH')) exit;

// 1. DATABASE & POST HANDLER
$show_success = false;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_POST['ihb_save_action']) && current_user_can('edit_posts')) {
    if (wp_verify_nonce($_POST['ihb_nonce'], 'ihb_save_secure')) {
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $guests     = $_POST['guests'] ?? []; 
        $room_id    = intval($_POST['room_id']);
        $checkin    = sanitize_text_field($_POST['checkin']);
        $checkout   = sanitize_text_field($_POST['checkout']);
        
        // Calculate Total Cost
        $rate       = floatval(get_post_meta($room_id, '_ihb_price', true));
        $date1      = strtotime($checkin);
        $date2      = strtotime($checkout);
        $nights     = ($date2 > $date1) ? ceil(($date2 - $date1) / 86400) : 0;
        $total_cost = $rate * $nights;

        $primary_guest = !empty($guests) ? sanitize_text_field($guests[0]['name']) : 'New Booking';

        $post_args = [
            'post_title'  => $primary_guest,
            'post_type'   => 'ihb_bookings',
            'post_status' => 'publish',
        ];

        if ($booking_id > 0) {
            $post_args['ID'] = $booking_id;
            wp_update_post($post_args);
        } else {
            $booking_id = wp_insert_post($post_args);
        }

        if ($booking_id) {
            update_post_meta($booking_id, '_ihb_guests_list', $guests);
            update_post_meta($booking_id, '_ihb_room_id', $room_id);
            update_post_meta($booking_id, '_ihb_checkin', $checkin);
            update_post_meta($booking_id, '_ihb_checkout', $checkout);
            update_post_meta($booking_id, '_ihb_total_price', $total_cost);
            update_post_meta($booking_id, '_ihb_pay_method', sanitize_text_field($_POST['pay_method']));
            update_post_meta($booking_id, '_ihb_mfs_phone', sanitize_text_field($_POST['mfs_phone']));
            update_post_meta($booking_id, '_ihb_mfs_trx', sanitize_text_field($_POST['mfs_trx']));
            $id = $booking_id;
            $show_success = true;
        }
    }
}

// 2. DATA PREPARATION
$saved_guests = get_post_meta($id, '_ihb_guests_list', true) ?: [];
$rooms_posts  = get_posts(['post_type' => 'ihb_rooms', 'numberposts' => -1]);
$categories   = []; 
$room_meta    = [];

foreach($rooms_posts as $rp) {
    $cat = get_post_meta($rp->ID, '_ihb_type', true) ?: 'Standard';
    $categories[$cat][] = ['id' => $rp->ID, 'no' => $rp->post_title];
    $room_meta[$rp->ID] = ['price' => get_post_meta($rp->ID, '_ihb_price', true), 'cat' => $cat];
}

$cur_room = get_post_meta($id, '_ihb_room_id', true);
$cur_meth = get_post_meta($id, '_ihb_pay_method', true) ?: 'cash';
?>

<style>
    /* CSS Variables & Reset */
    :root { 
        --gold: #c19b76; 
        --gold-light: #fdfaf7;
        --dark: #0f172a; 
        --slate: #64748b; 
        --border: #e2e8f0; 
        --bg: #f8fafc; 
    }
    
    .ihb-main-wrap { 
        display: grid; 
        grid-template-columns: 1fr 420px; 
        gap: 35px; 
        margin: 25px 20px 0 0; 
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        align-items: start;
    }
    
    /* Elegant Card Design */
    .ihb-card { 
        background: #fff; 
        border: 1px solid var(--border); 
        border-radius: 16px; 
        padding: 30px; 
        margin-bottom: 25px; 
        box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
    }
    
    .ihb-card-header { 
        font-size: 13px; 
        font-weight: 800; 
        color: var(--dark); 
        margin-bottom: 25px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        text-transform: uppercase; 
        letter-spacing: 0.8px; 
        border-bottom: 2px solid var(--bg); 
        padding-bottom: 12px; 
    }
    
    /* Guest Block UI */
    .guest-block { 
        background: #fff; 
        border: 1px solid var(--border); 
        border-left: 5px solid var(--gold); 
        border-radius: 14px; 
        padding: 28px; 
        margin-bottom: 25px; 
        position: relative; 
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .guest-block:hover { 
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.06); 
    }
    
    .ihb-grid { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 20px; 
        margin-bottom: 20px; 
    }
    
    .ihb-label { 
        display: block; 
        font-size: 10px; 
        font-weight: 800; 
        color: var(--slate); 
        margin-bottom: 8px; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
    }
    
    .ihb-input { 
        width: 100%; 
        padding: 12px 16px; 
        border: 1px solid #d1d5db; 
        border-radius: 10px; 
        font-size: 14px; 
        transition: 0.2s; 
        box-sizing: border-box; 
        background: #fff; 
        color: var(--dark);
    }
    
    .ihb-input:focus { 
        border-color: var(--gold); 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(193, 155, 118, 0.12); 
    }
    
    /* Media Uploader Box */
    .media-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 20px; 
        margin-top: 15px; 
    }
    
    .media-btn { 
        height: 120px; 
        border: 2px dashed #cbd5e1; 
        border-radius: 12px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        cursor: pointer; 
        background: var(--bg); 
        position: relative; 
        overflow: hidden; 
        transition: 0.2s;
    }
    
    .media-btn:hover { border-color: var(--gold); background: var(--gold-light); }
    .media-btn img { width: 100%; height: 100%; object-fit: cover; position: absolute; z-index: 1; }
    .media-btn span { 
        font-size: 10px; 
        font-weight: 800; 
        color: var(--slate); 
        z-index: 2; 
        background: #fff; 
        padding: 6px 12px; 
        border-radius: 6px; 
        box-shadow: 0 2px 5px rgba(0,0,0,0.08); 
    }

    /* Billing Sidebar UI */
    .sidebar-sticky { position: sticky; top: 40px; }
    
    .bill-summary { 
        background: #fffcf0; 
        border: 1px solid #fae8d8; 
        border-radius: 16px; 
        padding: 35px 25px; 
        text-align: center; 
        margin: 25px 0; 
    }
    
    .price-main { 
        font-size: 52px; 
        font-weight: 900; 
        color: var(--dark); 
        display: block; 
        letter-spacing: -2.5px; 
        line-height: 1;
        margin: 10px 0;
    }
    
    .price-sub { 
        color: var(--slate); 
        font-size: 13px; 
        font-weight: 600; 
        display: block; 
    }
    
    .stat-row { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        padding: 14px 0; 
        border-bottom: 1px dashed var(--border); 
        font-size: 14px; 
    }
    
    .badge-count { 
        background: var(--dark); 
        color: #fff; 
        padding: 5px 12px; 
        border-radius: 50px; 
        font-size: 11px; 
        font-weight: 900; 
    }

    /* Action Buttons */
    .btn-gold { 
        background: var(--gold); 
        color: #fff; 
        border: none; 
        width: 100%; 
        padding: 22px; 
        border-radius: 14px; 
        font-size: 18px; 
        font-weight: 800; 
        cursor: pointer; 
        transition: 0.3s; 
        box-shadow: 0 8px 25px rgba(193, 155, 118, 0.35); 
        margin-top: 20px;
    }
    
    .btn-gold:hover { filter: brightness(1.05); transform: translateY(-2px); }
    
    .btn-add-bottom { 
        background: #fff; 
        color: var(--dark); 
        border: 2px dashed var(--gold); 
        width: 100%; 
        padding: 18px; 
        border-radius: 14px; 
        font-weight: 800; 
        font-size: 14px; 
        cursor: pointer; 
        transition: 0.2s; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        gap: 10px; 
        margin-top: 10px;
    }
    
    .btn-add-bottom:hover { background: var(--gold); color: #fff; border-style: solid; }
    
    /* Success Notification */
    .toast-success { 
        background: #059669; 
        color: white; 
        padding: 20px; 
        border-radius: 12px; 
        margin-bottom: 30px; 
        font-weight: 700; 
        box-shadow: 0 10px 15px rgba(5,150,105,0.2); 
    }
</style>

<div class="wrap">
    <?php if ($show_success): ?>
        <div class="toast-success">✓ Booking successfully processed. Updating reservations ledger...</div>
        <script>setTimeout(() => { window.location.href = "admin.php?page=infinity-hotel&tab=all_bookings"; }, 1500);</script>
    <?php endif; ?>

    <form method="POST">
        <?php wp_nonce_field('ihb_save_secure', 'ihb_nonce'); ?>
        <input type="hidden" name="booking_id" value="<?= $id ?>">
        <input type="hidden" name="ihb_save_action" value="1">

        <div class="ihb-main-wrap">
            
            <div class="ihb-col-main">
                <div id="guest-repeater-root">
                    </div>
                
                <button type="button" class="btn-add-bottom" id="add-guest-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"></path></svg>
                    ADD NEW GUEST REPEATER
                </button>
            </div>

            <div class="ihb-col-sidebar">
                <div class="sidebar-sticky">
                    
                    <div class="ihb-card">
                        <div class="ihb-card-header">Stay Timeline & Stats</div>
                        
                        <div class="stat-row" style="margin-bottom:20px; border-bottom:none;">
                            <span class="ihb-label" style="margin:0;">Total Guest(s)</span>
                            <span class="badge-count" id="guest-count-badge">0</span>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                            <div>
                                <label class="ihb-label">Arrival Date</label>
                                <input type="date" name="checkin" id="checkin" class="ihb-input" value="<?= get_post_meta($id, '_ihb_checkin', true) ?>" required>
                            </div>
                            <div>
                                <label class="ihb-label">Departure Date</label>
                                <input type="date" name="checkout" id="checkout" class="ihb-input" value="<?= get_post_meta($id, '_ihb_checkout', true) ?>" required>
                            </div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="ihb-label">Room Category</label>
                            <select id="sel-cat" class="ihb-input">
                                <option value="">-- Choose Category --</option>
                                <?php foreach(array_keys($categories) as $cat): ?>
                                    <option value="<?= $cat ?>" <?= ($id && isset($room_meta[$cur_room]) && $room_meta[$cur_room]['cat'] == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="ihb-label">Room Number</label>
                            <select name="room_id" id="sel-room" class="ihb-input" required>
                                <option value="">-- No --</option>
                            </select>
                        </div>

                        <div class="bill-summary">
                            <span class="ihb-label" style="color:var(--gold); font-weight:900;">Total Payment Due</span>
                            <span class="price-main">৳<span id="txt-total">0</span></span>
                            <span class="price-sub">
                                <span id="txt-nights">0</span> Night(s) x ৳<span id="txt-rate">0</span>
                            </span>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="ihb-label">Payment Method</label>
                            <select name="pay_method" id="pay_method" class="ihb-input">
                                <option value="cash" <?= selected($cur_meth, 'cash') ?>>Cash at Reception</option>
                                <option value="bkash" <?= selected($cur_meth, 'bkash') ?>>bKash Payment</option>
                                <option value="nagad" <?= selected($cur_meth, 'nagad') ?>>Nagad Payment</option>
                            </select>
                        </div>

                        <div id="mfs-box" style="display:none; background:#fffcf0; padding:18px; border-radius:12px; border:1px solid #fae8d8; margin-bottom:20px;">
                            <input type="text" name="mfs_phone" class="ihb-input" placeholder="Sender Account Number" value="<?= get_post_meta($id, '_ihb_mfs_phone', true) ?>" style="margin-bottom:12px;">
                            <input type="text" name="mfs_trx" class="ihb-input" placeholder="Transaction ID (TrxID)" value="<?= get_post_meta($id, '_ihb_mfs_trx', true) ?>">
                        </div>

                        <button type="submit" class="btn-gold">Confirm Booking</button>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('guest-repeater-root');
    const addBtn = document.getElementById('add-guest-btn');
    const guestBadge = document.getElementById('guest-count-badge');
    const roomCats = <?= json_encode($categories) ?>;
    const roomMeta = <?= json_encode($room_meta) ?>;
    const saved = <?= json_encode($saved_guests) ?>;
    let count = 0;

    // --- REPEATER LOGIC ---

    function updateBadge() {
        guestBadge.innerText = document.querySelectorAll('.guest-block').length;
    }

    function createGuestRow(data = {}) {
        const i = count++;
        const row = document.createElement('div');
        row.className = 'guest-block';
        row.innerHTML = `
            <div style="display:flex; justify-content:space-between; margin-bottom:22px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                <span style="font-weight:900; color:var(--gold); font-size:12px; letter-spacing:1px;">GUEST PROFILE #${i+1}</span>
                ${i > 0 ? `<span style="color:#ef4444; cursor:pointer; font-size:11px; font-weight:800;" onclick="this.closest('.guest-block').remove(); updateBadge();">REMOVE GUEST</span>` : ''}
            </div>
            <div class="ihb-grid">
                <div><label class="ihb-label">Full Name</label><input type="text" name="guests[${i}][name]" class="ihb-input" value="${data.name||''}" required></div>
                <div><label class="ihb-label">Email Address</label><input type="email" name="guests[${i}][email]" class="ihb-input" value="${data.email||''}"></div>
                <div><label class="ihb-label">Phone Number</label><input type="text" name="guests[${i}][phone]" class="ihb-input" value="${data.phone||''}"></div>
            </div>
            <div class="ihb-grid">
                <div>
                    <label class="ihb-label">Gender</label>
                    <select name="guests[${i}][gender]" class="ihb-input">
                        <option value="Male" ${data.gender=='Male'?'selected':''}>Male</option>
                        <option value="Female" ${data.gender=='Female'?'selected':''}>Female</option>
                        <option value="Other" ${data.gender=='Other'?'selected':''}>Other</option>
                    </select>
                </div>
                <div><label class="ihb-label">Age</label><input type="number" name="guests[${i}][age]" class="ihb-input" value="${data.age||''}"></div>
                <div><label class="ihb-label">NID/Passport</label><input type="text" name="guests[${i}][id_type]" class="ihb-input" value="${data.id_type||''}"></div>
            </div>
            <div style="margin-bottom:22px;">
                <label class="ihb-label">Full Residential Address</label>
                <textarea name="guests[${i}][address]" class="ihb-input" rows="2" style="resize:none; padding-top:12px;">${data.address||''}</textarea>
            </div>
            <div class="media-grid">
                <div class="media-btn" onclick="openWPMedia(${i}, 'photo')">
                    <input type="hidden" name="guests[${i}][photo]" id="val-photo-${i}" value="${data.photo||''}">
                    <img src="${data.photo_url||''}" id="img-photo-${i}" style="${data.photo?'':'display:none'}">
                    <span id="p-photo-${i}">+ UPLOAD PHOTO</span>
                </div>
                <div class="media-btn" onclick="openWPMedia(${i}, 'nid')">
                    <input type="hidden" name="guests[${i}][nid]" id="val-nid-${i}" value="${data.nid||''}">
                    <img src="${data.nid_url||''}" id="img-nid-${i}" style="${data.nid?'':'display:none'}">
                    <span id="p-nid-${i}">+ UPLOAD ID DOCUMENT</span>
                </div>
            </div>`;
        root.appendChild(row);
        updateBadge();
    }

    window.openWPMedia = function(i, type) {
        let frame = wp.media({ title: 'Select Guest Document', multiple: false });
        frame.on('select', () => {
            const att = frame.state().get('selection').first().toJSON();
            document.getElementById(`val-${type}-${i}`).value = att.id;
            const preview = document.getElementById(`img-${type}-${i}`);
            preview.src = att.url; preview.style.display = 'block';
            document.getElementById(`p-${type}-${i}`).style.display = 'none';
        }).open();
    };

    // --- BILLING CALCULATOR LOGIC ---

    const cin = document.getElementById('checkin'), cout = document.getElementById('checkout');
    const cSel = document.getElementById('sel-cat'), rSel = document.getElementById('sel-room');

    cSel.onchange = function() {
        rSel.innerHTML = '<option value="">-- No --</option>';
        if(this.value && roomCats[this.value]) {
            roomCats[this.value].forEach(r => {
                let opt = new Option('Room ' + r.no, r.id);
                if(r.id == "<?= $cur_room ?>") opt.selected = true;
                rSel.add(opt);
            });
        }
        refreshBill();
    };

    function refreshBill() {
        const rid = rSel.value;
        let rate = (rid && roomMeta[rid]) ? parseFloat(roomMeta[rid].price) : 0;
        let nights = 0;
        if(cin.value && cout.value) {
            nights = Math.max(0, Math.ceil((new Date(cout.value) - new Date(cin.value)) / 86400000));
        }
        document.getElementById('txt-rate').innerText = rate.toLocaleString();
        document.getElementById('txt-nights').innerText = nights;
        document.getElementById('txt-total').innerText = (rate * nights).toLocaleString();
    }

    const payM = document.getElementById('pay_method');
    payM.onchange = () => {
        document.getElementById('mfs-box').style.display = (payM.value !== 'cash') ? 'block' : 'none';
    };

    // EVENT LISTENERS
    addBtn.onclick = () => createGuestRow();
    [rSel, cin, cout].forEach(el => el.onchange = refreshBill);

    // INITIALIZATION
    if(saved.length) saved.forEach(g => createGuestRow(g)); else createGuestRow();
    if(cSel.value) cSel.dispatchEvent(new Event('change'));
    if(payM.value !== 'cash') payM.dispatchEvent(new Event('change'));
});
</script>