<?php 
if (!defined('ABSPATH')) exit;

function ihb_render_dashboard() {
    /**
     * 1. CALCULATION ENGINE
     */
    // Define the date range (Current Month)
    $start_date = date('Y-m-01');
    $end_date   = date('Y-m-t');

    // Fetch Bookings for Revenue
    $bookings = get_posts([
        'post_type'   => 'ihb_bookings',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $total_rev = 0;
    $monthly_booking_count = 0;
    foreach ($bookings as $b) {
        $checkin = get_post_meta($b->ID, '_ihb_checkin', true);
        $price   = get_post_meta($b->ID, '_ihb_total_price', true); 
        
        // Filter by Month
        if ($checkin >= $start_date && $checkin <= $end_date) {
            $total_rev += (float)$price;
            $monthly_booking_count++;
        }
    }

    // Fetch Expenses
    $expenses = get_posts([
        'post_type'   => 'ihb_expenses',
        'post_status' => 'publish',
        'numberposts' => -1,
    ]);

    $total_exp = 0;
    foreach ($expenses as $e) {
        // FIXED: Using keys from your save handler (_ihb_date and _ihb_amount)
        $exp_date = get_post_meta($e->ID, '_ihb_date', true);
        $amount   = get_post_meta($e->ID, '_ihb_amount', true);
        
        // Filter by Month
        if ($exp_date >= $start_date && $exp_date <= $end_date) {
            $total_exp += (float)$amount;
        }
    }

    $net_profit = $total_rev - $total_exp;

    // Get Recent 5 Activity
    $recent_bookings = get_posts([
        'post_type'   => 'ihb_bookings',
        'numberposts' => 5,
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC'
    ]);
    ?>
    
    <style>
        :root { --gold: #c19b76; --slate: #0f172a; --border: #e2e8f0; --success: #10b981; --danger: #ef4444; }
        
        .ihb-welcome-section { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .ihb-welcome-section h2 { font-size: 32px; font-weight: 900; color: var(--slate); margin: 0; letter-spacing: -1.5px; }
        
        .ihb-status-bar { display: flex; gap: 12px; align-items: center; }
        .ihb-status-pill { 
            background: #fff; border: 1px solid var(--border); padding: 10px 18px; 
            border-radius: 14px; display: flex; align-items: center; gap: 10px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .ihb-status-pill span { font-size: 13px; font-weight: 700; color: var(--slate); }
        .ihb-status-pill .live-time { font-family: monospace; font-size: 14px; color: var(--gold); }

        .ihb-dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .ihb-dash-card { background: #fff; padding: 25px; border-radius: 18px; border: 1px solid var(--border); transition: 0.3s; }
        .ihb-dash-card:hover { transform: translateY(-5px); border-color: var(--gold); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .ihb-dash-card small { display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 12px; }
        .ihb-dash-card h3 { font-size: 32px; font-weight: 900; margin: 0; color: var(--slate); letter-spacing: -1px; }
        
        .ihb-main-layout { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        .ihb-banner { 
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
            padding: 50px; border-radius: 24px; color: white; position: relative; 
            overflow: hidden; margin-bottom: 30px;
        }
        .ihb-banner h3 { font-size: 32px; font-weight: 800; margin: 0 0 15px 0; color: #fff; }
        
        .ihb-quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .action-item { 
            background: #fff; border: 1px solid var(--border); padding: 25px 15px; border-radius: 18px; 
            text-decoration: none; color: var(--slate); display: flex; flex-direction: column; align-items: center; gap: 12px;
            transition: 0.2s;
        }
        .action-item:hover { border-color: var(--gold); transform: scale(1.03); }
        .action-item .dashicons { font-size: 28px; color: var(--gold); }
        .action-item .text { font-weight: 800; font-size: 11px; text-transform: uppercase; }

        .recent-feed { background: #fff; border-radius: 20px; border: 1px solid var(--border); padding: 25px; }
        .feed-item { display: flex; align-items: center; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .feed-icon { width: 40px; height: 40px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--gold); }

        .ihb-btn-gold { 
            display: inline-flex; align-items: center; background: var(--gold); color: #fff; padding: 16px 32px; border-radius: 14px; 
            font-weight: 800; text-decoration: none; transition: 0.3s;
        }
    </style>

    <div class="ihb-welcome-section">
        <div>
            <h2>Operations Center</h2>
            <p>Welcome back to <strong>Infinity Hotel</strong> Management Dashboard.</p>
        </div>
        
        <div class="ihb-status-bar">
            <div class="ihb-status-pill">
                <span class="dashicons dashicons-calendar-alt"></span>
                <span><?= date('l, F j, Y') ?></span>
            </div>
            <div class="ihb-status-pill">
                <span class="dashicons dashicons-clock"></span>
                <span id="ihb-live-clock" class="live-time">00:00:00 AM</span>
            </div>
        </div>
    </div>

    <div class="ihb-dash-grid">
        <div class="ihb-dash-card">
            <small>Total Revenue</small>
            <h3>৳<?= number_format($total_rev) ?></h3>
        </div>
        <div class="ihb-dash-card">
            <small>Operational Expenses</small>
            <h3 style="color: var(--danger);">৳<?= number_format($total_exp) ?></h3>
        </div>
        <div class="ihb-dash-card">
            <small>Net Monthly Profit</small>
            <h3 style="color: <?= $net_profit >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                ৳<?= number_format($net_profit) ?>
            </h3>
        </div>
        <div class="ihb-dash-card">
            <small>Monthly Bookings</small>
            <h3><?= $monthly_booking_count ?></h3>
        </div>
    </div>

    <div class="ihb-main-layout">
        <div class="main-column">
            <div class="ihb-banner">
                <div class="ihb-banner-content">
                    <h3>New Guest Arrival?</h3>
                    <p style="color:#94a3b8; margin-bottom: 30px;">Process a new check-in, assign rooms, and collect payments instantly.</p>
                    <a href="?page=infinity-hotel&tab=add_booking" class="ihb-btn-gold">
                        <span class="dashicons dashicons-plus-alt" style="margin-right: 8px;"></span> Register New Booking
                    </a>
                </div>
                <span class="dashicons dashicons-building" style="position: absolute; right: -20px; bottom: -40px; font-size: 200px; width: 200px; height: 200px; opacity: 0.05; color: white;"></span>
            </div>

            <div class="ihb-quick-actions">
                <a href="?page=infinity-hotel&tab=add_expense" class="action-item">
                    <span class="dashicons dashicons-cart"></span>
                    <span class="text">Log Expense</span>
                </a>
                <a href="?page=infinity-hotel&tab=all_rooms" class="action-item">
                    <span class="dashicons dashicons-admin-home"></span>
                    <span class="text">Manage Rooms</span>
                </a>
                <a href="?page=infinity-hotel&tab=analytics" class="action-item">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <span class="text">View Reports</span>
                </a>
                <a href="?page=infinity-hotel&tab=expense_settings" class="action-item">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <span class="text">Master Lists</span>
                </a>
            </div>
        </div>

        <div class="side-column">
            <div class="recent-feed">
                <div style="font-weight: 800; font-size: 14px; margin-bottom: 20px; color: var(--slate);">
                    <span class="dashicons dashicons-update" style="color: var(--gold);"></span> RECENT ACTIVITY
                </div>
                
                <?php if ($recent_bookings): foreach($recent_bookings as $rb): 
                    $rid = get_post_meta($rb->ID, '_ihb_room_id', true);
                ?>
                    <div class="feed-item">
                        <div class="feed-icon"><span class="dashicons dashicons-businessman"></span></div>
                        <div style="flex:1;">
                            <div style="font-weight: 800; font-size: 13px; color: var(--slate);"><?= esc_html($rb->post_title) ?></div>
                            <div style="font-size: 11px; color: #94a3b8;">Room: <?= $rid ? get_the_title($rid) : 'N/A' ?></div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p style="text-align: center; color: #94a3b8; font-size: 12px; padding: 20px;">No recent activity.</p>
                <?php endif; ?>
                
                <a href="?page=infinity-hotel&tab=all_bookings" style="display: block; text-align: center; margin-top: 20px; font-size: 11px; font-weight: 800; color: var(--gold); text-decoration: none; text-transform: uppercase;">View All Ledger &rarr;</a>
            </div>
        </div>
    </div>

    <script>
    function updateIHBClock() {
        const now = new Date();
        let hours = now.getHours(), minutes = now.getMinutes(), seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        minutes = minutes < 10 ? '0'+minutes : minutes;
        seconds = seconds < 10 ? '0'+seconds : seconds;
        const clockEl = document.getElementById('ihb-live-clock');
        if(clockEl) clockEl.textContent = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
    }
    setInterval(updateIHBClock, 1000); updateIHBClock();
    </script>
    <?php
}