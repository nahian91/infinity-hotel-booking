<?php
if (!defined('ABSPATH')) exit;

function ihb_room_controller() {
    // Get current tab, default to 'all_rooms'
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all_rooms';
    $path = plugin_dir_path(__FILE__) . 'room/';

    // SUB-TABS NAVIGATION
    echo '<div class="ihb-sub-tabs">
            <a href="?page=infinity-hotel&tab=all_rooms" class="'.($tab=='all_rooms' || $tab=='view_room' ?'active':'').'">All Rooms</a>
            <a href="?page=infinity-hotel&tab=add_room" class="'.($tab=='add_room' || $tab=='edit_room' ?'active':'').'">'.($tab=='edit_room' ? 'Edit Room' : 'Add Room').'</a>
            <a href="?page=infinity-hotel&tab=room_settings" class="'.($tab=='room_settings'?'active':'').'">Settings</a>
          </div>';

    // ROUTING LOGIC
    if ($tab == 'add_room' || $tab == 'edit_room') {
        include $path . 'add-edit-room.php';
    } 
    elseif ($tab == 'view_room') {
        include $path . 'view-room.php';
    } 
    elseif ($tab == 'room_settings') {
        include $path . 'room-settings.php';
    } 
    else {
        include $path . 'all-rooms.php';
    }
}