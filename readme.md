# HelloCode Notification Hub

A modular WordPress notification management plugin for Telegram, Slack, Email, WooCommerce, Contact Form 7, and WordPress events.

HelloCode Notification Hub helps WordPress site owners centralize operational events and route notifications through multiple external channels from a unified admin dashboard.

The project originally started as a broader notification platform experiment with Free/Pro concepts. Version 1.x is now maintained as a free-only open-source WordPress plugin.

---

# Overview

Managing operational events across WordPress websites becomes difficult when administrators need to constantly monitor:

* Comments
* WooCommerce orders
* Forms
* User activity
* System events
* Failed actions
* Custom workflows

HelloCode Notification Hub centralizes those events and delivers actionable notifications through multiple channels such as Telegram, Slack, and Email.

The plugin is designed with a modular architecture where:

* Channels are isolated
* Integrations are separated
* Templates are reusable
* Delivery pipelines are extensible
* Admin tooling is centralized
* Automation logic is configurable

This makes the plugin easier to extend and maintain over time.

---

# Core Features

## Unified Notification Dashboard

* Unified admin notification dashboard using `WP_List_Table`
* Notification detail preview modal
* Admin bar unread notification badge
* Notification state management
* Read / unread / important actions
* Bulk actions and advanced filtering
* Priority scoring system (0–100)
* Structured notification records
* Channel-aware notification rendering

## Notification Channels

### Telegram Notifications

* Telegram Bot API integration
* Inline action buttons
* Rich formatted messages
* Channel-aware templates
* Test notification actions
* Telegram-specific delivery validation
* Action buttons for comments, posts, WooCommerce orders, forms, and hooks

### Slack Notifications

* Slack Incoming Webhook integration
* Structured Slack messages
* Action links
* Test webhook actions
* Slack-specific formatting

### Email Notifications

* Native WordPress `wp_mail()` support
* HTML email templates
* Channel-aware formatting
* Email test actions
* SMTP-compatible delivery

---

# Automation System

The plugin includes a configurable automation system for dispatching actions based on notification conditions.

## Features

* Rules manager in admin
* JSON-based conditions and actions
* Per-event notification rules
* Immediate dispatch mode
* Queue-ready dispatch processing
* Notification state actions
* Archive / important / mark-read actions
* Multi-channel dispatch support
* Extensible automation structure

---

# Analytics and API

## Analytics

* 7-day analytics view
* 30-day analytics view
* Daily totals with zero-filled continuity
* Notification metrics
* Channel activity metrics
* CSV export support

## REST API

Example endpoint:

```txt
GET /nh/v1/metrics?range=7d|30d
```

## Webhook Support

* Webhook endpoint support
* Signature validation
* Timestamp validation
* Basic replay protection
* Request validation
* Extensible webhook structure

---

# Integrations

## WordPress Core

* Comment events
* Post events
* User-related events
* Admin-triggered actions

## WooCommerce

* Order-related events
* WooCommerce notifications
* Actionable order links

## Contact Form 7

* Form submission events
* External notification dispatch

## Custom Hooks

* Custom hook manager
* Create/update/delete hooks
* Test trigger actions
* Extensible event registration

---

# Telegram Setup

## Step 1 — Create a Telegram Bot

Open Telegram and search for:

```txt
@BotFather
```

Create a bot using:

```txt
/newbot
```

BotFather will generate a bot token.

Example:

```txt
123456789:AAExampleToken
```

---

## Step 2 — Open a Chat With Your Bot

Search for your bot username.

Example:

```txt
@Notifi_hub_bot
```

Open the bot and send at least one message.

---

## Step 3 — Find Your Chat ID

Open the following URL in your browser:

```txt
https://api.telegram.org/botYOUR_BOT_TOKEN/getUpdates
```

Replace:

```txt
YOUR_BOT_TOKEN
```

with your real Telegram bot token.

Inside the response, find:

```json
"chat":{"id":123456789}
```

That numeric value is your Telegram chat ID.

---

## Step 4 — Configure Notification Hub

Open WordPress admin:

```txt
Notification Hub → Settings → Channels → Telegram
```

Add:

* Bot Token
* Chat ID

Then send a test notification.

---

# Telegram Inline Action Buttons

Telegram notifications support inline action buttons for:

* Comments
* WooCommerce orders
* Posts
* Forms
* Custom hooks/events

Telegram buttons are automatically displayed when notifications contain valid URLs.

For local development environments:

* `localhost`
* `.local`
* private/local IPs

Telegram buttons are disabled by default.

Developers can enable local testing using:

```php
add_filter('notification_hub_allow_local_telegram_buttons', '__return_true');
```

This filter should only be used for local development/testing.

---

# Slack Setup

## Step 1 — Create an Incoming Webhook

Open:

```txt
https://api.slack.com/messaging/webhooks
```

Create a Slack Incoming Webhook.

Slack will generate a webhook URL.

Example:

```txt
https://hooks.slack.com/services/XXXX/YYYY/ZZZZ
```

---

## Step 2 — Configure Notification Hub

Open:

```txt
Notification Hub → Settings → Channels → Slack
```

Paste your webhook URL.

Then send a test notification.

---

# Email Notifications

HelloCode Notification Hub uses native WordPress email delivery through:

```php
wp_mail()
```

For local development environments such as XAMPP, email delivery may require SMTP configuration.

Recommended SMTP plugins:

* FluentSMTP
* WP Mail SMTP

---

# Installation

## Manual Installation

1. Upload the plugin to:

```txt
/wp-content/plugins/notification-hub
```

2. Activate the plugin from the WordPress Plugins page.

3. Open:

```txt
Notification Hub
```

from the WordPress admin menu.

4. Configure your channels and notification settings.

---

# Internationalization

The plugin is translation-ready.

## Included Languages

* Persian (`fa_IR`)
* Italian (`it_IT`)

## RTL Support

The admin interface includes RTL support for RTL languages such as Persian.

---

# Project Structure

The plugin uses a modular architecture with separated responsibilities:

* Channel senders
* Event integrations
* Admin routes
* Settings registration
* Notification templates
* Translation files
* RTL styles
* Delivery processing
* REST routes
* Analytics

This structure helps keep the project maintainable and extensible.

---

# Repository Branches

## main

Contains the current free-only open-source version.

## legacy/free-pro-history

Contains the earlier Free/Pro development history and archived versions.

---

# Requirements

* WordPress 5.8+
* PHP 7.4+

---

# Release History

## v1.0.0

Initial public free-only open-source release.

Included:

* Telegram notifications
* Slack notifications
* Email notifications
* WooCommerce integration
* Contact Form 7 integration
* WordPress comment and post notifications
* Telegram inline action buttons
* Internationalization support
* Persian and Italian translations
* RTL support
* WordPress.org-ready packaging

---

# Author

**Faryan Rajabi Jorshari**
HelloCode

Website:
[https://www.hellocode.ir/](https://www.hellocode.ir/)

LinkedIn:
[https://www.linkedin.com/in/reza-rajabi-jorshari/](https://www.linkedin.com/in/reza-rajabi-jorshari/)

---

# License

GPLv2 or later
