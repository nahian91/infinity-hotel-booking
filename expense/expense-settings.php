<?php
if (!defined('ABSPATH')) exit;

/**
 * 1. DATABASE SAVE HANDLER
 */
$show_success = false;
if (isset($_POST['ihb_save_expense_settings_action']) && current_user_can('manage_options')) {
    if (isset($_POST['ihb_nonce']) && wp_verify_nonce($_POST['ihb_nonce'], 'ihb_expense_settings_secure')) {
        
        $process_repeater = function($field_name) {
            if (!isset($_POST[$field_name]) || !is_array($_POST[$field_name])) return [];
            return array_values(array_filter(array_map('sanitize_text_field', $_POST[$field_name])));
        };

        update_option('ihb_expense_categories', $process_repeater('exp_cats'));
        update_option('ihb_payment_methods', $process_repeater('pay_methods'));
        update_option('ihb_paid_to_list', $process_repeater('paid_to'));

        $show_success = true;
    }
}

// Fetch existing data
$cats    = get_option('ihb_expense_categories', ['Utilities', 'Maintenance', 'Salaries']);
$methods = get_option('ihb_payment_methods', ['Cash', 'bKash', 'Bank Transfer']);
$paid_to = get_option('ihb_paid_to_list', ['Electric Board', 'Local Market', 'Staff']);
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; }
    
    .ihb-settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; margin-top: 25px; }
    .ihb-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .ihb-card-title { font-size: 14px; font-weight: 800; color: var(--slate); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px; }
    
    /* Repeater Style */
    .repeater-row { display: flex; gap: 8px; margin-bottom: 12px; }
    .ihb-input { flex: 1; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background: #f8fafc; }
    .ihb-input:focus { border-color: var(--gold); outline: none; background: #fff; }
    
    .btn-add { background: #f1f5f9; color: var(--slate); border: none; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 800; cursor: pointer; text-transform: uppercase; }
    .btn-add:hover { background: var(--gold); color: #fff; }
    
    .btn-remove { background: #fff1f2; color: #f43f5e; border: 1px solid #fecdd3; width: 36px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .btn-remove:hover { background: #f43f5e; color: #fff; }

    /* Success Toast */
    .ihb-toast { position: fixed; top: 60px; right: 30px; background: #fff; border-left: 4px solid #10b981; padding: 16px 25px; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); z-index: 1000; }
    .spin { animation: spin 1s linear infinite; display: inline-block; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
</style>

<?php if ($show_success): ?>
    <div class="ihb-toast">
        <div style="font-weight: 800; color: #1e293b;">Settings Saved</div>
        <div style="font-size: 12px; color: #64748b;">Expense dropdowns updated successfully.</div>
    </div>
    <script>setTimeout(() => { document.querySelector('.ihb-toast').style.display='none'; }, 3000);</script>
<?php endif; ?>

<form method="POST" id="ihb-settings-form">
    <?php wp_nonce_field('ihb_expense_settings_secure', 'ihb_nonce'); ?>
    <input type="hidden" name="ihb_save_expense_settings_action" value="1">

    <div class="ihb-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2 style="margin:0; font-weight:800;">Accounting Settings</h2>
            <p style="margin:5px 0 0; color: #64748b;">Configure global lists for your expense management.</p>
        </div>
        <button type="submit" id="save-btn-top" class="ihb-btn-gold" style="min-width: 200px; padding: 12px 24px; border-radius: 10px; font-weight: 700; border: none; cursor: pointer;">
            Save Configuration
        </button>
    </div>

    <div class="ihb-settings-grid">
        
        <div class="ihb-card">
            <div class="ihb-card-title">
                <span>Categories</span>
                <button type="button" class="btn-add" onclick="addRow('cat-list', 'exp_cats[]')">+ Add</button>
            </div>
            <div id="cat-list">
                <?php foreach($cats as $c): ?>
                    <div class="repeater-row">
                        <input type="text" name="exp_cats[]" class="ihb-input" value="<?= esc_attr($c) ?>">
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ihb-card">
            <div class="ihb-card-title">
                <span>Payment Methods</span>
                <button type="button" class="btn-add" onclick="addRow('pay-list', 'pay_methods[]')">+ Add</button>
            </div>
            <div id="pay-list">
                <?php foreach($methods as $m): ?>
                    <div class="repeater-row">
                        <input type="text" name="pay_methods[]" class="ihb-input" value="<?= esc_attr($m) ?>">
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ihb-card">
            <div class="ihb-card-title">
                <span>Paid To / Entities</span>
                <button type="button" class="btn-add" onclick="addRow('paid-list', 'paid_to[]')">+ Add</button>
            </div>
            <div id="paid-list">
                <?php foreach($paid_to as $p): ?>
                    <div class="repeater-row">
                        <input type="text" name="paid_to[]" class="ihb-input" value="<?= esc_attr($p) ?>">
                        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</form>

<script>
function addRow(containerId, inputName) {
    const container = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <input type="text" name="${inputName}" class="ihb-input" placeholder="New entry...">
        <button type="button" class="btn-remove" onclick="this.parentElement.remove()">×</button>
    `;
    container.appendChild(div);
    div.querySelector('input').focus();
}

document.getElementById('ihb-settings-form').onsubmit = function() {
    const btn = document.getElementById('save-btn-top');
    btn.innerHTML = '<span class="dashicons dashicons-update spin" style="margin-top:4px;"></span> Synchronizing...';
    btn.style.opacity = '0.7';
    btn.style.pointerEvents = 'none';
};
</script>