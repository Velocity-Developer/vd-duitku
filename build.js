const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const ROOT = __dirname;
const PLUGIN_DIR = 'vd-duitku';

// Ambil versi dari package.json
function getVersion() {
  const pkgPath = path.join(ROOT, 'package.json');
  const pkg = JSON.parse(fs.readFileSync(pkgPath, 'utf8'));

  if (!pkg.version) {
    throw new Error('version tidak ditemukan di package.json');
  }

  return pkg.version;
}

// File/folder runtime yang masuk ke ZIP
const ENTRIES = [
  'vd-duitku.php',
  'includes',
  'assets',
  'uninstall.php',
  'README.md',
];

async function main() {
  const version = getVersion();

  const distDir = path.join(ROOT, 'dist');

  // Buat folder dist jika belum ada
  fs.mkdirSync(distDir, { recursive: true });

  const zipName = `vd-duitku-${version}.zip`;
  const zipPath = path.join(distDir, zipName);

  // Hapus ZIP lama jika sudah ada
  if (fs.existsSync(zipPath)) {
    fs.unlinkSync(zipPath);
  }

  const output = fs.createWriteStream(zipPath);

  const archive = archiver('zip', {
    zlib: {
      level: 9,
    },
  });

  output.on('close', () => {
    console.log('');
    console.log('Build selesai.');
    console.log(`File   : ${zipPath}`);
    console.log(`Ukuran : ${archive.pointer()} bytes`);
    console.log(`Root   : ${PLUGIN_DIR}/`);
  });

  output.on('error', (err) => {
    throw err;
  });

  archive.on('warning', (err) => {
    if (err.code === 'ENOENT') {
      console.warn(err.message);
    } else {
      throw err;
    }
  });

  archive.on('error', (err) => {
    throw err;
  });

  archive.pipe(output);

  for (const entry of ENTRIES) {
    const fullPath = path.join(ROOT, entry);

    if (!fs.existsSync(fullPath)) {
      console.warn(`Lewati (tidak ada): ${entry}`);
      continue;
    }

    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      // Contoh:
      // includes -> vd-duitku/includes
      archive.directory(
        fullPath,
        `${PLUGIN_DIR}/${entry}`
      );
    } else {
      // Contoh:
      // vd-duitku.php -> vd-duitku/vd-duitku.php
      archive.file(fullPath, {
        name: `${PLUGIN_DIR}/${entry}`,
      });
    }
  }

  await archive.finalize();
}

main().catch((err) => {
  console.error('Build gagal:');
  console.error(err);
  process.exit(1);
});
