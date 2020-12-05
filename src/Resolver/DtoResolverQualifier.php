<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Http\Request;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Exceptions\DtoResolverException;

final class DtoResolverQualifier
{
    /**
     * @var callable
     */
    private $serviceLocator;

    public function __construct(callable $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param Request $request
     * @param Dto     $dto
     *
     * @throws \Throwable
     *
     * @return Dto
     */
    public function qualify(Request $request, Dto $dto): Dto
    {
        $resolver = ($this->serviceLocator)($dto::resolvedBy());

        if (!($resolver instanceof DtoResolver)) {
            throw DtoResolverException::invalidResolverProvided($dto::resolvedBy());
        }

        return $resolver->resolve($request, $dto);
    }
}
