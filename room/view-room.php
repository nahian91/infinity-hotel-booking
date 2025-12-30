<?php
if (!defined('ABSPATH')) exit;

$room_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$room_id || get_post_type($room_id) !== 'ihb_rooms') {
    echo '<div class="notice notice-error"><p>Room not found.</p></div>';
    return;
}

// Fetch Data
$room_no    = get_the_title($room_id);
$content    = get_post_field('post_content', $room_id);
$type       = get_post_meta($room_id, '_ihb_type', true);
$price      = (float)get_post_meta($room_id, '_ihb_price', true);
$status     = get_post_meta($room_id, '_ihb_status', true) ?: 'available';
$facilities = get_post_meta($room_id, '_ihb_features', true) ?: []; 

$img_url = get_the_post_thumbnail_url($room_id, 'full');
if (!$img_url) {
    $img_url = 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80';
}

$status_colors = [
    'available'   => ['bg' => '#ecfdf5', 'text' => '#10b981', 'label' => 'Available'],
    'booked'      => ['bg' => '#fffbeb', 'text' => '#f59e0b', 'label' => 'Occupied'],
    'maintenance' => ['bg' => '#fef2f2', 'text' => '#ef4444', 'label' => 'Maintenance'],
];
$current_status = $status_colors[$status] ?? $status_colors['available'];

