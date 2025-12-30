<?php 
if (!defined('ABSPATH')) exit; 

/**
 * 1. DATABASE SAVE HANDLER
 */
$show_success = false;
if (isset($_POST['ihb_save_settings_action']) && current_user_can('manage_options')) {
    if (isset($_POST['ihb_settings_nonce']) && wp_verify_nonce($_POST['ihb_settings_nonce'], 'ihb_settings_verify')) {
        
        // Save Room Aliases (Repeater)
        $aliases = [];
        if (isset($_POST['r_num']) && is_array($_POST['r_num'])) {
            foreach ($_POST['r_num'] as $index => $num) {
                if (!empty($num)) {
                    $aliases[] = [
                        'num'   => sanitize_text_field($num),
                        'alias' => sanitize_text_field($_POST['r_alias'][$index])
                    ];
                }
            }
        }
        update_option('ihb_room_aliases', $aliases);

        // Save Facilities (Checkboxes)
        $enabled_facilities = isset($_POST['facilities']) ? $_POST['facilities'] : [];
        update_option('ihb_enabled_facilities', $enabled_facilities);
        
        $show_success = true;
    }
}

/**
 * 2. LOAD DATA
 */
$saved_aliases = get_option('ihb_room_aliases', []);
$saved_facilities = get_option('ihb_enabled_facilities', []);

if (empty($saved_aliases)) {
    $saved_aliases = [['num' => '', 'alias' => '']];
}

$facilities_data = [
    ['wifi', 'High-Speed WiFi', 'M12,21L15.6,16.2C14.6,15.45 13.35,15 12,15C10.65,15 9.4,15.45 8.4,16.2L12,21M12,3C7.95,3 4.21,4.34 1.2,6.6L3,9C5.5,7.12 8.62,6 12,6C15.38,6 18.5,7.12 21,9L22.8,6.6C19.79,4.34 16.05,3 12,3M12,9C9.3,9 6.81,9.89 4.8,11.4L6.6,13.8C8.1,12.67 9.97,12 12,12C14.03,12 15.9,12.67 17.4,13.8L19.2,11.4C17.19,9.89 14.7,9 12,9Z'],
    ['ac', 'Climate Control', 'M12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4'],
    ['tv', 'Entertainment TV', 'M21,3H3C1.9,3 1,3.9 1,5V17C1,18.1 1.9,19 3,19H10V21H14V19H21C22.1,19 23,18.1 23,17V5C23,3.9 22.1,3 21,3M21,17H3V5H21V17Z'],
    ['bar', 'Mini Bar Access', 'M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2H7M7,4V10H17V4H7M7,12V20H17V12H7M13,14V18H15V14H13Z'],
    ['safe', 'Personal Safe', 'M12,1L3,5V11C3,16.55 6.84,21.74 12,23C17.16,21.74 21,16.55 21,11V5L12,1M12,7C13.66,7 15,8.34 15,10C15,11.66 13.66,13 12,13C10.34,13 9,11.66 9,10C9,8.34 10.34,7 12,7Z'],
    ['service', 'Daily Room Service', 'M11,9H13V11H11V9M11,13H13V17H11V13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z'],
    ['bed', 'King/Twin Beds', 'M19,7H5V14H19V7M19,15H5V17H19V15M19,5H5A2,2 0 0,0 3,7V17A2,2 0 0,0 5,19H19A2,2 0 0,0 21,17V7A2,2 0 0,0 19,5Z'],
    ['bath', 'Luxury Bathtub', 'M21 11.2V11A1 1 0 0 0 20 10H4A1 1 0 0 0 3 11V11.2A3 3 0 0 0 1 14V17A2 2 0 0 0 3 19H4A2 2 0 0 0 6 17H18A2 2 0 0 0 20 19H21A2 2 0 0 0 23 17V14A3 3 0 0 0 21 11.2M7 17A1 1 0 1 1 8 18A1 1 0 0 1 7 17M17 17A1 1 0 1 1 16 18A1 1 0 0 1 17 17M21 17H3V14A1 1 0 0 1 4 13H20A1 1 0 0 1 21 14Z'],
    ['coffee', 'Coffee Station', 'M13,2V4H17V14H11V4H13V2H8V4H10V14H4V16H20V14H18V2H13M6,18V20H18V18H6Z'],
    ['balcony', 'Private Balcony', 'M19,3H5C3.89,3 3,3.9 3,5V19C3,20.1 3.89,21 5,21H19C20.1,21 21,20.1 21,19V5C21,3.9 20.1,3 19,3M19,19H5V5H19V19Z']
];
?>

