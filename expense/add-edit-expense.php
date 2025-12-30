<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. DATABASE SAVE HANDLER
 */
$show_success = false;
if (isset($_POST['ihb_save_expense_action']) && current_user_can('edit_posts')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_expense_secure_save')) {
        
        $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
        $title      = sanitize_text_field($_POST['title']);
        
        $post_args = [
            'post_title'   => $title,
            'post_status'  => 'publish',
            'post_type'    => 'ihb_expenses', // Ensure this matches your registered type
        ];

        if ($expense_id > 0) {
            $post_args['ID'] = $expense_id;
            wp_update_post($post_args);
        } else {
            $expense_id = wp_insert_post($post_args);
        }

        if ($expense_id) {
            update_post_meta($expense_id, '_ihb_amount', sanitize_text_field($_POST['amount']));
            update_post_meta($expense_id, '_ihb_date', sanitize_text_field($_POST['date']));
            update_post_meta($expense_id, '_ihb_category', sanitize_text_field($_POST['category']));
            update_post_meta($expense_id, '_ihb_paid_to', sanitize_text_field($_POST['paid_to']));
            update_post_meta($expense_id, '_ihb_method', sanitize_text_field($_POST['method']));
            update_post_meta($expense_id, '_ihb_notes', sanitize_textarea_field($_POST['notes']));
            
            $show_success = true;
        }
    }
}

/**
 * 2. DATA PREPARATION
 */
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($expense_id) ? $expense_id : 0);

// Fetch lists from your Repeater Settings
$cats    = get_option('ihb_expense_categories', ['Utilities', 'Maintenance']);
$methods = get_option('ihb_payment_methods', ['Cash', 'bKash']);
$paid_to = get_option('ihb_paid_to_list', ['Electric Board', 'Local Market']);

// Load existing meta if editing
$amount   = get_post_meta($id, '_ihb_amount', true);
$date     = get_post_meta($id, '_ihb_date', true) ?: date('Y-m-d');
$sel_cat  = get_post_meta($id, '_ihb_category', true);
$sel_p_to = get_post_meta($id, '_ihb_paid_to', true);
$sel_meth = get_post_meta($id, '_ihb_method', true);
$notes    = get_post_meta($id, '_ihb_notes', true);
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; }
    .ihb-grid { display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 25px; }
    .ihb-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .ihb-label-sm { display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px; }
    .ihb-input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #f8fafc; margin-bottom: 20px; }
    .ihb-input:focus { border-color: var(--gold); outline: none; background: #fff; }
    
    .ihb-toast { position: fixed; top: 50px; right: 30px; background: #fff; border-left: 4px solid var(--gold); box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-radius: 8px; padding: 16px 24px; z-index: 999; }
</style>

<?php if ($show_success): ?>
<div class="ihb-toast">
    <strong>Expense Logged</strong><br><small>Redirecting to ledger...</small>
</div>
<script>setTimeout(() => { window.location.href = "admin.php?page=infinity-hotel&tab=all_expenses"; }, 1500);</script>
<?php endif; ?>

<form method="POST" id="expense-form">
    <?php wp_nonce_field('ihb_expense_secure_save', 'ihb_nonce'); ?>
    <input type="hidden" name="expense_id" value="<?= $id ?>">
    <input type="hidden" name="ihb_save_expense_action" value="1">

    <div class="ihb-header">
        <div>
            <h2><?= $id ? 'Edit' : 'Add' ?> Expense</h2>
            <p>Record outgoing costs and vendor payments.</p>
        </div>
        <button type="submit" id="save-btn" class="ihb-btn-gold" style="min-width: 200px;">
            <?= $id ? 'Update Record' : 'Save Expense' ?>
        </button>
    </div>

    <div class="ihb-grid">
        <div class="ihb-main">
            <div class="ihb-card">
                <label class="ihb-label-sm">Expense Title / Description</label>
                <input type="text" name="title" class="ihb-input" value="<?= $id ? get_the_title($id) : '' ?>" placeholder="e.g. Monthly Electricity Bill" required>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label class="ihb-label-sm">Amount (৳)</label>
                        <input type="number" step="0.01" name="amount" class="ihb-input" value="<?= $amount ?>" placeholder="0.00" required>
                    </div>
                    <div>
                        <label class="ihb-label-sm">Date</label>
                        <input type="date" name="date" class="ihb-input" value="<?= $date ?>" required>
                    </div>
                </div>

                <label class="ihb-label-sm">Additional Notes</label>
                <textarea name="notes" class="ihb-input" style="height: 120px; resize: none;"><?= $notes ?></textarea>
            </div>
        </div>

        <div class="ihb-sidebar">
            <div class="ihb-card">
                <label class="ihb-label-sm">Accounting Category</label>
                <select name="category" class="ihb-input" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach($cats as $cat): ?>
                        <option value="<?= esc_attr($cat) ?>" <?= selected($sel_cat, $cat) ?>><?= esc_html($cat) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="ihb-label-sm">Paid To (Vendor/Staff)</label>
                <select name="paid_to" class="ihb-input" required>
                    <option value="">-- Select Recipient --</option>
                    <?php foreach($paid_to as $p): ?>
                        <option value="<?= esc_attr($p) ?>" <?= selected($sel_p_to, $p) ?>><?= esc_html($p) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="ihb-label-sm">Payment Method</label>
                <select name="method" class="ihb-input" required>
                    <option value="">-- Select Method --</option>
                    <?php foreach($methods as $m): ?>
                        <option value="<?= esc_attr($m) ?>" <?= selected($sel_meth, $m) ?>><?= esc_html($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('expense-form').onsubmit = function() {
    const btn = document.getElementById('save-btn');
    btn.innerHTML = '<span class="dashicons dashicons-update spin" style="margin-top:4px;"></span> Syncing...';
    btn.style.opacity = '0.7';
};
</script>