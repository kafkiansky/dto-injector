<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Mapper;

use Kafkiansky\DtoInjector\Dto;

interface ObjectPopulator
{
    /**
     * @psalm-param array<array-key, mixed> $data
     * @param array $data
     * @param Dto   $dto
     *
     * @return Dto
     */
    public function populate(array $data, Dto $dto): Dto;
}
