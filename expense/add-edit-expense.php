<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. DATABASE SAVE HANDLER (Logic remains the same)
 */
$show_success = false;
if (isset($_POST['ihb_save_expense_action']) && current_user_can('edit_posts')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_expense_secure_save')) {
        $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
        $title      = sanitize_text_field($_POST['title']);
        $post_args  = ['post_title' => $title, 'post_status' => 'publish', 'post_type' => 'ihb_expenses'];

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
$cats    = get_option('ihb_expense_categories', ['Utilities', 'Maintenance', 'Salary', 'Marketing']);
$methods = get_option('ihb_payment_methods', ['Cash', 'Bank Transfer', 'bKash']);
$paid_to = get_option('ihb_paid_to_list', ['Electric Board', 'Local Market', 'Staff']);

$amount   = get_post_meta($id, '_ihb_amount', true);
$date     = get_post_meta($id, '_ihb_date', true) ?: date('Y-m-d');
$sel_cat  = get_post_meta($id, '_ihb_category', true);
$sel_p_to = get_post_meta($id, '_ihb_paid_to', true);
$sel_meth = get_post_meta($id, '_ihb_method', true);
$notes    = get_post_meta($id, '_ihb_notes', true);
?>

<style>
    :root { --gold: #c19b76; --dark: #0f172a; --slate: #64748b; --border: #e2e8f0; }
    
    .ihb-form-wrapper { max-width: 1100px; margin: 30px auto; padding: 0 20px; font-family: 'Inter', sans-serif; }
    
    /* Layout Grid */
    .ihb-2col-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start; }
    
    /* Card Styles */
    .ihb-card { background: #fff; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
    .ihb-card-header { padding: 20px 30px; border-bottom: 1px solid var(--border); background: #fafafa; }
    .ihb-card-header h3 { margin: 0; font-size: 14px; font-weight: 800; color: var(--dark); text-transform: uppercase; letter-spacing: 1px; }
    .ihb-card-body { padding: 30px; }

    /* Input Styling */
    .ihb-group { margin-bottom: 20px; }
    .ihb-group label { display: block; font-size: 12px; font-weight: 700; color: var(--slate); margin-bottom: 8px; text-transform: uppercase; }
    .ihb-field { width: 100%; padding: 12px 15px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 15px; transition: 0.2s; background: #fff; }
    .ihb-field:focus { border-color: var(--gold); outline: none; box-shadow: 0 0 0 4px rgba(193, 155, 118, 0.1); }
    
    /* Header Row */
    .ihb-top-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .ihb-btn-primary { background: var(--dark); color: #fff; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; }
    .ihb-btn-primary:hover { background: #000; transform: translateY(-1px); }

    .ihb-toast { position: fixed; top: 40px; right: 40px; background: #10b981; color: #fff; padding: 15px 25px; border-radius: 12px; font-weight: 700; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
</style>

<div class="ihb-form-wrapper">

    <?php if ($show_success): ?>
        <div class="ihb-toast">✓ Ledger Updated Successfully</div>
        <script>setTimeout(() => { window.location.href = "admin.php?page=infinity-hotel&tab=all_expenses"; }, 1500);</script>
    <?php endif; ?>

    <form method="POST">
        <?php wp_nonce_field('ihb_expense_secure_save', 'ihb_nonce'); ?>
        <input type="hidden" name="expense_id" value="<?= $id ?>">
        <input type="hidden" name="ihb_save_expense_action" value="1">

        <div class="ihb-top-row">
            <div>
                <h1 style="margin:0; font-weight:900; letter-spacing:-1px;"><?= $id ? 'Edit Voucher' : 'Create New Expense' ?></h1>
                <p style="margin:0; color:var(--slate);">Recording operational outflow #<?= $id ?: 'NEW' ?></p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="?page=infinity-hotel&tab=all_expenses" class="button" style="padding:8px 20px; border-radius:8px;">Cancel</a>
                <button type="submit" class="ihb-btn-primary"><?= $id ? 'Update Ledger' : 'Confirm Expense' ?></button>
            </div>
        </div>

        <div class="ihb-2col-grid">
            <div class="ihb-col-main">
                <div class="ihb-card">
                    <div class="ihb-card-header"><h3>Transaction Details</h3></div>
                    <div class="ihb-card-body">
                        <div class="ihb-group">
                            <label>Description / Voucher Name</label>
                            <input type="text" name="title" class="ihb-field" value="<?= $id ? get_the_title($id) : '' ?>" placeholder="e.g. June Internet Bill" required>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                            <div class="ihb-group">
                                <label>Amount (৳)</label>
                                <input type="number" step="0.01" name="amount" class="ihb-field" value="<?= $amount ?>" placeholder="0.00" required>
                            </div>
                            <div class="ihb-group">
                                <label>Expense Date</label>
                                <input type="date" name="date" class="ihb-field" value="<?= $date ?>" required>
                            </div>
                        </div>

                        <div class="ihb-group" style="margin-bottom:0;">
                            <label>Internal Notes / Remarks</label>
                            <textarea name="notes" class="ihb-field" style="height:120px; resize:none;" placeholder="Add specific details about this payment..."><?= $notes ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ihb-col-side">
                <div class="ihb-card">
                    <div class="ihb-card-header"><h3>Categorization</h3></div>
                    <div class="ihb-card-body">
                        <div class="ihb-group">
                            <label>Accounting Category</label>
                            <select name="category" class="ihb-field" required>
                                <option value="">Select Category</option>
                                <?php foreach($cats as $cat): ?>
                                    <option value="<?= esc_attr($cat) ?>" <?= selected($sel_cat, $cat) ?>><?= esc_html($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ihb-group">
                            <label>Paid To (Recipient)</label>
                            <select name="paid_to" class="ihb-field" required>
                                <option value="">Select Recipient</option>
                                <?php foreach($paid_to as $p): ?>
                                    <option value="<?= esc_attr($p) ?>" <?= selected($sel_p_to, $p) ?>><?= esc_html($p) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="ihb-group" style="margin-bottom:0;">
                            <label>Payment Method</label>
                            <select name="method" class="ihb-field" required>
                                <option value="">Select Method</option>
                                <?php foreach($methods as $m): ?>
                                    <option value="<?= esc_attr($m) ?>" <?= selected($sel_meth, $m) ?>><?= esc_html($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="margin-top:20px; padding:20px; background:#fffbeb; border-radius:12px; border:1px solid #fde68a;">
                    <p style="margin:0; font-size:12px; color:#92400e; font-weight:600;">
                        <span class="dashicons dashicons-warning" style="font-size:16px; width:16px; height:16px;"></span>
                        Ensure all receipts are filed physically after digital entry.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>