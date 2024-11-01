<?php

if (!class_exists("Sync_SugarCRM")) {

class Sync_SugarCRM {
  
  var $plugin = null;
  var $messages = array();

  function __construct($plugin) {
    $this->plugin = $plugin;
  }
  
  function getUsers($sugarcrm_module) {
    $users    = array();
    $result   = array('result'=>array(), 'messages'=>array(),);

    // validation
    $ret_messages = $this->plugin->checkConfigFilled();
    if (!empty($ret_messages)) {
      $result['messages'][] = $ret_messages;
      return $result;
    }
    // init soap client
    $ret_messages = $this->plugin->soapInit();
    if (WP_DEBUG == true) {
      $this->messages       = array_merge($this->messages, $ret_messages);
    }

    $offset = 0;
    $limit  = Sync_SugarCRM_Users::USERS_LIMIT;
    
    try {
      
      $response = $this->plugin->client->__soapCall('get_entries_count',array('session'=>$this->plugin->session_id,'module_name'=>$sugarcrm_module,'query'=>'', 'deleted'=>0));
      if (isset($response->result_count) && $response->error->number == 0) {
        if (WP_DEBUG == true) {
          $this->messages[]   = "SugarCRM {$sugarcrm_module} count: {$response->result_count}";
        }
        $limit = $response->result_count;
      } else {
        if (isset($response->error, $response->error->description)) {
          if (WP_DEBUG == true) {
            $this->messages[]   = "SugarCRM {$sugarcrm_module} count: {$response->error->description}";
          }
        } else {
          if (WP_DEBUG == true) {
            $this->messages[]   = "SugarCRM {$sugarcrm_module} count: Error: {$response->error->number}";
          }
        }
        $this->messages[]   = "No {$sugarcrm_module}";
      }
      
      switch ($sugarcrm_module) {
        case 'Users':
        $order_field    = 'users.user_name';
        $select_fields  = array('id', 'user_name', 'email1');
        break;
        case 'Accounts':
        $order_field = 'accounts.name';
        $select_fields  = array('id', 'name', 'email1');
        break;
        case 'Contacts':
        $order_field = 'contacts.last_name';
        $select_fields  = array('id', 'first_name', 'last_name', 'email1');
        break;
      }
      
      $response = $this->plugin->client->__soapCall('get_entry_list',array('session'=>$this->plugin->session_id,'module_name'=>$sugarcrm_module,'query'=>'','order_by'=>"{$order_field} asc",'offset'=>$offset, 'select_fields'=>$select_fields, 'max_results'=>$limit));

      if (WP_DEBUG == true) {
        $this->messages[]   = "SugarCRM Fetched {$sugarcrm_module}: ".sizeof($response->entry_list);
      }
      
      foreach ($response->entry_list as $user_obj) {
        $user_detail_str  = '';
        $user_name    = '';
        $user_email   = '';
        $name         = '';
        $first_name   = '';
        $last_name    = '';
        
        foreach ($user_obj->name_value_list as $key=>$user_field) {
          if (in_array($user_field->name, array('user_name', 'email1', 'name', 'first_name', 'last_name'))) {
            //$user_detail_str  .= $user_field->name.': '.$user_field->value.'&nbsp; |  &nbsp;';
            $user_detail_str  .= $user_field->value.'&nbsp; |  &nbsp;';
            
            switch ($user_field->name) {
              case 'user_name':
                $user_name  = $user_field->value;
                break;
              case 'email1':
                $user_email  = $user_field->value;
                break;
              case 'name':
                $name  = $user_field->value;
                break;
              case 'first_name':
                $first_name  = $user_field->value;
                break;
              case 'last_name':
                $last_name  = $user_field->value;
                break;
            }
          }
        }

        switch ($sugarcrm_module) {
          case 'Users':
          $user_name = $user_name;
          break;
          case 'Accounts':
          $user_name = str_replace(' ', '', $name);
          break;
          case 'Contacts':
          $user_name = str_replace(' ', '', $first_name).str_replace(' ', '', $last_name);
          break;
        }
        
        $users[] = array(
          'user_name'   => $user_name, 
          'user_email'  => $user_email, 
          'name'        => $name, 
          'first_name'  => $first_name, 
          'last_name'   => $last_name, 
        );
      }
      
      $result['result']   = $users;
      $result['messages'] = $this->messages;
      
      $response = $this->plugin->client->__soapCall('logout',array('session'=>$session));
      
    } catch (Exception $ex) {
      if (WP_DEBUG == true) {
        $this->messages[] = $ex->getMessage();
      }
      $result['messages'] = $this->messages;
    }
    
    return $result;
  }
  
  function push_users($sugarcrm_module, $blogusers) {
    $data = array();
    foreach ($blogusers as $bloguser) {
      $data[] = $this->get_soap_name_value_list($bloguser, $sugarcrm_module);
    }
    
    try {
      $response = $this->plugin->client->__soapCall('set_entries',array('session'=>$this->plugin->session_id, 'module_name'=>$sugarcrm_module, $data));
    } catch (Exception $ex) {
      if (WP_DEBUG == true) {
        $this->messages[] = $ex->getMessage();
      }
    }
    if ( isset($response->error, $response->error->number) && $response->error->number == 0 ) {
      if (WP_DEBUG == true) {
        $this->messages[]   = 'SugarCRM push_users: success';
      }
    } elseif (isset($response->error, $response->error->description)) {
      if (WP_DEBUG == true) {
        $this->messages[]   = 'SugarCRM push_users: '.$response->error->description;
      }
    } else {
      if (WP_DEBUG == true) {
        $this->messages[]   = 'SugarCRM push_users: Error';
      }
    }
    
    return $this->messages;
  }
  
  function push_user($user_info, $sugarcrm_module) {
    try { 
      // Get count of users from SugarCRM with same username
      $response = $this->plugin->client->__soapCall('get_entries_count',array('session'=>$this->plugin->session_id,'module_name'=>$sugarcrm_module,'query'=>" users.user_name = '{$user_info->user_login}' ", 'deleted'=>0));

      // SOAP request checking if user exists in SugarCRM failed
      if ($response->error->number) {
        if (WP_DEBUG == true) {
          $this->messages[] = "SugarCRM searched user count: {$response->error->description}";
        }
        return $this->messages;
      } elseif ($response->result_count > 0) {
        // User already exists in SugarCRM
        if (WP_DEBUG == true) {
          $this->messages[] = "{$sugarcrm_module} already exists in SugarCRM: {$user_info->user_login}";
        }
        return $this->messages;
      }
      if (WP_DEBUG == true) {
        $this->messages[] = 'SugarCRM searched user count: '.sizeof($response->result_count);
      }
      
      $user_arr  = $this->get_soap_name_value_list($user_info, $sugarcrm_module);
      
      // Add user to SugarCRM
      $response = $this->plugin->client->__soapCall('set_entry',array('session'=>$this->plugin->session_id,'module_name'=>$sugarcrm_module,'name_value_list'=>$user_arr));
      
      if (WP_DEBUG == true) {
        if ($response->error->number) {
          $this->messages[] =  "SugarCRM user created: error: {$response->error->description}";
        } else {
          $this->messages[] =  "SugarCRM user created: id: {$response->id}";
        }
      }
    } catch (Exception $ex) {
      if (WP_DEBUG == true) {
        $this->messages[] = $ex->getMessage();
      }
    }

    return $this->messages;
  }
  
  function get_soap_name_value_list($user_info, $sugarcrm_module) {
    $user_arr = array();
    
    switch ($sugarcrm_module) {
      case 'Users':
        $last_name   = empty($user_info->last_name) ? $user_info->user_login : $user_info->last_name;
        $user_arr = array(
          array('name' => 'user_name',          'value' => $user_info->user_login ), 
          array('name' => 'user_hash',          'value' => md5($user_info->user_login) ), 
          array('name' => 'first_name',         'value' => $user_info->first_name ), 
          array('name' => 'last_name',          'value' => $last_name ), 
          array('name' => 'email1',             'value' => $user_info->user_email ), 
          array('name' => 'assigned_user_id',   'value' => $this->plugin->user_id ), 
          array('name' => 'status',             'value' => 'Active' ), 
          array('name' => 'employee_status',    'value' => 'Active' ), 
          array('name' => 'description',        'value' => $user_info->description ), 
        );
        $this->messages[] = "{$user_info->user_login} | {$user_info->user_email} | {$sugarcrm_module} Created";
        break;
        
      case 'Accounts':
        $user_arr = array(
          array('name' => 'name',               'value' => $user_info->user_login ), 
          array('name' => 'email1',             'value' => $user_info->user_email ), 
          array('name' => 'assigned_user_id',   'value' => $this->plugin->user_id ), 
          array('name' => 'description',        'value' => $user_info->description ), 
        );
        $this->messages[] = "{$user_info->user_login} | {$user_info->user_email} | {$sugarcrm_module} Created";
        break;
        
      case 'Contacts':
        $last_name   = empty($user_info->last_name) ? $user_info->user_login : $user_info->last_name;
        $user_arr = array(
          array('name' => 'first_name',         'value' => $user_info->first_name ), 
          array('name' => 'last_name',          'value' => $last_name ), 
          array('name' => 'title',              'value' => $user_info->user_login ), 
          array('name' => 'email1',             'value' => $user_info->user_email ), 
          array('name' => 'assigned_user_id',   'value' => $this->plugin->user_id ), 
          array('name' => 'description',        'value' => $user_info->description ), 
        );
        $name = (empty($user_info->first_name)) ? $last_name : "{$user_info->first_name} {$last_name}";
        $this->messages[] = "{$name} | {$user_info->user_email} | {$sugarcrm_module} Created";
        break;            
    }
    
    return $user_arr;
  }
  
}

}
