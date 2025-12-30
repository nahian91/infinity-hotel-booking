<?php 
/**
 * INFINITY HOTEL - PROPERTY ATTRIBUTES & CONFIGURATIONS
 */
if (!defined('ABSPATH')) exit; 

// --- 1. DATABASE SAVE HANDLER ---
$show_success = false;
if (isset($_POST['ihb_save_settings_action']) && current_user_can('manage_options')) {
    if (isset($_POST['ihb_settings_nonce']) && wp_verify_nonce($_POST['ihb_settings_nonce'], 'ihb_settings_verify')) {
        
        // Save Room Types (Categories)
        $room_types = isset($_POST['room_types']) ? array_filter(array_map('sanitize_text_field', $_POST['room_types'])) : [];
        update_option('ihb_room_types', array_values($room_types));

        // Save Room Numbers
        $room_numbers = isset($_POST['room_numbers']) ? array_filter(array_map('sanitize_text_field', $_POST['room_numbers'])) : [];
        update_option('ihb_room_numbers', array_values($room_numbers));

        // Save Facilities
        $enabled_facilities = isset($_POST['facilities']) ? $_POST['facilities'] : [];
        update_option('ihb_enabled_facilities', $enabled_facilities);
        
        $show_success = true;
    }
}

// --- 2. LOAD DATA ---
$saved_types     = get_option('ihb_room_types', ['Standard Room', 'Deluxe Room']);
$saved_numbers   = get_option('ihb_room_numbers', ['101', '102']);
$saved_facilities = get_option('ihb_enabled_facilities', []);

