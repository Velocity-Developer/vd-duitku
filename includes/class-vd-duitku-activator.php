<?php

if (!defined('ABSPATH')) {
  exit;
}

class VD_Duitku_Activator
{
  public static function activate()
  {
    global $wpdb;

    if (!function_exists('dbDelta')) {
      require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }

    $charset_collate = $wpdb->get_charset_collate();
    $invoice_table = $wpdb->prefix . 'vd_duitku_invoice';
    $callback_table = $wpdb->prefix . 'vd_duitku_callback';

    $sql_invoice = "CREATE TABLE {$invoice_table} (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice varchar(255) NOT NULL,
            merchant_code varchar(114) NOT NULL,
            reference varchar(255) NOT NULL,
            payment_url text NOT NULL,
            status_code varchar(5) NOT NULL,
            status_message varchar(255) NOT NULL,
            created_at varchar(114) NOT NULL,
            update_at varchar(114) NOT NULL,
            amount varchar(114) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

    $sql_callback = "CREATE TABLE {$callback_table} (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            invoice varchar(255) NOT NULL,
            merchant_code varchar(114) NOT NULL,
            amount varchar(255) NOT NULL,
            payment_code varchar(255) NOT NULL,
            result_code varchar(255) NOT NULL,
            reference varchar(255) NOT NULL,
            created_at varchar(114) NOT NULL,
            update_at varchar(114) NOT NULL,
            detail text DEFAULT NULL,
            PRIMARY KEY  (id)
        ) {$charset_collate};";

    dbDelta($sql_invoice);
    dbDelta($sql_callback);

    $options = get_option('velocity_duitku_options', array());
    if (!is_array($options)) {
      $options = array();
    }

    $new_callback_url = get_site_url() . '/wp-json/vd-duitku/v1/callback';
    $legacy_callback_url = get_site_url() . '/wp-json/velocityaddons/v1/duitku_callback';

    if (empty($options['callback_url']) || $options['callback_url'] === $legacy_callback_url) {
      $options['callback_url'] = $new_callback_url;
    }

    update_option('velocity_duitku_options', wp_parse_args($options, array(
      'mode' => 'sandbox',
      'kode_merchant' => '',
      'merchant_key' => '',
      'callback_url' => $new_callback_url,
      'return_url' => '',
    )));

    update_option('vd_duitku_version', VD_DUITKU_VERSION);
  }
}
