<?php
/**
* Plugin Name: My CRM Connector By API
* Plugin URI: http://test.com
* Description: Create New Subscriber User By API
* Version: 1.0.0
* Author: @techiethemastermind
* Author URI: http://techiethemastermind.com
* License: GPL2
*/

if(!defined('ABSPATH')) {
    define('ABSPATH', 'wordpress/');
}

if(!defined('APIKEY')) {
    define('APIKEY', '6cb28f4d-ccce-45bd-95e3-2290845934f9');
}

require_once(ABSPATH . 'wp-config.php'); 
require_once(ABSPATH . 'wp-includes/wp-db.php'); 
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php'); 

if (isset($_POST['action']) && $_POST['action'] == 'create_user') {

    $apiKey = $_POST['apiKey'];

    if ($apiKey != APIKEY) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid API key'
        ]);
        exit;
    } else {
        $name  = $_POST['name'];
        $email = $_POST['email'];
        $crmId = $_POST['crmId'];
        $pwd   = 'test12345';

        // Create User
        $result = wp_create_user($name, $pwd, $email);

        if (is_wp_error($result)){
            $error = $result->get_error_message();
            echo json_encode([
                'success' => false,
                'message' => $error
            ]);
            exit;
        } else {
            $user = get_user_by('id', $result);
            update_user_meta( $result, 'crm_user_id', $crmId);
            echo json_encode([
                'success' => true,
                'message' => $user,
            ]);
            exit;
        }
    }
}

add_filter( 'manage_users_columns', 'crm_add_new_user_column' );
  
function crm_add_new_user_column( $columns ) {
    $columns['crm_action'] = 'CRM Action';
    return $columns;
}
  
add_filter( 'manage_users_custom_column', 'crm_add_new_user_column_content', 10, 3 );
  
function crm_add_new_user_column_content( $content, $column, $user_id ) {
    
    $user  = get_user_by('id', $user_id);
    $crmId = get_user_meta($user_id, 'crm_user_id', true);

    if ('crm_action' === $column && $crmId !== '') {
        $content = '<a href="http://localhost:8000/dashboard/customers/view/'. $crmId .'" target="_blank">View Profile</a>';
    }
    
    return $content;
}

