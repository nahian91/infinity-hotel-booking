<?php
/**
 * INFINITY HOTEL - DYNAMIC GUEST DATABASE (v2.0)
 * Scans Multi-Guest Repeater data to build individual customer profiles.
 */
if (!defined('ABSPATH')) exit;

function ihb_customers_view() {
    // 1. DATA PROCESSING
    $all_bookings = get_posts([
        'post_type'      => 'ihb_bookings',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);

    $customer_data = [];
    
    if ($all_bookings) {
        foreach ($all_bookings as $booking) {
            // Get the Repeater List of Guests
            $guests_list = get_post_meta($booking->ID, '_ihb_guests_list', true) ?: [];
            $checkin     = get_post_meta($booking->ID, '_ihb_checkin', true);
            $checkout    = get_post_meta($booking->ID, '_ihb_checkout', true);
            $total_bill  = (float)get_post_meta($booking->ID, '_ihb_total_price', true);
            $room_id     = get_post_meta($booking->ID, '_ihb_room_id', true);
            $room_name   = get_the_title($room_id);

            foreach ($guests_list as $guest) {
                $name  = !empty($guest['name']) ? esc_html($guest['name']) : 'Unknown Guest';
                $phone = !empty($guest['phone']) ? esc_html($guest['phone']) : 'No Phone';
                
                // Group by Name + Phone to ensure unique profiles even if names match
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
                
                // Add this booking to the guest's lifetime value
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

    // 2. ASSETS
    echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">';
    echo '<script src="https://code.jquery.com/jquery-3.7.0.js"></script>';
    echo '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
    ?>

    <style>
        :root { --p-gold: #c19b76; --p-dark: #0f172a; --p-slate: #64748b; }
        .ihb-cust-wrap { margin-top: 20px; font-family: 'Inter', sans-serif; }
        .ihb-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        
        /* Table Styling */
        #customerTable { border: none !important; }
        #customerTable thead th { background: #f8fafc; padding: 15px; border-bottom: 2px solid #edf2f7; color: #475569; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        #customerTable tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        
        .cust-name-cell { display: flex; align-items: center; gap: 12px; }
        .cust-initial { width: 38px; height: 38px; background: var(--p-dark); color: var(--p-gold); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; }
        
        /* Detail Row */
        .child-row-content { background: #f8fafc; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin: 10px; }
        .history-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; }
        .history-card { background: white; border: 1px solid #e2e8f0; padding: 15px; border-radius: 10px; position: relative; }
        .role-badge { position: absolute; top: 12px; right: 12px; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 800; background: #eee; }
        .role-Primary { background: #fff7ed; color: #c2410c; }

        .view-btn { background: #fff; color: var(--p-dark); border: 1px solid #e2e8f0; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; transition: 0.2s; }
        .view-btn:hover { border-color: var(--p-gold); color: var(--p-gold); }
        .view-btn.active { background: var(--p-gold); color: white; border-color: var(--p-gold); }
    </style>

    <div class="ihb-cust-wrap">
        <div style="margin-bottom: 30px;">
            <h1 style="font-weight: 900; letter-spacing: -1.5px; margin:0; color:var(--p-dark);">Guest Intelligence</h1>
            <p style="color: var(--p-slate); margin: 5px 0 0;">Automated profiling based on Multi-Guest reservation logs.</p>
        </div>

        <div class="ihb-card">
            <table id="customerTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Guest Identity</th>
                        <th>Contact info</th>
                        <th>Total Stays</th>
                        <th>LTV (Revenue)</th>
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
                                        <div style="font-weight:800; color:var(--p-dark);"><?php echo $data['name']; ?></div>
                                        <div style="font-size:11px; color:var(--p-slate);">ID: <?php echo strtoupper($key); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; font-size:13px;"><?php echo $data['phone']; ?></div>
                                <div style="font-size:12px; color:var(--p-slate);"><?php echo $data['email']; ?></div>
                            </td>
                            <td><span style="background:#f1f5f9; padding:4px 10px; border-radius:6px; font-weight:700;"><?php echo $data['visit_count']; ?> Visits</span></td>
                            <td data-sort="<?php echo $data['total_spent']; ?>">
                                <span style="font-weight: 900; color: #10b981; font-size:15px;">৳<?php echo number_format($data['total_spent'], 0); ?></span>
                            </td>
                            <td style="text-align:right">
                                <button class="view-btn" data-guest="<?php echo $key; ?>">View History</button>
                                
                                <div class="details-data" style="display:none;">
                                    <div class="child-row-content">
                                        <h4 style="margin: 0 0 20px 0; display:flex; align-items:center; gap:10px;">
                                            <span class="dashicons dashicons-calendar-alt" style="color:var(--p-gold)"></span> 
                                            Stay History for <?php echo $data['name']; ?>
                                        </h4>
                                        <div class="history-grid">
                                            <?php foreach ($data['history'] as $h) : ?>
                                                <div class="history-card">
                                                    <span class="role-badge role-<?php echo $h['role']; ?>"><?php echo $h['role']; ?></span>
                                                    <div style="font-size:11px; font-weight:800; color:var(--p-slate); margin-bottom:10px;">BOOKING #<?php echo $h['booking_id']; ?></div>
                                                    <div style="font-weight:700; color:var(--p-dark); margin-bottom:5px;">Room: <?php echo $h['room']; ?></div>
                                                    <div style="font-size:13px; color:var(--p-slate); mb-10">
                                                        <?php echo date('M d, Y', strtotime($h['checkin'])); ?> — <?php echo date('M d, Y', strtotime($h['checkout'])); ?>
                                                    </div>
                                                    <div style="margin-top:12px; padding-top:10px; border-top:1px dashed #eee; font-weight:800; color:#10b981;">
                                                        Paid: ৳<?php echo number_format($h['paid'], 0); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var table = $('#customerTable').DataTable({
            "pageLength": 10,
            "order": [[3, "desc"]], // Sort by Revenue (LTV)
            "dom": '<"top"f>rt<"bottom"lp><"clear">',
            "language": {
                "search": "",
                "searchPlaceholder": "Search Guest by Name or Phone..."
            }
        });

        // Toggle Details
        $('#customerTable tbody').on('click', '.view-btn', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var btn = $(this);

            if (row.child.isShown()) {
                row.child.hide();
                btn.text('View History').removeClass('active');
            } else {
                var detailsHtml = tr.find('.details-data').html();
                row.child(detailsHtml).show();
                btn.text('Close History').addClass('active');
            }
        });
    });
    </script>
    <?php
}