<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector;

use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Kafkiansky\DtoInjector\Resolver\CompositeDtoResolver;

final class DtoInjectorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->resolving(Dto::class, static function (Dto $dto, Container $container): Dto {
            return (new CompositeDtoResolver($container))->resolve(
                $container->get(Request::class),
                $dto,
            );
        });
    }
}
