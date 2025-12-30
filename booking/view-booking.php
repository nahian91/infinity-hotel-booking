<?php 
if (!defined('ABSPATH')) exit; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) { echo "Invalid ID"; return; }

// 1. DATA COLLECTION
$guests   = get_post_meta($id, '_ihb_guests_list', true) ?: [];
$rid      = get_post_meta($id, '_ihb_room_id', true);
$checkin  = get_post_meta($id, '_ihb_checkin', true);
$checkout = get_post_meta($id, '_ihb_checkout', true);
$method   = get_post_meta($id, '_ihb_pay_method', true) ?: 'cash';
$mfs_trx  = get_post_meta($id, '_ihb_mfs_trx', true);
$mfs_acc  = get_post_meta($id, '_ihb_mfs_phone', true);

// Room Details
$room_title = get_the_title($rid);
$room_price = get_post_meta($rid, '_ihb_price', true) ?: 0;
$room_type  = get_post_meta($rid, '_ihb_type', true) ?: 'Standard';

// Calculation
$date1 = new DateTime($checkin);
$date2 = new DateTime($checkout);
$nights = $date1->diff($date2)->days;
$nights = $nights > 0 ? $nights : 1;
$total_bill = get_post_meta($id, '_ihb_total_price', true) ?: ($nights * $room_price);
?>

