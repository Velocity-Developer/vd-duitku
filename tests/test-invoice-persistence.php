<?php

class VD_Duitku_Invoice_Persistence_Test extends WP_UnitTestCase
{
    public function set_up(): void
    {
        parent::set_up();
        VD_Duitku_Activator::activate();
    }

    public function test_save_invoice_inserts_and_updates_same_invoice()
    {
        $plugin = new VD_Duitku();

        $insert = $plugin->save_invoice('INV-1', array(
            'merchantCode' => 'D123',
            'reference' => 'REF-1',
            'paymentUrl' => 'https://example.com/pay-1',
            'statusCode' => '00',
            'statusMessage' => 'CREATED',
            'amount' => '10000',
        ));

        $first_row = $plugin->get_by_invoice('INV-1');

        $update = $plugin->save_invoice('INV-1', array(
            'merchantCode' => 'D123',
            'reference' => 'REF-2',
            'paymentUrl' => 'https://example.com/pay-2',
            'statusCode' => '00',
            'statusMessage' => 'PAID',
            'amount' => '12000',
        ));

        $updated_row = $plugin->get_by_invoice('INV-1');

        $this->assertSame($insert['id'], $update['id']);
        $this->assertSame($first_row->id, $updated_row->id);
        $this->assertSame('REF-2', $updated_row->reference);
        $this->assertSame('12000', $updated_row->amount);
        $this->assertSame('PAID', $updated_row->status_message);
    }
}
