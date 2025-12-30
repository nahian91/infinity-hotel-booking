<?php 
if (!defined('ABSPATH')) exit; 

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id || get_post_type($id) !== 'ihb_bookings') { 
    echo "<div class='notice notice-error'><p>Invalid Booking ID or Record not found.</p></div>"; 
    return; 
}

// 1. DATA COLLECTION
$guests   = get_post_meta($id, '_ihb_guests_list', true) ?: [];
$rid      = get_post_meta($id, '_ihb_room_id', true);
$checkin  = get_post_meta($id, '_ihb_checkin', true);
$checkout = get_post_meta($id, '_ihb_checkout', true);
$method   = get_post_meta($id, '_ihb_pay_method', true) ?: 'cash';
$mfs_trx  = get_post_meta($id, '_ihb_mfs_trx', true);
$mfs_acc  = get_post_meta($id, '_ihb_mfs_phone', true);

// Room Details
$room_exists = get_post_status($rid);
$room_title  = $room_exists ? get_the_title($rid) : 'Deleted Room';
$room_price  = get_post_meta($rid, '_ihb_price', true) ?: 0;
$room_type   = get_post_meta($rid, '_ihb_type', true) ?: 'Standard';

// Calculation Logic
$nights = 1;
if ($checkin && $checkout) {
    $date1 = new DateTime($checkin);
    $date2 = new DateTime($checkout);
    $diff = $date1->diff($date2);
    $nights = $diff->days > 0 ? $diff->days : 1;
}
$total_bill = get_post_meta($id, '_ihb_total_price', true) ?: ($nights * $room_price);
?>

