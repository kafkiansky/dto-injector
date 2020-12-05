<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Mapper;

use Kafkiansky\DtoInjector\Dto;

final class CompositeObjectPopulator implements ObjectPopulator
{
    /**
     * @var ObjectPopulator[]
     */
    private array $populators;

    /**
     * @param ObjectPopulator[] $populators
     */
    public function __construct(ObjectPopulator ...$populators)
    {
        $this->populators = $populators;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(array $data, Dto $dto): Dto
    {
        foreach ($this->populators as $populator) {
            $dto = $populator->populate($data, $dto);
        }

        return $dto;
    }
}
