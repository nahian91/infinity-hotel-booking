<?php 
if (!defined('ABSPATH')) exit; 

/**
 * 1. ACTION HANDLER
 */
if (isset($_GET['action']) && isset($_GET['id']) && current_user_can('manage_options')) {
    $booking_id = intval($_GET['id']);
    if ($_GET['action'] === 'trash') {
        wp_trash_post($booking_id);
        echo '<script>window.location.href="admin.php?page=infinity-hotel&tab=all_bookings&status=trashed";</script>';
        exit;
    }
}

// Fetch bookings
$bookings = get_posts([
    'post_type'   => 'ihb_bookings', 
    'numberposts' => -1,
    'post_status' => 'publish'
]); 
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; --border: #e2e8f0; --bg: #f8fafc; }
    
    .ihb-ledger-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .ihb-search-box { position: relative; width: 300px; }
    .ihb-search-box input { 
        width: 100%; padding: 10px 15px 10px 35px; 
        border: 1px solid var(--border); border-radius: 8px; font-size: 13px;
    }
    .ihb-search-box .dashicons { position: absolute; left: 10px; top: 10px; color: #94a3b8; }

    .ihb-table-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
    .ihb-native-table { width: 100%; border-collapse: collapse; text-align: left; }
    .ihb-native-table thead th { 
        background: var(--bg); padding: 15px 20px; color: #64748b;
        font-size: 11px; text-transform: uppercase; border-bottom: 2px solid var(--border);
    }
    .ihb-native-table tbody td { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    
    .guest-name { font-weight: 700; color: var(--slate); display: block; }
    .room-badge { background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight: 800; font-size: 12px; color: #475569; }
    
    .ihb-badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
    .badge-cash { background: #ecfdf5; color: #059669; }
    .badge-mfs { background: #fff7ed; color: #ea580c; }

    /* ACTION BUTTONS WITH TEXT */
    .ihb-action-wrap { display: flex; gap: 12px; justify-content: flex-end; }
    .ihb-btn-text { 
        display: inline-flex; align-items: center; gap: 5px;
        text-decoration: none; font-size: 12px; font-weight: 700; transition: 0.2s;
    }
    .btn-view { color: #64748b; }
    .btn-edit { color: var(--gold); }
    .btn-delete { color: #fca5a5; }
    
    .ihb-btn-text:hover { opacity: 0.7; }
    .ihb-btn-text .dashicons { font-size: 17px; width: 17px; height: 17px; }
</style>

<div class="ihb-ledger-header">
    <div>
        <h2 style="margin:0; font-weight:900; font-size:24px;">Master Ledger</h2>
        <p style="color: #64748b; margin: 5px 0 0;">Overview of all guest reservations and check-ins.</p>
    </div>
    <div class="ihb-search-box">
        <span class="dashicons dashicons-search"></span>
        <input type="text" id="ledgerSearch" placeholder="Search guests or rooms...">
    </div>
</div>

<div class="ihb-table-card">
    <table class="ihb-native-table" id="ledgerTable">
        <thead>
            <tr>
                <th>Guest Information</th>
                <th>Room</th>
                <th>Stay Period</th>
                <th>Total Bill</th>
                <th>Method</th>
                <th style="text-align: right;">Management</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings): foreach($bookings as $b): 
                $guests = get_post_meta($b->ID, '_ihb_guests_list', true) ?: [];
                $rid    = get_post_meta($b->ID, '_ihb_room_id', true);
                $in     = get_post_meta($b->ID, '_ihb_checkin', true);
                $out    = get_post_meta($b->ID, '_ihb_checkout', true);
                $total  = get_post_meta($b->ID, '_ihb_total_price', true);
                $method = get_post_meta($b->ID, '_ihb_pay_method', true) ?: 'cash';
                $phone  = !empty($guests) ? $guests[0]['phone'] : 'N/A';
            ?>
            <tr>
                <td>
                    <span class="guest-name"><?= esc_html($b->post_title) ?></span>
                    <span style="font-size:12px; color:#94a3b8;"><?= esc_html($phone) ?></span>
                </td>
                <td><span class="room-badge">Room <?= $rid ? get_the_title($rid) : 'N/A' ?></span></td>
                <td style="font-size: 13px;">
                    <?= date('d M', strtotime($in)) ?> - <?= date('d M', strtotime($out)) ?>
                </td>
                <td><strong>৳<?= number_format((float)$total) ?></strong></td>
                <td><span class="ihb-badge badge-<?= $method ?>"><?= ucfirst($method) ?></span></td>
                <td>
                    <div class="ihb-action-wrap">
                        <a href="?page=infinity-hotel&tab=view_booking&id=<?= $b->ID ?>" class="ihb-btn-text btn-view">
                            <span class="dashicons dashicons-visibility"></span> View
                        </a>
                        
                        <a href="?page=infinity-hotel&tab=add_booking&id=<?= $b->ID ?>" class="ihb-btn-text btn-edit">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </a>
                        
                        <a href="?page=infinity-hotel&tab=all_bookings&action=trash&id=<?= $b->ID ?>" 
                           class="ihb-btn-text btn-delete" 
                           onclick="return confirm('Move to trash?')">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="6" style="text-align:center; padding:50px; color:#94a3b8;">No bookings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('ledgerSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('#ledgerTable tbody tr');
    rows.forEach(row => {
        let text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>