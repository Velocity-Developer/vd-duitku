# Rencana: test fungsi plugin, README.md, redesign dashboard, dan .gitignore

## Summary
Akan dikerjakan empat area di plugin [vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/vd-duitku.php):
1. Tambah test otomatis WordPress/PHPUnit untuk alur inti plugin.
2. Tambah [README.md](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/README.md) di root plugin.
3. Redesign admin jadi dashboard penuh berbasis data invoice/callback yang saat ini masih berasal dari [class-vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L120-L165).
4. Tambah [.gitignore](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/.gitignore) di root repo.

## Current State Analysis

### Struktur plugin saat ini
- Entry point plugin ada di [vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/vd-duitku.php#L18-L34). File ini mendefinisikan konstanta plugin, memuat aktivator dan class utama, lalu bootstrap `VD_Duitku`.
- Class utama ada di [class-vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L7-L431). Satu file ini memegang settings admin, REST callback, shortcode, API request, dan persistence DB.
- Aktivasi tabel dan default options ada di [class-vd-duitku-activator.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku-activator.php#L7-L74).
- Uninstall cuma guard, tanpa cleanup data, di [uninstall.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/uninstall.php#L1-L5).

### Kondisi admin/dashboard saat ini
- Dashboard admin belum ada dalam arti penuh.
- Yang ada baru settings page sederhana di [render_settings_page](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L120-L165).
- Belum ada enqueue asset admin, belum ada statistik, tabel riwayat invoice/callback, atau pemisahan template/view.

### Kondisi test/tooling saat ini
- Belum ada `tests/`, `composer.json`, `phpunit.xml`, atau bootstrap test WordPress.
- Permintaan user sudah diputuskan ke test otomatis WP/PHPUnit, jadi perlu scaffolding minimal untuk environment test.

### Area logic inti yang perlu dicakup test
- Default option dan sanitasi settings: [admin_init](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L29-L32), [sanitize_options](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L47-L59), [options](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L101-L118)
- Aktivasi dan schema tabel: [activate](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku-activator.php#L9-L73)
- Persistence invoice: [save_invoice](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L255-L298)
- Shortcode output: [tombol_bayar](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L305-L354)
- Persistence callback dan validasi callback flow: [save_callback](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L355-L395), [callback](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L396-L421), [register_rest](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L422-L430), [rest_callback](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L431-L439)

## Assumptions & Decisions
1. Test otomatis akan pakai WordPress/PHPUnit minimal, bukan unit test PHP murni, karena plugin tergantung kuat pada hook, option API, REST API, shortcode API, dan `$wpdb`.
2. Redesign dashboard akan tetap berpusat di plugin admin page yang ada sekarang (`vd-duitku`), bukan membangun SPA atau dependency frontend baru.
3. Dashboard penuh berarti halaman admin baru akan memuat:
   - kartu ringkasan statistik dari tabel invoice/callback,
   - panel konfigurasi Duitku,
   - tabel/list riwayat invoice,
   - tabel/list log callback.
4. README yang diminta adalah `README.md` di root plugin, bukan `readme.txt` format WordPress.org.
5. `.gitignore` akan disesuaikan untuk repo plugin PHP/WordPress kecil tanpa mengunci workflow ke tool yang belum tentu dipakai.

## Proposed Changes

### 1) Tambah scaffolding test otomatis
**File baru/diubah:**
- [composer.json](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/composer.json)
- [phpunit.xml.dist](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/phpunit.xml.dist)
- [tests/bootstrap.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/bootstrap.php)
- [tests/test-activator.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/test-activator.php)
- [tests/test-settings.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/test-settings.php)
- [tests/test-shortcode.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/test-shortcode.php)
- [tests/test-rest-callback.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/test-rest-callback.php)
- [tests/test-invoice-persistence.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/tests/test-invoice-persistence.php)

**What/why/how:**
- Tambah `composer.json` minimal untuk dev dependency PHPUnit/WordPress test tooling jika memang dibutuhkan oleh runner lokal.
- Tambah `phpunit.xml.dist` untuk mendefinisikan bootstrap, test suite, dan environment.
- Tambah `tests/bootstrap.php` untuk load WordPress test environment dan plugin entry point [vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/vd-duitku.php).
- Tambah test aktivasi untuk memastikan `VD_Duitku_Activator::activate()` membuat tabel dan default options sesuai [class-vd-duitku-activator.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku-activator.php#L17-L72).
- Tambah test settings untuk sanitasi mode, merchant code, callback URL, dan return URL sesuai [sanitize_options](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L47-L59).
- Tambah test shortcode untuk dua kasus: invoice tidak ada dan invoice ada, berdasarkan [tombol_bayar](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L305-L354).
- Tambah test callback/REST untuk memastikan route `/vd-duitku/v1/callback` terdaftar dan callback valid tersimpan ke tabel lewat [register_rest](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L422-L430) dan [rest_callback](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L431-L439).
- Tambah test persistence invoice untuk insert/update behavior di [save_invoice](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L255-L298).

**Catatan implementasi penting:**
- Karena `callback()` masih membaca `$_POST` langsung, test REST kemungkinan perlu menset superglobal saat request. Agar test stabil, implementasi kemungkinan perlu merapikan `rest_callback()`/`callback()` supaya bisa menerima payload request dengan cara yang tetap backward-compatible di dalam plugin.
- Perubahan ini boleh dilakukan karena termasuk bagian agar test otomatis bisa benar-benar berjalan.

### 2) Redesign admin jadi dashboard penuh
**File utama diubah:**
- [class-vd-duitku.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php)

**File baru yang kemungkinan ditambah:**
- [includes/admin/class-vd-duitku-admin-dashboard.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/admin/class-vd-duitku-admin-dashboard.php)
- [includes/admin/views/dashboard.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/admin/views/dashboard.php)
- [assets/admin.css](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/assets/admin.css)

**What/why/how:**
- Pisahkan tanggung jawab render dashboard dari class monolitik agar diff tetap terarah dan markup tidak menumpuk di satu method.
- Ubah halaman admin `vd-duitku` dari form polos menjadi dashboard penuh yang berisi:
  - header/status koneksi plugin,
  - kartu statistik total invoice, total callback, invoice sukses/tercatat, dan callback terbaru,
  - section konfigurasi merchant/settings,
  - tabel invoice terbaru dari tabel `${prefix}vd_duitku_invoice`,
  - tabel callback terbaru dari tabel `${prefix}vd_duitku_callback`.
- Tambah CSS admin ringan via enqueue native WordPress, tanpa framework baru.
- Pertahankan settings API yang ada agar penyimpanan option tetap lewat [admin_init](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L29-L32).
- Tambah helper query sederhana untuk statistik dan list terbaru, langsung memakai `$wpdb`, karena data sudah tersedia di tabel plugin.
- Desain visual mengikuti arah dashboard admin modern yang bersih, padat, dan mudah scan; tidak perlu frontend build system.

**Batas sengaja:**
- Tidak menambah chart JS, SPA, atau dependency UI baru.
- Tidak mengubah struktur data tabel di fase ini kecuali benar-benar dibutuhkan untuk menampilkan data yang sudah ada.

### 3) Tambah README.md
**File baru:**
- [README.md](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/README.md)

**What/why/how:**
- Dokumen root repo berisi:
  - deskripsi plugin,
  - requirement,
  - cara install,
  - cara set merchant code/API key,
  - mode sandbox vs production,
  - callback URL default,
  - penggunaan shortcode,
  - ringkasan tabel custom,
  - cara menjalankan test otomatis,
  - catatan redesign dashboard.
- Isi README harus konsisten dengan route callback di [options](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku.php#L101-L118) dan schema tabel di [class-vd-duitku-activator.php](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/includes/class-vd-duitku-activator.php#L21-L47).

### 4) Tambah .gitignore
**File baru:**
- [.gitignore](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/.gitignore)

**What/why/how:**
- Tambah ignore minimal untuk file lokal/dev artefact seperti:
  - `/vendor/`
  - `/node_modules/`
  - `/.idea/`
  - `/.vscode/`
  - `/phpunit.result.cache`
  - `Thumbs.db`
  - `.DS_Store`
  - log/temp file umum
- Jika test setup menambah tooling tertentu, ignore akan disesuaikan agar tidak membuang file yang seharusnya di-commit.

## Implementation Order
1. Tambah scaffolding test dan test cases dasar.
2. Rapikan bagian plugin yang menghambat test otomatis bila diperlukan, terutama callback request flow.
3. Bangun helper data dashboard dan redesign admin page.
4. Tambah asset admin ringan bila diperlukan.
5. Tulis README.md sesuai implementasi akhir.
6. Tambah `.gitignore` final sesuai file/tooling yang benar-benar dipakai.

## Verification
1. Jalankan PHPUnit dan pastikan suite untuk aktivasi, settings, shortcode, invoice persistence, dan REST callback lulus.
2. Aktifkan plugin di WordPress lokal dan buka menu admin `VD Duitku`; pastikan dashboard tampil, settings bisa disimpan, dan statistik/list tidak error saat tabel kosong.
3. Uji shortcode untuk invoice ada/tidak ada.
4. Uji endpoint callback lokal dengan payload valid dan pastikan log callback masuk ke tabel.
5. Review [README.md](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/README.md) dan [.gitignore](file:///d:/local-site/dev/app/public/wp-content/plugins/vd-duitku/.gitignore) agar sesuai struktur final repo.