$master_facilities = [
    'wifi' => ['label' => 'High-Speed WiFi', 'icon' => 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    'ac'   => ['label' => 'Climate Control', 'icon' => 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    'tv'   => ['label' => 'Smart TV', 'icon' => 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    'bed'  => ['label' => 'King Bed', 'icon' => 'M19,7H5V14H19V7M19,15H5V17H19V15M19,5H5A2,2 0 0,0 3,7V17A2,2 0 0,0 5,19H19A2,2 0 0,0 21,17V7A2,2 0 0,0 19,5Z'],
    'bar'  => ['label' => 'Mini Bar', 'icon' => 'M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2H7M7,4V10H17V4H7M7,12V20H17V12H7M13,14V18H15V14H13Z']
];
?>

<style>
    :root { --ihb-gold: #c19b76; --ihb-dark: #1e293b; --ihb-slate: #64748b; --ihb-bg: #f8fafc; }
    
    .ihb-view-wrap { max-width: 900px; margin: 40px auto; padding: 0 20px; font-family: 'Inter', -apple-system, sans-serif; }
    
    /* Top Header Navigation */
    .ihb-top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .ihb-back-link { text-decoration: none; color: var(--ihb-slate); font-weight: 600; font-size: 14px; display: flex; align-items: center; transition: 0.2s; }
    .ihb-back-link:hover { color: var(--ihb-gold); }

    /* Hero Banner */
    .ihb-full-hero { position: relative; width: 100%; height: 500px; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.15); margin-bottom: -60px; z-index: 1; }
    .ihb-full-hero img { width: 100%; height: 100%; object-fit: cover; }
    .ihb-hero-badge { position: absolute; top: 20px; right: 20px; padding: 8px 16px; border-radius: 100px; font-size: 12px; font-weight: 700; text-transform: uppercase; background: rgba(255,255,255,0.9); backdrop-filter: blur(5px); }

    /* Main Container Card */
    .ihb-main-content { position: relative; background: #fff; border-radius: 24px; padding: 100px 50px 50px; border: 1px solid #f1f5f9; box-shadow: 0 10px 30px rgba(0,0,0,0.05); z-index: 0; }
    
    /* Title & Price Row */
    .ihb-title-row { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 30px; }
    .ihb-main-title h1 { font-size: 42px; font-weight: 800; color: var(--ihb-dark); margin: 0; letter-spacing: -1.5px; }
    .ihb-main-title span { color: var(--ihb-gold); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; }
    
    .ihb-price-tag { text-align: right; }
    .ihb-price-tag .amount { display: block; font-size: 36px; font-weight: 800; color: var(--ihb-dark); }
    .ihb-price-tag .period { color: var(--ihb-slate); font-size: 14px; }

    /* Section Typography */
    .ihb-section-h { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--ihb-slate); margin-bottom: 20px; display: block; border-left: 3px solid var(--ihb-gold); padding-left: 12px; }
    
    /* Amenities Horizontal Scroll/Grid */
    .ihb-amenities-wrap { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 50px; }
    .ihb-amenity-pill { display: flex; align-items: center; gap: 10px; padding: 12px 18px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; transition: 0.3s; }
    .ihb-amenity-pill:hover { background: #fff; border-color: var(--ihb-gold); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .ihb-amenity-pill svg { width: 20px; height: 20px; fill: var(--ihb-gold); }
    .ihb-amenity-pill span { font-size: 13px; font-weight: 600; color: var(--ihb-dark); }

    /* Floating Action Bar */
    .ihb-action-bar { display: flex; gap: 15px; margin-top: 40px; padding-top: 30px; border-top: 1px solid #f1f5f9; }
    .ihb-btn-full { flex: 1; text-align: center; padding: 16px; border-radius: 14px; font-weight: 700; text-decoration: none; transition: 0.3s; }
    .ihb-btn-gold { background: var(--ihb-gold); color: #fff; border: none; }
    .ihb-btn-gold:hover { background: #a88562; box-shadow: 0 10px 20px rgba(193, 155, 118, 0.3); }
    .ihb-btn-outline { border: 1px solid #e2e8f0; color: var(--ihb-dark); }
    .ihb-btn-outline:hover { background: #f8fafc; }
</style>

<div class="ihb-view-wrap">
    
    <div class="ihb-top-bar">
        <a href="?page=infinity-hotel&tab=all_rooms" class="ihb-back-link">
            <span class="dashicons dashicons-arrow-left-alt2" style="margin-right:8px;"></span> BACK TO INVENTORY
        </a>
        <div style="font-size: 12px; color: var(--ihb-slate); font-weight: 600;">
            ROOM ID: #<?php echo $room_id; ?>
        </div>
    </div>

    <div class="ihb-full-hero">
        <img src="<?php echo esc_url($img_url); ?>" alt="Room Header">
        <div class="ihb-hero-badge" style="color: <?php echo $current_status['text']; ?>">
            ● <?php echo $current_status['label']; ?>
        </div>
    </div>

    <div class="ihb-main-content">
        <div class="ihb-title-row">
            <div class="ihb-main-title">
                <span><?php echo esc_html($type); ?> Suite</span>
                <h1>Room <?php echo esc_html($room_no); ?></h1>
            </div>
            <div class="ihb-price-tag">
                <span class="amount">৳<?php echo number_format($price, 0); ?></span>
                <span class="period">per night (excl. VAT)</span>
            </div>
        </div>

        <div style="margin-bottom: 50px;">
            <span class="ihb-section-h">Overview</span>
            <div style="font-size: 17px; line-height: 1.8; color: #475569;">
                <?php echo wpautop($content) ?: 'No description provided for this premium room. Contact management for full property details.'; ?>
            </div>
        </div>

        <div>
            <span class="ihb-section-h">Amenities & Features</span>
            <div class="ihb-amenities-wrap">
                <?php 
                if ($facilities): 
                    foreach($facilities as $fid): 
                        if(!isset($master_facilities[$fid])) continue; 
                        $f = $master_facilities[$fid]; 
                ?>
                    <div class="ihb-amenity-pill">
                        <svg viewBox="0 0 24 24"><path d="<?php echo $f['icon']; ?>"/></svg>
                        <span><?php echo $f['label']; ?></span>
                    </div>
                <?php 
                    endforeach; 
                else: 
                    echo '<p style="color:var(--ihb-slate);">Contact concierge for amenity list.</p>'; 
                endif; 
                ?>
            </div>
        </div>

        <div class="ihb-action-bar">
            <a href="?page=infinity-hotel&tab=edit_room&id=<?php echo $room_id; ?>" class="ihb-btn-full ihb-btn-gold">
                <span class="dashicons dashicons-edit" style="font-size:16px; margin-right:8px;"></span> Edit Room Details
            </a>
            <a href="#" class="ihb-btn-full ihb-btn-outline">
                <span class="dashicons dashicons-calendar-alt" style="font-size:16px; margin-right:8px;"></span> View Bookings
            </a>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <p style="font-size: 12px; color: var(--ihb-slate);">Property synchronized on <?php echo get_the_modified_date('F j, Y'); ?></p>
        </div>
    </div>
</div>