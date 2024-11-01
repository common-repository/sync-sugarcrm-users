<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2><?php echo SK_PLUGIN_NAME; ?> - Config</h2>
<table class="form-table">
<tbody>
<tr valign="top">
<th scope="row"><label for="crm_url">CRM URL</label></th>
<td><input type="text" name="crm_url" id="crm_url" value="<?php _e(apply_filters('format_to_edit',$devOptions['crm_url']), 'Sync_SugarCRM_Users') ?>" class="regular-text" /> &nbsp;<span class="description"><?php _e('eg: http://example.com/crm/.', 'Sync_SugarCRM_Users') ?></span></td>
</th>
</tr>
<tr valign="top">
<th scope="row"><label for="crm_user_name">Username</label></th>
<td><input type="text" name="crm_user_name" id="crm_user_name" value="<?php _e(apply_filters('format_to_edit',$devOptions['crm_user_name']), 'Sync_SugarCRM_Users') ?>" class="regular-text" /></td>
</tr>
<tr>
<th scope="row"><label for="crm_user_hash">Password</label></th>
<td><input type="password" name="crm_user_hash" id="crm_user_hash" value="" class="regular-text" /></td>
</tr>
<tr>
<th scope="row"><label for="crm_auto_create_user">Auto sync user to SugarCRM</label></th>
<td><input type="checkbox" name="crm_auto_create_user" id="crm_auto_create_user" value="1" <?php if ($devOptions['crm_auto_create_user']) echo 'checked="checked"'; ?> /> &nbsp;<span class="description"><?php _e('If checked, when a user is created in Wordpress, the user will automatically be synced to SugarCRM', 'Sync_SugarCRM_Users') ?></span></td>
</tr>
<tr>
<th scope="row"></th>
<td>
<input type="radio" name="crm_auto_create_module" id="crm_auto_create_module_users" value="Users" <?php if ($devOptions['crm_auto_create_module'] == 'Users' || ! in_array($devOptions['crm_auto_create_module'], array('Accounts', 'Contacts', ))) echo 'checked="checked"'; ?> />&nbsp;<label for="crm_auto_create_module_users"><?php _e('Users', 'Sync_SugarCRM_Users') ?></label>&nbsp;&nbsp;&nbsp;
<input type="radio" name="crm_auto_create_module" id="crm_auto_create_module_accounts" value="Accounts" <?php if ($devOptions['crm_auto_create_module'] == 'Accounts') echo 'checked="checked"'; ?> />&nbsp;<label for="crm_auto_create_module_accounts"><?php _e('Accounts', 'Sync_SugarCRM_Users') ?></label>&nbsp;&nbsp;&nbsp;
<input type="radio" name="crm_auto_create_module" id="crm_auto_create_module_contacts" value="Contacts" <?php if ($devOptions['crm_auto_create_module'] == 'Contacts') echo 'checked="checked"'; ?> />&nbsp;<label for="crm_auto_create_module_contacts"><?php _e('Contacts', 'Sync_SugarCRM_Users') ?></label>&nbsp;
</td>
</tr>

</tbody>
</table>
<p class="submit">
<input type="submit" name="update_sync_sugarcrm_users_parameters" class="button-primary" value="<?php _e('Save Changes', 'Sync_SugarCRM_Users') ?>" /></p>
</form>
 </div>
