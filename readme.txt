=== HelloCode Notification Hub ===
Contributors: faryanra
Tags: notifications, telegram, slack, dashboard, automation, woocommerce
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modular WordPress notification dashboard with Telegram, Slack, Email, WooCommerce, Contact Form 7, and event-based integrations.

== Description ==

HelloCode Notification Hub helps WordPress site owners centralize operational events and deliver notifications through multiple channels from a unified admin dashboard.

The plugin supports Email, Telegram, and Slack delivery while keeping notification management inside WordPress.

Version 1.0.0 is the first free-only open-source public release.

= Core Features =

* Unified admin notification dashboard
* Email notifications
* Telegram notifications
* Slack notifications
* Telegram inline action buttons
* WooCommerce event support
* Contact Form 7 event support
* WordPress post and comment notifications
* Per-event notification controls
* Channel-aware templates
* Notification analytics
* CSV exports
* Rule-based automations
* Queue-ready processing
* Test notifications from settings
* Internationalization support
* Persian and Italian translations
* RTL support

= Telegram Setup =

1. Open Telegram and search for `@BotFather`.
2. Create a new bot using `/newbot`.
3. Choose a bot name and username.
4. Save the Telegram bot token provided by BotFather.

Example token:

`123456789:AAExampleToken`

5. Open a chat with your bot and send at least one message.
6. Open the following URL in your browser:

`https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates`

7. Replace `YOUR_BOT_TOKEN` with your real token.
8. Find:

`"chat":{"id":123456789}`

9. That numeric value is your Telegram Chat ID.
10. In WordPress, go to:

`Notification Hub > Settings > Channels > Telegram`

11. Paste:
* Telegram Bot Token
* Telegram Chat ID

12. Click `Send Test to Telegram`.

You can use the sample project bot username `@Notifi_hub_bot` as a reference example.

= Telegram Inline Buttons =

Telegram notifications support inline action buttons for:
* Comments
* WooCommerce orders
* Posts
* Forms
* Custom hooks/events

Inline buttons are automatically displayed when notifications contain valid URLs.

For local development environments such as:
* localhost
* .local
* private/local IPs

Telegram buttons are disabled by default.

Developers can enable local testing using:

`add_filter('notification_hub_allow_local_telegram_buttons', '__return_true');`

= Slack Setup =

1. Open:
`https://api.slack.com/messaging/webhooks`

2. Create or configure a Slack App.
3. Enable `Incoming Webhooks`.
4. Add a webhook for your target channel.
5. Copy the generated webhook URL.

Example:

`https://hooks.slack.com/services/XXXX/YYYY/ZZZZ`

6. In WordPress, go to:

`Notification Hub > Settings > Channels > Slack`

7. Paste the webhook URL.
8. Click `Send Test to Slack`.

= Email Notifications =

HelloCode Notification Hub uses native WordPress email delivery through `wp_mail()`.

For local development environments such as XAMPP, email delivery may require SMTP configuration.

Recommended SMTP plugins:
* FluentSMTP
* WP Mail SMTP

= Usage =

1. Go to `Notification Hub > Settings`.
2. Configure Email, Telegram, or Slack channels.
3. Enable the events you want to monitor.
4. Save settings.
5. Send test notifications.
6. Open the dashboard to review notifications and analytics.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/notification-hub`.
2. Activate `HelloCode Notification Hub` from the Plugins page.
3. Open `Notification Hub` in wp-admin.
4. Configure channels and event settings.
5. Send test notifications to verify setup.

== Frequently Asked Questions ==

= Are all v1 features free? =

Yes. All features included in version 1.0.0 are free.

= Do I need WooCommerce or Contact Form 7? =

No. The plugin works with WordPress core events by default.

WooCommerce and Contact Form 7 integrations are only used when those plugins are active.

= Where do I configure Telegram and Slack? =

Go to:

`Notification Hub > Settings > Channels`

= Can I disable specific events? =

Yes. You can enable or disable supported events from the settings page.

= Can I test each channel before going live? =

Yes. The plugin includes test actions for Email, Telegram, and Slack.

= Does the plugin support RTL languages? =

Yes. Version 1.0.0 includes RTL support and Persian translations.

== Screenshots ==

1. Unified notification dashboard.
2. General settings and event configuration.
3. Telegram, Slack, and Email channel settings.
4. Automation and rules manager.
5. Analytics and CSV export tools.

== Changelog ==

= 1.0.0 =

* First free-only open-source public release.
* Unified WordPress notification dashboard.
* Telegram, Slack, and Email channels.
* Telegram inline action buttons.
* WooCommerce integration.
* Contact Form 7 integration.
* Automation and analytics tools.
* CSV export support.
* Persian and Italian translations.
* RTL support.

== Upgrade Notice ==

= 1.0.0 =

Initial public open-source release.

== Author ==

Faryan Rajabi Jorshari
HelloCode

Website:
https://www.hellocode.ir/

LinkedIn:
https://www.linkedin.com/in/reza-rajabi-jorshari/