<?php 
if (!defined('ABSPATH')) exit; 

/**
 * 1. DELETE HANDLER
 */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (current_user_can('manage_options')) {
        wp_delete_post(intval($_GET['id']), true);
        echo '<script>window.location.href="admin.php?page=infinity-hotel&tab=all_rooms";</script>';
        exit;
    }
}

// Fetch Rooms
$rooms = get_posts([
    'post_type' => 'ihb_rooms', 
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
]); 
?>

<style>
    :root { --gold: #c19b76; --border: #e2e8f0; --dark: #1e293b; --slate: #64748b; }

    .ihb-inventory-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .ihb-search-wrapper { position: relative; }
    .ihb-search-input {
        padding: 10px 15px 10px 40px; border: 1px solid var(--border);
        border-radius: 10px; width: 280px; font-size: 13px; outline: none;
    }
    .ihb-search-icon { position: absolute; left: 12px; top: 10px; color: var(--slate); }

    .ihb-table-card { background: #fff; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
    .ihb-native-table { width: 100%; border-collapse: collapse; text-align: left; }
    .ihb-native-table thead th { 
        background: #f8fafc; padding: 18px 25px; color: var(--slate);
        font-size: 11px; text-transform: uppercase; border-bottom: 1px solid var(--border);
    }
    .ihb-native-table tbody td { padding: 16px 25px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    /* IMAGE FIX */
    .room-info { display: flex; align-items: center; gap: 15px; }
    .room-img-container { 
        width: 50px; height: 50px; border-radius: 10px; 
        overflow: hidden; border: 1px solid var(--border); 
        background: #f8fafc; display: flex; align-items: center; justify-content: center;
    }
    .room-img-container img { width: 100%; height: 100%; object-fit: cover; }
    .room-img-container .dashicons { font-size: 24px; color: #cbd5e1; width: 24px; height: 24px; }

    .room-name { font-weight: 800; color: var(--dark); font-size: 14px; display: block; }
    .room-type-label { font-size: 12px; color: var(--slate); }
    .price-tag { font-weight: 800; color: var(--gold); }
    
    .badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .badge-available { background: #f0fdf4; color: #16a34a; }
    .badge-booked { background: #fff7ed; color: #ea580c; }

    .ihb-action-wrap { display: flex; gap: 18px; justify-content: flex-end; }
    .ihb-action-link {
        text-decoration: none; display: flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 700; transition: 0.2s;
    }
    .btn-view { color: #64748b; }
    .btn-edit { color: var(--gold); }
    .btn-delete { color: #fca5a5; }
    .ihb-action-link .dashicons { font-size: 17px; width: 17px; height: 17px; }
    
    .ihb-btn-gold-lg { background: var(--gold); color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; }
</style>

<div class="ihb-inventory-header">
    <div>
        <h2 style="margin:0; font-weight:800; color:var(--dark);">Room Inventory</h2>
        <p style="margin:5px 0 0; color: var(--slate); font-size:13px;">Manage units and availability.</p>
    </div>
    <div style="display:flex; gap:15px; align-items:center;">
        <div class="ihb-search-wrapper">
            <span class="dashicons dashicons-search ihb-search-icon"></span>
            <input type="text" id="ihb-room-search" class="ihb-search-input" placeholder="Search rooms...">
        </div>
        <a href="?page=infinity-hotel&tab=add_room" class="ihb-btn-gold-lg">+ Add New Room</a>
    </div>
</div>

<div class="ihb-table-card">
    <table class="ihb-native-table" id="roomTable">
        <thead>
            <tr>
                <th>Room Information</th>
                <th>Price / Night</th>
                <th>Status</th>
                <th style="text-align: right;">Management</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rooms): foreach($rooms as $r): 
                // 1. GET DATA FROM META
                $price    = get_post_meta($r->ID, '_ihb_price', true);
                $type     = get_post_meta($r->ID, '_ihb_type', true);
                $status   = get_post_meta($r->ID, '_ihb_status', true) ?: 'available';
                
                // 2. IMAGE FIX: Get the ID from meta, then the URL
                $img_id   = get_post_meta($r->ID, '_ihb_room_image', true);
                $img_url  = wp_get_attachment_image_url($img_id, 'thumbnail');
                
                $title    = str_replace('Room ', '', $r->post_title);
            ?>
            <tr>
                <td>
                    <div class="room-info">
                        <div class="room-img-container">
                            <?php if ($img_url): ?>
                                <img src="<?= esc_url($img_url) ?>">
                            <?php else: ?>
                                <span class="dashicons dashicons-format-image"></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="room-name">Room <?= esc_html($title) ?></span>
                            <span class="room-type-label"><?= esc_html($type) ?></span>
                        </div>
                    </div>
                </td>
                <td><span class="price-tag">৳<?= number_format((float)$price, 0) ?></span></td>
                <td><span class="badge badge-<?= esc_attr($status) ?>"><?= ucfirst($status) ?></span></td>
                <td>
                    <div class="ihb-action-wrap">
                        <a href="?page=infinity-hotel&tab=view_room&id=<?= $r->ID ?>" class="ihb-action-link btn-view">
                            <span class="dashicons dashicons-visibility"></span> View
                        </a>
                        <a href="?page=infinity-hotel&tab=edit_room&id=<?= $r->ID ?>" class="ihb-action-link btn-edit">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </a>
                        <a href="?page=infinity-hotel&tab=all_rooms&action=delete&id=<?= $r->ID ?>" 
                           class="ihb-action-link btn-delete" 
                           onclick="return confirm('Delete permanently?');">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="4" style="text-align:center; padding:50px;">No rooms found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('ihb-room-search').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#roomTable tbody tr');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>