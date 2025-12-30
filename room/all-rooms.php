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

$rooms = get_posts([
    'post_type' => 'ihb_rooms', 
    'numberposts' => -1,
    'post_status' => 'publish'
]); 
?>

<style>
    /* Table Styling for DataTables Compatibility */
    .ihb-table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; margin-top: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    
    #ihb-rooms-table { width: 100% !important; border-collapse: collapse !important; border: none !important; }
    #ihb-rooms-table thead th { background: #f8fafc; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #f1f5f9; }
    #ihb-rooms-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }

    /* Customizing DataTables UI to match Gold design */
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_length select { border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current { background: var(--gold) !important; color: white !important; border: none !important; border-radius: 6px; }
    
    .room-info { display: flex; align-items: center; gap: 12px; }
    .room-img { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; }
    .room-name { font-weight: 700; color: #1e293b; display: block; }
    .room-type-label { font-size: 11px; color: #64748b; }

    .price-tag { font-weight: 700; color: var(--gold); }
    .badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
    .badge-available { background: #f0fdf4; color: #16a34a; }
    .badge-booked { background: #fff7ed; color: #ea580c; }
    .badge-maintenance { background: #fef2f2; color: #dc2626; }

    .ihb-action-wrap { display: flex; gap: 12px; justify-content: flex-end; }
    .ihb-action-link { text-decoration: none; font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 4px; }
    .btn-view { color: #64748b; }
    .btn-edit { color: var(--gold); }
    .btn-delete { color: #fca5a5; }
    .btn-delete:hover { color: #ef4444; }
</style>

<div class="ihb-header">
    <div>
        <h2>Room Inventory</h2>
        <p style="color: #64748b; margin-top:5px;">Search, sort, and manage your property rooms.</p>
    </div>
    <a href="?page=infinity-hotel&tab=add_room" class="ihb-btn-gold">+ Add New Room</a>
</div>

<div class="ihb-table-card">
    <table id="ihb-rooms-table" class="display">
        <thead>
            <tr>
                <th>Room Info</th>
                <th>Price (৳)</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($rooms as $r): 
                $price = get_post_meta($r->ID, '_ihb_price', true);
                $type = get_post_meta($r->ID, '_ihb_type', true);
                $status = get_post_meta($r->ID, '_ihb_status', true) ?: 'available';
                $img_url = get_the_post_thumbnail_url($r->ID, 'thumbnail') ?: 'https://via.placeholder.com/150';
            ?>
            <tr>
                <td>
                    <div class="room-info">
                        <img src="<?= esc_url($img_url) ?>" class="room-img">
                        <div>
                            <span class="room-name">Room <?= esc_html($r->post_title) ?></span>
                            <span class="room-type-label"><?= esc_html($type) ?></span>
                        </div>
                    </div>
                </td>
                <td><span class="price-tag"><?= number_format($price, 0) ?></span></td>
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
                           onclick="return confirm('Delete this room?');">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ihb-rooms-table').DataTable({
        "pageLength": 10,
        "ordering": true,
        "info": true,
        "dom": '<"top"f>rt<"bottom"lip><"clear">', // Custom layout
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search rooms...",
            "paginate": {
                "next": "Next",
                "previous": "Prev"
            }
        }
    });
});
</script>