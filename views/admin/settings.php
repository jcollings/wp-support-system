<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Support Ticket Settings</h2>

    <?php $current_tab = isset($_GET['tab']) ? $_GET['tab'] : key($tabs); ?>

    <p><strong>Next Scheduled Check:</strong> <?php echo date('H:i:s \o\n \t\h\e d/m/Y', wp_next_scheduled( 'jc_support_system_cron')); ?></p>

    <h3 class="nav-tab-wrapper">
        <?php foreach($tabs as $id => $tab): ?>
        <a href="?page=support-ticket-settings&tab=<?php echo $id; ?>" class="nav-tab <?php if($id == $current_tab): ?>nav-tab-active<?php endif; ?>"><?php echo $tab['title']; ?></a>
        <?php endforeach; ?>
    </h3>

    <form action="options.php" method="post" enctype="multipart/form-data">  
        <?php
        // settings_fields($this->settings_optgroup);
        settings_fields( $current_tab );
        do_settings_sections($current_tab);
        ?>  
        <p class="submit">  
            <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />  
        </p>  
    </form> 
</div>