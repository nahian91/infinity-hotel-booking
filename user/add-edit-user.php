<?php if (!defined('ABSPATH')) exit; ?>
<div class="ihb-header"><h2>Register New Staff</h2></div>
<div class="ihb-card">
    <form method="post" action="<?= admin_url('user-new.php') ?>">
        <p>To ensure WordPress security, please use the core user creation tool to add staff members. You can assign them roles like "Editor" or "Author" to manage bookings.</p>
        <a href="<?= admin_url('user-new.php') ?>" class="ihb-btn-gold">Go to WordPress User Tool</a>
    </form>
</div>