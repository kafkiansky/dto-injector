<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Attributes;

use Kafkiansky\DtoInjector\Dto;

final class Extractor
{
    /**
     * @param Dto $dto
     *
     * @psalm-return Result
     * @return Result
     */
    public function extract(Dto $dto): Result
    {
        $rules    = [];
        $messages = [];

        foreach (self::attributesOf($dto) as $name => $attribute) {
            /** @var Validate $validateAttribute */
            $validateAttribute = $attribute->newInstance();

            $rules[$name] = $validateAttribute->rules;

            if ($validateAttribute->messagesExists()) {
                $messages[key($validateAttribute->messages)] = reset($validateAttribute->messages);
            }
        }

        return new Result($rules, $messages);
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

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyAttributes = $property->getAttributes(Validate::class);

            if (0 !== count($propertyAttributes)) {
                $attributes[$property->getName()] = reset($propertyAttributes);
            }
        }

        return $attributes;
    }
}
