<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. CORE CALCULATION ENGINE (With Date Filtering)
 */
function ihb_calculate_data($start_date = null, $end_date = null) {
    $rev = 0; $exp = 0;
    
    // Revenue from Bookings
    $booking_args = ['post_type' => 'ihb_bookings', 'numberposts' => -1, 'post_status' => 'publish'];
    $bookings = get_posts($booking_args);
    
    foreach($bookings as $b) {
        $checkin  = get_post_meta($b->ID, '_ihb_checkin', true);
        
        // Apply Date Filter
        if ($start_date && $checkin < $start_date) continue;
        if ($end_date && $checkin > $end_date) continue;

        $rid      = get_post_meta($b->ID, '_ihb_room_id', true);
        $checkout = get_post_meta($b->ID, '_ihb_checkout', true);
        $price    = (float)get_post_meta($rid, '_ihb_price', true);

        $date1 = new DateTime($checkin);
        $date2 = new DateTime($checkout);
        $nights = $date1->diff($date2)->days ?: 1;

        $rev += ($price * $nights);
    }

    // Expenses
    $expenses = get_posts(['post_type' => 'ihb_expenses', 'numberposts' => -1, 'post_status' => 'publish']);
    foreach($expenses as $e) { 
        $ex_date = get_post_meta($e->ID, '_ihb_date', true);
        
        // Apply Date Filter
        if ($start_date && $ex_date < $start_date) continue;
        if ($end_date && $ex_date > $end_date) continue;

        $exp += (float)get_post_meta($e->ID, '_ihb_amount', true); 
    }

    return [
        'rev'    => $rev, 
        'exp'    => $exp, 
        'profit' => $rev - $exp,
        'count'  => count($bookings)
    ];
}

/**
 * 2. MAIN ANALYTICS PAGE
 */
function ihb_render_analytics() {
    // Handle Filter Inputs
    $start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // Default to start of month
    $end   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');    // Default to today
    
    $data = ihb_calculate_data($start, $end);
    $profit_color = ($data['profit'] >= 0) ? '#10b981' : '#ef4444';
    ?>

    <style>
        .ihb-filter-bar { background: #fff; padding: 15px 25px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .ihb-filter-bar label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
        .ihb-filter-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; color: #1e293b; }
        
        .ihb-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 30px; }
        .ihb-stat-box { background: #fff; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; position: relative; overflow: hidden; }
        .ihb-stat-box small { display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px; }
        .ihb-stat-box h2 { font-size: 32px; font-weight: 900; margin: 0; position: relative; z-index: 1; }
        .icon-bg { position: absolute; right: -10px; bottom: -10px; font-size: 70px; color: #f8fafc; z-index: 0; }
    </style>

    <div class="ihb-header">
        <div>
            <h2>Business Intelligence</h2>
            <p style="color: #64748b;">Showing data from <?= date('M d', strtotime($start)) ?> to <?= date('M d, Y', strtotime($end)) ?></p>
        </div>
        
        <form method="GET" class="ihb-filter-bar">
            <input type="hidden" name="page" value="infinity-hotel">
            <input type="hidden" name="tab" value="analytics">
            
            <div>
                <label>From</label><br>
                <input type="date" name="start_date" class="ihb-filter-input" value="<?= $start ?>">
            </div>
            <div>
                <label>To</label><br>
                <input type="date" name="end_date" class="ihb-filter-input" value="<?= $end ?>">
            </div>
            <button type="submit" class="ihb-btn-gold" style="margin-top: 18px; padding: 10px 20px;">Filter</button>
        </form>
    </div>

    <div class="ihb-stat-grid">
        <div class="ihb-stat-box">
            <span class="dashicons dashicons-chart-area icon-bg"></span>
            <small>Revenue</small>
            <h2>৳<?= number_format($data['rev'], 2) ?></h2>
        </div>
        <div class="ihb-stat-box">
            <span class="dashicons dashicons-cart icon-bg"></span>
            <small>Expenses</small>
            <h2 style="color:#ef4444;">৳<?= number_format($data['exp'], 2) ?></h2>
        </div>
        <div class="ihb-stat-box">
            <span class="dashicons dashicons-bank icon-bg"></span>
            <small>Net Profit</small>
            <h2 style="color: <?= $profit_color ?>;">৳<?= number_format($data['profit'], 2) ?></h2>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
        <div class="ihb-card" style="background:#fff; border:1px solid #e2e8f0; height:300px; display:flex; align-items:center; justify-content:center; border-radius:12px;">
            <div style="text-align:center; color:#94a3b8;">
                <span class="dashicons dashicons-chart-line" style="font-size:40px; width:40px; height:40px;"></span>
                <p>Trend data visualization for selected period</p>
            </div>
        </div>

        <div class="ihb-card" style="background:#1e293b; color:#fff; padding:30px; border-radius:12px;">
            <h3 style="color:var(--gold); margin-top:0;">Period Insights</h3>
            <p style="font-size:13px; color:#94a3b8;">Analysis for the selected <?= (new DateTime($start))->diff(new DateTime($end))->days ?> day window.</p>
            <div style="margin-top:20px; font-size:14px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:15px; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">
                    <span>Profit Margin:</span>
                    <span style="font-weight:700; color:#10b981;"><?= $data['rev'] > 0 ? round(($data['profit'] / $data['rev']) * 100, 1) : 0 ?>%</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span>Daily Avg:</span>
                    <span style="font-weight:700;">৳<?= number_format($data['rev'] / ((new DateTime($start))->diff(new DateTime($end))->days ?: 1), 2) ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php
}