<?php
$tabs = array(
    1 => 'base_settings',
    2 => 'email_settings'
);
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Ticket Settings</h2>

    <?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 1; ?>

    <p><strong>Next Scheduled Check:</strong> <?php echo date('H:i:s \o\n \t\h\e d/m/Y', wp_next_scheduled( 'jc_support_system_cron')); ?></p>

    <h3 class="nav-tab-wrapper">
        <a href="?page=support-ticket-settings&tab=1" class="nav-tab <?php if($tab == 1): ?>nav-tab-active<?php endif; ?>">General Settings</a>
        <a href="?page=support-ticket-settings&tab=2" class="nav-tab <?php if($tab == 2): ?>nav-tab-active<?php endif; ?>">Email</a>
    </h3>

    <?php if($tab == 1 || $tab == 2): ?>
    <form action="options.php" method="post" enctype="multipart/form-data">  
        <?php
        settings_fields($this->settings_optgroup);
        do_settings_sections($tabs[$tab])
        ?>  
        <p class="submit">  
            <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />  
        </p>  
    </form> 
    <?php endif; ?>
</div>