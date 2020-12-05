<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Tests;

use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Attributes\Validate;
use Kafkiansky\DtoInjector\Dto;
use PHPUnit\Framework\TestCase;

final class ExtractorTest extends TestCase
{
    /**
     * @test
     */
    public function extractNoRulesAndMessagesInEmptyDto()
    {
        $extractor = new Extractor();

        $result = $extractor->extract(new EmptyDto());

        self::assertEmpty($result->rules);
        self::assertEmpty($result->messages);
    }

    /**
     * @test
     */
    public function extractRulesAndNoMessagesInDto()
    {
        $extractor = new Extractor();

        $result = $extractor->extract(new JustRulesContainsDto());

        self::assertEquals(
            [
                'name'  => [
                    0 => 'string',
                    1 => 'max:255',
                ],
                'email' => [
                    0 => 'email',
                ],
            ],
            $result->rules,
        );

        self::assertEmpty($result->messages);
    }

    /**
     * @test
     */
    public function extractRulesAndMessagesInDto()
    {
        $extractor = new Extractor();

        $result = $extractor->extract(new RuleAndMessagesContainsDto());

        self::assertEquals(
            [
                'name'  => [
                    0 => 'string',
                    1 => 'max:255',
                ],
            ],
            $result->rules,
        );

        self::assertEquals(
            [
                'name.string' => 'Name must be of type string',
                'name.max'    => 'Name is too length',
            ],
            $result->messages,
        );

        $result = $extractor->extract(new RuleAndMessagesContainsDto2());

        self::assertEquals(
            [
                'name'  => [
                    0 => 'string',
                    1 => 'max:255',
                ],
                'email' => [
                    0 => 'email',
                ],
            ],
            $result->rules,
        );

        self::assertEquals(
            [
                'name.string' => 'Name must be of type string',
                'name.max'    => 'Name is too length',
                'email.email' => 'It\'s not an email',
            ],
            $result->messages,
        );

        $result = $extractor->extract(new RuleAndMessagesContainsDto3());

        self::assertEmpty($result->rules);

        self::assertEquals(
            [
                'name.string' => 'Name must be of type string',
                'name.max'    => 'Name is too length',
                'email.email' => 'It\'s not an email',
            ],
            $result->messages,
        );
    }
}

final class EmptyDto extends Dto
{
}

final class JustRulesContainsDto extends Dto
{
    #[Validate(
        rules: ['string', 'max:255'],
    )]
    public string $name;

    #[Validate(
        rules: ['email'],
    )]
    public string $email;
}

final class RuleAndMessagesContainsDto extends Dto
{
    #[Validate(
        rules: ['string', 'max:255'],
        messages: ['name.string' => 'Name must be of type string', 'name.max' => 'Name is too length'],
    )]
    public string $name;
}

final class RuleAndMessagesContainsDto2 extends Dto
{
    #[Validate(
        rules: ['string', 'max:255'],
        messages: ['name.string' => 'Name must be of type string', 'name.max' => 'Name is too length'],
    )]
    public string $name;

    #[Validate(
        rules: ['email'],
        messages: ['email.email' => 'It\'s not an email'],
    )]
    public string $email;
}

final class RuleAndMessagesContainsDto3 extends Dto
{
    #[Validate(
        messages: ['name.string' => 'Name must be of type string', 'name.max' => 'Name is too length'],
    )]
    public string $name;

    #[Validate(
        messages: ['email.email' => 'It\'s not an email'],
    )]
    public string $email;
}
