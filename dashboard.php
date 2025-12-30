<?php 
if (!defined('ABSPATH')) exit;

function ihb_render_dashboard() {
    // 1. DATA COLLECTION
    // Calculate data for the current month
    $data = ihb_calculate_data(date('Y-m-01'), date('Y-m-d')); 
    
    $rev    = (float)($data['rev'] ?? 0);
    $exp    = (float)($data['exp'] ?? 0);
    $profit = $rev - $exp;
    $count  = intval($data['count'] ?? 0);

    // Get Recent 5 Bookings for the Feed
    $recent_bookings = get_posts([
        'post_type'   => 'ihb_bookings',
        'numberposts' => 5,
        'post_status' => 'publish'
    ]);
    ?>
    
    <style>
        :root { --gold: #c19b76; --slate: #0f172a; --border: #e2e8f0; }
        
        /* Header & Pills */
        .ihb-welcome-section { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .ihb-welcome-section h2 { font-size: 32px; font-weight: 900; color: var(--slate); margin: 0; letter-spacing: -1px; }
        .ihb-welcome-section p { color: #64748b; margin-top: 5px; font-size: 15px; }

        .ihb-status-bar { display: flex; gap: 10px; align-items: center; }
        .ihb-status-pill { 
            background: #fff; border: 1px solid var(--border); padding: 8px 16px; 
            border-radius: 100px; display: flex; align-items: center; gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .ihb-status-pill .dashicons { font-size: 16px; width: 16px; height: 16px; color: var(--gold); }
        .ihb-status-pill span { font-size: 13px; font-weight: 700; color: #64748b; }
        .ihb-status-pill .live-time { color: var(--slate); font-family: monospace; min-width: 85px; font-size: 14px; }

        /* Dashboard Stat Grid */
        .ihb-dash-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .ihb-dash-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.02); transition: 0.3s; }
        .ihb-dash-card:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.05); }
        .ihb-dash-card small { display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 10px; }
        .ihb-dash-card h3 { font-size: 28px; font-weight: 900; margin: 0; color: var(--slate); letter-spacing: -1px; }
        
        /* Layout Structure */
        .ihb-main-layout { display: grid; grid-template-columns: 1fr 350px; gap: 30px; }
        .ihb-banner { 
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); 
            padding: 45px; border-radius: 20px; color: white; position: relative; 
            overflow: hidden; display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 30px;
        }
        .ihb-banner-content { position: relative; z-index: 2; }
        .ihb-banner h3 { font-size: 28px; font-weight: 800; margin: 0 0 12px 0; color: #fff; }
        .ihb-banner p { color: #94a3b8; font-size: 16px; max-width: 400px; margin-bottom: 25px; line-height: 1.6; }
        
        /* Recent Feed */
        .recent-feed { background: #fff; border-radius: 16px; border: 1px solid var(--border); padding: 25px; }
        .feed-title { font-weight: 800; font-size: 14px; text-transform: uppercase; color: var(--slate); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .feed-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .feed-item:last-child { border: none; }
        .feed-icon { width: 36px; height: 36px; background: #f8fafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--gold); }
        
        /* Quick Actions */
        .ihb-quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 30px; }
        .action-item { 
            background: #fff; border: 1px solid var(--border); padding: 22px; border-radius: 14px; 
            text-decoration: none; color: var(--slate); display: flex; flex-direction: column; align-items: center; gap: 10px;
            transition: 0.2s; text-align: center;
        }
        .action-item:hover { border-color: var(--gold); background: #fdfaf7; }
        .action-item span.dashicons { font-size: 24px; width: 24px; height: 24px; color: var(--gold); }
        .action-item span.text { font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }

        .ihb-btn-gold { 
            background: var(--gold); color: #fff; padding: 14px 28px; border-radius: 10px; 
            font-weight: 800; transition: 0.3s; cursor: pointer; border: none; text-decoration: none;
        }
        .ihb-btn-gold:hover { background: #ae8a65; transform: translateY(-2px); }
    </style>

    <div class="ihb-welcome-section">
        <div>
            <h2>Dashboard</h2>
            <p>Welcome back to <strong>Infinity Hotel</strong> Management.</p>
        </div>
        
        <div class="ihb-status-bar">
            <div class="ihb-status-pill">
                <span class="dashicons dashicons-calendar-alt"></span>
                <span id="ihb-live-date"><?= date('l, F j, Y') ?></span>
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
            <h3>৳<?= number_format($rev) ?></h3>
        </div>
        <div class="ihb-dash-card">
            <small>Total Expenses</small>
            <h3 style="color: #ef4444;">৳<?= number_format($exp) ?></h3>
        </div>
        <div class="ihb-dash-card">
            <small>Net Profit</small>
            <h3 style="color: <?= $profit >= 0 ? '#10b981' : '#ef4444' ?>;">৳<?= number_format($profit) ?></h3>
        </div>
        <div class="ihb-dash-card">
            <small>Active Bookings</small>
            <h3><?= $count ?></h3>
        </div>
    </div>

    <div class="ihb-main-layout">
        <div class="main-column">
            <div class="ihb-banner">
                <div class="ihb-banner-content">
                    <h3>New Guest Arrival?</h3>
                    <p>Process a new check-in, assign rooms, and collect payments in seconds.</p>
                    <a href="?page=infinity-hotel&tab=add_booking" class="ihb-btn-gold">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align:middle; margin-right: 5px;"></span> Create New Booking
                    </a>
                </div>
                <span class="dashicons dashicons-building" style="position: absolute; right: -20px; bottom: -40px; font-size: 200px; width: 200px; height: 200px; opacity: 0.05; color: white;"></span>
            </div>

            <div class="ihb-quick-actions">
                <a href="?page=infinity-hotel&tab=add_expense" class="action-item">
                    <span class="dashicons dashicons-cart"></span>
                    <span class="text">Add Expense</span>
                </a>
                <a href="?page=infinity-hotel&tab=all_rooms" class="action-item">
                    <span class="dashicons dashicons-admin-home"></span>
                    <span class="text">Rooms</span>
                </a>
                <a href="?page=infinity-hotel&tab=analytics" class="action-item">
                    <span class="dashicons dashicons-chart-bar"></span>
                    <span class="text">Reports</span>
                </a>
                <a href="?page=infinity-hotel&tab=expense_settings" class="action-item">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <span class="text">Settings</span>
                </a>
            </div>
        </div>

        <div class="side-column">
            <div class="recent-feed">
                <div class="feed-title">
                    <span class="dashicons dashicons-update"></span> Recent Activity
                </div>
                
                <?php if ($recent_bookings): foreach($recent_bookings as $rb): 
                    $rid = get_post_meta($rb->ID, '_ihb_room_id', true);
                ?>
                    <div class="feed-item">
                        <div class="feed-icon"><span class="dashicons dashicons-businessman"></span></div>
                        <div>
                            <div style="font-weight: 800; font-size: 13px; color: var(--slate);"><?= esc_html($rb->post_title) ?></div>
                            <div style="font-size: 11px; color: #94a3b8;">Room: <strong><?= $rid ? get_the_title($rid) : 'N/A' ?></strong></div>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p style="font-size: 12px; color: #94a3b8; text-align: center; padding: 20px;">No recent activity.</p>
                <?php endif; ?>
                
                <a href="?page=infinity-hotel&tab=all_bookings" style="display: block; text-align: center; margin-top: 20px; font-size: 11px; font-weight: 800; color: var(--gold); text-decoration: none; text-transform: uppercase;">View All Bookings</a>
            </div>
        </div>
    </div>

    <script>
    function updateIHBClock() {
        const now = new Date();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; 
        minutes = minutes < 10 ? '0' + minutes : minutes;
        seconds = seconds < 10 ? '0' + seconds : seconds;
        
        const timeString = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        const clockEl = document.getElementById('ihb-live-clock');
        if(clockEl) clockEl.textContent = timeString;
    }
    setInterval(updateIHBClock, 1000);
    updateIHBClock(); 
    </script>

    <?php
}