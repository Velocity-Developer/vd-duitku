<?php

class VD_Duitku_Rest_Callback_Test extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        VD_Duitku_Activator::activate();
        update_option('velocity_duitku_options', array(
            'mode' => 'sandbox',
            'kode_merchant' => 'D123',
            'merchant_key' => 'secret-key',
            'callback_url' => get_site_url() . '/wp-json/vd-duitku/v1/callback',
            'return_url' => 'https://example.com/return',
        ));
        do_action('rest_api_init');
    }

    public function test_rest_route_registered()
    {
        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/vd-duitku/v1/callback', $routes);
    }

    public function test_rest_callback_saves_valid_payload()
    {
        global $wpdb;

        $payload = array(
            'merchantCode' => 'D123',
            'amount' => '50000',
            'merchantOrderId' => 'INV-CB-1',
            'paymentCode' => 'VC',
            'resultCode' => '00',
            'reference' => 'REF-CB-1',
        );
        $payload['signature'] = md5($payload['merchantCode'] . $payload['amount'] . $payload['merchantOrderId'] . 'secret-key');

        $request = new WP_REST_Request('POST', '/vd-duitku/v1/callback');
        $request->set_body_params($payload);

        $response = rest_do_request($request);
        $data = $response->get_data();

        $this->assertSame('INV-CB-1', $data['merchantOrderId']);

        $table = $wpdb->prefix . 'vd_duitku_callback';
        $saved = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE invoice = %s", 'INV-CB-1'));
        $this->assertNotNull($saved);
        $this->assertSame('REF-CB-1', $saved->reference);
    }
}