<style>
    :root { --ihb-gold: #c19b76; --ihb-dark: #0f172a; --ihb-slate: #64748b; --ihb-border: #f1f5f9; }
    
    .ihb-folio-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
    
    /* Top Navigation */
    .ihb-nav-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .ihb-btn-back { text-decoration: none; color: var(--ihb-slate); font-weight: 600; font-size: 13px; display: flex; align-items: center; }
    
    /* Layout */
    .ihb-folio-grid { display: grid; grid-template-columns: 1fr 360px; gap: 40px; }
    
    /* Folio Main Card */
    .ihb-main-folio { background: #fff; border-radius: 24px; border: 1px solid var(--ihb-border); box-shadow: 0 10px 40px rgba(0,0,0,0.03); overflow: hidden; }
    
    /* Header Aesthetic */
    .folio-hero { padding: 60px; background: #fafafa; border-bottom: 1px solid var(--ihb-border); position: relative; }
    .folio-hero::after { content: 'OFFICIAL RECORD'; position: absolute; top: 40px; right: -25px; transform: rotate(45deg); background: var(--ihb-gold); color: #fff; font-size: 9px; font-weight: 800; padding: 5px 40px; letter-spacing: 1px; }
    
    .folio-id-tag { font-size: 11px; font-weight: 800; color: var(--ihb-gold); letter-spacing: 2px; margin-bottom: 10px; display: block; }
    .folio-title { font-size: 42px; font-weight: 800; color: var(--ihb-dark); margin: 0; letter-spacing: -1.5px; }
    .room-indicator { margin-top: 15px; display: flex; align-items: center; gap: 15px; }
    .room-no-circle { background: var(--ihb-dark); color: #fff; padding: 6px 16px; border-radius: 100px; font-weight: 800; font-size: 14px; }
    
    .folio-content { padding: 60px; }
    .section-label { font-size: 10px; font-weight: 800; color: #cbd5e1; text-transform: uppercase; letter-spacing: 2px; display: block; margin-bottom: 30px; border-bottom: 1px solid var(--ihb-border); padding-bottom: 10px; }

    /* Guest Profile System */
    .guest-profile { margin-bottom: 40px; display: grid; grid-template-columns: 140px 1fr; gap: 30px; padding-bottom: 40px; border-bottom: 1px dashed var(--ihb-border); }
    .guest-avatar-wrap { text-align: center; }
    .guest-avatar { width: 120px; height: 120px; border-radius: 20px; object-fit: cover; border: 4px solid #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.05); background: #f8fafc; margin-bottom: 10px; }
    
    .guest-info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .info-cell label { font-size: 10px; font-weight: 700; color: var(--ihb-slate); text-transform: uppercase; display: block; margin-bottom: 4px; }
    .info-cell span { font-size: 15px; font-weight: 600; color: var(--ihb-dark); }

    /* Identity Docs Hover Effect */
    .id-doc-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 15px; font-size: 12px; font-weight: 700; color: var(--ihb-gold); text-decoration: none; padding: 6px 12px; background: rgba(193, 155, 118, 0.08); border-radius: 8px; transition: 0.2s; }
    .id-doc-link:hover { background: var(--ihb-gold); color: #fff; }

    /* Sidebar Summary Style */
    .summary-box { background: #fff; border-radius: 24px; padding: 40px; border: 1px solid var(--ihb-border); position: sticky; top: 30px; }
    .summary-title { font-size: 18px; font-weight: 800; color: var(--ihb-dark); margin-bottom: 25px; }
    .sum-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; color: var(--ihb-slate); }
    .sum-row.total { margin-top: 25px; padding-top: 25px; border-top: 2px solid var(--ihb-dark); color: var(--ihb-dark); font-weight: 900; font-size: 28px; }
    
    .payment-status-badge { background: #ecfdf5; color: #10b981; padding: 15px; border-radius: 16px; margin-top: 30px; display: flex; align-items: center; gap: 12px; font-size: 13px; font-weight: 700; }

    @media print {
        .ihb-nav-top, .summary-box, .id-doc-link, #adminmenuback, #adminmenuwrap { display: none !important; }
        .ihb-folio-grid { grid-template-columns: 1fr; }
        .ihb-main-folio { border: none; }
        .folio-hero { padding: 30px; background: #fff; border-bottom: 2px solid #000; }
    }
</style>

<div class="ihb-folio-container">
    <div class="ihb-nav-top">
        <a href="?page=infinity-hotel&tab=all_bookings" class="ihb-btn-back">
            <span class="dashicons dashicons-arrow-left-alt2" style="margin-right:8px;"></span> Back to Ledger
        </a>
        <button onclick="window.print()" class="button button-large">Print Statement</button>
    </div>

    <div class="ihb-folio-grid">
        <div class="ihb-main-folio">
            <div class="folio-hero">
                <span class="folio-id-tag">RESERVATION #<?= $id ?></span>
                <h1 class="folio-title"><?= get_the_title($id) ?></h1>
                <div class="room-indicator">
                    <div class="room-no-circle">ROOM <?= $room_title ?></div>
                    <span style="font-size:12px; font-weight:700; color:var(--ihb-slate);"><?= strtoupper($room_type) ?> SUITE</span>
                </div>
            </div>

            <div class="folio-content">
                <span class="section-label">Registered Guest Details</span>
                
                <?php foreach($guests as $g): ?>
                    <div class="guest-profile">
                        <div class="guest-avatar-wrap">
                            <?php 
                                $photo_url = !empty($g['photo']) ? wp_get_attachment_url($g['photo']) : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                            ?>
                            <img src="<?= $photo_url ?>" class="guest-avatar">
                            <span style="font-size:10px; font-weight:800; color:var(--ihb-gold);"><?= strtoupper($g['id_type']) ?></span>
                        </div>
                        
                        <div class="guest-info-body">
                            <div class="guest-info-grid">
                                <div class="info-cell"><label>Legal Name</label><span><?= esc_html($g['name']) ?></span></div>
                                <div class="info-cell"><label>Contact</label><span><?= esc_html($g['phone'] ?: 'N/A') ?></span></div>
                                <div class="info-cell"><label>Gender/Age</label><span><?= $g['gender'] ?> (<?= $g['age'] ?> Yrs)</span></div>
                                <div class="info-cell"><label>Address</label><span><?= esc_html($g['address']) ?></span></div>
                            </div>
                            
                            <?php if(!empty($g['nid'])): ?>
                                <a href="<?= wp_get_attachment_url($g['nid']) ?>" target="_blank" class="id-doc-link">
                                    <span class="dashicons dashicons-id"></span> View Identity Document
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <span class="section-label" style="margin-top:20px;">Stay Information</span>
                <div class="guest-info-grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div class="info-cell"><label>Arrival</label><span><?= date('F j, Y', strtotime($checkin)) ?></span></div>
                    <div class="info-cell"><label>Departure</label><span><?= date('F j, Y', strtotime($checkout)) ?></span></div>
                    <div class="info-cell"><label>Duration</label><span><?= $nights ?> Luxury Night(s)</span></div>
                </div>
            </div>
        </div>

        <div class="ihb-sidebar-wrap">
            <div class="summary-box">
                <h3 class="summary-title">Financial Summary</h3>
                
                <div class="sum-row">
                    <span>Base Rate</span>
                    <strong>৳<?= number_format($room_price) ?></strong>
                </div>
                <div class="sum-row">
                    <span>Stay Duration</span>
                    <strong>x <?= $nights ?></strong>
                </div>
                <div class="sum-row">
                    <span>Taxes & Fees</span>
                    <strong>Incl.</strong>
                </div>

                <div class="sum-row total">
                    <span style="font-size:12px; font-weight:700; color:var(--ihb-slate);">TOTAL</span>
                    <span>৳<?= number_format($total_bill) ?></span>
                </div>

                <div class="payment-status-badge">
                    <span class="dashicons dashicons-shield"></span>
                    <div>
                        <div style="text-transform:uppercase; font-size:10px;">Payment via <?= esc_html($method) ?></div>
                        Settled & Verified
                    </div>
                </div>

                <?php if($method !== 'cash'): ?>
                    <div style="margin-top:25px; font-size:11px; color:var(--ihb-slate); background:#fdfdfd; padding:15px; border-radius:12px; border:1px solid #f1f5f9;">
                        <strong>MFS TRN:</strong> <?= esc_html($mfs_trx) ?><br>
                        <strong>SENDER:</strong> <?= esc_html($mfs_acc) ?>
                    </div>
                <?php endif; ?>

                <p style="font-size:10px; color:#cbd5e1; text-align:center; margin-top:30px;">
                    This is an electronically generated statement.<br>© <?= date('Y') ?> Infinity Hotel Management.
                </p>
            </div>
        </div>
    </div>
</div>