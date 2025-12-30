<?php
/**
 * Plugin Name: Infinity Hotel Booking Pro
 * Version: 7.0
 * Description: Premium Hotel Management System with Master Ledger and Analytics.
 */

if (!defined('ABSPATH')) exit;

// 1. REGISTER ENTITIES (Rooms, Bookings, Expenses)
add_action('init', function() {
    $types = [
        'ihb_rooms'    => 'Rooms', 
        'ihb_bookings' => 'Bookings', 
        'ihb_expense'  => 'Expenses'
    ];
    foreach ($types as $slug => $label) {
        register_post_type($slug, [
            'labels'      => ['name' => $label], 
            'public'      => false, 
            'show_ui'     => true, 
            'supports'    => ['title', 'editor', 'thumbnail'],
            'has_archive' => false,
            'rewrite'     => false,
            'show_in_menu'=> false,
        ]);
    }
});

// 2. LOAD CONTROLLERS (Ensure these files exist in your plugin folder)
require_once plugin_dir_path(__FILE__) . 'dashboard.php';
require_once plugin_dir_path(__FILE__) . 'calendar.php';
require_once plugin_dir_path(__FILE__) . 'booking.php';
require_once plugin_dir_path(__FILE__) . 'room.php';
require_once plugin_dir_path(__FILE__) . 'expense.php';
require_once plugin_dir_path(__FILE__) . 'customers.php';
require_once plugin_dir_path(__FILE__) . 'analytics.php';

// 3. ADMIN MENU
add_action('admin_menu', function() {
    add_menu_page(
        'Infinity Hotel', 
        'Infinity Pro', 
        'manage_options', 
        'infinity-hotel', 
        'ihb_master_render', 
        'dashicons-building', 
        2
    );
});

// 4. MASTER RENDER ENGINE
function ihb_master_render() {
    $tab = $_GET['tab'] ?? 'overview';
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap');
        
        :root { 
            --gold: #c19b76; 
            --gold-light: #F4E07D;
            --gold-dark: #B8860B; 
            --bg: #F4F7F6; 
            --sidebar: #0f172a; 
            --sidebar-active: #1e293b; 
            --white: #ffffff; 
            --text-main: #111827; 
            --text-muted: #6B7280;
        }

        .ihb-wrap { 
            display: flex; margin: 20px 20px 0 0; background: var(--bg); 
            font-family: 'Inter', sans-serif; min-height: 88vh; border-radius: 20px; 
            overflow: hidden; box-shadow: 0 30px 60px rgba(0,0,0,0.12); 
        }

        /* Sidebar & Brand Identity */
        .ihb-sidebar { width: 260px; background: var(--sidebar); color: var(--white); display: flex; flex-direction: column; }
        
        .ihb-brand { padding: 50px 20px; text-align: center; position: relative; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .brand-logo { 
            width: 42px; height: 42px; margin: 0 auto 15px; border: 2px solid var(--gold); 
            border-radius: 10px; display: flex; align-items: center; justify-content: center; transform: rotate(45deg); 
        }
        .brand-logo .dashicons { color: var(--gold); transform: rotate(-45deg); font-size: 22px; width: 22px; height: 22px; }
        
        .ihb-brand h1 { 
            background: linear-gradient(to bottom, var(--gold-light) 0%, var(--gold) 50%, var(--gold-dark) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin: 0; font-size: 24px; letter-spacing: 5px; font-weight: 900; line-height: 1;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .ihb-brand p { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 3px; margin: 10px 0 0; font-weight: 700; opacity: 0.8; }
        
        /* Navigation */
        .ihb-nav { flex: 1; padding: 30px 0; }
        .ihb-nav a { 
            display: flex; align-items: center; padding: 14px 22px; color: #94a3b8; 
            text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            font-size: 14px; margin: 4px 18px; border-radius: 10px; font-weight: 600;
        }
        .ihb-nav a:hover { color: var(--white); background: rgba(255,255,255,0.03); }
        .ihb-nav a.active { background: var(--gold); color: var(--white); box-shadow: 0 10px 20px rgba(212, 175, 55, 0.2); }
        .ihb-nav a span { margin-right: 15px; font-size: 18px; }

        /* Main Content Area */
        .ihb-main { flex: 1; padding: 40px; background: var(--white); overflow-y: auto; position: relative; }
    </style>

    <div class="ihb-wrap">
        <div class="ihb-sidebar">
            <div class="ihb-brand">
                <div class="brand-logo"><span class="dashicons dashicons-building"></span></div>
                <h1>INFINITY</h1>
                <p>Hotel Properties</p>
            </div>
            
            <div class="ihb-nav">
                <a href="?page=infinity-hotel&tab=overview" class="<?= $tab == 'overview' ? 'active' : '' ?>">
                    <span class="dashicons dashicons-dashboard"></span> Dashboard
                </a>
                <a href="?page=infinity-hotel&tab=calendar" class="<?= $tab == 'calendar' ? 'active' : '' ?>">
                    <span class="dashicons dashicons-calendar"></span> Calendar
                </a>
                <a href="?page=infinity-hotel&tab=all_rooms" class="<?= strpos($tab, 'room') !== false ? 'active' : '' ?>">
                    <span class="dashicons dashicons-admin-home"></span> Rooms
                </a>
                <a href="?page=infinity-hotel&tab=all_bookings" class="<?= strpos($tab, 'booking') !== false ? 'active' : '' ?>">
                    <span class="dashicons dashicons-calendar-alt"></span> Bookings
                </a>
                <a href="?page=infinity-hotel&tab=all_expenses" class="<?= strpos($tab, 'expense') !== false ? 'active' : '' ?>">
                    <span class="dashicons dashicons-cart"></span> Expenses
                </a>
                <a href="?page=infinity-hotel&tab=all_users" class="<?= strpos($tab, 'user') !== false ? 'active' : '' ?>">
                    <span class="dashicons dashicons-businessman"></span> Customers
                </a>
                <a href="?page=infinity-hotel&tab=analytics" class="<?= $tab == 'analytics' ? 'active' : '' ?>">
                    <span class="dashicons dashicons-chart-bar"></span> Reports
                </a>
            </div>
        </div>

        <div class="ihb-main">
            <?php
            // Route Handling
            switch(true) {
                case ($tab === 'overview'):
                    ihb_render_dashboard();
                    break;
                case ($tab === 'calendar'):
                    ihb_render_calendar_view();
                    break;
                case (strpos($tab, 'room') !== false):
                    ihb_room_controller();
                    break;
                case (strpos($tab, 'booking') !== false):
                    ihb_booking_controller();
                    break;
                case (strpos($tab, 'expense') !== false):
                    ihb_expense_controller();
                    break;
                case (strpos($tab, 'user') !== false):
                    ihb_customers_view();
                    break;
                case ($tab === 'analytics'):
                    ihb_render_analytics();
                    break;
                default:
                    ihb_render_dashboard();
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}

// 5. ASSETS & MEDIA
add_action('admin_enqueue_scripts', function($hook) {
    // OPTIONAL: Only load on your specific plugin page to keep WP fast
    // if (strpos($hook, 'infinity-hotel') === false) return;

    wp_enqueue_media();

    // DataTables CSS
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css');

    // DataTables JS (depends on jQuery)
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js', array('jquery'), null, true);

    // Custom CSS for Luxury Gold Theme
    // If you have a style.css file, enqueue it here:
    // wp_enqueue_style('ihb-luxury-style', plugin_dir_url(__FILE__) . 'assets/style.css');
});