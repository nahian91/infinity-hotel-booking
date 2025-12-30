<?php
if (!defined('ABSPATH')) exit;

function ihb_booking_controller() {
    // 1. ROUTING & TAB LOGIC
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all_bookings';
    $path = plugin_dir_path(__FILE__) . 'booking/';
    ?>

    <style>
        :root {
            --p-gold: #c19b76;
            --p-dark: #0f172a;
            --p-bg: #f8fafc;
            --p-border: #e2e8f0;
            --p-slate: #64748b;
        }

        /* NAVIGATION CONTAINER (Matches Expense UI) */
        .ihb-nav-container {
            background: #fff;
            padding: 8px;
            border-radius: 12px;
            border: 1px solid var(--p-border);
            display: inline-flex;
            gap: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .ihb-nav-item {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: var(--p-slate);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ihb-nav-item .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
        }

        /* Active State */
        .ihb-nav-item.active {
            background: var(--p-dark);
            color: #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        /* Hover State */
        .ihb-nav-item:not(.active):hover {
            background: #f1f5f9;
            color: var(--p-dark);
        }

        /* Header UI */
        .ihb-view-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 25px;
        }

        .ihb-view-title h2 { font-weight: 900; letter-spacing: -1.5px; margin: 0; font-size: 28px; color: var(--p-dark); }
        .ihb-view-title p { color: var(--p-slate); margin: 5px 0 0; font-size: 15px; }

        /* Quick Info Badge (For context) */
        .ihb-status-indicator {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            color: var(--p-slate);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>

    <div class="wrap">
        <div class="ihb-view-header">
            <div class="ihb-view-title">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:5px;">
                    <h2>Reservations</h2>
                    <span class="ihb-status-indicator">Live Ledger</span>
                </div>
                <p>Manage front-desk operations, check-ins, and guest folios.</p>
            </div>
        </div>

        <div class="ihb-nav-container">
            <a href="?page=infinity-hotel&tab=all_bookings" 
               class="ihb-nav-item <?php echo ($tab == 'all_bookings' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-calendar-alt"></span> Master Ledger
            </a>
            
            <a href="?page=infinity-hotel&tab=add_booking" 
               class="ihb-nav-item <?php echo ($tab == 'add_booking' || $tab == 'edit_booking' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-plus"></span> 
                <?php echo ($tab == 'edit_booking' ? 'Edit Booking' : 'New Reservation'); ?>
            </a>

            <?php if($tab == 'view_booking'): ?>
            <a href="#" class="ihb-nav-item active">
                <span class="dashicons dashicons-visibility"></span> Guest Folio
            </a>
            <?php endif; ?>
        </div>

        <div class="ihb-main-content">
            <?php
            if ($tab == 'add_booking' || $tab == 'edit_booking') {
                if (file_exists($path . 'add-edit-booking.php')) include $path . 'add-edit-booking.php';
            } 
            elseif ($tab == 'view_booking') {
                if (file_exists($path . 'view-booking.php')) include $path . 'view-booking.php';
            } 
            else {
                if (file_exists($path . 'all-bookings.php')) include $path . 'all-bookings.php';
            }
            ?>
        </div>
    </div>
    <?php
}