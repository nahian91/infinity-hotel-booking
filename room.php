<?php
if (!defined('ABSPATH')) exit;

function ihb_room_controller() {
    // 1. ROUTING & TAB LOGIC
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all_rooms';
    $path = plugin_dir_path(__FILE__) . 'room/';
    ?>

    <style>
        :root {
            --p-gold: #c19b76;
            --p-dark: #0f172a;
            --p-border: #e2e8f0;
            --p-slate: #64748b;
        }

        /* NAVIGATION CONTAINER */
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
    </style>

    <div class="wrap">
        <div class="ihb-view-header">
            <div class="ihb-view-title">
                <h2>Room Inventory</h2>
                <p>Manage room types, pricing, and availability status.</p>
            </div>
        </div>

        <div class="ihb-nav-container">
            <a href="?page=infinity-hotel&tab=all_rooms" 
               class="ihb-nav-item <?php echo ($tab == 'all_rooms' || $tab == 'view_room' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-admin-home"></span> All Rooms
            </a>
            
            <a href="?page=infinity-hotel&tab=add_room" 
               class="ihb-nav-item <?php echo ($tab == 'add_room' || $tab == 'edit_room' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-plus"></span> 
                <?php echo ($tab == 'edit_room' ? 'Edit Room' : 'Add New Room'); ?>
            </a>

            <a href="?page=infinity-hotel&tab=room_settings" 
               class="ihb-nav-item <?php echo ($tab == 'room_settings' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-admin-tools"></span> Room Settings
            </a>
        </div>

        <div class="ihb-main-content">
            <?php
            if ($tab == 'add_room' || $tab == 'edit_room') {
                if (file_exists($path . 'add-edit-room.php')) include $path . 'add-edit-room.php';
            } 
            elseif ($tab == 'view_room') {
                if (file_exists($path . 'view-room.php')) include $path . 'view-room.php';
            } 
            elseif ($tab == 'room_settings') {
                if (file_exists($path . 'room-settings.php')) include $path . 'room-settings.php';
            } 
            else {
                if (file_exists($path . 'all-rooms.php')) include $path . 'all-rooms.php';
            }
            ?>
        </div>
    </div>
    <?php
}