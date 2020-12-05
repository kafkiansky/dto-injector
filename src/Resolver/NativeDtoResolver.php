<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Resolver;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Kafkiansky\DtoInjector\Attributes\Extractor;
use Kafkiansky\DtoInjector\Mapper\ObjectPopulator;

final class NativeDtoResolver extends DtoResolver
{
    /**
     * @var UrlGenerator
     */
    private UrlGenerator $urlGenerator;

    /**
     * @param ObjectPopulator   $objectPopulator
     * @param ValidationFactory $validator
     * @param Extractor         $extractor
     * @param UrlGenerator      $urlGenerator
     */
    public function __construct(
        ObjectPopulator $objectPopulator,
        ValidationFactory $validator,
        Extractor $extractor,
        UrlGenerator $urlGenerator
    ) {
        parent::__construct($objectPopulator, $validator, $extractor);
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
}
