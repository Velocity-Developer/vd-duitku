# Plan: `npm run build` untuk zip distribusi plugin

## Summary
Tambahkan *build script* berbasis Node (`archiver`) agar perintah `npm run build`
menghasilkan file zip distribusi di folder `/dist/` dengan nama
`vd-duitku-<version>.zip`. Versi diambil otomatis dari konstanta
`VD_DUITKU_VERSION` di `vd-duitku.php` supaya nama zip selalu sinkron.

## Current State Analysis
- Plugin saat ini **tidak punya** `package.json` → tidak ada `npm run build`.
- Versi plugin: `1.0.0` (konstanta `VD_DUITKU_VERSION` di `vd-duitku.php:18`).
- Folder `/dist/` **sudah** di-ignore di `.gitignore:6` → output build aman tidak
  ter-commit.
- Struktur runtime plugin: `vd-duitku.php`, `includes/`, `assets/`, `uninstall.php`,
  `README.md`.
- File dev yang harus **dikecualikan** dari zip: `tests/`, `vendor/`, `node_modules/`,
  `.trae/`, `composer.json`, `phpunit.xml.dist`, `.gitignore`.

## Proposed Changes

### 1. `package.json` (baru, di root plugin)
```json
{
  "name": "vd-duitku",
  "version": "1.0.0",
  "description": "WordPress plugin VD Duitku",
  "private": true,
  "scripts": {
    "build": "node build.js"
  },
  "devDependencies": {
    "archiver": "^7.0.1"
  }
}
```
- `version` disamakan dengan `VD_DUITKU_VERSION` (1.0.0) sebagai fallback/default.
- `build` menjalankan `build.js`.

### 2. `build.js` (baru, di root plugin)
- Baca konstanta `VD_DUITKU_VERSION` dari `vd-duitku.php` lewat regex sederhana
  (`define('VD_DUITKU_VERSION', '...')`) → pakai sebagai versi zip.
- Buat folder `dist/` jika belum ada (`fs.mkdirSync`).
- Pakai `archiver('zip')` untuk menulis `dist/vd-duitku-<version>.zip`.
- Masukkan **hanya** file/folder runtime (clean build):
  - `vd-duitku.php`
  - `includes/` (seluruh isi)
  - `assets/` (seluruh isi)
  - `uninstall.php`
  - `README.md`
- Gunakan `archiver.append` / `glob` dengan daftar eksplisit; jangan ikutkan
  `tests/`, `vendor/`, `node_modules/`, `.trae/`, config dev.
- Log nama file output + ukuran saat selesai; `finalize()` dan tangani error.

### 3. `.gitignore` (tidak diubah)
`/dist/` sudah di-ignore (baris 6). Tidak perlu edit.

### 4. `README.md` (tambah 1 section kecil, opsional tapi berguna)
Tambahkan section "Build distribusi":
```md
## Build distribusi
npm install
npm run build
# hasil: dist/vd-duitku-<version>.zip
```

## Assumptions & Decisions
- Tooling: **archiver (Node)** (pilihan user). Perlu `npm install` sekali untuk
  menarik `devDependencies`.
- Isi zip: **clean / runtime only** (pilihan user) — tests, vendor, node_modules,
  .trae, dan config dev dikecualikan.
- Versi zip diambil dari `vd-duitku.php`, bukan duplikat manual, agar tidak
  out-of-sync.
- `archiver ^7` pada Node modern; bila executor pakai Node <18 bisa turun ke
  `^5`/`^6` (catat kalau perlu).

## Verification Steps
1. `cd` ke root plugin, jalankan `npm install` (tarik `archiver`).
2. Jalankan `npm run build`.
3. Pastikan file `dist/vd-duitku-1.0.0.zip` tercipta.
4. Ekstrak zip dan verifikasi isinya hanya: `vd-duitku.php`, `includes/`,
   `assets/`, `uninstall.php`, `README.md` (tanpa `tests/`, `vendor/`, `.trae/`).
5. Ubah `VD_DUITKU_VERSION` → jalankan build lagi → nama zip ikut berubah.
6. Pastikan `git status` tidak menampilkan `dist/` (sudah di-ignore).
