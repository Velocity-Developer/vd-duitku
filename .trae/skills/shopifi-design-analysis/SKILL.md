---
name: "shopifi-design-analysis"
description: "Applies Shopifi-inspired cinematic commerce design system. Invoke when user asks to design or refine UI using this black/cream dual-canvas visual language."
---

# Shopifi Design Analysis

Gunakan skill ini saat user minta bikin, revisi, atau audit UI yang harus mengikuti bahasa visual Shopifi: dua track desain, cinematic dark untuk marketing dan light transactional untuk pricing/signup/dashboard.

## Kapan dipakai

- Saat user minta halaman marketing / landing hero dengan nuansa cinematic gelap.
- Saat user minta halaman pricing, signup, dashboard, atau form dengan canvas terang/cream.
- Saat user minta konsistensi visual yang pakai pill button, display tipis, dan aksen aloe/pistachio.
- Saat user minta audit UI agar tetap selaras dengan sistem desain ini.

## Inti brand

- Dua canvas utama, jangan dicampur dalam satu section:
  - Dark cinematic: `#000000` / `#0a0a0a`
  - Light transactional: `#ffffff` / `#fbfbf5`
- Typography split:
  - Display/headline: Neue Haas Grotesk Display, weight tipis 330–500
  - UI/body: Inter Variable 420–550
  - Code: ui-monospace
- Global typographic signature: `font-feature-settings: "ss03"`
- Semua button wajib bentuk pill (`9999px`)
- Aloe/pistachio hanya untuk light track, bukan dark cinematic

## Design rules wajib

### 1. Canvas polarity

- Marketing / brand storytelling pakai dark canvas.
- Pricing / signup / comparison / transactional pakai light atau cream canvas.
- Jangan tambahkan canvas ketiga. Jangan blend dark + mint dalam satu band cinematic.

### 2. Typography

- Display paling besar tetap tipis. Hero ideal:
  - 96px / weight 330 / line-height 1 / tracking 2.4px
- Display kecil turun bertahap: 70 / 55 / 48 / 36 mobile.
- Body default 16px Inter weight 420.
- Emphasis body 16px weight 550.
- Jangan pakai display heavy 400+ untuk hero kalau masih bisa 330.

### 3. Button system

- Hanya pakai pill.
- Varian utama:
  - black fill + white text
  - dark outline + white stroke/text di dark canvas
  - light outline + black text di light canvas
  - aloe fill untuk featured CTA di pricing
- Pressed state primary: `#3f3f46`

### 4. Color tokens inti

- `primary`: `#000000`
- `on-primary`: `#ffffff`
- `canvas-night`: `#000000`
- `canvas-night-elevated`: `#0a0a0a`
- `canvas-light`: `#ffffff`
- `canvas-cream`: `#fbfbf5`
- `aloe-10`: `#c1fbd4`
- `pistachio-10`: `#d4f9e0`
- `hairline-light`: `#e4e4e7`
- `hairline-dark`: `#1e2c31`
- `shade-70`: `#3f3f46`

### 5. Spacing dan shape

- Base spacing 8px.
- Tokens: 2 / 4 / 8 / 12 / 16 / 24 / 32 / 64.
- Radius:
  - xs 4
  - sm 5
  - md 8
  - lg 12
  - xl 20
  - pill 9999
- Pricing card pakai radius 12px.
- Input pakai radius 8px.

### 6. Photography dan depth

- Dark cinematic: full-bleed photography, jangan ditumpuk overlay text di atas image kalau bisa hindari.
- Light transactional: depth dari layered soft shadow, bukan glow keras.
- Dark card jangan kebanyakan shadow; cukup subtle inset/top highlight.

## Component guidance

### Marketing dark

- Hero headline besar, tipis, banyak negative space.
- Satu CTA utama per band.
- Nav dark dengan outline pills.
- Footer dark dengan muted cool links.

### Pricing / signup light

- Card putih atau aloe featured card.
- CTA utama hitam atau aloe.
- Table/comparison/form pakai border hairline light.
- Bisa pakai band pistachio untuk highlight fitur.

### Inputs

- Background putih.
- Border `#e4e4e7`.
- Text hitam.
- Padding kira-kira 10px 12px.
- Radius 8px.

## Do

- Jaga kontras canvas: dark untuk cinematic, light/cream untuk transactional.
- Pakai pill buttons konsisten.
- Pakai aloe/pistachio hanya di light track.
- Biarkan whitespace besar di marketing sections.
- Gunakan `ss03` global kalau stack font mendukung.

## Don't

- Jangan bikin button rounded rectangle biasa.
- Jangan pakai hijau mint di dark hero.
- Jangan pakai display berat untuk hero.
- Jangan tambah canvas abu/beige/biru di luar token inti.
- Jangan pecah CTA jadi banyak aksi setara dalam satu band cinematic.

## Responsive

- Wide `>= 1440`: hero full cinematic, pricing 4 kolom.
- Desktop `1024–1440`: layout default.
- Tablet `768–1023`: pricing 2 kolom.
- Mobile `< 768`: pricing 1 kolom, nav hamburger, display turun ke 56–64px lalu 36px untuk section kecil.
- Semua touch target minimal 44x44px.

## Cara pakai saat implementasi

1. Tentukan dulu screen ini marketing atau transactional.
2. Pilih canvas sesuai jenis screen.
3. Terapkan hierarchy type: thin display untuk headline, Inter untuk UI body.
4. Pakai pill CTA yang sesuai polarity.
5. Tambahkan aloe/pistachio hanya bila screen light dan butuh featured emphasis.
6. Audit akhir: cek radius, spacing, polarity, jumlah CTA, dan weight display.

## Checklist audit cepat

- Apakah halaman ini dark cinematic atau light transactional?
- Apakah button semuanya pill?
- Apakah hero/display masih tipis?
- Apakah mint/pistachio hanya muncul di light track?
- Apakah section marketing cukup lega, bukan padat seperti dashboard?
- Apakah form/table light memakai border hairline ringan?
