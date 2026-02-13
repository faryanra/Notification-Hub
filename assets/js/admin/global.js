/**
 * Notification Hub - Admin Global Scripts
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

(function($) {
	'use strict';

	// Add Hook Button
	$('#nh-add-hook').on('click', function(e) {
		e.preventDefault();
		$('#nh-modal-title').text('Add Custom Hook');
		$('#nh-hook-form')[0].reset();
		$('#nh-hook-id').val('');
		$('#nh-hook-modal').fadeIn();
	});

	// Edit Hook Button
	$('.nh-edit-hook').on('click', function() {
		var hookId = $(this).data('id');
		$('#nh-modal-title').text('Edit Custom Hook');
		$('#nh-hook-id').val(hookId);
		
		// TODO: Load hook data via AJAX
		
		$('#nh-hook-modal').fadeIn();
	});

	// Close Modal
	$('.nh-modal-close').on('click', function() {
		$('#nh-hook-modal').fadeOut();
	});

	// Click outside modal to close
	$(window).on('click', function(e) {
		if ($(e.target).hasClass('nh-modal')) {
			$('.nh-modal').fadeOut();
		}
	});

	// Submit Hook Form
	$('#nh-hook-form').on('submit', function(e) {
		e.preventDefault();

		var hookId = $('#nh-hook-id').val();
		var action = hookId ? 'nh_update_hook' : 'nh_create_hook';

		var data = {
			action: action,
			nonce: nhData.nonce,
			id: hookId,
			hook_name: $('#nh-hook-name').val(),
			title: $('#nh-hook-title').val(),
			message: $('#nh-hook-message').val()
		};

		$.post(nhData.ajaxUrl, data, function(response) {
			if (response.success) {
				alert(response.data.message);
				location.reload();
			} else {
				alert(response.data.message || 'An error occurred');
			}
		});
	});

	// Delete Hook Button
	$('.nh-delete-hook').on('click', function() {
		if (!confirm('Are you sure you want to delete this hook?')) {
			return;
		}

		var hookId = $(this).data('id');

		$.post(nhData.ajaxUrl, {
			action: 'nh_delete_hook',
			nonce: nhData.nonce,
			id: hookId
		}, function(response) {
			if (response.success) {
				alert(response.data.message);
				location.reload();
			} else {
				alert(response.data.message || 'An error occurred');
			}
		});
	});

	// Test Hook Button
	$('.nh-test-hook').on('click', function() {
		var hookId = $(this).data('id');
		var $button = $(this);
		var originalText = $button.text();

		$button.prop('disabled', true).text('Testing...');

		$.post(nhData.ajaxUrl, {
			action: 'nh_test_hook',
			nonce: nhData.nonce,
			id: hookId
		}, function(response) {
			$button.prop('disabled', false).text(originalText);
			
			if (response.success) {
				alert(response.data.message);
			} else {
				alert(response.data.message || 'An error occurred');
			}
		});
	});

})(jQuery);
