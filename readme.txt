=== HelloCode Notification Hub ===
Contributors: faryanra
Tags: notifications, telegram, slack, dashboard, automation
Requires at least: 5.8
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A central WordPress notification dashboard with Email, Telegram, and Slack delivery.

== Description ==

Notification Hub helps you capture and manage WordPress events in one place, then send alerts to your team through Email, Telegram, and Slack.

Version 1.0.0 includes all v1 features for free.

Key capabilities:

* Unified notification dashboard
* Channel delivery (Email, Telegram, Slack)
* Event-based notification controls
* Test notifications from plugin settings
* Rule-based automations
* Analytics and exports

= Telegram Setup =

1. Open Telegram and start a chat with BotFather.
2. Create a new bot using `/newbot`.
3. Use a bot name and username, then save the bot token from BotFather.
4. You can use the project bot username `@Notifi_hub_bot` as your reference format.
5. In WordPress, go to `Notification Hub > Settings > Channels`.
6. Paste the token in `Telegram Bot Token`.
7. Send a message to your bot from your Telegram account.
8. Get your chat ID from Telegram updates and paste it in `Telegram Chat ID`.
9. Click `Send Test to Telegram`.

= Slack Setup =

1. In your Slack workspace, create or open a Slack App.
2. Enable `Incoming Webhooks`.
3. Add a webhook to your target channel and copy the webhook URL.
4. In WordPress, go to `Notification Hub > Settings > Channels`.
5. Paste the URL into `Slack Webhook URL`.
6. Click `Send Test to Slack`.

= Usage =

1. Go to `Notification Hub > Settings`.
2. Enable and configure your preferred channels.
3. Select the notification events you want to track.
4. Save settings.
5. Send a test notification for Email, Telegram, or Slack.
6. Open the dashboard to review incoming notifications.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/notification-hub`.
2. Activate `Notification Hub` from the Plugins page.
3. Open `Notification Hub` in wp-admin.
4. Configure channels and event settings.
5. Send test notifications to verify setup.

== Frequently Asked Questions ==

= Are all v1 features free? =

Yes. All features included in version 1.0.0 are free.

= Do I need WooCommerce or Contact Form 7? =

No. The plugin works with WordPress core events. WooCommerce and Contact Form 7 events are used only when those plugins are active.

= Where do I configure Telegram and Slack? =

Go to `Notification Hub > Settings > Channels`.

= Can I disable specific events? =

Yes. In `Settings > General`, choose which events are enabled.

= Can I test each channel before going live? =

Yes. Use the `Send Test` buttons in the settings page.

== Screenshots ==

1. Notification dashboard overview (placeholder).
2. General settings and event selection (placeholder).
3. Channel settings for Email, Telegram, and Slack (placeholder).
4. Rules and automation screen (placeholder).
5. Analytics and export tools (placeholder).

== Changelog ==

= 1.0.0 =

* First public release.
* Dashboard for WordPress notifications.
* Email, Telegram, and Slack channels.
* Event controls and test notifications.
* Rules, analytics, and CSV exports.
