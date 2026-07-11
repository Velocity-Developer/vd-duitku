<?php

class VD_Duitku_Settings_Test extends WP_UnitTestCase
{
    public function test_sanitize_options_normalizes_values()
    {
        $plugin = new VD_Duitku();

        $result = $plugin->sanitize_options(array(
            'mode' => 'production',
            'kode_merchant' => ' abc<em>123</em> ',
            'merchant_key' => ' key<script>1</script> ',
            'callback_url' => 'https://example.com/callback',
            'return_url' => 'https://example.com/return',
        ));

        $this->assertSame('production', $result['mode']);
        $this->assertSame('abc123', $result['kode_merchant']);
        $this->assertSame('key1', $result['merchant_key']);
        $this->assertSame('https://example.com/callback', $result['callback_url']);
        $this->assertSame('https://example.com/return', $result['return_url']);
    }
}
