<?php
namespace NotificationHub\Builders;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helps build channel payloads (email/telegram/slack) consistently.
 *
 * @since 1.0.0
 */
final class PayloadBuilder {
    /**
     * @var array<string,mixed>
     */
    private $payload = [];

    public static function make(): self {
        return new self();
    }

    public function title(string $title): self {
        $this->payload['title'] = $title;
        return $this;
    }

    public function summary(string $summary): self {
        $this->payload['summary'] = $summary;
        return $this;
    }

    public function source(string $source): self {
        $this->payload['source'] = $source;
        return $this;
    }

    public function type(string $type): self {
        $this->payload['type'] = $type;
        return $this;
    }

    /**
     * @param array<string,mixed> $context
     */
    public function context(array $context): self {
        $this->payload['context'] = $context;
        return $this;
    }

    public function link(string $link): self {
        $this->payload['link'] = $link;
        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function build(): array {
        $defaults = [
            'title'   => '',
            'summary' => '',
            'source'  => '',
            'type'    => '',
            'context' => [],
            'link'    => '',
            'no_log'  => true,
        ];

        return array_merge($defaults, $this->payload);
    }
}

