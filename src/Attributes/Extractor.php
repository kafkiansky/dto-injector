<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

use Kafkiansky\DtoInjector\Dto;

final class Extractor
{
    /**
     * @param Dto $dto
     *
     * @return Result
     */
    public function extract(Dto $dto): Result
    {
        /** @psalm-var array<array-key, array<array-key, string>> $rules */
        $rules = [];

        /** @psalm-var array<array-key, string> $messages */
        $messages = [];

        foreach (self::attributesOf($dto) as $name => $attribute) {
            /** @var Validate $validateAttribute */
            $validateAttribute = $attribute->newInstance();

            if ($validateAttribute->rulesExists()) {
                $rules[$name] = $validateAttribute->rules;
            }

            if ($validateAttribute->messagesExists()) {
                $messages[] = $validateAttribute->messages;
            }
        }

        /** @psalm-suppress InvalidArgument */
        return new Result($rules, array_merge(...$messages));
    }

    /**
     * @param Dto $dto
     *
     * @psalm-return array<\ReflectionAttribute>
     * @return array
     */
    private static function attributesOf(Dto $dto): array
    {
        $reflection = new \ReflectionObject($dto);

        $attributes = [];

        foreach ($reflection->getProperties() as $property) {
            $propertyAttributes = $property->getAttributes(Validate::class);

            if (0 !== count($propertyAttributes)) {
                $attributes[$property->getName()] = reset($propertyAttributes);
            }
        }

        return $attributes;
    }
}
