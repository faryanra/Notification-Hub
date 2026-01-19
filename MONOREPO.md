# Monorepo layout (Free + Pro)

This repository is being refactored into a **monorepo** that contains two separate WordPress plugins:

- `plugins/notification-hub/` (Free)
- `plugins/notification-hub-pro/` (Pro addon)

## Goals

- Keep development in **one repo**.
- Ship **two separate ZIPs** (Free and Pro).
- Ensure Pro-only code (handlers, integrations, templates, assets) is **fully isolated** from Free.

## Current status

Work in progress.

### Planned build outputs

- `dist/notification-hub.zip`
- `dist/notification-hub-pro.zip`
