<?php
if (!defined('ABSPATH')) exit;

/**
 * MASTER CALENDAR RENDERER
 * Features: Month Grid, Guest Badges, Dynamic Navigation, Room Assignments
 */
function ihb_render_calendar_view() {
    // 1. DATE CALCULATIONS
    $month = isset($_GET['m']) ? intval($_GET['m']) : intval(date('m'));
    $year  = isset($_GET['y']) ? intval($_GET['y']) : intval(date('Y'));

    $prev_m = $month - 1; $prev_y = $year;
    if ($prev_m < 1) { $prev_m = 12; $prev_y--; }
    $next_m = $month + 1; $next_y = $year;
    if ($next_m > 12) { $next_m = 1; $next_y++; }

    $first_day_ts = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day_ts);
    $start_offset  = date('w', $first_day_ts); // 0 (Sun) to 6 (Sat)
    $month_label   = date('F Y', $first_day_ts);

    // 2. DATA ORCHESTRATION
    $rooms = get_posts(['post_type' => 'ihb_rooms', 'numberposts' => -1]);
    $bookings = get_posts(['post_type' => 'ihb_bookings', 'numberposts' => -1, 'post_status' => 'publish']);
    
    $daily_events = [];
    foreach ($bookings as $b) {
        $checkin = get_post_meta($b->ID, '_ihb_checkin', true);
        if (date('m', strtotime($checkin)) == $month && date('Y', strtotime($checkin)) == $year) {
            $day = (int)date('j', strtotime($checkin));
            $rid = get_post_meta($b->ID, '_ihb_room_id', true);
            $daily_events[$day][] = [
                'id'     => $b->ID,
                'guest'  => get_the_title($b->ID),
                'room'   => get_the_title($rid),
                'status' => get_post_meta($b->ID, '_ihb_status', true) // e.g., 'paid' or 'pending'
            ];
        }
    }
    ?>

    <style>
        :root { --p-gold: #c19b76; --p-blue: #2271b1; --p-bg: #f0f2f5; --p-border: #dcdcde; }

        /* Container & Navigation */
        .ihb-cal-wrapper { background: #fff; border-radius: 12px; border: 1px solid var(--p-border); box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        
        .ihb-cal-top { display: flex; justify-content: space-between; align-items: center; padding: 25px; background: #fff; border-bottom: 1px solid var(--p-border); }
        .ihb-cal-title { font-size: 24px; font-weight: 800; color: #1d2327; letter-spacing: -0.5px; }
        
        .ihb-cal-actions { display: flex; gap: 10px; }
        .ihb-btn-outline { text-decoration: none; padding: 8px 16px; background: #fff; border: 1px solid var(--p-border); border-radius: 6px; color: #3c434a; font-weight: 600; font-size: 13px; transition: 0.2s; }
        .ihb-btn-outline:hover { border-color: var(--p-gold); color: var(--p-gold); background: #fdfaf7; }
        .ihb-btn-today { background: #1d2327; color: #fff; border: none; }
        .ihb-btn-today:hover { background: #000; color: #fff; }

        /* The Grid */
        .ihb-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); background: var(--p-border); gap: 1px; }
        .ihb-cal-weekday { background: #f6f7f7; padding: 12px; text-align: center; font-weight: 700; font-size: 11px; color: #646970; text-transform: uppercase; letter-spacing: 1px; }
        
        .ihb-cal-day { background: #fff; min-height: 140px; padding: 10px; transition: 0.2s; }
        .ihb-cal-day:hover { background: #fdfcfb; }
        .ihb-day-empty { background: #f9f9f9; }
        
        .ihb-day-label { font-size: 15px; font-weight: 500; color: #8c8f94; margin-bottom: 10px; display: block; }
        .is-today { background: #f0f6fb !important; }
        .is-today .ihb-day-label { color: var(--p-blue); font-weight: 900; }

        /* Booking Badges */
        .ihb-guest-badge { 
            display: block; text-decoration: none; padding: 6px 10px; border-radius: 6px;
            background: var(--p-blue); color: #fff; font-size: 12px; font-weight: 600;
            margin-bottom: 5px; box-shadow: 0 2px 4px rgba(34, 113, 177, 0.2);
            transition: 0.2s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            border-left: 3px solid rgba(255,255,255,0.3);
        }
        .ihb-guest-badge:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(34, 113, 177, 0.3); filter: brightness(1.1); }
        .ihb-guest-badge small { display: block; font-size: 9px; font-weight: 400; opacity: 0.9; text-transform: uppercase; }
        
        /* Specific status coloring */
        .status-pending { background: #e6a23c; box-shadow: 0 2px 4px rgba(230, 162, 60, 0.2); }
    </style>

    <div class="ihb-header">
        <div>
            <h2 style="font-weight: 900;">Calendar Overview</h2>
            <p style="color: #64748b;">Manage guest check-ins and monthly availability.</p>
        </div>
        <div class="ihb-cal-actions">
            <a href="?page=infinity-hotel&tab=add_booking" class="ihb-btn-gold" style="text-decoration:none;">+ New Booking</a>
        </div>
    </div>

    <div class="ihb-cal-wrapper">
        <div class="ihb-cal-top">
            <div class="ihb-cal-title"><?= $month_label ?></div>
            <div class="ihb-cal-actions">
                <a href="?page=infinity-hotel&tab=calendar&m=<?= date('m') ?>&y=<?= date('Y') ?>" class="ihb-btn-outline ihb-btn-today">Today</a>
                <a href="?page=infinity-hotel&tab=calendar&m=<?= $prev_m ?>&y=<?= $prev_y ?>" class="ihb-btn-outline">&larr; Previous</a>
                <a href="?page=infinity-hotel&tab=calendar&m=<?= $next_m ?>&y=<?= $next_y ?>" class="ihb-btn-outline">Next &rarr;</a>
            </div>
        </div>

        <div class="ihb-cal-grid">
            <?php 
            $labels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            foreach ($labels as $l) echo "<div class='ihb-cal-weekday'>$l</div>";

            // Offset for the start of the month
            for ($x = 0; $x < $start_offset; $x++) {
                echo '<div class="ihb-cal-day ihb-day-empty"></div>';
            }

            // The Days
            for ($day = 1; $day <= $days_in_month; $day++) {
                $is_today = ($day == date('j') && $month == date('m') && $year == date('Y'));
                ?>
                <div class="ihb-cal-day <?= $is_today ? 'is-today' : '' ?>">
                    <span class="ihb-day-label"><?= $day ?></span>
                    
                    <?php if (isset($daily_events[$day])): foreach ($daily_events[$day] as $evt): ?>
                        <a href="?page=infinity-hotel&tab=add_booking&id=<?= $evt['id'] ?>" 
                           class="ihb-guest-badge <?= $evt['status'] == 'pending' ? 'status-pending' : '' ?>">
                            <small>RM <?= esc_html($evt['room']) ?></small>
                            <?= esc_html($evt['guest']) ?>
                        </a>
                    <?php endforeach; endif; ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}