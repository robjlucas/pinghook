<?php
/*
Plugin Name: pinghook
Description: Posts a JSON webhook to an external API URL simply to notify when a post or page is published, updated, or deleted.
Author: Rob Lucas
Author URI: https://newleftreview.org
Version: 1
License: GPL3
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// Register settings for the external API
function pinghook_register_settings() {
  add_option('pinghook_api_url', '');
  add_option('pinghook_api_secret', '');
  register_setting('pinghook_options_group', 'pinghook_api_url', 'pinghook_sanitize_url');
  register_setting('pinghook_options_group', 'pinghook_api_secret');
}
add_action('admin_init', 'pinghook_register_settings');

function pinghook_sanitize_url($url) {
  return esc_url_raw($url);
}

// Register admin menu for settings
function pinghook_create_admin_menu() {
  add_menu_page(
    'Pinghook Settings',        // Page title
    'pinghook',                 // Menu title
    'manage_options',           // Capability
    'pinghook-settings',        // Menu slug
    'pinghook_settings_page',   // Callback function
    'dashicons-admin-settings', // Icon
    100                         // Position
  );
}
add_action('admin_menu', 'pinghook_create_admin_menu');

function pinghook_settings_page() {
  $template_path = plugin_dir_path(__FILE__) . 'templates/admin-page.php';

  // Handle form submission
  if (isset($_POST['pinghook_save_settings'])) {
    update_option('pinghook_api_url', sanitize_text_field($_POST['pinghook_api_url']));
    update_option('pinghook_api_secret', sanitize_text_field($_POST['pinghook_api_secret']));

    $alert = '<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>';
  }
 
  if (file_exists($template_path)) {
    include $template_path;
  } else {
    echo '<div><p>Admin template not found.</p></div>';
  }
}

function pinghook_send_request($post_id, $action) {
  $post = get_post($post_id);

  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
    error_log("Skipping pinghook for post autosave or revision");
    return;
  }

  $unacceptable_statuses = array('pending', 'draft', 'auto-draft', 'private');
  $status = get_post_status($post_id);
  if (in_array($status, $unacceptable_statuses)){
    error_log("Skipping pinghook for post with status $status");
    return;
  }

  $url = get_option('pinghook_api_url');
  $secret = get_option('pinghook_api_secret');

  if (empty($url)) {
    error_log('pinghook request triggered with no URL');
    return;
  }

  $args = array(
    'body' => json_encode(array(
      'wordpress_id' => $post->ID,
      'title' => $post->post_title,
      'type' => "{$post->post_type}_$action",
      'secret' => $secret,
    )),
    'headers' => array(
      'Content-Type' => 'application/json',
    ),
  );

  error_log("Calling pinghook URL $url with the following arguments: $args");
  $response = wp_remote_post($url, $args);

  if (is_wp_error($response)) {
    error_log('Error sending POST request: ' . $response->get_error_message());
  } else {
    error_log('POST request sent successfully. Response: ' . wp_remote_retrieve_body($response));
  }
}

// Hook for when a post or page is published or updated
function pinghook_post_published_or_updated($post_id) {
  pinghook_send_request($post_id, 'update');
}
add_action('publish_post', 'pinghook_post_published_or_updated');
add_action('save_post', 'pinghook_post_published_or_updated');

// Hook for when a post or page is deleted
function pinghook_post_deleted($post_id) {
  pinghook_send_request($post_id, 'delete');
}
add_action('before_delete_post', 'pinghook_post_deleted');
