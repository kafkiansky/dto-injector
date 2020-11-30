<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator as ValidationResult;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Dto;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

abstract class DtoResolver
{
    /**
     * @var DenormalizerInterface
     */
    protected DenormalizerInterface $denormalizer;

    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var Extractor
     */
    private Extractor $extractor;

    /**
     * @param DenormalizerInterface $denormalizer
     * @param Validator             $validator
     * @param Extractor             $extractor
     */
    public function __construct(DenormalizerInterface $denormalizer, Validator $validator, Extractor $extractor)
    {
        $this->denormalizer = $denormalizer;
        $this->validator    = $validator;
        $this->extractor    = $extractor;
    }

    /**
     * @param Request $request
     *
     * @psalm-return non-empty-array<string, mixed>
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
     * @psalm-param non-empty-array<string, mixed>
     * @param array $data
     * @param Dto   $dto
     *
     * @throws \Exception
     * @throws \Throwable
     *
     * @return Dto
     */
    abstract protected function doDenormalize(array $data, Dto $dto): Dto;

    /**
     * @param Request $request
     * @param Dto     $dto
     *
     * @throws \Exception
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

        return $this->doDenormalize($mergedData, $dto);
    }
}
