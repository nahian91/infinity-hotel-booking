<?php 
if (!defined('ABSPATH')) exit; 

/**
 * 1. ACTION HANDLER (Delete/Trash)
 * Integrated logic to handle requests before the table renders
 */
if (isset($_GET['action']) && isset($_GET['id']) && current_user_can('manage_options')) {
    $booking_id = intval($_GET['id']);
    
    if ($_GET['action'] === 'trash') {
        wp_trash_post($booking_id);
        echo '<script>window.location.href="admin.php?page=infinity-hotel&tab=all_bookings&status=trashed";</script>';
        exit;
    }
    
    if ($_GET['action'] === 'delete') {
        wp_delete_post($booking_id, true);
        echo '<script>window.location.href="admin.php?page=infinity-hotel&tab=all_bookings&status=deleted";</script>';
        exit;
    }
}

// Fetch all active bookings
$bookings = get_posts([
    'post_type'   => 'ihb_bookings', 
    'numberposts' => -1,
    'post_status' => 'publish'
]); 
?>

<style>
    :root { --gold: #c19b76; --slate: #1e293b; --green: #10b981; --red: #ef4444; --border: #e2e8f0; }
    
    /* Container & Layout */
    .ihb-table-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); margin-top: 20px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03); padding: 20px; }
    
    /* Table Styling */
    #ihb-ledger-table { width: 100% !important; border-collapse: collapse !important; border: none !important; }
    #ihb-ledger-table thead th { background: #f8fafc; color: #94a3b8; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; padding: 15px; border-bottom: 2px solid #f1f5f9; text-align: left; }
    #ihb-ledger-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 14px; color: var(--slate); }

    /* Guest Badge */
    .guest-count-tag { background: #f1f5f9; color: #64748b; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 8px; border: 1px solid #e2e8f0; }
    
    /* Status Badges */
    .ihb-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .badge-cash { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
    .badge-mfs { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    
    /* Action Buttons */
    .ihb-action-group { display: flex; gap: 8px; justify-content: flex-end; }
    .btn-action { 
        display: flex; align-items: center; justify-content: center;
        width: 32px; height: 32px; border-radius: 8px; 
        text-decoration: none; transition: 0.2s; border: 1px solid #e2e8f0;
        background: #fff; color: #64748b;
    }
    .btn-action:hover { background: #f8fafc; color: var(--slate); border-color: #cbd5e1; }
    .btn-view:hover { color: #3b82f6; border-color: #3b82f6; background: #eff6ff; }
    .btn-edit:hover { color: var(--gold); border-color: var(--gold); background: #fdfaf7; }
    .btn-delete:hover { color: var(--red); border-color: var(--red); background: #fef2f2; }

    .room-no { background: #f1f5f9; padding: 4px 10px; border-radius: 6px; font-weight: 800; color: #475569; font-size: 12px; }
    .price-tag { font-weight: 800; color: var(--slate); }
</style>

<div class="ihb-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="margin:0; font-weight:900;">Master Ledger</h2>
        <p style="color: #64748b; margin: 5px 0 0; font-size: 14px;">Real-time tracking of guest check-ins and payments.</p>
    </div>
    <a href="?page=infinity-hotel&tab=add_booking" class="ihb-btn-gold" style="text-decoration:none;">+ New Reservation</a>
</div>

<?php if(isset($_GET['status'])): ?>
    <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Booking successfully <?= $_GET['status'] === 'trashed' ? 'moved to trash' : 'deleted permanently' ?>.
    </div>
<?php endif; ?>

<div class="ihb-table-card">
    <table id="ihb-ledger-table" class="display">
        <thead>
            <tr>
                <th>Guest Profile</th>
                <th>Room Info</th>
                <th>Stay Duration</th>
                <th>Total Bill</th>
                <th>Payment</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings): foreach($bookings as $b): 
                // Guest Data from Repeater
                $guests   = get_post_meta($b->ID, '_ihb_guests_list', true) ?: [];
                $g_count  = count($guests);
                $primary_phone = !empty($guests) ? $guests[0]['phone'] : 'No Phone';
                
                // Room & Metadata
                $rid      = get_post_meta($b->ID, '_ihb_room_id', true);
                $checkin  = get_post_meta($b->ID, '_ihb_checkin', true);
                $checkout = get_post_meta($b->ID, '_ihb_checkout', true);
                $total    = get_post_meta($b->ID, '_ihb_total_price', true);
                $method   = get_post_meta($b->ID, '_ihb_pay_method', true) ?: 'cash';

                $nights = 0;
                if ($checkin && $checkout) {
                    $nights = ceil((strtotime($checkout) - strtotime($checkin)) / 86400);
                }
            ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <span style="font-weight: 700; color: var(--slate);"><?= esc_html($b->post_title) ?></span>
                            <?php if($g_count > 1): ?>
                                <span class="guest-count-tag">+<?= $g_count - 1 ?> Others</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;"><?= esc_html($primary_phone) ?></div>
                    </td>
                    <td>
                        <span class="room-no">Room <?= $rid ? get_the_title($rid) : 'N/A' ?></span>
                    </td>
                    <td>
                        <div style="font-weight: 600; font-size:13px;">
                            <?= date('d M', strtotime($checkin)) ?> - <?= date('d M', strtotime($checkout)) ?>
                        </div>
                        <div style="font-size: 11px; color: #94a3b8;"><?= $nights ?> Nights Stay</div>
                    </td>
                    <td>
                        <div class="price-tag">৳<?= number_format((float)$total) ?></div>
                    </td>
                    <td>
                        <span class="ihb-badge <?= ($method === 'cash') ? 'badge-cash' : 'badge-mfs' ?>">
                            <?= ucfirst($method) ?>
                        </span>
                    </td>
                    <td>
                        <div class="ihb-action-group">
                            <a href="?page=infinity-hotel&tab=view_booking&id=<?= $b->ID ?>" class="btn-action btn-view" title="View Folio">
                                <span class="dashicons dashicons-visibility"></span>
                            </a>
                            
                            <a href="?page=infinity-hotel&tab=add_booking&id=<?= $b->ID ?>" class="btn-action btn-edit" title="Edit Booking">
                                <span class="dashicons dashicons-edit"></span>
                            </a>
                            
                            <a href="?page=infinity-hotel&tab=all_bookings&action=trash&id=<?= $b->ID ?>" 
                               class="btn-action btn-delete" 
                               title="Move to Trash" 
                               onclick="return confirm('Move this reservation to trash?');">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
jQuery(document).ready(function($) {
    $('#ihb-ledger-table').DataTable({
        "pageLength": 10,
        "order": [[2, "desc"]], // Sort by Stay Duration/Date
        "dom": '<"top"f>rt<"bottom"lip><"clear">',
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search Guest or Phone...",
            "paginate": { "next": "Next", "previous": "Prev" }
        }
    });
});
</script>