<?php
if (!defined('ABSPATH')) exit;

function ihb_expense_controller() {
    // 1. ROUTING & DATA
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all_expenses';
    $path = plugin_dir_path(__FILE__) . 'expense/';
    ?>

    <style>
        :root {
            --p-gold: #c19b76;
            --p-dark: #0f172a;
            --p-bg: #f8fafc;
            --p-border: #e2e8f0;
        }

        /* 2. SUB-NAVIGATION UI/UX */
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
            color: #64748b;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ihb-nav-item .dashicons {
            font-size: 18px;
            width: 18px;
            height: 18px;
            margin-top: -2px;
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

        /* Header Area Styling */
        .ihb-expense-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }
        
        .ihb-view-title h2 { font-weight: 900; letter-spacing: -1px; margin: 0; font-size: 24px; }
        .ihb-view-title p { color: #64748b; margin: 5px 0 20px; font-size: 14px; }
    </style>

    <div class="wrap">
        <div class="ihb-expense-header">
            <div class="ihb-view-title">
                <h2>Expense Management</h2>
                <p>Track operating costs, maintenance, and utility bills.</p>
            </div>
        </div>

        <div class="ihb-nav-container">
            <a href="?page=infinity-hotel&tab=all_expenses" 
               class="ihb-nav-item <?php echo ($tab == 'all_expenses' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-list-view"></span> All Expenses
            </a>
            
            <a href="?page=infinity-hotel&tab=add_expense" 
               class="ihb-nav-item <?php echo ($tab == 'add_expense' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-plus-alt"></span> Add Expense
            </a>
            
            <a href="?page=infinity-hotel&tab=expense_settings" 
               class="ihb-nav-item <?php echo ($tab == 'expense_settings' ? 'active' : ''); ?>">
                <span class="dashicons dashicons-admin-settings"></span> Categories
            </a>
        </div>

        <div class="ihb-view-body">
            <?php
            // Router logic
            if ($tab == 'add_expense' || $tab == 'edit_expense') {
                if (file_exists($path . 'add-edit-expense.php')) {
                    include $path . 'add-edit-expense.php';
                }
            } 
            elseif ($tab == 'expense_settings') {
                if (file_exists($path . 'expense-settings.php')) {
                    include $path . 'expense-settings.php';
                }
            } 
            else {
                if (file_exists($path . 'all-expenses.php')) {
                    include $path . 'all-expenses.php';
                }
            }
            ?>
        </div>
    </div>
    <?php
}