$facilities_data = [
    ['wifi', 'High-Speed WiFi', 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    ['ac', 'Climate Control', 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    ['tv', 'Entertainment TV', 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    ['bar', 'Mini Bar Access', 'M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2H7M7,4V10H17V4H7M7,12V20H17V12H7M13,14V18H15V14H13Z'],
    ['safe', 'Personal Safe', 'M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.66,7 15,8.34 15,10C15,11.66 13.66,13 12,13C10.34,13 9,11.66 9,10C9,8.34 10.34,7 12,7Z'],
    ['bath', 'Luxury Bathtub', 'M21 11.2V11A1 1 0 0 0 20 10H4A1 1 0 0 0 3 11V11.2A3 3 0 0 0 1 14V17A2 2 0 0 0 3 19H4A2 2 0 0 0 6 17H18A2 2 0 0 0 20 19H21A2 2 0 0 0 23 17V14A3 3 0 0 0 21 11.2M7 17A1 1 0 1 1 8 18A1 1 0 0 1 7 17M17 17A1 1 0 1 1 16 18A1 1 0 0 1 17 17M21 17H3V14A1 1 0 0 1 4 13H20A1 1 0 0 1 21 14Z'],
];
?>

<style>
    :root { --gold: #c19b76; --border: #e2e8f0; --dark: #1e293b; --slate: #64748b; }

    /* HEADER & WRAPPER */
    .ihb-header-modern {
        display: flex; justify-content: space-between; align-items: center;
        padding: 25px 30px; background: #fff; border-radius: 16px;
        border: 1px solid var(--border); margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
    .ihb-header-icon {
        width: 48px; height: 48px; background: rgba(193, 155, 118, 0.1);
        color: var(--gold); border-radius: 12px; display: flex; align-items: center; justify-content: center;
    }
    .ihb-btn-gold-lg {
        background: var(--gold); color: white; border: none; padding: 12px 24px;
        border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer;
        display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px rgba(193, 155, 118, 0.3);
    }

    /* GRID SYSTEM */
    .ihb-settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
    .ihb-card { background: #fff; padding: 25px; border-radius: 16px; border: 1px solid var(--border); height: fit-content; }
    .ihb-card h3 { margin: 0 0 20px 0; font-size: 16px; font-weight: 800; color: var(--dark); border-bottom: 1px solid #f8fafc; padding-bottom: 12px; }

    /* REPEATER STYLING */
    .ihb-repeater-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
    .ihb-repeater-row input { flex: 1; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; }
    .btn-remove { 
        background: #fff1f2; color: #f43f5e; border: 1px solid #ffe4e6; 
        width: 38px; height: 38px; border-radius: 8px; cursor: pointer; font-size: 18px; 
    }
    .btn-remove:hover { background: #f43f5e; color: white; }
    .btn-add-inline { 
        width: 100%; padding: 10px; background: transparent; border: 1.5px dashed var(--gold); 
        color: var(--gold); border-radius: 8px; cursor: pointer; font-weight: 700; margin-top: 5px; 
    }

    /* FACILITIES GRID */
    .ihb-facility-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .ihb-facility-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 12px; }
    .ihb-f-label { flex: 1; font-size: 13px; font-weight: 600; color: var(--dark); }

    /* SWITCH */
    .ihb-switch { position: relative; width: 34px; height: 18px; display: inline-block; }
    .ihb-switch input { opacity: 0; width: 0; height: 0; }
    .ihb-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #cbd5e1; border-radius: 20px; transition: 0.3s; }
    .ihb-slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; }
    input:checked + .ihb-slider { background: var(--gold); }
    input:checked + .ihb-slider:before { transform: translateX(16px); }

    /* TOAST */
    .ihb-toast { position: fixed; top: 30px; right: 30px; background: white; padding: 15px 25px; border-left: 4px solid var(--gold); border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); z-index: 9999; display: flex; align-items: center; gap: 15px; }
    .spin { animation: ihb-spin 1s linear infinite; }
    @keyframes ihb-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<?php if ($show_success): ?>
<div id="ihb-toast" class="ihb-toast">
    <span class="dashicons dashicons-yes-alt" style="color:var(--gold);"></span>
    <div><strong style="display:block;">Settings Saved</strong><small style="color:var(--slate);">Attributes synced successfully.</small></div>
</div>
<script>setTimeout(() => document.getElementById('ihb-toast').remove(), 3000);</script>
<?php endif; ?>

<form method="POST">
    <?php wp_nonce_field('ihb_settings_verify', 'ihb_settings_nonce'); ?>
    <input type="hidden" name="ihb_save_settings_action" value="1">

    <div class="ihb-header-modern">
        <div style="display:flex; align-items:center; gap:20px;">
            <div class="ihb-header-icon"><span class="dashicons dashicons-admin-settings"></span></div>
            <div>
                <h2 style="margin:0;">Property Attributes</h2>
                <p style="margin:4px 0 0; color:var(--slate);">Configure room categories and unit numbering.</p>
            </div>
        </div>
        <button type="submit" class="ihb-btn-gold-lg" id="save-btn">
            <span class="dashicons dashicons-cloud-upload"></span> Save Configurations
        </button>
    </div>

    <div class="ihb-settings-grid">
        <div class="ihb-card">
            <h3>Room Types (Categories)</h3>
            <div id="type-repeater">
                <?php foreach ($saved_types as $type): ?>
                <div class="ihb-repeater-row">
                    <input type="text" name="room_types[]" value="<?= esc_attr($type) ?>" placeholder="e.g. Deluxe Suite">
                    <button type="button" class="btn-remove">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add-inline" data-target="type-repeater" data-name="room_types[]">+ Add Category</button>
        </div>

        <div class="ihb-card">
            <h3>Specific Room Numbers</h3>
            <div id="number-repeater">
                <?php foreach ($saved_numbers as $num): ?>
                <div class="ihb-repeater-row">
                    <input type="text" name="room_numbers[]" value="<?= esc_attr($num) ?>" placeholder="e.g. 101">
                    <button type="button" class="btn-remove">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add-inline" data-target="number-repeater" data-name="room_numbers[]">+ Add Room Number</button>
        </div>

        <div class="ihb-card" style="grid-column: span 2;">
            <h3>Available Facilities</h3>
            <div class="ihb-facility-grid">
                <?php foreach ($facilities_data as $f): 
                    $is_checked = in_array($f[0], $saved_facilities) ? 'checked' : '';
                ?>
                <div class="ihb-facility-item">
                    <svg style="width:20px; height:20px; fill:var(--gold);" viewBox="0 0 24 24"><path d="<?= $f[2] ?>"/></svg>
                    <div class="ihb-f-label"><?= $f[1] ?></div>
                    <label class="ihb-switch">
                        <input type="checkbox" name="facilities[]" value="<?= $f[0] ?>" <?= $is_checked ?>>
                        <span class="ihb-slider"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handling Both Repeaters
    document.querySelectorAll('.btn-add-inline').forEach(btn => {
        btn.onclick = function() {
            const target = document.getElementById(this.dataset.target);
            const name = this.dataset.name;
            const row = document.createElement('div');
            row.className = 'ihb-repeater-row';
            row.innerHTML = `<input type="text" name="${name}" placeholder="Type here..."><button type="button" class="btn-remove">&times;</button>`;
            target.appendChild(row);
        };
    });

    // Remove logic
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove')) {
            const container = e.target.parentElement.parentElement;
            if (container.children.length > 1) {
                e.target.parentElement.remove();
            } else {
                alert("At least one item is required.");
            }
        }
    });

    // Loading State
    document.querySelector('form').onsubmit = function() {
        const btn = document.getElementById('save-btn');
        btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Saving...';
    };
});
</script>