<?php 
if (!defined('ABSPATH')) exit; 

/**
 * 1. DATA FETCHING
 */
$expenses = get_posts([
    'post_type'   => 'ihb_expenses', 
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby'     => 'date',
    'order'       => 'DESC'
]); 

// Calculation Logic for Header Stats
$total_spent = 0;
$this_month_spent = 0;
$current_month = date('m');

if ($expenses) {
    foreach ($expenses as $e) {
        $amt = floatval(get_post_meta($e->ID, '_ihb_amount', true));
        $date = get_post_meta($e->ID, '_ihb_date', true);
        
        $total_spent += $amt;
        
        if (date('m', strtotime($date)) == $current_month) {
            $this_month_spent += $amt;
        }
    }
}
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; --red: #ef4444; }
    
    /* Stats Bar Styling */
    .ihb-stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
    .ihb-stat-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .ihb-stat-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
    .ihb-stat-value { font-size: 28px; font-weight: 900; color: var(--slate); }
    .ihb-stat-value.expense { color: var(--red); }

    /* Table Styling */
    .ihb-table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 25px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .ihb-table { width: 100%; border-collapse: collapse; background: white; }
    .ihb-table th { background: #f8fafc; padding: 15px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    .ihb-table td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: var(--slate); vertical-align: middle; }
    .ihb-table tr:hover { background: #fbfcfe; }
    
    /* Badges & Text */
    .exp-cat-badge { background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .exp-amount { color: var(--red); font-weight: 800; font-family: 'Courier New', Courier, monospace; font-size: 16px; }
    
    /* Action Buttons (Matches Rooms/Bookings) */
    .ihb-action-group { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-action { 
        display: flex; align-items: center; justify-content: center;
        width: 34px; height: 34px; border-radius: 8px; 
        text-decoration: none; border: 1px solid #e2e8f0; background: #fff; color: #64748b;
        transition: 0.2s;
    }
    .btn-action:hover { border-color: var(--gold); color: var(--gold); background: #fdfaf7; }
    .btn-delete:hover { border-color: #fca5a5; color: var(--red); background: #fef2f2; }
    .btn-action span { font-size: 18px; }
</style>

<div class="ihb-header">
    <div>
        <h2>Expense Ledger</h2>
        <p style="color: #64748b;">Managing all hotel operational costs and vendor payments.</p>
    </div>
    <a href="?page=infinity-hotel&tab=add_expense" class="ihb-btn-gold" style="text-decoration:none;">+ New Expense Entry</a>
</div>

<div class="ihb-stats-bar">
    <div class="ihb-stat-card">
        <span class="ihb-stat-label">Total Expenditure</span>
        <div class="ihb-stat-value expense">৳<?= number_format($total_spent, 2) ?></div>
    </div>
    <div class="ihb-stat-card">
        <span class="ihb-stat-label">Spending (This Month)</span>
        <div class="ihb-stat-value">৳<?= number_format($this_month_spent, 2) ?></div>
    </div>
    <div class="ihb-stat-card">
        <span class="ihb-stat-label">Total Entries</span>
        <div class="ihb-stat-value" style="color:var(--gold)"><?= count($expenses) ?></div>
    </div>
</div>

<div class="ihb-table-card">
    <table class="ihb-table">
        <thead>
            <tr>
                <th style="width: 25%;">Description & Date</th>
                <th>Category</th>
                <th>Paid To</th>
                <th>Method</th>
                <th>Amount</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($expenses): foreach($expenses as $e): 
                $amt     = get_post_meta($e->ID, '_ihb_amount', true);
                $date    = get_post_meta($e->ID, '_ihb_date', true);
                $cat     = get_post_meta($e->ID, '_ihb_category', true);
                $p_to    = get_post_meta($e->ID, '_ihb_paid_to', true);
                $method  = get_post_meta($e->ID, '_ihb_method', true);
            ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; margin-bottom: 3px;"><?= esc_html($e->post_title) ?></div>
                        <div style="font-size: 11px; color: #94a3b8; font-weight: 600;">
                            <span class="dashicons dashicons-calendar-alt" style="font-size:13px; width:13px; height:13px; margin-right:3px;"></span>
                            <?= date('M d, Y', strtotime($date)) ?>
                        </div>
                    </td>
                    <td><span class="exp-cat-badge"><?= esc_html($cat ?: 'General') ?></span></td>
                    <td>
                        <div style="font-weight: 600;"><?= esc_html($p_to ?: 'Unknown') ?></div>
                    </td>
                    <td>
                        <div style="font-size: 12px; color: #64748b;"><?= esc_html($method ?: 'Cash') ?></div>
                    </td>
                    <td><span class="exp-amount">-৳<?= number_format((float)$amt, 2) ?></span></td>
                    <td>
                        <div class="ihb-action-group">
                            <a href="?page=infinity-hotel&tab=edit_expense&id=<?= $e->ID ?>" class="btn-action" title="Edit Entry">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            
                            <a href="<?= get_delete_post_link($e->ID, '', true) ?>" 
                               class="btn-action btn-delete" 
                               title="Permanent Delete" 
                               onclick="return confirm('Are you sure you want to permanently delete this expense record?')">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 60px; color: #94a3b8;">
                        <span class="dashicons dashicons-info" style="font-size: 40px; width: 40px; height: 40px; opacity: 0.3; display: block; margin: 0 auto 10px;"></span>
                        No expense records found in the ledger.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>