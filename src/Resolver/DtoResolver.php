<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator as ValidationResult;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Dto;
use Kafkiansky\DtoInjector\Mapper\ObjectPopulator;

abstract class DtoResolver
{
    /**
     * @var ObjectPopulator
     */
    protected ObjectPopulator $objectPopulator;

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var Extractor
     */
    private Extractor $extractor;

    /**
     * @param ObjectPopulator $objectPopulator
     * @param Validator       $validator
     * @param Extractor       $extractor
     */
    public function __construct(ObjectPopulator $objectPopulator, Validator $validator, Extractor $extractor)
    {
        $this->objectPopulator = $objectPopulator;
        $this->validator       = $validator;
        $this->extractor       = $extractor;
    }

    /**
     * @param Request $request
     *
     * @psalm-return array<array-key, mixed>
     * @return array
     */
    abstract protected function prepareForResolving(Request $request): array;

    /**
     * @param ValidationResult $validator
     *
     * @return \Exception
     */
    abstract protected function onValidationFailed(ValidationResult $validator): \Exception;

    /**
     * @psalm-param array<array-key, mixed> $data
     * @param array $data
     * @param Dto   $dto
     *
     * @return Dto
     */
    protected function doMap(array $data, Dto $dto): Dto
    {
        return $this->objectPopulator->populate($data, $dto);
    }

    /**
     * @param Request $request
     * @param Dto     $dto
     *
     * @throws \Throwable
     *
     * @return Dto
     */
    final public function resolve(Request $request, Dto $dto): Dto
    {
        $mergedData = $this->prepareForResolving($request);

        $extraction = $this->extractor->extract($dto);

        $validationRules = array_merge($extraction->rules, $dto->with());

        $validation = $this->validator->make($mergedData, $validationRules, $extraction->messages);

        if ($validation->fails()) {
            throw $this->onValidationFailed($validation);
        }

        return $this->doMap($mergedData, $dto);
    }
}
