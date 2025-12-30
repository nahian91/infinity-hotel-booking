<?php
if (!defined('ABSPATH')) exit;

function ihb_expense_controller() {
    // Default to all_expenses if no tab is set
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all_expenses';
    $path = plugin_dir_path(__FILE__) . 'expense/';

    // Sub-navigation Menu
    echo '<div class="ihb-sub-tabs">
            <a href="?page=infinity-hotel&tab=all_expenses" class="'.($tab == 'all_expenses' ? 'active' : '').'">All Expenses</a>
            <a href="?page=infinity-hotel&tab=add_expense" class="'.($tab == 'add_expense' ? 'active' : '').'">Add Expense</a>
            <a href="?page=infinity-hotel&tab=expense_settings" class="'.($tab == 'expense_settings' ? 'active' : '').'">Settings</a>
          </div>';

    // Router logic
    if ($tab == 'add_expense' || $tab == 'edit_expense') {
        include $path . 'add-edit-expense.php';
    } 
    elseif ($tab == 'expense_settings') {
        include $path . 'expense-settings.php';
    } 
    else {
        include $path . 'all-expenses.php';
    }
}