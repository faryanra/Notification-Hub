# Changelog

All notable changes to Notification Hub will be documented here.

---

## [2.0.0] - 2026-02-13

### 🎉 Major Refactor

**Complete architectural rewrite for maintainability and scalability.**

### Added
- ✨ PSR-4 autoloading with namespaces
- ✨ Dependency Injection Container
- ✨ Interface-based integration system
- ✨ Repository pattern for data access
- ✨ Factory classes for object creation
- ✨ Service layer (Notifier, Queue Processor)
- ✨ Validation helpers
- ✨ Formatter helpers
- ✨ Utility classes (Logger, Cache, Request)
- ✨ Template system for admin UI
- ✨ REST API endpoints
- ✨ Analytics presenter
- ✨ License management system

### Changed
- 🔄 **Breaking:** File structure moved to `src/` folder
- 🔄 **Breaking:** Class names now use namespaces
- 🔄 Improved admin menu structure
- 🔄 Refactored all event listeners
- 🔄 Rewritten channel senders
- 🔄 Enhanced database queries

### Improved
- ⚡ Better performance (caching, optimized queries)
- 🎨 Cleaner code organization
- 📖 Improved inline documentation
- 🧪 More testable architecture
- 🔒 Enhanced security (nonces, capability checks)

### Backward Compatibility
- ✅ Legacy class aliases maintained
- ✅ Existing hooks preserved
- ✅ Database schema unchanged (auto-migration)

---

## [1.7.2] - 2025-12-01

### Fixed
- 🐛 CF7 integration hook priority
- 🐛 WooCommerce order status check

### Added
- ✨ Custom hooks loader

---

## [1.7.0] - 2025-10-15

### Added
- ✨ Contact Form 7 integration
- ✨ Admin bar badge
- ✨ Bulk actions

---

## [1.6.2] - 2025-08-20

### Added
- ✨ Queue system
- ✨ Priority calculation
- ✨ Multisite support

---

## [1.5.0] - 2025-06-10

### Added
- ✨ WooCommerce integration
- ✨ Low stock alerts
- ✨ CSV export

---

## [1.0.0] - 2025-01-01

### Initial Release
- 🎉 Email notifications
- 🎉 Dashboard UI
- 🎉 WordPress core events
