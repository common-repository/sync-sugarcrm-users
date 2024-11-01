<div class=wrap>
<h2><?php echo SK_PLUGIN_NAME; ?> - SugarCRM</h2>
<?php    
    if (sizeof($messages) > 0) {
?>    
    <div class="updated"><p><strong><?php _e(implode('<br />', $messages), "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
    }
    
    if ($login_is_success) {
?>
<iframe width="100%" height="100%" style="min-height: 1200px;" src="<?php echo $devOptions['crm_url']; ?>index.php?module=Home&action=index&MSID=<?php echo $this->session_id; ?>" ></iframe>
<?php
    }
?>
</div>
