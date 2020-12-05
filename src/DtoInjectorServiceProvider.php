<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Kafkiansky\DtoInjector\Resolver\DtoResolverQualifier;
use Illuminate\Contracts\Foundation\Application;

final class DtoInjectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving(Dto::class, static function (Dto $dto, Application $container): Dto {
            /** @var Request $request */
            $request = $container->make(Request::class);

            return (new DtoResolverQualifier(fn(string $serviceId): mixed => $container->make($serviceId)))
                ->qualify(clone $request, $dto);
        });
    }
}