<style>
    /* Layout & Settings Grid */
    .ihb-settings-grid { display: grid; grid-template-columns: 1fr 1.6fr; gap: 30px; align-items: start; margin-top: 20px; }
    
    /* Modern Switch UI */
    .ihb-switch { position: relative; display: inline-block; width: 38px; height: 20px; }
    .ihb-switch input { opacity: 0; width: 0; height: 0; }
    .ihb-slider { 
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; 
        background-color: #e2e8f0; transition: .3s; border-radius: 20px; 
    }
    .ihb-slider:before { 
        position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; 
        background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    input:checked + .ihb-slider { background-color: var(--gold); }
    input:checked + .ihb-slider:before { transform: translateX(18px); }

    /* Facility Cards */
    .ihb-facility-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .ihb-facility-card { 
        display: flex; align-items: center; gap: 15px; padding: 15px; 
        background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: 0.2s;
    }
    .ihb-facility-card:hover { border-color: var(--gold); transform: translateY(-2px); }
    .ihb-f-icon { width: 24px; height: 24px; fill: var(--gold); opacity: 0.9; }
    .ihb-f-label { flex: 1; font-size: 13px; font-weight: 600; color: #334155; }

    /* Repeater Row Styling */
    .ihb-repeater-container { background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 15px;}
    .ihb-row { display: flex; gap: 10px; margin-bottom: 12px; animation: slideIn 0.3s ease-out; }
    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    .ihb-row input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; font-size: 13px; flex: 1; transition: 0.2s; }
    .ihb-row input:focus { border-color: var(--gold); outline: none; box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1); }
    .btn-remove { background: #fee2e2; color: #ef4444; border: none; width: 38px; height: 38px; border-radius: 8px; cursor: pointer; }
    .btn-add-bottom { width: 100%; padding: 10px; background: #fff; border: 1px dashed var(--gold); color: var(--gold); border-radius: 8px; cursor: pointer; font-weight: 600; }

    /* SUCCESS TOAST NOTIFICATION */
    .ihb-toast-notification {
        position: fixed; top: 50px; right: 30px; background: #ffffff; 
        border-left: 4px solid var(--gold); box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-radius: 8px; display: flex; align-items: center; padding: 16px 24px;
        z-index: 9999; animation: toastSlideIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); transition: 0.5s ease;
    }
    @keyframes toastSlideIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
    .ihb-toast-icon { background: rgba(212, 175, 55, 0.1); color: var(--gold); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; }
    .ihb-toast-content { display: flex; flex-direction: column; }
    .ihb-toast-content strong { color: #1e293b; font-size: 14px; margin-bottom: 2px; }
    .ihb-toast-content span { color: #64748b; font-size: 12px; }
</style>

<?php if ($show_success): ?>
<div id="ihb-success-toast" class="ihb-toast-notification">
    <div class="ihb-toast-icon"><span class="dashicons dashicons-saved"></span></div>
    <div class="ihb-toast-content">
        <strong>Configurations Updated</strong>
        <span>Property attributes have been synced successfully.</span>
    </div>
</div>
<script>
    setTimeout(() => {
        const toast = document.getElementById("ihb-success-toast");
        if(toast) {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-20px)";
            setTimeout(() => toast.remove(), 500);
        }
    }, 4000);
</script>
<?php endif; ?>

<form method="POST">
    <?php wp_nonce_field('ihb_settings_verify', 'ihb_settings_nonce'); ?>
    <input type="hidden" name="ihb_save_settings_action" value="1">

    <div class="ihb-header">
        <div>
            <h2>Property Attributes</h2>
            <p style="color: #64748b; margin: 5px 0 0;">Manage global room identifiers and facility availability.</p>
        </div>
        <button type="submit" class="ihb-btn-gold" id="save-btn">Save Configurations</button>
    </div>

    <div class="ihb-settings-grid">
        
        <div class="ihb-card">
            <h3 style="margin:0 0 20px 0; font-size:16px;">Room Numbering Aliases</h3>
            <div class="ihb-repeater-container" id="alias-list">
                <?php foreach ($saved_aliases as $row): ?>
                <div class="ihb-row">
                    <input type="text" name="r_num[]" placeholder="ID (101)" style="max-width:80px;" value="<?= esc_attr($row['num']) ?>">
                    <input type="text" name="r_alias[]" placeholder="Label (Suite A)" value="<?= esc_attr($row['alias']) ?>">
                    <button type="button" class="btn-remove">&times;</button>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add-bottom" id="add-alias">+ Add New Room Alias</button>
        </div>

        <div class="ihb-card">
            <h3 style="margin-top:0; margin-bottom:20px; font-size:16px;">Property Facilities</h3>
            <div class="ihb-facility-grid">
                <?php foreach ($facilities_data as $f): 
                    $is_checked = in_array($f[0], $saved_facilities) ? 'checked' : '';
                ?>
                    <div class="ihb-facility-card">
                        <div class="ihb-f-icon"><svg viewBox="0 0 24 24"><path d="<?= $f[2] ?>"/></svg></div>
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
    const list = document.getElementById('alias-list');
    const addBtn = document.getElementById('add-alias');

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'ihb-row';
        row.innerHTML = `
            <input type="text" name="r_num[]" placeholder="ID" style="max-width:80px;">
            <input type="text" name="r_alias[]" placeholder="Label">
            <button type="button" class="btn-remove">&times;</button>
        `;
        list.appendChild(row);
    });

    list.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-remove')) {
            if(list.getElementsByClassName('ihb-row').length > 1) {
                e.target.closest('.ihb-row').remove();
            }
        }
    });

    // Loading State on Button
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('save-btn');
        btn.innerHTML = '<span class="dashicons dashicons-update spin" style="font-size:16px; width:16px; height:16px; vertical-align:middle; margin-right:5px;"></span> Syncing...';
        btn.style.opacity = '0.7';
    });
});
</script>