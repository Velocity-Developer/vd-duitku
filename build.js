const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const ROOT = __dirname;

// Ambil versi dari konstanta VD_DUITKU_VERSION di vd-duitku.php
function getVersion() {
  const main = path.join(ROOT, 'vd-duitku.php');
  const src = fs.readFileSync(main, 'utf8');
  const match = src.match(/define\(\s*['"]VD_DUITKU_VERSION['"]\s*,\s*['"]([^'"]+)['"]\s*\)/);
  if (!match) {
    throw new Error('VD_DUITKU_VERSION tidak ditemukan di vd-duitku.php');
  }
  return match[1];
}

// File/folder runtime yang masuk ke zip (clean build)
const ENTRIES = [
  'vd-duitku.php',
  'includes',
  'assets',
  'uninstall.php',
  'README.md',
];

function main() {
  const version = getVersion();
  const distDir = path.join(ROOT, 'dist');
  fs.mkdirSync(distDir, { recursive: true });

  const zipName = `vd-duitku-${version}.zip`;
  const zipPath = path.join(distDir, zipName);
  const output = fs.createWriteStream(zipPath);
  const archive = archiver('zip', { zlib: { level: 9 } });

  output.on('close', () => {
    console.log(`Build selesai: ${zipPath} (${archive.pointer()} bytes)`);
  });

  archive.on('warning', (err) => {
    if (err.code === 'ENOENT') {
      console.warn(err);
    } else {
      throw err;
    }
  });

  archive.on('error', (err) => {
    throw err;
  });

  archive.pipe(output);

  for (const entry of ENTRIES) {
    const full = path.join(ROOT, entry);
    if (!fs.existsSync(full)) {
      console.warn(`Lewati (tidak ada): ${entry}`);
      continue;
    }
    const stat = fs.statSync(full);
    if (stat.isDirectory()) {
      archive.directory(full, entry);
    } else {
      archive.file(full, { name: entry });
    }
  }

  archive.finalize();
}

main();
