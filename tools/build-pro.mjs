import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function rmDir(p) {
  if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
}

function ensureDir(p) {
  fs.mkdirSync(p, { recursive: true });
}

function copyFile(src, dest) {
  ensureDir(path.dirname(dest));
  fs.copyFileSync(src, dest);
}

function copyDir(src, dest, include = null) {
  const allow = include ? new Set(include.map((x) => x.replace(/\\/g, '/'))) : null;

  const walk = (s, d, relBase = '') => {
    ensureDir(d);
    for (const ent of fs.readdirSync(s, { withFileTypes: true })) {
      const srcPath = path.join(s, ent.name);
      const rel = (relBase ? relBase + '/' : '') + ent.name;
      const relNorm = rel.replace(/\\/g, '/');

      if (allow) {
        // Allow if exact or parent of allowed (so we can descend).
        const isExact = allow.has(relNorm);
        const isParent = Array.from(allow).some((p) => p.startsWith(relNorm + '/'));
        if (!isExact && !isParent) continue;
      }

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
  const root = path.resolve(__dirname, '..');
  const dist = path.resolve(root, 'dist');
  const stage = path.resolve(dist, 'notification-hub-pro');

  // Clean only pro stage.
  rmDir(stage);
  ensureDir(stage);

  // Minimal Pro bundle (temporary during migration):
  // - pro entrypoint
  // - pro module folder
  // - pro notifier handlers
  // - pro templates partials
  copyFile(path.join(root, 'notification-hub-pro.php'), path.join(stage, 'notification-hub-pro.php'));

  copyDir(root, stage, [
    'modules/pro',
    'modules/notifier/class-nh-notifier-telegram.php',
    'modules/notifier/class-nh-notifier-slack.php',
    'templates/partials/license-box.php',
    'templates/partials/pro-settings-fields.php',
    'assets/css',
    'assets/js',
    'languages',
  ]);

  console.log('Staged Pro addon to:', stage);
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
