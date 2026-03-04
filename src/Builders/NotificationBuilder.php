<?php

namespace NotificationHub\Builders;

/**
 * Build a normalized notification payload for persistence.
 *
 * This is intentionally lightweight and can be extended later.
 *
 * @since 1.7.2
 */
final class NotificationBuilder {
    /**
     * @var array<string,mixed>
     */
    private $data = [];

    public static function make(): self {
        return new self();
    }

    public function source(string $source): self {
        $this->data['source'] = $source;
        return $this;
    }

    public function type(string $type): self {
        $this->data['type'] = $type;
        return $this;
    }

    public function title(string $title): self {
        $this->data['title'] = $title;
        return $this;
    }

    public function message(string $message): self {
        $this->data['message'] = $message;
        return $this;
    }

    public function status(int $status): self {
        $this->data['status'] = $status;
        return $this;
    }

    public function priority(int $priority): self {
        $this->data['priority'] = $priority;
        return $this;
    }

    /**
     * @param array<int,string> $tags
     */
    public function tags(array $tags): self {
        $this->data['tags'] = wp_json_encode(array_values($tags));
        return $this;
    }

    /**
     * @param array<string,mixed> $context
     */
    public function context(array $context): self {
        $this->data['context'] = wp_json_encode($context);
        return $this;
    }

    public function link(string $link): self {
        $this->data['link'] = $link;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function build(): array {
        $defaults = [
            'source'   => '',
            'type'     => '',
            'title'    => '',
            'message'  => '',
            'status'   => 0,
            'priority' => 1,
            'tags'     => wp_json_encode([]),
            'context'  => wp_json_encode([]),
            'link'     => '',
        ];

        return array_merge($defaults, $this->data);
    }
}
