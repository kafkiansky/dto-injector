<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

use Kafkiansky\DtoInjector\Validation\ValidationHandler;

#[\Attribute(flags: \Attribute::TARGET_CLASS)]
final class ValidateBy
{
    /**
     * @psalm-param class-string<ValidationHandler> $class
     */
    public function __construct(public string $class)
    {
    }
}
