# HelloCode Notification Hub (v1.0.0)

HelloCode Notification Hub is a modular notification management plugin for WordPress that centralizes operational events and delivers them through multiple channels.

## Core Features

- Unified admin notification dashboard (`WP_List_Table`)
- Notification details modal preview
- Admin bar unread badge
- AJAX actions for read, unread, important, and delete
- Bulk actions and advanced filtering
- Priority scoring system (0-100)
- Email notifications
- Telegram notifications
- Slack notifications
- Channel test actions in settings
- Queue-based delivery pipeline
- Retry and backoff for retryable delivery failures
- WP-Cron fallback processing
- Structured event and channel logs
- Per-event notification controls in settings

## Automation

- Rules manager in admin
- JSON-based conditions and actions
- State actions (archive, important, mark read)
- Dispatch actions to email, telegram, and slack
- Queued and immediate dispatch modes

## Analytics and API

- Analytics page with 7-day and 30-day ranges
- Daily totals with zero-filled dates for continuity
- REST metrics endpoint: `GET /nh/v1/metrics?range=7d|30d`
- Webhook endpoint with signature verification, timestamp validation, replay protection, and rate limiting
- CSV export for notifications
- CSV export for analytics metrics

## Integrations

- WordPress core events
- WooCommerce events
- Contact Form 7 events
- Custom hooks manager (create, update, delete, test trigger)
- Channel-aware templates for Email, Telegram, and Slack
- Actionable links in channel notifications (post/comment/order/user/forms/hooks)

## Installation

1. Upload the plugin to `/wp-content/plugins/notification-hub`.
2. Activate it from the WordPress Plugins page.
3. Open `Notification Hub` from the admin menu.
4. Configure `General` and `Channels` settings.

## Release

### 1.0.0

Initial public release with full dashboard, channel delivery, automation, analytics, webhook, REST metrics, queue processing, and CSV export features.

## License

GPL v3 or later
