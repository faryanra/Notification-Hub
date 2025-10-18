// JavaScript for dynamic updates in Notification Hub
(function($) {
    $(document).ready(function() {
        // Poll for unread count every 30 seconds (simple MVP, replace with AJAX later)
        setInterval(function() {
            $.ajax({
                url: ajaxurl,  // WordPress AJAX endpoint
                type: 'POST',
                data: {
                    action: 'nh_get_unread_count'  // Action for custom AJAX (add later)
                },
                success: function(response) {
                    $('.nh-count').text(response);  // Update badge count
                }
            });
        }, 30000);  // 30 seconds interval
    });
})(jQuery);