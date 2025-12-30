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

// Calculation Logic
$total_spent = 0;
$this_month_spent = 0;
$current_month = date('m');
$current_year = date('Y');

if ($expenses) {
    foreach ($expenses as $e) {
        $amt = floatval(get_post_meta($e->ID, '_ihb_amount', true));
        $date = get_post_meta($e->ID, '_ihb_date', true);
        
        $total_spent += $amt;
        
        if (date('m', strtotime($date)) == $current_month && date('Y', strtotime($date)) == $current_year) {
            $this_month_spent += $amt;
        }
    }
}

// Category Color Map for visual cues
$cat_colors = [
    'Utility'     => ['bg' => '#e0f2fe', 'text' => '#0369a1'],
    'Salary'      => ['bg' => '#f0fdf4', 'text' => '#15803d'],
    'Maintenance' => ['bg' => '#fff7ed', 'text' => '#c2410c'],
    'Marketing'   => ['bg' => '#faf5ff', 'text' => '#7e22ce'],
    'Food'        => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
];
?>

<style>
    :root { --gold: #c19b76; --slate: #0f172a; --red: #dc2626; --border: #e2e8f0; }
    
    .ihb-expense-wrap { max-width: 1200px; margin: 20px auto; font-family: 'Inter', -apple-system, sans-serif; }
    
    /* Stats Aesthetic */
    .ihb-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .ihb-stat-box { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); position: relative; overflow: hidden; }
    .ihb-stat-box::after { content: ""; position: absolute; right: -20px; bottom: -20px; font-family: 'dashicons'; font-size: 80px; opacity: 0.03; color: var(--slate); }
    .stat-total::after { content: "\f174"; }
    .stat-month::after { content: "\f507"; }
    .stat-entries::after { content: "\f109"; }

    .stat-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--slate); margin-top: 5px; }
    .stat-value.neg { color: var(--red); }

    /* Ledger Table Styling */
    .ledger-container { background: #fff; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); overflow: hidden; }
    .ledger-table { width: 100%; border-collapse: collapse; }
    .ledger-table th { background: #f8fafc; padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid var(--border); }
    .ledger-table td { padding: 16px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .ledger-table tr:last-child td { border: none; }
    .ledger-table tr:hover { background: #fafafa; }

    /* UI Components */
    .cat-pill { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .amount-text { font-family: 'Monaco', 'Consolas', monospace; font-weight: 700; color: var(--red); font-size: 15px; }
    .method-icon { display: inline-flex; align-items: center; gap: 5px; color: #64748b; font-size: 12px; font-weight: 500; }

    .ihb-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .btn-add { background: var(--slate); color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 13px; transition: 0.3s; }
    .btn-add:hover { background: var(--gold); transform: translateY(-1px); }
</style>

<div class="ihb-expense-wrap">
    
    <div class="ihb-header">
        <div>
            <h1 style="font-weight:800; margin:0; letter-spacing:-1px;">Financial Ledger</h1>
            <p style="margin:0; color:#64748b; font-size:14px;">Operational expenses and outgoing cash flow.</p>
        </div>
        <a href="?page=infinity-hotel&tab=add_expense" class="btn-add">
            <span class="dashicons dashicons-plus-alt" style="font-size:16px; margin-top:2px;"></span> Record New Expense
        </a>
    </div>

    <div class="ihb-stats-grid">
        <div class="ihb-stat-box stat-total">
            <span class="stat-label">Lifetime Spend</span>
            <div class="stat-value neg">৳<?= number_format($total_spent, 0) ?></div>
        </div>
        <div class="ihb-stat-box stat-month">
            <span class="stat-label">This Month (<?= date('F') ?>)</span>
            <div class="stat-value">৳<?= number_format($this_month_spent, 0) ?></div>
        </div>
        <div class="ihb-stat-box stat-entries">
            <span class="stat-label">Voucher Count</span>
            <div class="stat-value" style="color:var(--gold)"><?= count($expenses) ?></div>
        </div>
    </div>

    <div class="ledger-container">
        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Voucher / Description</th>
                    <th>Category</th>
                    <th>Recipient</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th style="text-align:right;">Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($expenses): foreach($expenses as $e): 
                    $amt    = get_post_meta($e->ID, '_ihb_amount', true);
                    $date   = get_post_meta($e->ID, '_ihb_date', true);
                    $cat    = get_post_meta($e->ID, '_ihb_category', true);
                    $p_to   = get_post_meta($e->ID, '_ihb_paid_to', true);
                    $method = get_post_meta($e->ID, '_ihb_method', true);
                    
                    $style = $cat_colors[$cat] ?? ['bg' => '#f1f5f9', 'text' => '#475569'];
                ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color:var(--slate);"><?= esc_html($e->post_title) ?></div>
                            <div style="font-size: 11px; color: #94a3b8; font-weight: 600; margin-top: 3px;">
                                <?= date('M d, Y', strtotime($date)) ?> • #EXP-<?= $e->ID ?>
                            </div>
                        </td>
                        <td>
                            <span class="cat-pill" style="background:<?= $style['bg'] ?>; color:<?= $style['text'] ?>;">
                                <?= esc_html($cat ?: 'General') ?>
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size:13px;"><?= esc_html($p_to ?: 'Vendor/Staff') ?></div>
                        </td>
                        <td>
                            <div class="method-icon">
                                <span class="dashicons dashicons-cart" style="font-size:14px; width:14px; height:14px;"></span>
                                <?= esc_html($method ?: 'Cash') ?>
                            </div>
                        </td>
                        <td>
                            <span class="amount-text">-৳<?= number_format((float)$amt, 2) ?></span>
                        </td>
                        <td>
                            <div class="ihb-action-group">
                                <a href="?page=infinity-hotel&tab=edit_expense&id=<?= $e->ID ?>" class="btn-action" title="Edit Voucher">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <a href="<?= get_delete_post_link($e->ID, '', true) ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Archive this voucher permanently?')">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 80px 0;">
                            <div style="color:#cbd5e1; margin-bottom:10px;"><span class="dashicons dashicons-media-spreadsheet" style="font-size:48px; width:48px; height:48px;"></span></div>
                            <div style="font-weight:600; color:#94a3b8;">No transactions recorded in the current period.</div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>