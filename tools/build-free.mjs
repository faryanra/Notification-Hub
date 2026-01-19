import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { createGzip } from 'node:zlib';
import { pipeline } from 'node:stream/promises';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function rmDir(p) {
  if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
}

function ensureDir(p) {
  fs.mkdirSync(p, { recursive: true });
}

function copyDir(src, dest, exclude = []) {
  const ex = new Set(exclude.map((x) => x.replace(/\\/g, '/')));

  const walk = (s, d, relBase = '') => {
    ensureDir(d);
    for (const ent of fs.readdirSync(s, { withFileTypes: true })) {
      const srcPath = path.join(s, ent.name);
      const rel = (relBase ? relBase + '/' : '') + ent.name;
      const relNorm = rel.replace(/\\/g, '/');

      // Exclude exact path or any child of excluded dir.
      let blocked = false;
      for (const exPath of ex) {
        if (relNorm === exPath || relNorm.startsWith(exPath + '/')) {
          blocked = true;
          break;
        }
      }
      if (blocked) continue;

      const dstPath = path.join(d, ent.name);
      if (ent.isDirectory()) {
        walk(srcPath, dstPath, rel);
      } else {
        fs.copyFileSync(srcPath, dstPath);
      }
    }
  };

  walk(src, dest);
}

async function main() {
  // Free plugin staging root.
  const root = path.resolve(__dirname, '..');
  const dist = path.resolve(root, 'dist');
  const stage = path.resolve(dist, 'notification-hub');

  rmDir(dist);
  ensureDir(dist);

  // Stage Free plugin from current repo root (temporary during migration).
  // Exclude pro entrypoint + pro module folder.
  copyDir(root, stage, [
    'dist',
    '.git',
    '.github',
    'tools',
    'node_modules',
    'package.json',
    'package-lock.json',
    'MONOREPO.md',
    'notification-hub-pro.php',
    'modules/pro',
  ]);

  // NOTE: ZIP step intentionally omitted (pure staging) to keep tool minimal.
  // You can zip the staged folder into a distributable.

  console.log('Staged Free plugin to:', stage);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
