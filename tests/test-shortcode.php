<?php

class VD_Duitku_Shortcode_Test extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        VD_Duitku_Activator::activate();
    }

    public function test_shortcode_returns_error_when_invoice_missing()
    {
        $plugin = new VD_Duitku();
        $output = $plugin->tombol_bayar(array('invoice' => 'INV404'));

        $this->assertStringContainsString('Data Duitku Invoice Tidak Ditemukan', $output);
    }

    public function test_shortcode_renders_button_when_invoice_exists()
    {
        $plugin = new VD_Duitku();
        $plugin->save_invoice('INV100', array(
            'merchantCode' => 'D123',
            'reference' => 'REF100',
            'paymentUrl' => 'https://example.com/pay',
            'statusCode' => '00',
            'statusMessage' => 'SUCCESS',
            'amount' => '50000',
        ));

        $output = $plugin->tombol_bayar(array('invoice' => 'INV100', 'class' => 'pay-now'));

        $this->assertStringContainsString('Bayar Sekarang 50000', $output);
        $this->assertStringContainsString('REF100', $output);
        $this->assertStringContainsString('pay-now', $output);
    }
}
