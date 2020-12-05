<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

/**
 * @psalm-immutable
 */
final class Result
{
    /**
     * @psalm-param array<array-key, array<array-key, string>> $rules
     * @psalm-param array<array-key, string>                   $messages
     */
    public function __construct(public array $rules = [], public array $messages = [])
    {
    }
}
