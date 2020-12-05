<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Tests;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Routing\Route;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\ValidationException;
use Kafkiansky\DtoInjector\Attributes\Validate;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Mapper\ObjectPopulator;
use Kafkiansky\DtoInjector\Mapper\PropertyTraverseObjectPopulator;
use Kafkiansky\DtoInjector\Resolver\DtoResolverQualifier;
use PHPUnit\Framework\TestCase;
use Illuminate\Container\Container;

final class FullFledgedUsingFeatureTest extends TestCase
{
    private Container $container;

    private ControllerDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();

        $container->bind(ObjectPopulator::class, function () {
            return new PropertyTraverseObjectPopulator();
        });

        $container->resolving(Dto::class, static function (Dto $dto, Container $container): Dto {
            /** @var Request $request */
            $request = $container->get(Request::class);

            return (new DtoResolverQualifier(fn(string $serviceId): mixed => $container->make($serviceId)))
                ->qualify($request, $dto);
        });

        $container->bind(Factory::class, function () {
            return new \Illuminate\Validation\Factory(new Translator(new ArrayLoader(), 'ru'));
        });

        $container->bind(UrlGenerator::class, function () {
            return new class implements \Illuminate\Contracts\Routing\UrlGenerator {

                public function current()
                {
                }

                public function previous($fallback = false)
                {
                    return 'http://localhost/prev';
                }

                public function to($path, $extra = [], $secure = null)
                {
                }

                public function secure($path, $parameters = [])
                {
                }

                public function asset($path, $secure = null)
                {
                }

                public function route($name, $parameters = [], $absolute = true)
                {
                }

                public function action($action, $parameters = [], $absolute = true)
                {
                }

                public function setRootControllerNamespace($rootNamespace)
                {
                }
            };
        });

        $this->container = $container;

        $this->dispatcher = new ControllerDispatcher($this->container);
    }

    /**
     * @test
     */
    public function resolveWithValidationErrors()
    {
        $invalidRequest = Request::create('/createInvalidUser', 'POST', [
            'email' => 'blackbox',
        ]);

        $this->container->bind(Request::class, fn () => $invalidRequest);

        $routeForInvalidRequest = new Route('POST', '/createInvalidUser', [DtoAwareController::class, 'wait']);

        $routeForInvalidRequest->bind($invalidRequest);

        try {
            $this->dispatcher->dispatch($routeForInvalidRequest, new DtoAwareController, 'wait');
        } catch (ValidationException $exception) {
            self::assertEquals('http://localhost/prev', $exception->redirectTo);
            self::assertEquals(
                [
                    'name' => [
                        0 => 'Name is required',
                    ],
                    'email' => [
                        0 => 'Provided an invalid email',
                    ],
                ],
                $exception->errors()
            );
        }
    }

    /**
     * @test
     */
    public function resolveWithoutValidationErrors()
    {
        $validRequest = Request::create('/createValidUser', 'POST', [
            'email' => 'blackbox@gmail.com',
            'name'  => 'John Doe',
        ]);

        $this->container->bind(Request::class, fn () => $validRequest);

        $routeForValidRequest = new Route('POST', '/createValidUser', [DtoAwareController::class, 'wait']);

        $routeForValidRequest->bind($validRequest);

        /** @var JsonResponse $response */
        $response = $this->dispatcher->dispatch($routeForValidRequest, new DtoAwareController(), 'wait');

        self::assertEquals(
            [
                'name'  => 'John Doe',
                'email' => 'blackbox@gmail.com',
            ],
            $response->getData(true),
        );
    }
}

final class CreateUser extends Dto
{
    #[Validate(
        rules: ['required', 'max:255'],
        messages: ['name.required' => 'Name is required', 'name.max' => 'Name is too long'],
    )]
    public string $name;

    #[Validate(
        rules: ['required', 'email'],
        messages: ['email.required' => 'Email is required', 'email.email' => 'Provided an invalid email'],
    )]
    public string $email;
}

final class DtoAwareController
{
    public function wait(CreateUser $command): JsonResponse
    {
        return new JsonResponse([
            'name'  => $command->name,
            'email' => $command->email,
        ]);
    }
}
