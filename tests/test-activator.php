<?php

class VD_Duitku_Activator_Test extends WP_UnitTestCase
{
    public function test_activate_creates_tables_and_defaults()
    {
        global $wpdb;

        VD_Duitku_Activator::activate();

        $invoice_table = $wpdb->prefix . 'vd_duitku_invoice';
        $callback_table = $wpdb->prefix . 'vd_duitku_callback';

        $this->assertSame($invoice_table, $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $invoice_table)));
        $this->assertSame($callback_table, $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $callback_table)));

        $options = get_option('velocity_duitku_options');
        $this->assertSame('sandbox', $options['mode']);
        $this->assertArrayHasKey('callback_url', $options);
    }
}
