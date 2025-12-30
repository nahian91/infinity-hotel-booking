<?php
if (!defined('ABSPATH')) exit;

// 1. Get Room ID from URL
$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$room_id || get_post_type($room_id) !== 'ihb_rooms') {
    echo '<div class="notice notice-error"><p>Room not found.</p></div>';
    return;
}

// 2. Fetch Data
$room_no    = get_the_title($room_id); // This is your Dynamic Room No
$content    = get_post_field('post_content', $room_id);
$type       = get_post_meta($room_id, '_ihb_type', true);
$price      = get_post_meta($room_id, '_ihb_price', true);
$status     = get_post_meta($room_id, '_ihb_status', true) ?: 'available';
$facilities = get_post_meta($room_id, '_ihb_facilities', true) ?: [];
$img_url    = get_the_post_thumbnail_url($room_id, 'large') ?: 'https://via.placeholder.com/800x400';

// Facility Icons Mapping
$master_facilities = [
    'wifi' => ['label' => 'WiFi', 'icon' => 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    'ac' => ['label' => 'A/C', 'icon' => 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    'tv' => ['label' => 'TV', 'icon' => 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    'bed' => ['label' => 'King Bed', 'icon' => 'M19,7H5V14H19V7M19,15H5V17H19V15M19,5H5A2,2 0 0,0 3,7V17A2,2 0 0,0 5,19H19A2,2 0 0,0 21,17V7A2,2 0 0,0 19,5Z']
];
?>

<style>
    .ihb-view-container { display: grid; grid-template-columns: 1fr 340px; gap: 30px; margin-top: 25px; }
    
    /* Hero Image */
    .ihb-view-hero { border-radius: 15px; overflow: hidden; height: 380px; margin-bottom: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .ihb-view-hero img { width: 100%; height: 100%; object-fit: cover; }

    /* Content Styling */
    .ihb-view-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 25px; }
    .ihb-view-title { font-size: 28px; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; }
    .ihb-view-meta { display: flex; gap: 20px; color: #64748b; font-size: 14px; margin-bottom: 25px; }
    .ihb-desc { color: #475569; line-height: 1.8; font-size: 15px; }
    .ihb-label-sm { display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; }

    /* Info Sidebar Rows */
    .ihb-info-row { display: flex; justify-content: space-between; padding: 18px 0; border-bottom: 1px solid #f1f5f9; }
    .ihb-info-row:last-child { border-bottom: none; }
    .ihb-info-label { font-weight: 700; color: #94a3b8; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
    .ihb-info-value { font-weight: 700; color: #1e293b; font-size: 14px; }
    .ihb-price-large { font-size: 28px; color: var(--gold); font-weight: 800; margin-bottom: 5px; }

    /* Amenities Grid */
    .ihb-view-facilities { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 15px; margin-top: 20px; }
    .ihb-f-item { background: #f8fafc; padding: 15px; border-radius: 10px; text-align: center; border: 1px solid #e2e8f0; transition: 0.3s; }
    .ihb-f-item:hover { border-color: var(--gold); background: #fff; transform: translateY(-3px); }
    .ihb-f-item svg { width: 24px; height: 24px; fill: var(--gold); margin-bottom: 8px; opacity: 0.8; }
    .ihb-f-item span { display: block; font-size: 11px; font-weight: 700; color: #334155; }
</style>

<div class="ihb-header">
    <div>
        <a href="?page=infinity-hotel&tab=all_rooms" style="text-decoration: none; color: var(--gold); font-weight: 700; font-size: 13px;">← Back to Inventory</a>
        <h2 style="margin-top: 8px;">Room Preview</h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="?page=infinity-hotel&tab=edit_room&id=<?= $room_id ?>" class="ihb-btn-gold" style="background: #1e293b;">Edit Details</a>
        <a href="<?= get_permalink($room_id) ?>" target="_blank" class="ihb-btn-gold">View Live</a>
    </div>
</div>

<div class="ihb-view-container">
    
    <div class="ihb-view-main">
        <div class="ihb-view-hero">
            <img src="<?= $img_url ?>" alt="Room Image">
        </div>

        <div class="ihb-view-card">
            <h1 class="ihb-view-title">Room <?= esc_html($room_no) ?></h1>
            <div class="ihb-view-meta">
                <span><span class="dashicons dashicons-category" style="font-size: 17px; margin-right: 5px; color: var(--gold);"></span> <?= esc_html($type) ?></span>
            </div>
            
            <label class="ihb-label-sm">About this Room</label>
            <div class="ihb-desc">
                <?= wpautop($content) ?: '<i style="color:#94a3b8;">No description provided for this room.</i>' ?>
            </div>
        </div>

        <div class="ihb-view-card">
            <label class="ihb-label-sm">Room Amenities</label>
            <div class="ihb-view-facilities">
                <?php if ($facilities): ?>
                    <?php foreach($facilities as $fid): 
                        if(!isset($master_facilities[$fid])) continue;
                        $f = $master_facilities[$fid];
                    ?>
                    <div class="ihb-f-item">
                        <svg viewBox="0 0 24 24"><path d="<?= $f['icon'] ?>"/></svg>
                        <span><?= $f['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #94a3b8; font-style: italic;">No amenities assigned.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ihb-view-sidebar">
        <div class="ihb-view-card">
            <label class="ihb-label-sm">Pricing</label>
            <div class="ihb-price-large">৳<?= number_format($price, 2) ?></div>
            
            <div style="margin-top: 20px;">
                <div class="ihb-info-row">
                    <span class="ihb-info-label">Room Number</span>
                    <span class="ihb-info-value"><?= esc_html($room_no) ?></span>
                </div>

                <div class="ihb-info-row">
                    <span class="ihb-info-label">Room Type</span>
                    <span class="ihb-info-value"><?= esc_html($type) ?></span>
                </div>

                <div class="ihb-info-row">
                    <span class="ihb-info-label">Availability Status</span>
                    <span class="ihb-info-value" style="color: <?= $status === 'available' ? '#16a34a' : ($status === 'booked' ? '#ea580c' : '#dc2626') ?>;">
                        <?= ucfirst($status) ?>
                    </span>
                </div>
                
                <div class="ihb-info-row">
                    <span class="ihb-info-label">System ID</span>
                    <span class="ihb-info-value">#<?= $room_id ?></span>
                </div>
            </div>
        </div>
    </div>
</div>