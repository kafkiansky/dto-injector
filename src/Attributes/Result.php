<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

/**
 * @psalm-immutable
 */
final class Result
{
    /**
     * @psalm-param array<array-key, non-empty-list<string>> $rules
     * @psalm-param array<array-key, array<string, string>>  $messages
     */
    public function __construct(public array $rules = [], public array $messages = [])
    {
    }
}
