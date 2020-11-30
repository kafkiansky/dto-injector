<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Tests\Resolver;

use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Resolver\DtoResolver;
use Kafkiansky\DtoInjector\Resolver\NativeDtoResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class NativeDtoResolverTest extends TestCase
{
    /**
     * @var DtoResolver
     */
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $serializer = new Serializer(
            [
                new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter()),
            ],
            [
                new JsonEncoder(),
            ],

        );

        $this->resolver = new NativeDtoResolver(
            $serializer,
            new Factory(new Translator(new ArrayLoader(), 'ru')),
            new Extractor(),
            new class implements \Illuminate\Contracts\Routing\UrlGenerator {

                public function current()
                {
                    // TODO: Implement current() method.
                }

                public function previous($fallback = false)
                {
                    // TODO: Implement previous() method.
                }

                public function to($path, $extra = [], $secure = null)
                {
                    // TODO: Implement to() method.
                }

                public function secure($path, $parameters = [])
                {
                    // TODO: Implement secure() method.
                }

                public function asset($path, $secure = null)
                {
                    // TODO: Implement asset() method.
                }

                public function route($name, $parameters = [], $absolute = true)
                {
                    // TODO: Implement route() method.
                }

                public function action($action, $parameters = [], $absolute = true)
                {
                    // TODO: Implement action() method.
                }

                public function setRootControllerNamespace($rootNamespace)
                {
                    // TODO: Implement setRootControllerNamespace() method.
                }
            },
        );
    }

    /**
     * @test
     */
    public function resolveSuccessWithoutValidationAttributes()
    {
        $userDto = new class extends Dto {
            public string $name;
            public string $email;
        };

        $userDtoMapped = $this->resolver->resolve(
            Request::create('/', 'POST', [
                'name' => 'John Doe',
                'email' => 'blackbox@mail.ru',
            ]),
            $userDto,
        );

        self::assertEquals('John Doe', $userDtoMapped->name);
        self::assertEquals('blackbox@mail.ru', $userDtoMapped->email);
    }
}
