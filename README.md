# VD Duitku

Plugin WordPress untuk integrasi payment gateway Duitku dengan callback REST, penyimpanan invoice, shortcode tombol bayar, dan dashboard admin.

## Fitur
- Mode sandbox dan production
- Simpan konfigurasi merchant dari admin WordPress
- Endpoint callback REST di `/wp-json/vd-duitku/v1/callback`
- Simpan invoice ke tabel custom plugin
- Shortcode tombol bayar Duitku
- Dashboard admin untuk statistik invoice dan callback

## Struktur data
Plugin membuat dua tabel saat aktivasi:
- `wp_vd_duitku_invoice`
- `wp_vd_duitku_callback`

Nama tabel mengikuti prefix WordPress aktif.

## Instalasi
1. Salin plugin ke folder `wp-content/plugins/vd-duitku`.
2. Aktifkan plugin dari admin WordPress.
3. Buka menu **VD Duitku**.
4. Isi `Kode Merchant`, `API Key`, `Callback URL`, dan `Return URL`.
5. Simpan perubahan.

## Konfigurasi
- `Mode`: `sandbox` atau `production`
- `Callback URL` default: `/wp-json/vd-duitku/v1/callback`
- `Return URL`: URL kembali setelah proses pembayaran selesai

## Shortcode
Gunakan shortcode berikut:

```text
[tombol_bayar_duitku invoice="INV-001" class="btn btn-primary"]
```

Shortcode akan menampilkan tombol bayar jika invoice ada di tabel plugin.

## Dashboard admin
Halaman admin menampilkan:
- status koneksi plugin
- total invoice
- total callback
- jumlah callback sukses
- daftar invoice terbaru
- log callback terbaru

## Menjalankan test otomatis
Plugin menyiapkan skeleton test WordPress/PHPUnit.

### Kebutuhan
- PHP
- Composer
- WordPress test suite (`wordpress-tests-lib`)
- environment variable `WP_TESTS_DIR`

### Langkah umum
1. Install dependency:

```bash
composer install
```

2. Set `WP_TESTS_DIR` ke folder `wordpress-tests-lib`.
3. Jalankan test:

```bash
composer test
```

Jika test suite WordPress belum ada, `tests/bootstrap.php` akan berhenti dan memberi pesan error.

## Catatan
- Callback memvalidasi signature MD5 dari `merchantCode + amount + merchantOrderId + merchant_key`.
- File uninstall saat ini belum menghapus tabel atau option plugin.
