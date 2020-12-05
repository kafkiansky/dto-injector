<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Kafkiansky\DtoInjector\Mapper\ObjectPopulator;
use Kafkiansky\DtoInjector\Mapper\PropertyTraverseObjectPopulator;
use Kafkiansky\DtoInjector\Resolver\DtoResolverQualifier;
use Illuminate\Contracts\Foundation\Application;

final class DtoInjectorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->bind(ObjectPopulator::class, PropertyTraverseObjectPopulator::class);

        $this->app->resolving(Dto::class, static function (Dto $dto, Application $container): Dto {
            /** @var Request $request */
            $request = $container->make(Request::class);

            return (new DtoResolverQualifier(fn(string $serviceId): mixed => $container->make($serviceId)))
                ->qualify(clone $request, $dto);
        });
    }
}
