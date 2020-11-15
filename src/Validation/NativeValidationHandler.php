<?php

declare(strict_types=1);

namespace Kafkiansky\DtoInjector\Validation;

use Illuminate\Http\Request;

final class NativeValidationHandler extends ValidationHandler
{
    /**
     * {@inheritdoc}
     */
    public function prepareForValidation(Request $request): array
    {
        return $request->request->all() + $request->files->all();
    }
}
