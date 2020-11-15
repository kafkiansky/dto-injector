<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Validation;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator as ValidationResult;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\ValidationException;

abstract class ValidationHandler
{
    /**
     * @param Request $request
     *
     * @psalm-return array<string, mixed>
     */
    abstract public function prepareForValidation(Request $request): array;

    /**
     * @param ValidationResult $validator
     * @param Redirector       $redirector
     *
     * @throws ValidationException
     */
    public function onValidationFailed(ValidationResult $validator, Redirector $redirector): void
    {
        $url = $redirector->getUrlGenerator();

        throw (new ValidationException($validator))->redirectTo($url->previous());
    }
}
