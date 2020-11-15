<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

/**
 * @psalm-immutable
 */
#[\Attribute(flags: \Attribute::TARGET_PROPERTY)]
final class Validate
{
    /**
     * @psalm-param non-empty-list<string> $rules
     * @psalm-param array<string, string>  $messages
     */
    public function __construct(public array $rules, public array $messages = []) {}

    /**
     * @return bool
     */
    public function messagesExists(): bool
    {
        return !empty($this->messages);
    }
}
