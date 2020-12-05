<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector;

use Kafkiansky\DtoInjector\Resolver\DtoResolver;
use Kafkiansky\DtoInjector\Resolver\NativeDtoResolver;

/**
 * @template R of DtoResolver
 */
abstract class Dto
{
    /**
     * Set of more flexible rules such as callable or class-string that cannot be used inside the attribute syntax.
     *
     * @psalm-return array<string, string|non-empty-list<class-string|callable|string>>
     * @return array
     */
    public function with(): array
    {
        return [];
    }

    /**
     * @psalm-return class-string<R>
     * @return string
     */
    public static function resolvedBy(): string
    {
        return NativeDtoResolver::class;
    }
}
