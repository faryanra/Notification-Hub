# Notification Hub

A central hub for aggregating and managing notifications in WordPress. This plugin collects notifications from various sources (like WordPress core events) and displays them in a simple admin dashboard. The pro version adds external integrations like Telegram notifications.

This is the initial MVP (Minimum Viable Product) version of the plugin. It's designed for testing and feedback before expanding to full features like WooCommerce integration, AI prioritization, push notifications, and SaaS syncing.

## Features (MVP Version)
- **Central Dashboard**: View all notifications in one place with a simple table (ID, Title, Message, Created At, Status).
- **Admin Bar Badge**: Dynamic count of unread notifications for quick access.
- **Basic Collection**: Automatically collects and stores notifications from WordPress core events (e.g., new comments).
- **Pro Features (Teaser)**: Telegram integration for sending alerts (requires license activation in settings).
- **Freemium Model**: Free version includes base functionality; pro unlocks advanced integrations.
- **RTL/LTR Support**: Basic compatibility for right-to-left languages like Persian.

## Screenshots
- Dashboard View: ![Dashboard Screenshot](path/to/screenshot-dashboard.png)  <!-- Replace with your actual screenshot path or URL -->
- Settings Page: ![Settings Screenshot](path/to/screenshot-settings.png)
- Admin Bar Badge: ![Badge Screenshot](path/to/screenshot-badge.png)

## Installation
1. Download the ZIP file from this repository.
2. Upload the `notification-hub` folder to your WordPress site's `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Go to the 'Notifications' menu in the admin sidebar to view the dashboard.
5. For pro features (like Telegram), enter your license key in Notifications > Settings.

### Requirements
- WordPress 6.0 or higher
- PHP 8.0 or higher
- For Telegram integration: A valid Telegram Bot Token and Chat ID (obtained from @BotFather)

## Setup and Usage
1. **Activate Plugin**: After activation, a new menu "Notifications" appears in the admin sidebar.
2. **View Notifications**: Click on "Notifications" to see the dashboard. It lists recent notifications (limited to 20 for MVP).
3. **Test Collection**: Post a new comment on any post/page – it should appear in the dashboard as a notification.
4. **Telegram Setup (Pro)**:
   - Go to Notifications > Settings.
   - Enter your Telegram Bot Token and Chat ID.
   - Save changes.
   - Use the "Send Test Message" button to verify.
   - New notifications will automatically send to Telegram if pro is active.
5. **Pro Activation**: Enter a valid license key in the settings to unlock pro features (MVP placeholder – full Envato integration in future versions).

## Code Structure
- `notification-hub.php`: Main plugin file with headers, constants, and requires.
- `includes/`:
  - `class-nh-loader.php`: Handles hooks, menu, dashboard, and assets.
  - `class-nh-database.php`: Manages custom database table for notifications.
  - `class-nh-collector.php`: Collects notifications from events (e.g., comments).
  - `class-nh-notifier.php`: Sends notifications to external services (e.g., Telegram).
  - `class-nh-pro-features.php` (in `/pro/`): Handles pro license check and features.
- `assets/`:
  - `css/nh-styles.css`: Basic styles for dashboard table.
  - `js/nh-scripts.js`: JavaScript for dynamic badge updates (polling for MVP).

## Roadmap for Future Versions
- **Short-term (v1.1)**: Add integrations for WooCommerce (new orders) and Contact Form 7 (form submissions). Improve UI with WP_List_Table for sorting/filtering.
- **Mid-term (v2.0)**: AI prioritization using APIs (e.g., OpenAI for categorizing notifications). Push notifications with OneSignal. Full async sending for performance.
- **Long-term (v3.0+)**: SaaS mode for multi-site syncing. Add-ons for Slack/Discord. Advanced reporting (CSV export). Full i18n/translation support.

## Contribution
This is a private repository for now. If you're collaborating, fork and submit pull requests. Follow WordPress coding standards (OOP, sanitization, etc.).

## License
GPL-2.0-or-later. See the [LICENSE](LICENSE) file for details.

## Contact
- Author: Faryan Rajabi
- GitHub: [faryanra](https://github.com/faryanra)
- LinkedIn: [faryan-rajabi](https://www.linkedin.com/in/faryan-rajabi/)

If you have feedback or issues, open an issue in this repo.