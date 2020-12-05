<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Tests\Resolver;

use Illuminate\Http\Request;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Attributes\Validate;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Mapper\PropertyTraverseObjectPopulator;
use Kafkiansky\DtoInjector\Resolver\DtoResolver;
use Kafkiansky\DtoInjector\Resolver\NativeDtoResolver;
use PHPUnit\Framework\TestCase;

final class NativeDtoResolverTest extends TestCase
{
    /**
     * @var DtoResolver
     */
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new NativeDtoResolver(
            new PropertyTraverseObjectPopulator(),
            new Factory(new Translator(new ArrayLoader(), 'ru')),
            new Extractor(),
            new class implements \Illuminate\Contracts\Routing\UrlGenerator {

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

        $userDtoWithPrivateFields = new class extends Dto {
            public string $name;
            public string $email;
            private ?string $id = null;

            public function getId()
            {
                return $this->id;
            }
        };

        $userDtoMapped = $this->resolver->resolve(
            Request::create('/', 'POST', [
                'name' => 'John Doe',
                'email' => 'blackbox@mail.ru',
                'id'    => 1,
            ]),
            $userDto,
        );

        self::assertEquals('John Doe', $userDtoMapped->name);
        self::assertEquals('blackbox@mail.ru', $userDtoMapped->email);
        self::assertNull($userDtoWithPrivateFields->getId());
    }

    /**
     * @test
     */
    public function resolveWithValidationAttributes()
    {
        try {
            /** @var CreateUser $mappedDto */
            $mappedDto = $this->resolver->resolve(
                Request::create('/', 'POST', [
                    'name'  => 'X',
                    'email' => 'broken',
                    'phone' => '043204230423',
                ]),
                new CreateUser()
            );
        } catch (ValidationException $exception) {
            self::assertEquals('http://localhost/prev', $exception->redirectTo);
            self::assertEquals(
                [
                    'name' => [
                        0 => 'Name is too short',
                    ],
                    'email' => [
                        0 => 'Is email?',
                    ],
                    'phone' => [
                        0 => 'Accepts just russian phone numbers, sorry',
                    ]
                ],
                $exception->errors()
            );
            self::assertEquals('default', $exception->errorBag);
        }

        try {
            /** @var CreateUser $mappedDto */
            $this->resolver->resolve(
                Request::create('/', 'POST', [
                    'phone' => '+79096510000',
                ]),
                new CreateUser()
            );
        } catch (ValidationException $exception) {
            self::assertEquals('http://localhost/prev', $exception->redirectTo);
            self::assertEquals(
                [
                    'name' => [
                        0 => 'Name is required',
                    ],
                    'email' => [
                        0 => 'Email is required',
                    ],
                ],
                $exception->errors()
            );
            self::assertEquals('default', $exception->errorBag);
        }

        /** @var CreateUser $mappedDto */
        $mappedDto = $this->resolver->resolve(
            Request::create('/', 'POST', [
                'name'  => 'John',
                'email' => 'blackbox@gmail.com',
                'phone' => '+79096510000',
            ]),
            new CreateUser()
        );

        self::assertEquals('John', $mappedDto->name);
        self::assertEquals('blackbox@gmail.com', $mappedDto->email);
        self::assertEquals('+79096510000', $mappedDto->phone);
    }

    /**
     * @test
     */
    public function resolveUsingExtendedValidation()
    {
        $complexUser = $this->resolver->resolve(
            Request::create('/', 'POST', [
                'name'  => 'John',
                'email' => 'blackbox237@gmail.com',
                'phone' => '+79096510001',
            ]),
            new CreateComplexUser()
        );

        self::assertEquals('John', $complexUser->name);
        self::assertEquals('blackbox237@gmail.com', $complexUser->email);
        self::assertEquals('+79096510001', $complexUser->phone);

        try {
            $this->resolver->resolve(
                Request::create('/', 'POST', [
                    'name'  => 'John',
                    'email' => 'blackbox@gmail.com',
                    'phone' => '+79096510000',
                ]),
                new CreateComplexUser()
            );
        } catch (ValidationException $exception) {
            self::assertEquals('http://localhost/prev', $exception->redirectTo);
            self::assertEquals(
                [
                    'email' => [
                        0 => 'email is invalid.',
                    ],
                    'phone' => [
                        0 => 'Phone contains disallowed phone numbers',
                    ],
                ],
                $exception->errors(),
            );
        }

        try {
            $this->resolver->resolve(
                Request::create('/', 'POST', [
                    'email' => 'blackbox@gmail.com',
                    'phone' => '+79096510000',
                ]),
                new CreateComplexUser()
            );
        } catch (ValidationException $exception) {
            self::assertEquals('http://localhost/prev', $exception->redirectTo);
            self::assertEquals(
                [
                    'name'  => [
                        0 => 'Name is required',
                    ],
                    'email' => [
                        0 => 'email is invalid.',
                    ],
                    'phone' => [
                        0 => 'Phone contains disallowed phone numbers',
                    ],
                ],
                $exception->errors(),
            );
        }
    }
}

final class CreateUser extends Dto
{
    #[Validate(
        rules: ['required', 'min:3'],
        messages: ['name.required' => 'Name is required', 'name.min' => 'Name is too short'],
    )]
    public string $name;

    #[Validate(
        rules: ['required', 'email'],
        messages: ['email.required' => 'Email is required', 'email.email' => 'Is email?'],
    )]
    public string $email;

    #[Validate(
        rules: ['required', 'regex:/^\+79\d{9}$/'],
        messages: ['phone.required' => 'Phone is required', 'phone.regex' => 'Accepts just russian phone numbers, sorry'],
    )]
    public string $phone;
}

final class CreateComplexUser extends Dto
{
    #[Validate(
        rules: ['required', 'min:3'],
        messages: ['name.required' => 'Name is required', 'name.min' => 'Name is too short'],
    )]
    public string $name;

    #[Validate(
        rules: ['required', 'email'],
        messages: ['email.required' => 'Email is required', 'email.email' => 'Is email?'],
    )]
    public string $email;

    #[Validate(
        rules: ['required', 'regex:/^\+79\d{9}$/'],
        messages: [
            'phone.required' => 'Phone is required',
            'phone.regex' => 'Accepts just russian phone numbers, sorry',
            'phone.not_in' => 'Phone contains disallowed phone numbers',
        ],
    )]
    public string $phone;

    /**
     * {@inheritdoc}
     */
    public function with(): array
    {
        return [
            'phone' => Rule::notIn(['+79096510000']),
            'email' => function ($attribute, $value, $fail) {
                if ($value === 'blackbox@gmail.com') {
                    $fail($attribute.' is invalid.');
                }
            },
        ];
    }
}
