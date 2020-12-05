<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Exceptions;

use Kafkiansky\DtoInjector\Resolver\DtoResolver;

final class DtoResolverException extends \RuntimeException
{
    /**
     * @psalm-pure
     * @param string $invalidResolver
     *
     * @return DtoResolverException
     */
    public static function invalidResolverProvided(string $invalidResolver): DtoResolverException
    {
        return new DtoResolverException(
            sprintf('Wait for resolver which implements %s, but received %s', DtoResolver::class, $invalidResolver)
        );
    }
}
