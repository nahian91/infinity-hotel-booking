<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. OPTIMIZED CALCULATION ENGINE
 */
function ihb_calculate_data($start_date = null, $end_date = null) {
    $rev = 0; $exp = 0;
    
    $booking_args = ['post_type' => 'ihb_bookings', 'numberposts' => -1, 'post_status' => 'publish'];
    $bookings = get_posts($booking_args);
    
    foreach($bookings as $b) {
        $checkin = get_post_meta($b->ID, '_ihb_checkin', true);
        if ($start_date && $checkin < $start_date) continue;
        if ($end_date && $checkin > $end_date) continue;

        $total_price = (float)get_post_meta($b->ID, '_ihb_total_price', true);
        $rev += $total_price;
    }

    $expenses = get_posts(['post_type' => 'ihb_expenses', 'numberposts' => -1, 'post_status' => 'publish']);
    foreach($expenses as $e) { 
        $ex_date = get_post_meta($e->ID, '_ihb_date', true);
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
 * 2. RENDER ENGINE
 */
function ihb_render_analytics() {
    $start = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    $data = ihb_calculate_data($start, $end);
    $days = (new DateTime($start))->diff(new DateTime($end))->days ?: 1;
    $profit_color = ($data['profit'] >= 0) ? '#10b981' : '#ef4444';
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; --border: #e2e8f0; }

    /* Filter Bar UX */
    .ihb-analytics-header { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .ihb-filter-form { display: flex; align-items: flex-end; gap: 12px; }
    .ihb-input-group { display: flex; flex-direction: column; gap: 5px; }
    .ihb-input-group label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    .ihb-date-input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 13px; font-weight: 600; outline: none; transition: 0.2s; }
    .ihb-date-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(193, 155, 118, 0.1); }

    /* Stat Cards UI */
    .ihb-stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .ihb-stat-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); position: relative; overflow: hidden; }
    .ihb-stat-card .label { font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 15px; display: block; }
    .ihb-stat-card .value { font-size: 28px; font-weight: 900; color: var(--slate); letter-spacing: -1px; }
    .ihb-stat-card .trend { margin-top: 10px; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
    
    /* Chart and Insights Area */
    .ihb-dashboard-main { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
    .ihb-glass-card { background: #fff; border-radius: 16px; border: 1px solid var(--border); padding: 25px; }
    .ihb-dark-card { background: var(--slate); color: #fff; border-radius: 16px; padding: 30px; position: relative; }
    
    .insight-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
    .insight-row:last-child { border: none; }
    .insight-label { color: #94a3b8; font-size: 13px; }
    .insight-value { font-weight: 700; color: #fff; }

    .ihb-btn-refresh { background: var(--gold); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .ihb-btn-refresh:hover { background: #ad8a66; transform: translateY(-1px); }
</style>

<div class="ihb-analytics-header">
    <div>
        <h1 style="margin:0; font-weight:900; letter-spacing:-1px;">Business Intelligence</h1>
        <p style="margin:5px 0 0; color:#64748b; font-size:14px;">Real-time performance metrics for the selected period.</p>
    </div>
    
    <form method="GET" class="ihb-filter-form">
        <input type="hidden" name="page" value="infinity-hotel">
        <input type="hidden" name="tab" value="analytics">
        <div class="ihb-input-group">
            <label>Start Date</label>
            <input type="date" name="start_date" class="ihb-date-input" value="<?= $start ?>">
        </div>
        <div class="ihb-input-group">
            <label>End Date</label>
            <input type="date" name="end_date" class="ihb-date-input" value="<?= $end ?>">
        </div>
        <button type="submit" class="ihb-btn-refresh">Generate Report</button>
    </form>
</div>

<div class="ihb-stat-grid">
    <div class="ihb-stat-card">
        <span class="label">Gross Revenue</span>
        <div class="value">৳<?= number_format($data['rev']) ?></div>
        <div class="trend" style="color:#10b981;">
            <span class="dashicons dashicons-arrow-up-alt2"></span> Income Stream
        </div>
    </div>
    <div class="ihb-stat-card">
        <span class="label">Operational Costs</span>
        <div class="value" style="color:#ef4444;">৳<?= number_format($data['exp']) ?></div>
        <div class="trend" style="color:#94a3b8;">
            <span class="dashicons dashicons-arrow-down-alt2"></span> Outgoing Funds
        </div>
    </div>
    <div class="ihb-stat-card" style="border-left: 4px solid <?= $profit_color ?>;">
        <span class="label">Net Profit</span>
        <div class="value" style="color:<?= $profit_color ?>;">৳<?= number_format($data['profit']) ?></div>
        <div class="trend">
            Efficiency: <?= $data['rev'] > 0 ? round(($data['profit'] / $data['rev']) * 100, 1) : 0 ?>%
        </div>
    </div>
</div>

<div class="ihb-dashboard-main">
    <div class="ihb-glass-card">
        <h3 style="margin-top:0; font-weight:800;">Financial Performance Trend</h3>
        <div style="height:250px; display:flex; flex-direction:column; align-items:center; justify-content:center; border:2px dashed #f1f5f9; border-radius:12px;">
            <span class="dashicons dashicons-chart-line" style="font-size:48px; width:48px; height:48px; color:#cbd5e1; margin-bottom:15px;"></span>
            <p style="color:#94a3b8; font-size:14px;">Trend visualization for <?= $days ?> days period</p>
        </div>
    </div>

    <div class="ihb-dark-card">
        <h3 style="margin-top:0; color:var(--gold); font-weight:800;">Period Insights</h3>
        <p style="font-size:13px; color:#94a3b8; margin-bottom:25px;">Key performance indicators analyzed from <?= date('M d', strtotime($start)) ?> to <?= date('M d', strtotime($end)) ?>.</p>
        
        <div class="insight-row">
            <span class="insight-label">Total Reservations</span>
            <span class="insight-value"><?= $data['count'] ?> Bookings</span>
        </div>
        <div class="insight-row">
            <span class="insight-label">Avg. Revenue / Day</span>
            <span class="insight-value">৳<?= number_format($data['rev'] / $days) ?></span>
        </div>
        <div class="insight-row">
            <span class="insight-label">Avg. Expense / Day</span>
            <span class="insight-value">৳<?= number_format($data['exp'] / $days) ?></span>
        </div>
        <div class="insight-row">
            <span class="insight-label">Daily Net Profit</span>
            <span class="insight-value" style="color:<?= $profit_color ?>;">৳<?= number_format($data['profit'] / $days) ?></span>
        </div>

        <div style="margin-top:30px; padding:15px; background:rgba(255,255,255,0.05); border-radius:10px; font-size:12px; color:#cbd5e1; line-height:1.5;">
            <strong>Note:</strong> Profit margins are calculated based on total booking revenue minus logged operational expenses.
        </div>
    </div>
</div>

<?php
}