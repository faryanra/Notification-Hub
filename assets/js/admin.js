/**
 * Admin JavaScript
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		// Mark as read
		$('.nh-mark-read').on('click', function(e) {
			e.preventDefault();
			const id = $(this).data('id');

			$.post(ajaxurl, {
				action: 'nh_mark_as_read',
				id: id,
				nonce: nhAdmin.nonce
			}, function(response) {
				if (response.success) {
					location.reload();
				}
			});
		});

		// Delete notification
		$('.nh-delete').on('click', function(e) {
			e.preventDefault();
			if (!confirm(nhAdmin.confirmDelete)) {
				return;
			}

			const id = $(this).data('id');

			$.post(ajaxurl, {
				action: 'nh_delete_notification',
				id: id,
				nonce: nhAdmin.nonce
			}, function(response) {
				if (response.success) {
					location.reload();
				}
			});
		});
	});

})(jQuery);
