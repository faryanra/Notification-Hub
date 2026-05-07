<?php
namespace NotificationHub\Integrations\Events\WordPress;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a new comment is posted.
 *
 * @since 1.0.0
 */
final class CommentPosted implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('comment_post', [$this, 'handle'], 10, 3);
    }

    /**
     * @param int $comment_ID
     * @param int|string $comment_approved
     * @param array $commentdata
     */
    public function handle($comment_ID, $comment_approved, $commentdata): void {
        if ((string) $comment_approved === 'spam') {
            return;
        }

        $comment_id = (int) $comment_ID;
        if ($comment_id <= 0) {
            return;
        }

        $comment = get_comment($comment_id);
        $post_id = 0;
        if ($comment && isset($comment->comment_post_ID)) {
            $post_id = (int) $comment->comment_post_ID;
        } elseif (is_array($commentdata) && isset($commentdata['comment_post_ID'])) {
            $post_id = (int) $commentdata['comment_post_ID'];
        }

        $post_title = $post_id > 0 ? get_the_title($post_id) : '';
        if (!is_string($post_title) || $post_title === '') {
            $post_title = __('Untitled Post', 'notification-hub');
        }

        $author = '';
        if ($comment && isset($comment->comment_author)) {
            $author = (string) $comment->comment_author;
        } elseif (is_array($commentdata) && isset($commentdata['comment_author'])) {
            $author = (string) $commentdata['comment_author'];
        }
        $author = trim(wp_strip_all_tags($author));
        if ($author === '') {
            $author = __('Guest', 'notification-hub');
        }

        $status_text = ((string) $comment_approved === '0')
            ? __('awaiting moderation', 'notification-hub')
            : __('published', 'notification-hub');

        $comment_content = '';
        if ($comment && isset($comment->comment_content)) {
            $comment_content = (string) $comment->comment_content;
        } elseif (is_array($commentdata) && isset($commentdata['comment_content'])) {
            $comment_content = (string) $commentdata['comment_content'];
        }

        $comment_excerpt = trim(wp_strip_all_tags($comment_content));
        if ($comment_excerpt !== '') {
            $comment_excerpt = wp_trim_words($comment_excerpt, 20, '...');
        }

        $admin_link = admin_url('comment.php?action=editcomment&c=' . $comment_id);
        $message = sprintf(
            __('A new comment by %1$s was %2$s.', 'notification-hub'),
            $author,
            $status_text
        );
        if ($comment_excerpt !== '') {
            $message = sprintf(
                __('A new comment by %1$s was %2$s. Preview: "%3$s"', 'notification-hub'),
                $author,
                $status_text,
                $comment_excerpt
            );
        }

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('comment_posted')
            ->title(sprintf(__('New comment on "%s"', 'notification-hub'), wp_strip_all_tags($post_title)))
            ->message($message)
            ->status(0)
            ->priority(1)
            ->tags(['comments'])
            ->context([
                'comment_id' => $comment_id,
                'post_id' => $post_id,
                'post_title' => wp_strip_all_tags($post_title),
                'comment_author' => $author,
                'comment_excerpt' => $comment_excerpt,
                'actor' => $author,
                'admin_link' => $admin_link,
                'cta_label' => __('Open Comment', 'notification-hub'),
            ])
            ->link($admin_link)
            ->build();

        $repo->insert($data);
    }
}



