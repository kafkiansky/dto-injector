<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Exceptions\DtoResolverException;

final class CompositeDtoResolver
{
    /**
     * @var Container
     */
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Dto     $dto
     *
     * @throws \Throwable
     *
     * @return Dto
     */
    public function resolve(Request $request, Dto $dto): Dto
    {
        $resolver = $this->container->get($dto::resolvedBy());

        if (!($resolver instanceof DtoResolver)) {
            throw DtoResolverException::invalidResolverProvided($dto::resolvedBy());
        }

        return $resolver->resolve($request, $dto);
    }
}
