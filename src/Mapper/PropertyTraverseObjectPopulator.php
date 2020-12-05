<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Mapper;

use Kafkiansky\DtoInjector\Dto;

final class PropertyTraverseObjectPopulator implements ObjectPopulator
{
    /**
     * {@inheritdoc}
     */
    public function populate(array $data, Dto $dto): Dto
    {
        /**
         * @psalm-var string $property
         * @psalm-var mixed  $value
         */
        foreach (traverseRecursive($data) as $property => $value) {
            $camelizeProperty = toCamel($property);

            if (propertyAccessible($dto, $camelizeProperty)) {
                $dto->$camelizeProperty = $value;
            }
        }

        return $dto;
    }
}
