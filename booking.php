<?php
if (!defined('ABSPATH')) exit;
function ihb_booking_controller() {
    $tab = $_GET['tab'];
    $path = plugin_dir_path(__FILE__) . 'booking/';
    echo '<div class="ihb-sub-tabs">
            <a href="?page=infinity-hotel&tab=all_bookings" class="'.($tab=='all_bookings'?'active':'').'">All Bookings</a>
            <a href="?page=infinity-hotel&tab=add_booking" class="'.($tab=='add_booking'?'active':'').'">Add Booking</a>
          </div>';
    if ($tab == 'add_booking' || $tab == 'edit_booking') include $path . 'add-edit-booking.php';
    elseif ($tab == 'view_booking') include $path . 'view-booking.php';
    else include $path . 'all-bookings.php';
}