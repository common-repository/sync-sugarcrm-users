<?php

if (!class_exists("Sync_WordPress")) {

class Sync_WordPress {
  
  var $plugin = null;

  function __construct($plugin) {
    $this->plugin = $plugin;
  }

  function getUsers($param=array(), $additional=false) {
    $args = array('blog_id'=>$GLOBALS['blog_id'], 'orderby'=>'login' );
    $args = $args + $param;

    $blogusers = get_users($args);
    
    if ($additional) {
      foreach ($blogusers as &$bloguser) {
        $user_meta = get_user_meta($bloguser->ID);
        if (isset($user_meta['first_name'], $user_meta['first_name'][0]))
          $bloguser->first_name = $user_meta['first_name'][0];
        if (isset($user_meta['last_name'], $user_meta['last_name'][0]))
          $bloguser->last_name = $user_meta['last_name'][0];
        if (isset($user_meta['description'], $user_meta['description'][0]))
          $bloguser->description = $user_meta['description'][0];
      }
    }

    return $blogusers;
  }
  
  function push($users) {
    $messages  = array();
    foreach ($users as $key=>$user_obj) {
      $user_name = $user_obj['user_name'];
      $user_email = $user_obj['user_email'];
      $user_detail_str  = '';
      $user_detail_str  .= "{$user_name}&nbsp; |  &nbsp;";
      $user_detail_str  .= "{$user_email}&nbsp; |  &nbsp;";
      
      $user_id = username_exists( $user_name );
      if ( !$user_id and email_exists($user_email) == false ) {
        //$user_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_password = $user_name;
        $user_id = wp_create_user( $user_name, $user_password, $user_email );
        if (!is_object($user_id)) {
          $user_detail_str .= 'User Created';
        } else {
          if ( is_wp_error($user_id) )
            $messages[] = $user_id->get_error_message();
        }
      } else {
        $user_detail_str .= 'User Exists';
      }
      if ($user_detail_str != '')
        $messages[] = $user_detail_str;

      //$messages[] = '<br />';
      if (sizeof($messages) > (Sync_SugarCRM_Users::USERS_LIMIT+1)) {
        $messages[Sync_SugarCRM_Users::USERS_LIMIT] = '...';
      }
      $messages = array_slice($messages, 0, (Sync_SugarCRM_Users::USERS_LIMIT+1));
    }
    
    return $messages;
  }

}

}
