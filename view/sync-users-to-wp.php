<div class=wrap>
  <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <h2><?php echo SK_PLUGIN_NAME; ?> - Sync to WordPress</h2>
    <p><?php _e('This will fetch users from SugarCRM and add them to Wordpress. <br />Only username and email address will be fetched. <br />Default password of the user will be set as username. <br />Users with existing usernames or email will not be added.', 'Sync_SugarCRM_Users') ?></p>
    <p class="submit">
      <input type="submit" name="sync_sugarcrm_users" value="<?php _e('Sync', 'Sync_SugarCRM_Users') ?>" class="button-primary" />
    </p>
  </form>
</div>
