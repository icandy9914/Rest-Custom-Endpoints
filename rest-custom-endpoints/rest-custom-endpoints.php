<?php
/**
 * @package icandyrestcustomendpoints
 */

/*
/*
 * Plugin Name: Rest Custom Endpoints
 * Plugin URI: https://icandevelop.com/plugins/phonepe-by-icandevelop
 * Description: Custom endpoints.
 * Author: I Can Develop
 * Author URI: https://icandevelop.com
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: icandy-rest-custom-endpoints
 */


// If the file is called directly! abort !!
defined('ABSPATH') or die ("Hey Dud! You are not in the right place.");

add_action('rest_api_init','ganesh_add_callback_url_endpoint');
function ganesh_add_callback_url_endpoint()
{
	register_rest_route( 
		'icandy/v1', //Namespace
		'receive_callback', // Endpoint
		array(
		'methods' => 'POST',
		'callback' => 'ganesh_receive_callback'
	  ) );
}

function ganesh_receive_callback($request_data)
{
	$data = array();
	$parameters = $request_data->get_params();
	$name = $parameters['name'];
	$password = $parameters['password'];
	$title = $parameters['title'];
	$body = $parameters['body'];
  
  if( isset($name) && isset($password) )
  {
    $userdata = get_user_by( 'login', $name );
    if($userdata){
      $wp_check_password = wp_check_password($password, $userdata->user_pass, $userdata->ID);

      if($wp_check_password){
        $data['status'] = 'ok';
        $data['received_data'] = array(
          'name' => $name,
          'password' => $password,
          'userdata' => $userdata
        );

		/* insert post into post type */

		// Create an array of post data for the new post
		$new_post = array(
			'post_title'   => $title, // Valid post name
			'post_content' => $body, // Unslashed post data - Set the content of the new post
			'post_status'  => 'publish', // Unslashed post data - Set the status of the new post to 'publish'
			'post_author'  => 1, // Replace with the desired author's user ID
			'post_type'  => 'apidata'
		);

		// Insert post into the database
		$post_id = wp_insert_post($new_post, true); // Use $wp_error set to true for error handling

		// Check if there was an error during post insertion
		if (is_wp_error($post_id)) {
			$data['message'] = $post_id->get_error_message();
		} else {
			$data['message'] = 'Post inserted successfully';
			$data['post_data'] = $post_id;
		}
		/* insert post into post type */

      } else {
        $data['status'] = 'ok';
        $data['message'] = 'You are not authenticated User';
      }
      
    } else {
      $data['status'] = 'ok';
      $data['message'] = 'The current username does not exists';
    }
  } else {
    $data['status'] = 'failed';
    $data['message'] = 'You have an error';
  }
	

	return $data;
}

function icandy_set_up_post_type(){
	$args=array(
		'public' => true,
		'publicly_queryable' => false,
		'label' => __( 'API Data', 'prefix-plugin-name'),
		'menu_icon' => 'dashicons-analytics',
		'supports'	=> array('title','editor')
	);
	register_post_type( 'apidata', $args );
}

add_action('init','icandy_set_up_post_type');