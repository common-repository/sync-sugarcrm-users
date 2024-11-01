<?php

if (!class_exists("Sync_SugarCRM_Users")) {

class Sync_SugarCRM_Users {
  const USERS_LIMIT = 500;
  var $config_parameters_name = "sync_sugarcrm_users_parameters";
  var $sync_sugarcrm_users_parameters_ini = array(
      'crm_user_name' => '', 
      'crm_user_hash' => '', 
      'crm_url'       => '', 
      'crm_auto_create_user'  => 0, 
      'crm_auto_create_module'  => 'Users', 
    );
  var $sync_sugarcrm_users_parameters;
  
  var $client  = null;
  var $session_id     = null;
  var $user_id     = '';
  
  var $wordpress = null;
  var $sugarcrm  = null;

  function __construct() {
    $this->sync_sugarcrm_users_parameters = $this->getConfigParameters();
  }
  
  function init() {
    update_option($this->config_parameters_name, $this->sync_sugarcrm_users_parameters);
    //add_option($this->config_parameters_name, $this->sync_sugarcrm_users_parameters);
  }
  function deactivate() {
    update_option($this->config_parameters_name, $this->sync_sugarcrm_users_parameters_ini);
  }

  //Returns an array of config parameters
  function getConfigParameters() {
    $sync_sugarcrm_users_parameters = array(
      'crm_user_name' => '', 
      'crm_user_hash' => '', 
      'crm_url'       => '', 
      'crm_auto_create_user' => 0, 
      'crm_auto_create_module'  => 'Users', 
    );
    $devOptions = get_option($this->config_parameters_name);
    if (!empty($devOptions)) {
      foreach ($devOptions as $key => $option)
        $sync_sugarcrm_users_parameters[$key] = $option;
    }				
    //update_option($this->config_parameters_name, $sync_sugarcrm_users_parameters);
    return $sync_sugarcrm_users_parameters;
  }
  
  function printViewSugarCRM() {
    $devOptions = $this->sync_sugarcrm_users_parameters;
    $messages  = array();
    $login_is_success  = false;
    
    // validation
    $ret_messages = $this->checkConfigFilled();
    if (!empty($ret_messages)) {
      $messages[] = $ret_messages;
    } else {
      $ret_messages = $this->soapInit();
      if (WP_DEBUG == true) {
        $messages       = array_merge($messages, $ret_messages);
      }
      $response = $this->client->__soapCall('seamless_login',array($this->session_id));
      if (empty($response)) {
        $messges[]  = 'Seamless login failed.';
      } else {
        $login_is_success = true;
      }
    }

    include_once SYNC_SUGARCRM_USERS_PATH.'/view/sugarcrm.php';
  }
  
  function syncPage() {
    include_once SYNC_SUGARCRM_USERS_PATH.'/view/sync.php';
    if (!class_exists("SoapClient")) {
      echo 'SOAP extension must be enabled in PHP for this plugin to work';
    }
  }

  //Prints out the config page
  function printConfigPage() {
    $devOptions = $this->sync_sugarcrm_users_parameters;
              
    if (isset($_POST['update_sync_sugarcrm_users_parameters'])) { 
      if (isset($_POST['crm_url'])) {
        if (substr($_POST['crm_url'], -1) != '/')
          $_POST['crm_url'] .= '/';
        $devOptions['crm_url'] = apply_filters('crm_url', $_POST['crm_url']);
      }	
      if (isset($_POST['crm_user_name'])) {
        $devOptions['crm_user_name'] = apply_filters('crm_user_name', $_POST['crm_user_name']);
      }	
      if (isset($_POST['crm_user_hash']) && !empty($_POST['crm_user_hash'])) {
        $devOptions['crm_user_hash'] = apply_filters('crm_user_hash', md5($_POST['crm_user_hash']));
      }	
      if (isset($_POST['crm_auto_create_user'])) {
        $devOptions['crm_auto_create_user'] = apply_filters('crm_auto_create_user', $_POST['crm_auto_create_user']);
      }	else {
        $devOptions['crm_auto_create_user'] = 0;
      }
      if (isset($_POST['crm_auto_create_module']) && !empty($_POST['crm_auto_create_module'])) {
        $devOptions['crm_auto_create_module'] = apply_filters('crm_auto_create_module', $_POST['crm_auto_create_module']);
      }	
      update_option($this->config_parameters_name, $devOptions);
						
?>
<div class="updated"><p><strong><?php _e("Config Changed.", "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
    }

    include_once SYNC_SUGARCRM_USERS_PATH.'/view/config.php';
	}//End function printConfigPage()

  //Prints out the Sync page
  function syncUsersToWPPage() {
    $devOptions = $this->sync_sugarcrm_users_parameters;
    $messages  = array();
    $sugarcrm_module  = 'Users';
    // validation
    $ret_messages = $this->checkConfigFilled();
    if (!empty($ret_messages)) {
      $messages[] = $ret_messages;
?>    
    <div class="updated"><p><strong><?php _e(implode('<br />', $messages), "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
      return;
    }    
    if (isset($_POST['sync_sugarcrm_users'])) { 
      $result = $this->sugarcrm->getUsers($sugarcrm_module);
      $users = $result['result'];
      $messages = $this->wordpress->push($users);
?>
<div class="updated"><p><strong><?php _e(implode('<br />', $messages), "Sync_SugarCRM_Users");?></strong></p></div>
<?php if (WP_DEBUG == true) { ?>
<div class="updated"><p><strong><?php _e(implode('<br />', $result['messages']), "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
      }
    } 

    include_once SYNC_SUGARCRM_USERS_PATH.'/view/sync-users-to-wp.php';
  }//End function syncUsersToWPPage()
  
  function printWordPressUsersSelectForm() {
    $messages = array();
    $devOptions = $this->sync_sugarcrm_users_parameters;
    // validation
    $ret_messages = $this->checkConfigFilled();
    if (!empty($ret_messages)) {
      $messages[] = $ret_messages;
?>
    <div class="updated"><p><strong><?php _e(implode('<br />', $messages), "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
      return;
    }
    // init soap client
    $ret_messages  = $this->soapInit();
    if (WP_DEBUG == true) {
      $messages       = array_merge($messages, $ret_messages);
    }
    // on form submit, after wordpress users selected
    if (isset($_POST['select_wordpress_users']) && !empty($_POST['wordpress_users'])) {
      $sugarcrm_module  = $_POST['sugarcrm_module'];
      $blogusers  = $this->wordpress->getUsers(array('include'=>$_POST['wordpress_users']), true);
      $ret_messages = $this->syncWordpressUsersToSugarCRM($blogusers, $sugarcrm_module);
      $messages     = array_merge($messages, $ret_messages);
?>    
    <div class="updated"><p><strong><?php _e(implode('<br />', $messages), "Sync_SugarCRM_Users");?></strong></p></div>
<?php	
    } else {
    // initial form submit
      $blogusers  = $this->wordpress->getUsers();
      include_once SYNC_SUGARCRM_USERS_PATH.'/view/select-wp-users.php';
    }
  }
  
  function syncWordpressUsersToSugarCRM($blogusers, $sugarcrm_module) {
    $messages = array();
    $user_count = 0;
    $ret_messages  = $this->soapInit();
    if (WP_DEBUG == true) {
      $messages       = array_merge($messages, $ret_messages);
    }
    try {
      $result  = $this->sugarcrm->getUsers($sugarcrm_module);
      $users   = $result['result'];
      $ret_messages = $result['messages'];
      $messages       = array_merge($messages, $ret_messages);
      
      foreach ($users as $key=>$user_obj) {
        $sugarcrm_users_usernames[]   = $user_obj['user_name'];
        $sugarcrm_users_useremails[]  = $user_obj['user_email'];
      }
      
      $unique_users = array();
      foreach ($blogusers as $bloguser) {
        if (in_array($bloguser->user_login, $sugarcrm_users_usernames)){
          $messages[]   = "{$bloguser->user_login} | {$bloguser->user_email} | User Exists (existing username)";
        } elseif (in_array($bloguser->user_email, $sugarcrm_users_useremails)) {
          $messages[]   = "{$bloguser->user_login} | {$bloguser->user_email} | User Exists (existing email address)";
        } else {
          $unique_users[] = $bloguser;
        }
      }
      
      if (sizeof($blogusers) > 0) {
        $ret_messages = $this->sugarcrm->push_users($sugarcrm_module, $unique_users);
        $messages       = array_merge($messages, $ret_messages);
      }
      
      $response = $this->client->__soapCall('logout',array('session'=>$this->session_id));
      
    } catch (Exception $ex) {
      $messages[] = $ex->getMessage();
    }
    
    
    return $messages;
  }
  
  function soapInit() {
    $messages = array();
    if ($this->client !== null) return $messages;
    $devOptions = $this->sync_sugarcrm_users_parameters;
    $user_name  = $devOptions['crm_user_name'];
    $user_password  = $devOptions['crm_user_hash'];
    $options = array(
      "uri" => $devOptions['crm_url'],
      "trace" => true,
    );
    $messages[] = 'URL: '.$devOptions['crm_url'];
    $messages[] = 'User :'.$devOptions['crm_user_name'];
    //$messages[] = 'hash: '.$devOptions['crm_user_hash'];
    try {
      //$client = new SoapClient($options['uri'].'service/v4_1/soap.php?wsdl', $options);
      //$client = new SoapClient($options['uri'].'service/v2/soap.php?wsdl', $options);
      $client = new SoapClient($options['uri'].'soap.php?wsdl', $options);
      $this->client = $client;

      $response = $client->__soapCall('login',array('user_auth'=>array('user_name'=>$user_name,'password'=>$user_password, 'version'=>'.01'), 'application_name'=>'Sync_SugarCRM_Users'));

      $this->session_id = $response->id;
      $messages[]   = 'Session ID: '.$this->session_id;
      
      $response = $client->__soapCall('get_user_id',array('session'=>$this->session_id));
      $this->user_id = $response;
      $messages[]   = 'User ID: '.$response;
      
    } catch (Exception $ex) {
      if (empty($client)) {
        if (WP_DEBUG == true) echo $ex->getMessage();
        else echo 'error initiating soap client';
        exit;
      }
      $messages[] = $ex->getMessage();
    }
    
    return $messages;
  }
  
  function checkConfigFilled () {
    if (!class_exists("SoapClient")) {
      return 'SOAP extension must be enabled in PHP for this plugin to work';
    }
  
    $devOptions = $this->sync_sugarcrm_users_parameters;
    if (empty($devOptions['crm_url'])) {
      return 'SugarCRM url not saved in config page.';
    }
    if (empty($devOptions['crm_user_name'])) {
      return 'SugarCRM username not saved in config page.';
    }
    if (empty($devOptions['crm_user_hash'])) {
      return 'SugarCRM password not saved in config page.';
    }
    
    return '';
  }

  function sync_sugarcrm_user_register($user_id) {
    $messages = array();
    $devOptions = $this->sync_sugarcrm_users_parameters;
    if ($devOptions['crm_auto_create_user'] != 1) {
      return $messages;
    }
    
    $user_info = get_userdata($user_id);
    
    $user_meta = get_user_meta($user_info->ID);
    if (isset($user_meta['first_name'], $user_meta['first_name'][0]))
      $user_info->first_name = $user_meta['first_name'][0];
    if (isset($user_meta['last_name'], $user_meta['last_name'][0]))
      $user_info->last_name = $user_meta['last_name'][0];
    if (isset($user_meta['description'], $user_meta['description'][0]))
      $user_info->description = $user_meta['description'][0];
    
    if (!$user_info) {
      if (WP_DEBUG == true) {
        $messages[] = 'No user with id: '.$user_id;
      }
      return $messages;
    }
    if (WP_DEBUG == true) {
      $messages[] = 'New user created: '.$user_info->user_login;
    }
    
    $ret_messages  = $this->soapInit();
    if (WP_DEBUG == true) {
      $messages       = array_merge($messages, $ret_messages);
    }
    
    $ret_messages = $this->sugarcrm->push_user($user_info, $devOptions['crm_auto_create_module']);
    $messages       = array_merge($messages, $ret_messages);
    
    return $messages;
  }
  
  function set_wordpress($wordpress) {
    $this->wordpress = $wordpress;
  }
  
  function set_sugarcrm($sugarcrm) {
    $this->sugarcrm = $sugarcrm;
  }
  
}

}
