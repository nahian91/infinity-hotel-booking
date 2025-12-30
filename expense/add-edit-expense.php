<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. DATABASE SAVE HANDLER (Logic Kept Intact)
 */
$show_success = false;
if (isset($_POST['ihb_save_expense_action']) && current_user_can('edit_posts')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_expense_secure_save')) {
        $expense_id = isset($_POST['expense_id']) ? intval($_POST['expense_id']) : 0;
        $title      = sanitize_text_field($_POST['title']);
        $post_args = ['post_title' => $title, 'post_status' => 'publish', 'post_type' => 'ihb_expenses'];

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
$cats    = get_option('ihb_expense_categories', ['Utilities', 'Maintenance']);
$methods = get_option('ihb_payment_methods', ['Cash', 'bKash']);
$paid_to = get_option('ihb_paid_to_list', ['Electric Board', 'Local Market']);

$amount   = get_post_meta($id, '_ihb_amount', true);
$date     = get_post_meta($id, '_ihb_date', true) ?: date('Y-m-d');
$sel_cat  = get_post_meta($id, '_ihb_category', true);
$sel_p_to = get_post_meta($id, '_ihb_paid_to', true);
$sel_meth = get_post_meta($id, '_ihb_method', true);
$notes    = get_post_meta($id, '_ihb_notes', true);
?>

<style>
    :root { 
        --gold: #c19b76; 
        --gold-light: #dfc8b1;
        --slate: #0f172a; 
        --slate-light: #64748b;
        --bg: #f8fafc;
        --card-bg: #ffffff;
        --border: #e2e8f0;
    }

    .ihb-container { max-width: 1200px; margin: 20px auto; font-family: 'Inter', system-ui, sans-serif; padding: 0 20px; }
    
    /* Header & Navbar Style */
    .ihb-admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .ihb-admin-header h2 { font-size: 28px; font-weight: 800; color: var(--slate); margin: 0; letter-spacing: -0.5px; }
    .ihb-admin-header p { color: var(--slate-light); margin: 5px 0 0 0; font-size: 14px; }

    /* Grid Layout */
    .ihb-layout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; }

    /* Modern Card Design */
    .ihb-glass-card { 
        background: var(--card-bg); 
        border: 1px solid var(--border); 
        border-radius: 20px; 
        padding: 30px; 
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 10px 15px -3px rgba(0,0,0,0.03);
    }
    
    .ihb-section-title { font-size: 12px; font-weight: 800; color: var(--gold); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; }
    .ihb-section-title::after { content: ""; height: 1px; flex: 1; background: var(--border); }

    /* Form Elements */
    .ihb-form-group { margin-bottom: 20px; }
    .ihb-label { display: block; font-size: 13px; font-weight: 600; color: var(--slate); margin-bottom: 8px; }
    .ihb-input-styled { 
        width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 12px; 
        font-size: 15px; background: #fff; transition: all 0.2s ease; color: var(--slate);
    }
    .ihb-input-styled:focus { border-color: var(--gold); outline: none; box-shadow: 0 0 0 4px rgba(193, 155, 118, 0.15); }
    .ihb-input-styled::placeholder { color: #cbd5e1; }

    /* Button Styling */
    .ihb-btn-primary { 
        background: var(--slate); color: white; padding: 14px 28px; border-radius: 12px; 
        border: none; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 10px;
    }
    .ihb-btn-primary:hover { background: #000; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    
    .ihb-btn-cancel { 
        background: transparent; color: var(--slate-light); padding: 14px 28px; border-radius: 12px; 
        border: 1px solid var(--border); font-weight: 600; text-decoration: none; transition: 0.2s;
    }
    .ihb-btn-cancel:hover { background: #f1f5f9; color: var(--slate); }

    /* Toast Notification */
    .ihb-success-toast { 
        position: fixed; bottom: 30px; right: 30px; background: var(--slate); color: white; 
        padding: 16px 30px; border-radius: 16px; display: flex; align-items: center; gap: 15px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); z-index: 9999; animation: slideIn 0.4s ease;
    }
    @keyframes slideIn { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .spin { animation: fa-spin 2s infinite linear; }
    @keyframes fa-spin { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
</style>

<div class="ihb-container">
    <?php if ($show_success): ?>
        <div class="ihb-success-toast">
            <span class="dashicons dashicons-yes-alt" style="color:var(--gold); font-size:24px; width:24px; height:24px;"></span>
            <div>
                <strong style="display:block;">Transaction Saved</strong>
                <span style="font-size:12px; opacity:0.8;">Updating your ledger now...</span>
            </div>
        </div>
        <script>setTimeout(() => { window.location.href = "admin.php?page=infinity-hotel&tab=all_expenses"; }, 1500);</script>
    <?php endif; ?>

    <form method="POST" id="expense-form">
        <?php wp_nonce_field('ihb_expense_secure_save', 'ihb_nonce'); ?>
        <input type="hidden" name="expense_id" value="<?= $id ?>">
        <input type="hidden" name="ihb_save_expense_action" value="1">

        <div class="ihb-admin-header">
            <div>
                <h2><?= $id ? 'Update Voucher' : 'Create Expense' ?></h2>
                <p>Accounting &rsaquo; Operational Outflow #<?= $id ?: 'Draft' ?></p>
            </div>
            <div style="display:flex; gap:12px;">
                <a href="?page=infinity-hotel&tab=all_expenses" class="ihb-btn-cancel">Cancel</a>
                <button type="submit" id="save-btn" class="ihb-btn-primary">
                    <span class="dashicons dashicons-saved"></span>
                    <?= $id ? 'Apply Changes' : 'Confirm & Save' ?>
                </button>
            </div>
        </div>

        <div class="ihb-layout-grid">
            <div class="ihb-main-col">
                <div class="ihb-glass-card">
                    <div class="ihb-section-title">General Information</div>
                    
                    <div class="ihb-form-group">
                        <label class="ihb-label">Voucher Description</label>
                        <input type="text" name="title" class="ihb-input-styled" value="<?= $id ? get_the_title($id) : '' ?>" placeholder="e.g., Electricity Bill - June 2024" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="ihb-form-group">
                            <label class="ihb-label">Transaction Amount (৳)</label>
                            <input type="number" step="0.01" name="amount" class="ihb-input-styled" value="<?= $amount ?>" placeholder="0.00" required>
                        </div>
                        <div class="ihb-form-group">
                            <label class="ihb-label">Expense Date</label>
                            <input type="date" name="date" class="ihb-input-styled" value="<?= $date ?>" required>
                        </div>
                    </div>

                    <div class="ihb-form-group" style="margin-bottom: 0;">
                        <label class="ihb-label">Internal Remarks (Optional)</label>
                        <textarea name="notes" class="ihb-input-styled" style="height: 140px; resize: none;" placeholder="Add specific payment details or reference numbers..."><?= $notes ?></textarea>
                    </div>
                </div>
            </div>

            <div class="ihb-side-col">
                <div class="ihb-glass-card">
                    <div class="ihb-section-title">Classification</div>
                    
                    <div class="ihb-form-group">
                        <label class="ihb-label">Category</label>
                        <select name="category" class="ihb-input-styled" required>
                            <option value="">Select Category</option>
                            <?php foreach($cats as $cat): ?>
                                <option value="<?= esc_attr($cat) ?>" <?= selected($sel_cat, $cat) ?>><?= esc_html($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ihb-form-group">
                        <label class="ihb-label">Paid To (Recipient)</label>
                        <select name="paid_to" class="ihb-input-styled" required>
                            <option value="">Select Recipient</option>
                            <?php foreach($paid_to as $p): ?>
                                <option value="<?= esc_attr($p) ?>" <?= selected($sel_p_to, $p) ?>><?= esc_html($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ihb-form-group" style="margin-bottom: 0;">
                        <label class="ihb-label">Payment Mode</label>
                        <select name="method" class="ihb-input-styled" required>
                            <option value="">Select Method</option>
                            <?php foreach($methods as $m): ?>
                                <option value="<?= esc_attr($m) ?>" <?= selected($sel_meth, $m) ?>><?= esc_html($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 25px; padding: 20px; background: #fffcf0; border-radius: 15px; border: 1px solid #fef3c7; display: flex; gap: 15px; align-items: flex-start;">
                    <span class="dashicons dashicons-info" style="color:#d97706; margin-top:3px;"></span>
                    <p style="margin:0; font-size:12px; color:#92400e; line-height:1.6;">
                        <strong>Accounting Note:</strong><br>
                        Once saved, this expense will reflect in the monthly profit/loss statement immediately.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('expense-form').onsubmit = function() {
    const btn = document.getElementById('save-btn');
    btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Processing...';
    btn.style.opacity = '0.8';
    btn.style.pointerEvents = 'none';
};
</script>