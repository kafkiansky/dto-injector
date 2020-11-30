<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Dto;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class NativeDtoResolver extends DtoResolver
{
    /**
     * @var UrlGenerator
     */
    private UrlGenerator $urlGenerator;

    /**
     * @param DenormalizerInterface $denormalizer
     * @param Factory               $validator
     * @param Extractor             $extractor
     * @param UrlGenerator          $urlGenerator
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        Factory $validator,
        Extractor $extractor,
        UrlGenerator $urlGenerator
    ) {
        parent::__construct($denormalizer, $validator, $extractor);
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForResolving(Request $request): array
    {
        return $request->files->all() + $request->request->all();
    }

    /**
     * {@inheritdoc}
     */
    protected function onValidationFailed(Validator $validator): \Exception
    {
        return (new ValidationException($validator))
            ->errorBag('default')
            ->redirectTo($this->urlGenerator->previous());
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize(array $data, Dto $dto): Dto
    {
        /** @var Dto $mappedDto */
        $mappedDto = $this->denormalizer->denormalize($data, get_class($dto), 'array');

        return $mappedDto;
    }
}
