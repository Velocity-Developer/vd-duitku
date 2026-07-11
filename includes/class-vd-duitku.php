<?php

if (!defined('ABSPATH')) {
    exit;
}

class VD_Duitku
{
    public $wpdb;
    public $tb_invoice;
    public $tb_callback;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tb_invoice = $wpdb->prefix . 'vd_duitku_invoice';
        $this->tb_callback = $wpdb->prefix . 'vd_duitku_callback';
    }

    public function run()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_assets'));
        add_action('rest_api_init', array($this, 'register_rest'));
        add_shortcode('tombol_bayar_duitku', array($this, 'tombol_bayar'));
    }

    public function admin_init()
    {
        register_setting('vd_duitku_group', 'velocity_duitku_options', array($this, 'sanitize_options'));
    }

    public function admin_menu()
    {
        add_menu_page(
            'VD Duitku',
            'VD Duitku',
            'manage_options',
            'vd-duitku',
            array($this, 'render_settings_page'),
            'dashicons-cart',
            58
        );
    }

    public function admin_enqueue_assets($hook_suffix)
    {
        if ($hook_suffix !== 'toplevel_page_vd-duitku') {
            return;
        }

        wp_enqueue_style(
            'vd-duitku-admin',
            VD_DUITKU_PLUGIN_URL . 'assets/admin.css',
            array(),
            VD_DUITKU_VERSION
        );
    }

    public function sanitize_options($input)
    {
        $input = is_array($input) ? $input : array();
        $defaults = self::options();

        return array(
            'mode' => (isset($input['mode']) && $input['mode'] === 'production') ? 'production' : 'sandbox',
            'kode_merchant' => isset($input['kode_merchant']) ? sanitize_text_field($input['kode_merchant']) : $defaults['kode_merchant'],
            'merchant_key' => isset($input['merchant_key']) ? sanitize_text_field($input['merchant_key']) : $defaults['merchant_key'],
            'callback_url' => isset($input['callback_url']) ? esc_url_raw($input['callback_url']) : $defaults['callback_url'],
            'return_url' => isset($input['return_url']) ? esc_url_raw($input['return_url']) : $defaults['return_url'],
        );
    }

    public static function payment_methods($kode = null)
    {
        $paymentMethods = [
            'VC' => ['jenis' => 'Credit Card', 'keterangan' => '(Visa / Master Card / JCB)'],
            'BC' => ['jenis' => 'Virtual Account', 'keterangan' => 'BCA Virtual Account'],
            'M2' => ['jenis' => 'Virtual Account', 'keterangan' => 'Mandiri Virtual Account'],
            'VA' => ['jenis' => 'Virtual Account', 'keterangan' => 'Maybank Virtual Account'],
            'I1' => ['jenis' => 'Virtual Account', 'keterangan' => 'BNI Virtual Account'],
            'B1' => ['jenis' => 'Virtual Account', 'keterangan' => 'CIMB Niaga Virtual Account'],
            'BT' => ['jenis' => 'Virtual Account', 'keterangan' => 'Permata Bank Virtual Account'],
            'A1' => ['jenis' => 'Virtual Account', 'keterangan' => 'ATM Bersama'],
            'AG' => ['jenis' => 'Virtual Account', 'keterangan' => 'Bank Artha Graha'],
            'NC' => ['jenis' => 'Virtual Account', 'keterangan' => 'Bank Neo Commerce/BNC'],
            'BR' => ['jenis' => 'Virtual Account', 'keterangan' => 'BRIVA'],
            'S1' => ['jenis' => 'Virtual Account', 'keterangan' => 'Bank Sahabat Sampoerna'],
            'DM' => ['jenis' => 'Virtual Account', 'keterangan' => 'Danamon Virtual Account'],
            'BV' => ['jenis' => 'Virtual Account', 'keterangan' => 'BSI Virtual Account'],
            'FT' => ['jenis' => 'Ritel', 'keterangan' => 'Pegadaian/ALFA/Pos'],
            'IR' => ['jenis' => 'Ritel', 'keterangan' => 'Indomaret'],
            'OV' => ['jenis' => 'E-Wallet', 'keterangan' => 'OVO (Support Void)'],
            'SA' => ['jenis' => 'E-Wallet', 'keterangan' => 'ShopeePay Apps (Support Void)'],
            'LF' => ['jenis' => 'E-Wallet', 'keterangan' => 'LinkAja Apps (Fixed Fee)'],
            'LA' => ['jenis' => 'E-Wallet', 'keterangan' => 'LinkAja Apps (Percentage Fee)'],
            'DA' => ['jenis' => 'E-Wallet', 'keterangan' => 'DANA'],
            'SL' => ['jenis' => 'E-Wallet', 'keterangan' => 'ShopeePay Account Link'],
            'OL' => ['jenis' => 'E-Wallet', 'keterangan' => 'OVO Account Link'],
            'JP' => ['jenis' => 'E-Wallet', 'keterangan' => 'Jenius Pay'],
            'SP' => ['jenis' => 'QRIS', 'keterangan' => 'ShopeePay'],
            'LQ' => ['jenis' => 'QRIS', 'keterangan' => 'LinkAja'],
            'NQ' => ['jenis' => 'QRIS', 'keterangan' => 'Nobu'],
            'DQ' => ['jenis' => 'QRIS', 'keterangan' => 'Dana'],
            'GQ' => ['jenis' => 'QRIS', 'keterangan' => 'Gudang Voucher'],
            'SQ' => ['jenis' => 'QRIS', 'keterangan' => 'Nusapay'],
            'DN' => ['jenis' => 'Credit', 'keterangan' => 'Indodana Paylater'],
            'AT' => ['jenis' => 'Credit', 'keterangan' => 'ATOME'],
        ];

        return $kode ? $paymentMethods[$kode] : $paymentMethods;
    }

    public static function options()
    {
        $options = get_option('velocity_duitku_options');
        $data = array(
            'mode' => 'sandbox',
            'kode_merchant' => '',
            'merchant_key' => '',
            'callback_url' => get_site_url() . '/wp-json/vd-duitku/v1/callback',
            'return_url' => '',
        );

        $data = wp_parse_args($options, $data);
        $data['url_createinvoice'] = $data['mode'] === 'sandbox'
            ? 'https://api-sandbox.duitku.com/api/merchant/createinvoice'
            : 'https://api-prod.duitku.com/api/merchant/createinvoice';

        return $data;
    }

    public function get_dashboard_summary()
    {
        $invoice_count = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->tb_invoice}");
        $callback_count = (int) $this->wpdb->get_var("SELECT COUNT(*) FROM {$this->tb_callback}");
        $paid_count = (int) $this->wpdb->get_var($this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tb_callback} WHERE result_code = %s",
            '00'
        ));
        $last_callback = $this->wpdb->get_row("SELECT * FROM {$this->tb_callback} ORDER BY id DESC LIMIT 1");

        return array(
            'invoice_count' => $invoice_count,
            'callback_count' => $callback_count,
            'paid_count' => $paid_count,
            'last_callback' => $last_callback,
        );
    }

    public function get_recent_invoices($limit = 10)
    {
        $limit = max(1, (int) $limit);
        return $this->wpdb->get_results("SELECT * FROM {$this->tb_invoice} ORDER BY id DESC LIMIT {$limit}");
    }

    public function get_recent_callbacks($limit = 10)
    {
        $limit = max(1, (int) $limit);
        return $this->wpdb->get_results("SELECT * FROM {$this->tb_callback} ORDER BY id DESC LIMIT {$limit}");
    }

    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $options = self::options();
        $summary = $this->get_dashboard_summary();
        $recent_invoices = $this->get_recent_invoices();
        $recent_callbacks = $this->get_recent_callbacks();
        $status_label = self::is_active() ? 'Terkoneksi' : 'Belum lengkap';
        $status_class = self::is_active() ? 'is-connected' : 'is-pending';
        ?>
        <div class="wrap vd-duitku-admin">
            <div class="vd-duitku-hero">
                <div>
                    <h1>VD Duitku Dashboard</h1>
                    <p>Monitor invoice, callback, dan konfigurasi merchant dari satu halaman admin.</p>
                </div>
                <div class="vd-duitku-status <?php echo esc_attr($status_class); ?>">
                    <span class="vd-duitku-status__label">Status Plugin</span>
                    <strong><?php echo esc_html($status_label); ?></strong>
                </div>
            </div>

            <div class="vd-duitku-cards">
                <div class="vd-card">
                    <span>Total Invoice</span>
                    <strong><?php echo esc_html(number_format_i18n($summary['invoice_count'])); ?></strong>
                </div>
                <div class="vd-card">
                    <span>Total Callback</span>
                    <strong><?php echo esc_html(number_format_i18n($summary['callback_count'])); ?></strong>
                </div>
                <div class="vd-card">
                    <span>Callback Sukses</span>
                    <strong><?php echo esc_html(number_format_i18n($summary['paid_count'])); ?></strong>
                </div>
                <div class="vd-card">
                    <span>Callback Terakhir</span>
                    <strong><?php echo esc_html($summary['last_callback'] ? $summary['last_callback']->invoice : '-'); ?></strong>
                </div>
            </div>

            <div class="vd-duitku-grid">
                <section class="vd-panel">
                    <div class="vd-panel__head">
                        <h2>Konfigurasi Merchant</h2>
                        <p>Atur koneksi Duitku sandbox atau production.</p>
                    </div>
                    <form method="post" action="options.php" class="vd-settings-form">
                        <?php settings_fields('vd_duitku_group'); ?>
                        <div class="vd-form-grid">
                            <label>
                                <span>Mode</span>
                                <select id="velocity_duitku_mode" name="velocity_duitku_options[mode]">
                                    <option value="sandbox" <?php selected($options['mode'], 'sandbox'); ?>>Sandbox</option>
                                    <option value="production" <?php selected($options['mode'], 'production'); ?>>Production</option>
                                </select>
                            </label>
                            <label>
                                <span>Kode Merchant</span>
                                <input id="velocity_duitku_kode_merchant" type="text" name="velocity_duitku_options[kode_merchant]" value="<?php echo esc_attr($options['kode_merchant']); ?>">
                            </label>
                            <label>
                                <span>API Key (Merchant Key)</span>
                                <input id="velocity_duitku_merchant_key" type="text" name="velocity_duitku_options[merchant_key]" value="<?php echo esc_attr($options['merchant_key']); ?>">
                            </label>
                            <label>
                                <span>Callback URL</span>
                                <input id="velocity_duitku_callback_url" type="url" name="velocity_duitku_options[callback_url]" value="<?php echo esc_attr($options['callback_url']); ?>">
                            </label>
                            <label class="vd-form-grid__full">
                                <span>Return URL</span>
                                <input id="velocity_duitku_return_url" type="url" name="velocity_duitku_options[return_url]" value="<?php echo esc_attr($options['return_url']); ?>">
                            </label>
                        </div>
                        <div class="vd-settings-actions">
                            <?php submit_button('Simpan Perubahan', 'primary', 'submit', false); ?>
                            <code><?php echo esc_html(get_site_url() . '/wp-json/vd-duitku/v1/callback'); ?></code>
                        </div>
                    </form>
                </section>

                <section class="vd-panel">
                    <div class="vd-panel__head">
                        <h2>Invoice Terbaru</h2>
                        <p>Data diambil dari tabel invoice plugin.</p>
                    </div>
                    <div class="vd-table-wrap">
                        <table class="widefat striped vd-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Reference</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_invoices)) : ?>
                                    <tr><td colspan="5">Belum ada invoice.</td></tr>
                                <?php else : ?>
                                    <?php foreach ($recent_invoices as $invoice) : ?>
                                        <tr>
                                            <td><?php echo esc_html($invoice->invoice); ?></td>
                                            <td><?php echo esc_html($invoice->reference); ?></td>
                                            <td><?php echo esc_html($invoice->amount); ?></td>
                                            <td><?php echo esc_html($invoice->status_message); ?></td>
                                            <td><?php echo esc_html($invoice->update_at); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="vd-panel vd-panel--full">
                    <div class="vd-panel__head">
                        <h2>Log Callback</h2>
                        <p>Riwayat callback terbaru dari Duitku.</p>
                    </div>
                    <div class="vd-table-wrap">
                        <table class="widefat striped vd-table">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Merchant</th>
                                    <th>Amount</th>
                                    <th>Payment Code</th>
                                    <th>Result</th>
                                    <th>Reference</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_callbacks)) : ?>
                                    <tr><td colspan="7">Belum ada callback.</td></tr>
                                <?php else : ?>
                                    <?php foreach ($recent_callbacks as $callback) : ?>
                                        <tr>
                                            <td><?php echo esc_html($callback->invoice); ?></td>
                                            <td><?php echo esc_html($callback->merchant_code); ?></td>
                                            <td><?php echo esc_html($callback->amount); ?></td>
                                            <td><?php echo esc_html($callback->payment_code); ?></td>
                                            <td><?php echo esc_html($callback->result_code); ?></td>
                                            <td><?php echo esc_html($callback->reference); ?></td>
                                            <td><?php echo esc_html($callback->update_at); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
        <?php
    }

    public static function is_active()
    {
        return (bool) (self::options()['kode_merchant'] && self::options()['merchant_key']);
    }

    public function timestamp()
    {
        date_default_timezone_set('Asia/Jakarta');
        return round(microtime(true) * 1000);
    }

    public function signature()
    {
        return hash('sha256', self::options()['kode_merchant'] . $this->timestamp() . self::options()['merchant_key']);
    }

    public function createInvoice($params = null)
    {
        if (empty($params)) {
            return new WP_Error('invalid_params', 'Parameter tidak valid');
        }

        if (!isset($params['callbackUrl']) || empty($params['callbackUrl'])) {
            $params['callbackUrl'] = self::options()['callback_url'];
        }
        if (!isset($params['returnUrl']) || empty($params['returnUrl'])) {
            $params['returnUrl'] = self::options()['return_url'];
        }

        $required_params = array(
            'paymentAmount',
            'merchantOrderId',
            'productDetails',
            'additionalParam',
            'merchantUserInfo',
            'customerVaName',
            'email',
            'phoneNumber',
            'itemDetails',
            'customerDetail',
            'callbackUrl',
            'returnUrl',
        );

        $missing_params = array();
        foreach ($required_params as $param) {
            if (!isset($params[$param])) {
                $missing_params[] = $param;
            }
        }

        if (!empty($missing_params)) {
            return new WP_Error('missing_params', 'Parameter yang belum diisi: ' . implode(', ', $missing_params));
        }

        $params_string = wp_json_encode($params);
        $headers = array(
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($params_string),
            'x-duitku-signature' => $this->signature(),
            'x-duitku-timestamp' => $this->timestamp(),
            'x-duitku-merchantcode' => self::options()['kode_merchant'],
        );

        $response = wp_remote_post(self::options()['url_createinvoice'], array(
            'method' => 'POST',
            'body' => $params_string,
            'headers' => $headers,
            'timeout' => 15,
            'sslverify' => true,
        ));

        if (is_wp_error($response)) {
            return new WP_Error('request_failed', 'Request API gagal: ' . $response->get_error_message());
        }

        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code == 200) {
            $result = json_decode(wp_remote_retrieve_body($response), true);
            $this->save_invoice($params['merchantOrderId'], $result);
            return $result;
        }

        return new WP_Error('request_error', 'Request gagal dengan status code: ' . $http_code . ' - ' . wp_remote_retrieve_body($response));
    }

    public function save_invoice($invoice, $result_request, $amount = null)
    {
        if (is_wp_error($result_request)) {
            return false;
        }

        $cek_invoice = $this->get_by_invoice($invoice);
        $amount = $result_request['amount'] ?? $amount;

        if ($cek_invoice) {
            $this->wpdb->update($this->tb_invoice, array(
                'invoice' => $invoice,
                'merchant_code' => $result_request['merchantCode'],
                'reference' => $result_request['reference'],
                'payment_url' => $result_request['paymentUrl'],
                'status_code' => $result_request['statusCode'],
                'status_message' => $result_request['statusMessage'],
                'update_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
                'amount' => $amount,
            ), array('id' => $cek_invoice->id));

            $id = $cek_invoice->id;
        } else {
            $this->wpdb->insert($this->tb_invoice, array(
                'invoice' => $invoice,
                'merchant_code' => $result_request['merchantCode'],
                'reference' => $result_request['reference'],
                'payment_url' => $result_request['paymentUrl'],
                'status_code' => $result_request['statusCode'],
                'status_message' => $result_request['statusMessage'],
                'created_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
                'update_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
                'amount' => $amount,
            ));

            $id = $this->wpdb->insert_id;
        }

        return array(
            'id' => $id,
            'invoice' => $invoice,
            'reference' => $result_request['reference'],
        );
    }

    public function get_by_invoice($invoice)
    {
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->tb_invoice} WHERE invoice = %s", $invoice));
    }

    public function tombol_bayar($atts)
    {
        ob_start();

        $atribut = shortcode_atts(array(
            'invoice' => '',
            'class' => 'btn btn-primary',
        ), $atts);

        $cek_invoice = $this->get_by_invoice($atribut['invoice']);
        if (!$cek_invoice) {
            echo '<div class="alert alert-danger">Data Duitku Invoice Tidak Ditemukan, silahkan request invoice terlebih dahulu</div>';
            return ob_get_clean();
        }

        $invoice = $cek_invoice->invoice;
        $reference = $cek_invoice->reference;
        $amount = $cek_invoice->amount;
        $mode = self::options()['mode'];
        $js = $mode == 'sandbox' ? 'https://app-sandbox.duitku.com/lib/js/duitku.js' : 'https://app-prod.duitku.com/lib/js/duitku.js';
        ?>
        <button id="bayarduitku<?php echo esc_attr($invoice); ?>" class="<?php echo esc_attr($atribut['class']); ?>">
            Bayar Sekarang <?php echo esc_html($amount); ?>
        </button>
        <script src="<?php echo esc_url($js); ?>"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const button = document.getElementById('bayarduitku<?php echo esc_js($invoice); ?>');
                if (!button) {
                    return;
                }

                button.addEventListener('click', function() {
                    checkout.process('<?php echo esc_js($reference); ?>', {
                        defaultLanguage: 'id',
                        successEvent: function() {
                            button.style.display = 'none';
                        },
                        pendingEvent: function() {},
                        errorEvent: function() {},
                        closeEvent: function() {}
                    });
                });
            });
        </script>
        <?php

        return ob_get_clean();
    }

    public function save_callback($post_callback)
    {
        $invoice = $post_callback['merchantOrderId'];
        $available = $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM {$this->tb_callback} WHERE invoice = %s", $invoice));

        if ($available) {
            $this->wpdb->update($this->tb_callback, array(
                'invoice' => $post_callback['merchantOrderId'],
                'merchant_code' => $post_callback['merchantCode'],
                'amount' => $post_callback['amount'],
                'payment_code' => $post_callback['paymentCode'],
                'result_code' => $post_callback['resultCode'],
                'reference' => $post_callback['reference'],
                'detail' => wp_json_encode($post_callback),
                'update_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
            ), array('id' => $available->id));

            $id = $available->id;
        } else {
            $this->wpdb->insert($this->tb_callback, array(
                'invoice' => $post_callback['merchantOrderId'],
                'merchant_code' => $post_callback['merchantCode'],
                'amount' => $post_callback['amount'],
                'payment_code' => $post_callback['paymentCode'],
                'result_code' => $post_callback['resultCode'],
                'reference' => $post_callback['reference'],
                'detail' => wp_json_encode($post_callback),
                'created_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
                'update_at' => date('Y-m-d H:i:s', current_time('timestamp', 1)),
            ));

            $id = $this->wpdb->insert_id;
        }

        return array(
            'id' => $id,
            'invoice' => $invoice,
            'reference' => isset($post_callback['reference']) ? $post_callback['reference'] : '',
        );
    }

    public function callback($payload = null)
    {
        if ($payload === null) {
            $payload = $_POST;
        }

        if (!is_array($payload) || (!isset($payload['merchantCode']) && !isset($payload['signature']))) {
            return false;
        }

        $apiKey = self::options()['merchant_key'];
        $merchantCode = isset($payload['merchantCode']) ? $payload['merchantCode'] : null;
        $amount = isset($payload['amount']) ? $payload['amount'] : null;
        $merchantOrderId = isset($payload['merchantOrderId']) ? $payload['merchantOrderId'] : null;
        $signature = isset($payload['signature']) ? $payload['signature'] : null;

        if (!empty($merchantCode) && !empty($amount) && !empty($merchantOrderId) && !empty($signature)) {
            $calcSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
            if ($signature == $calcSignature) {
                $this->save_callback($payload);
                do_action('velocity_duitku_callback', $payload);
                return $payload;
            }

            return new WP_Error('missing_params', 'Bad Signature');
        }

        return new WP_Error('missing_params', 'Parameter tidak lengkap');
    }

    public function register_rest()
    {
        register_rest_route('vd-duitku/v1', '/callback', array(
            'methods' => 'POST',
            'callback' => array($this, 'rest_callback'),
            'permission_callback' => '__return_true',
        ));
    }

    public function rest_callback(WP_REST_Request $request)
    {
        if ($request->get_method() !== 'POST') {
            return new WP_Error('method_not_allowed', 'Metode tidak diizinkan', array('status' => 405));
        }

        $payload = $request->get_json_params();
        if (empty($payload)) {
            $payload = $request->get_body_params();
        }
        if (empty($payload)) {
            $payload = $request->get_params();
        }

        return $this->callback($payload);
    }
}
