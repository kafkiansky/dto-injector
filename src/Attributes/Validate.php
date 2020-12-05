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
     * @psalm-param array<array-key, array<array-key, string>> $rules
     * @psalm-param array<array-key, string>                   $messages
     */
    public function __construct(public array $rules = [], public array $messages = [])
    {
    }

    /**
     * @return bool
     */
    public function messagesExists(): bool
    {
        return !empty($this->messages);
    }

    /**
     * @return bool
     */
    public function rulesExists(): bool
    {
        return !empty($this->rules);
    }
}