<style>
    :root { --gold: #c19b76; --slate: #0f172a; --border: #e2e8f0; }
    
    .ihb-view-wrapper { display: grid; grid-template-columns: 1fr 380px; gap: 30px; margin-top: 20px; font-family: 'Inter', sans-serif; }
    .ihb-folio-card { background: #fff; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
    
    /* Folio Header */
    .folio-header { background: var(--slate); color: #fff; padding: 45px; display: flex; justify-content: space-between; align-items: center; }
    .folio-id { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.6; margin-bottom: 8px; font-weight: 700; }
    .folio-name { font-size: 32px; font-weight: 900; margin: 0; line-height: 1.1; letter-spacing: -1px; }
    
    .folio-body { padding: 40px; }
    .folio-section-title { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #f8fafc; padding-bottom: 12px; margin: 40px 0 25px; }
    .folio-section-title:first-child { margin-top: 0; }
    
    /* Guest Loop Styling */
    .guest-folio-item { background: #f8fafc; border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid var(--border); }
    .guest-folio-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px; }
    .data-box label { display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
    .data-box span { display: block; font-size: 14px; font-weight: 700; color: var(--slate); }

    /* Documents */
    .doc-preview-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .doc-item { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: 12px; text-align: center; }
    .doc-item img { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; margin-bottom: 10px; cursor: pointer; }
    .doc-item label { font-size: 10px; font-weight: 800; color: var(--slate); }

    /* Sidebar Summary */
    .sidebar-inner { position: sticky; top: 30px; }
    .summary-card { background: #fff; padding: 30px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .bill-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px dashed #e2e8f0; font-size: 14px; color: #64748b; }
    .bill-total { display: flex; justify-content: space-between; padding-top: 25px; font-size: 32px; font-weight: 900; color: var(--slate); letter-spacing: -1px; }
    
    @media print {
        #adminmenuback, #adminmenuwrap, .ihb-header, #footer-upgrade, #wpadminbar, .ihb-btn-gold { display: none !important; }
        .ihb-view-wrapper { grid-template-columns: 1fr; margin: 0; }
        .ihb-folio-card { border: none; box-shadow: none; }
        .folio-header { background: #fff !important; color: #000 !important; border-bottom: 3px solid #000; padding: 20px 0; }
        .folio-body { padding: 30px 0; }
        .guest-folio-item { border: 1px solid #eee; background: #fff; }
    }
</style>

<div class="ihb-header" style="display:flex; justify-content:space-between; align-items:center;">
    <div>
        <h2>Booking Folio</h2>
        <p style="color:#64748b; margin:0;">Official Guest Statement & Identity Records</p>
    </div>
    <div style="display:flex; gap:12px;">
        <button onclick="window.print()" class="ihb-btn-gold" style="background:var(--slate); border:none;"><span class="dashicons dashicons-printer" style="margin-top:4px;"></span> Print Statement</button>
        <a href="?page=infinity-hotel&tab=add_booking&id=<?= $id ?>" class="ihb-btn-gold" style="text-decoration:none;">Edit Reservation</a>
    </div>
</div>

<div class="ihb-view-wrapper">
    <div class="ihb-folio-card">
        <div class="folio-header">
            <div>
                <div class="folio-id">Reservation #<?= $id ?></div>
                <h1 class="folio-name"><?= get_the_title($id) ?></h1>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:900; font-size:24px; color:var(--gold);">ROOM <?= $room_title ?></div>
                <div style="font-size:11px; opacity:0.8; font-weight:700; letter-spacing:1px;"><?= strtoupper($room_type) ?> CATEGORY</div>
            </div>
        </div>

        <div class="folio-body">
            
            <div class="folio-section-title">Registered Guests (<?= count($guests) ?>)</div>
            
            <?php foreach($guests as $index => $g): ?>
                <div class="guest-folio-item">
                    <div style="font-weight:900; color:var(--gold); font-size:11px; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">GUEST #<?= $index + 1 ?> PROFILE</div>
                    
                    <div class="guest-folio-grid">
                        <div class="data-box"><label>Full Name</label><span><?= esc_html($g['name']) ?></span></div>
                        <div class="data-box"><label>Phone</label><span><?= esc_html($g['phone'] ?: 'N/A') ?></span></div>
                        <div class="data-box"><label>Email</label><span><?= esc_html($g['email'] ?: 'N/A') ?></span></div>
                    </div>

                    <div class="guest-folio-grid">
                        <div class="data-box"><label>Gender</label><span><?= esc_html($g['gender']) ?></span></div>
                        <div class="data-box"><label>Age</label><span><?= esc_html($g['age']) ?> Years</span></div>
                        <div class="data-box"><label>Identity Info</label><span><?= esc_html($g['id_type'] ?: 'N/A') ?></span></div>
                    </div>

                    <div style="margin-bottom:20px;">
                        <label class="folio-section-title" style="margin-top:0; font-size:9px; border:none; margin-bottom:5px;">Residential Address</label>
                        <span style="font-size:14px; font-weight:600; color:var(--slate);"><?= nl2br(esc_html($g['address'])) ?></span>
                    </div>

                    <div class="doc-preview-grid">
                        <div class="doc-item">
                            <?php if(!empty($g['photo'])): ?> 
                                <img src="<?= wp_get_attachment_url($g['photo']) ?>" onclick="window.open(this.src)"> 
                            <?php else: ?> 
                                <div style="height:160px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-admin-users" style="font-size:40px; width:40px; height:40px;"></span></div> 
                            <?php endif; ?>
                            <label>GUEST PHOTO</label>
                        </div>
                        <div class="doc-item">
                            <?php if(!empty($g['nid'])): ?> 
                                <img src="<?= wp_get_attachment_url($g['nid']) ?>" onclick="window.open(this.src)"> 
                            <?php else: ?> 
                                <div style="height:160px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><span class="dashicons dashicons-id-alt" style="font-size:40px; width:40px; height:40px;"></span></div> 
                            <?php endif; ?>
                            <label>ID DOCUMENT (NID/PASS)</label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="folio-section-title">Stay Information</div>
            <div class="guest-folio-grid">
                <div class="data-box"><label>Check-In</label><span><?= date('D, M j, Y', strtotime($checkin)) ?></span></div>
                <div class="data-box"><label>Check-Out</label><span><?= date('D, M j, Y', strtotime($checkout)) ?></span></div>
                <div class="data-box"><label>Stay Period</label><span><?= $nights ?> Nights</span></div>
            </div>
        </div>
    </div>

    <div class="sidebar-inner">
        <div class="summary-card">
            <div class="folio-section-title" style="margin-top:0; border-color:var(--gold);">Invoice Summary</div>
            
            <div class="bill-row"><span>Room Base Rate</span><strong>৳<?= number_format($room_price) ?></strong></div>
            <div class="bill-row"><span>Duration</span><strong><?= $nights ?> Nights</strong></div>
            <div class="bill-row" style="border:none;"><span>Occupancy</span><strong><?= count($guests) ?> Guests</strong></div>
            
            <div class="bill-total">
                <span style="font-size:14px; font-weight:700; color:#94a3b8; align-self:center;">TOTAL PAID</span>
                <span>৳<?= number_format($total_bill) ?></span>
            </div>

            <div style="margin-top:35px; padding:20px; background:#f8fafc; border-radius:12px; border:1px solid #eee;">
                <label class="data-box"><label>Payment Method</label>
                <div style="display:flex; align-items:center; gap:8px; margin-top:5px;">
                    <span class="dashicons dashicons-yes-alt" style="color:#10b981;"></span>
                    <span style="font-weight:800; color:var(--slate); text-transform:uppercase;"><?= esc_html($method) ?></span>
                </div>
                
                <?php if($method !== 'cash'): ?>
                    <div style="margin-top:15px; border-top:1px solid #eee; padding-top:15px; font-size:12px;">
                        <div style="margin-bottom:5px;"><strong>Acc:</strong> <?= esc_html($mfs_acc) ?></div>
                        <div><strong>TrxID:</strong> <code style="background:#fff; padding:2px 5px; border:1px solid #ddd;"><?= esc_html($mfs_trx) ?></code></div>
                    </div>
                <?php endif; ?>
            </div>
            
            <p style="text-align:center; font-size:11px; color:#94a3b8; margin-top:20px;">Issued by Infinity Hotel Management System</p>
        </div>
    </div>
</div>