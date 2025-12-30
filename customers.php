<?php
/**
 * INFINITY HOTEL - DYNAMIC GUEST DATABASE (Native UI)
 */
if (!defined('ABSPATH')) exit;

function ihb_customers_view() {
    // 1. DATA PROCESSING (Same logic as yours)
    $all_bookings = get_posts([
        'post_type'      => 'ihb_bookings',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $customer_data = [];
    
    if ($all_bookings) {
        foreach ($all_bookings as $booking) {
            $guests_list = get_post_meta($booking->ID, '_ihb_guests_list', true) ?: [];
            $checkin     = get_post_meta($booking->ID, '_ihb_checkin', true);
            $checkout    = get_post_meta($booking->ID, '_ihb_checkout', true);
            $total_bill  = (float)get_post_meta($booking->ID, '_ihb_total_price', true);
            $room_id     = get_post_meta($booking->ID, '_ihb_room_id', true);
            $room_name   = get_the_title($room_id);

            foreach ($guests_list as $guest) {
                $name  = !empty($guest['name']) ? esc_html($guest['name']) : 'Unknown Guest';
                $phone = !empty($guest['phone']) ? esc_html($guest['phone']) : 'No Phone';
                $unique_key = sanitize_title($name . '-' . $phone);

                if (!isset($customer_data[$unique_key])) {
                    $customer_data[$unique_key] = [
                        'name'        => $name,
                        'phone'       => $phone,
                        'email'       => $guest['email'] ?? 'N/A',
                        'total_spent' => 0,
                        'visit_count' => 0,
                        'history'     => []
                    ];
                }
                
                $customer_data[$unique_key]['total_spent'] += $total_bill;
                $customer_data[$unique_key]['visit_count']++;
                $customer_data[$unique_key]['history'][] = [
                    'booking_id' => $booking->ID,
                    'room'       => $room_name,
                    'checkin'    => $checkin,
                    'checkout'   => $checkout,
                    'paid'       => $total_bill,
                    'role'       => ($guests_list[0]['name'] === $guest['name']) ? 'Primary' : 'Companion'
                ];
            }
        }
    }
    ?>

    <style>
        :root { --p-gold: #c19b76; --p-dark: #0f172a; --p-slate: #64748b; }
        
        /* Modernized WP Table */
        .ihb-cust-table { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .wp-list-table.ihb-native-style { border: none; box-shadow: none; margin: 0; }
        .ihb-native-style thead th { background: #f8fafc !important; color: #475569 !important; font-weight: 800; padding: 15px !important; }
        
        .cust-name-cell { display: flex; align-items: center; gap: 12px; }
        .cust-initial { width: 35px; height: 35px; background: var(--p-dark); color: var(--p-gold); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 900; }
        
        /* History Section (Hidden by default) */
        .history-row { display: none; background: #f8fafc; }
        .history-row.is-active { display: table-row; }
        .history-container { padding: 20px; border-left: 4px solid var(--p-gold); }
        .history-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; }
        .history-item { background: #fff; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; }
        
        .toggle-history { border: 1px solid #ccd0d4; background: #f6f7f7; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; }
        .toggle-history:hover { background: #fff; color: var(--p-gold); border-color: var(--p-gold); }
        .toggle-history.active { background: var(--p-dark); color: #fff; }
    </style>

    <div class="wrap">
        <h1 style="font-weight: 900; letter-spacing: -1px; margin-bottom: 20px;">Guest Intelligence</h1>
        
        <div class="ihb-cust-table">
            <table class="wp-list-table widefat fixed striped posts ihb-native-style">
                <thead>
                    <tr>
                        <th width="30%">Guest Identity</th>
                        <th>Contact</th>
                        <th>Stays</th>
                        <th>Total Revenue</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customer_data)) : foreach ($customer_data as $key => $data) : ?>
                        <tr>
                            <td>
                                <div class="cust-name-cell">
                                    <div class="cust-initial"><?php echo substr($data['name'], 0, 1); ?></div>
                                    <div>
                                        <strong><?php echo $data['name']; ?></strong>
                                        <div style="font-size:10px; color:var(--p-slate);">UID: <?php echo esc_attr($key); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo $data['phone']; ?></div>
                                <div style="font-size:11px; color:var(--p-slate);"><?php echo $data['email']; ?></div>
                            </td>
                            <td><strong><?php echo $data['visit_count']; ?></strong> Visits</td>
                            <td><strong style="color: #10b981;">৳<?php echo number_format($data['total_spent'], 0); ?></strong></td>
                            <td style="text-align:right">
                                <button type="button" class="toggle-history" data-target="history-<?php echo $key; ?>">
                                    View History
                                </button>
                            </td>
                        </tr>
                        
                        <tr id="history-<?php echo $key; ?>" class="history-row">
                            <td colspan="5">
                                <div class="history-container">
                                    <h4 style="margin: 0 0 15px 0;">Lifetime Stay Records for <?php echo $data['name']; ?></h4>
                                    <div class="history-grid">
                                        <?php foreach ($data['history'] as $h) : ?>
                                            <div class="history-item">
                                                <div style="font-size:10px; color:var(--p-gold); font-weight:800;"><?php echo strtoupper($h['role']); ?> GUEST</div>
                                                <div style="font-weight:700; margin: 5px 0;">Room: <?php echo $h['room']; ?></div>
                                                <div style="font-size:12px; color:var(--p-slate);">
                                                    <?php echo date('M d', strtotime($h['checkin'])); ?> - <?php echo date('M d, Y', strtotime($h['checkout'])); ?>
                                                </div>
                                                <div style="margin-top:10px; font-weight:800; color:#1e293b; font-size:14px;">
                                                    ৳<?php echo number_format($h['paid'], 0); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5">No guest data found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.toggle-history');
        
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetRow = document.getElementById(targetId);
                
                // Toggle active class and visibility
                targetRow.classList.toggle('is-active');
                this.classList.toggle('active');
                
                // Change text
                if(this.classList.contains('active')) {
                    this.textContent = 'Close History';
                } else {
                    this.textContent = 'View History';
                }
            });
        });
    });
    </script>
    <?php
}