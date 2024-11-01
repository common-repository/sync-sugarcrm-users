<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2><?php echo SK_PLUGIN_NAME; ?> - Sync to SugarCRM</h2>
<p><?php _e('This will create Users/Contacts/Accounts in SugarCRM for the selected Wordpress users. <br />Only username, email, and if available first name and last name, of the Wordpress users will be used. <br />Default password of the user will be set as username. <br />Wordpress users whose username or email is present in SugarCRM will not be synced.', 'Sync_SugarCRM_Users') ?></p>
<table class="form-table">
<tbody>
<tr valign="top">
<th scope="row"><label for="wordpress_users">Wordpress Users</label></th>
<td><select name="wordpress_users[]" id="wordpress_users" class="regular-text" multiple="multiple" style="min-height:150px; min-width:200px"><?php
foreach ($blogusers as $bloguser) {
  echo '<option value="'.$bloguser->ID.'">'.$bloguser->user_login.'</option>';
}
?></select></td>
</tr>
<tr>
<th scope="row"><label><?php _e('SugarCRM Module', 'Sync_SugarCRM_Users') ?></label></th>
<td>
<input type="radio" name="sugarcrm_module" id="sugarcrm_module_users" value="Users" <?php if ($devOptions['crm_auto_create_module'] == 'Users' || ! in_array($devOptions['crm_auto_create_module'], array('Accounts', 'Contacts', ))) echo 'checked="checked"'; ?> />&nbsp;<label for="sugarcrm_module_users"><?php _e('Users', 'Sync_SugarCRM_Users') ?></label>&nbsp;&nbsp;&nbsp;
<input type="radio" name="sugarcrm_module" id="sugarcrm_module_accounts" value="Accounts" <?php if ($devOptions['crm_auto_create_module'] == 'Accounts') echo 'checked="checked"'; ?> />&nbsp;<label for="sugarcrm_module_accounts"><?php _e('Accounts', 'Sync_SugarCRM_Users') ?></label>&nbsp;&nbsp;&nbsp;
<input type="radio" name="sugarcrm_module" id="sugarcrm_module_contacts" value="Contacts" <?php if ($devOptions['crm_auto_create_module'] == 'Contacts') echo 'checked="checked"'; ?> />&nbsp;<label for="sugarcrm_module_contacts"><?php _e('Contacts', 'Sync_SugarCRM_Users') ?></label>&nbsp;
</td>
</tr>
</tbody>
</table>
<p class="submit">
<input type="submit" name="select_wordpress_users" class="button-primary" value="<?php _e('Sync', 'Sync_SugarCRM_Users') ?>" /></p>
</form>
</div>
