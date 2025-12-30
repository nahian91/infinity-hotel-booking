<?php
if (!defined('ABSPATH')) exit;

/**
 * MASTER CALENDAR RENDERER
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
    $start_offset  = date('w', $first_day_ts); 
    $month_label   = date('F Y', $first_day_ts);

    // 2. DATA ORCHESTRATION
    $bookings = get_posts(['post_type' => 'ihb_bookings', 'numberposts' => -1, 'post_status' => 'publish']);
    
    $daily_events = [];
    foreach ($bookings as $b) {
        $checkin  = get_post_meta($b->ID, '_ihb_checkin', true);
        $checkout = get_post_meta($b->ID, '_ihb_checkout', true);
        
        // Filter events strictly for the current month/year view
        if (date('m', strtotime($checkin)) == $month && date('Y', strtotime($checkin)) == $year) {
            $day = (int)date('j', strtotime($checkin));
            $rid = get_post_meta($b->ID, '_ihb_room_id', true);
            $daily_events[$day][] = [
                'id'     => $b->ID,
                'guest'  => get_the_title($b->ID),
                'room'   => get_the_title($rid) ?: 'N/A',
                'status' => get_post_meta($b->ID, '_ihb_status', true),
                'nights' => (strtotime($checkout) - strtotime($checkin)) / 86400
            ];
        }
    }
    ?>

    <style>
        :root { --gold: #c19b76; --slate: #0f172a; --border: #e2e8f0; --blue: #2563eb; --orange: #f59e0b; --green: #10b981; }

        .ihb-cal-container { max-width: 1300px; margin: 20px auto; font-family: 'Inter', sans-serif; }
        
        /* Navigation & Title Bar */
        .ihb-cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .ihb-cal-nav { background: #fff; border: 1px solid var(--border); border-radius: 12px; display: flex; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .ihb-cal-nav a { text-decoration: none; padding: 10px 20px; color: var(--slate); font-weight: 600; font-size: 13px; border-right: 1px solid var(--border); transition: 0.2s; }
        .ihb-cal-nav a:last-child { border-right: none; }
        .ihb-cal-nav a:hover { background: #f8fafc; color: var(--gold); }
        .ihb-cal-nav a.today { background: var(--slate); color: #fff; }

        /* The Calendar Grid */
        .ihb-cal-box { background: #fff; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.03); overflow: hidden; }
        .ihb-grid-header { display: grid; grid-template-columns: repeat(7, 1fr); background: #f8fafc; border-bottom: 1px solid var(--border); }
        .ihb-weekday { padding: 15px; text-align: center; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }

        .ihb-grid-body { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: var(--border); }
        
        /* Individual Day Cells */
        .ihb-day { background: #fff; min-height: 150px; padding: 12px; position: relative; transition: 0.2s; }
        .ihb-day:hover { background: #fdfcfb; z-index: 2; }
        .ihb-day-empty { background: #f1f5f9; opacity: 0.5; }
        
        .ihb-day-num { font-size: 16px; font-weight: 700; color: #cbd5e1; margin-bottom: 10px; display: block; }
        .is-current-day { background: #fff7ed !important; }
        .is-current-day .ihb-day-num { color: var(--gold); }
        .is-current-day::after { content: "TODAY"; position: absolute; top: 12px; right: 12px; font-size: 9px; font-weight: 800; color: var(--gold); }

        /* Enhanced Guest Badges */
        .ihb-badge { 
            display: block; text-decoration: none; padding: 6px 8px; border-radius: 8px;
            font-size: 11.5px; font-weight: 700; margin-bottom: 6px; 
            transition: 0.2s; border-left: 4px solid rgba(0,0,0,0.1);
            color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
        }
        .ihb-badge:hover { transform: scale(1.02); filter: brightness(1.1); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .ihb-badge small { display: block; font-size: 9px; font-weight: 500; opacity: 0.8; margin-bottom: 1px; text-transform: uppercase; }

        /* Booking Status Colors */
        .st-confirmed { background: var(--blue); }
        .st-pending   { background: var(--orange); }
        .st-paid      { background: var(--green); }
        .st-checkedin { background: var(--slate); }

        .ihb-cal-title-main { font-size: 28px; font-weight: 900; color: var(--slate); letter-spacing: -1px; }
    </style>

    <div class="ihb-cal-container">
        <div class="ihb-cal-header">
            <div>
                <h1 class="ihb-cal-title-main"><?= $month_label ?></h1>
                <p style="margin:0; color:var(--slate); font-size:14px; opacity:0.7;">Visual booking pipeline and room occupancy.</p>
            </div>
            <div style="display:flex; gap:15px; align-items:center;">
                <div class="ihb-cal-nav">
                    <a href="?page=infinity-hotel&tab=calendar&m=<?= $prev_m ?>&y=<?= $prev_y ?>">&larr;</a>
                    <a href="?page=infinity-hotel&tab=calendar&m=<?= date('m') ?>&y=<?= date('Y') ?>" class="today">Today</a>
                    <a href="?page=infinity-hotel&tab=calendar&m=<?= $next_m ?>&y=<?= $next_y ?>">&rarr;</a>
                </div>
                <a href="?page=infinity-hotel&tab=add_booking" class="ihb-btn-gold" style="text-decoration:none;">+ New Booking</a>
            </div>
        </div>

        <div class="ihb-cal-box">
            <div class="ihb-grid-header">
                <?php $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                foreach ($days as $d) echo "<div class='ihb-weekday'>$d</div>"; ?>
            </div>

            <div class="ihb-grid-body">
                <?php 
                // Padding for start of month
                for ($x = 0; $x < $start_offset; $x++) {
                    echo '<div class="ihb-day ihb-day-empty"></div>';
                }

                // Render Days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $is_today = ($day == date('j') && $month == date('m') && $year == date('Y'));
                    ?>
                    <div class="ihb-day <?= $is_today ? 'is-current-day' : '' ?>">
                        <span class="ihb-day-num"><?= $day ?></span>
                        
                        <?php if (isset($daily_events[$day])): foreach ($daily_events[$day] as $evt): 
                            // Determine CSS class based on status
                            $status_class = 'st-confirmed';
                            if ($evt['status'] == 'pending') $status_class = 'st-pending';
                            if ($evt['status'] == 'paid')    $status_class = 'st-paid';
                            if ($evt['status'] == 'checked_in') $status_class = 'st-checkedin';
                        ?>
                            <a href="?page=infinity-hotel&tab=add_booking&id=<?= $evt['id'] ?>" 
                               class="ihb-badge <?= $status_class ?>" 
                               title="Guest: <?= esc_attr($evt['guest']) ?>">
                                <small>RM <?= esc_html($evt['room']) ?> • <?= $evt['nights'] ?> NTS</small>
                                <?= esc_html($evt['guest']) ?>
                            </a>
                        <?php endforeach; endif; ?>
                    </div>
                    <?php
                }

                // Optional: Padding for end of month to keep grid square
                $remaining = (7 - (($days_in_month + $start_offset) % 7)) % 7;
                for ($x = 0; $x < $remaining; $x++) {
                    echo '<div class="ihb-day ihb-day-empty"></div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}