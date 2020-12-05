<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Mapper;

use Kafkiansky\DtoInjector\Dto;

final class SetterMethodUsesObjectPopulator implements ObjectPopulator
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
            $setter = 'set'.ucfirst(toCamel($property));

            if (method_exists($dto, $setter)) {
                $dto->$setter($value);
            }
        }

        return $dto;
    }
}
