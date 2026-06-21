<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Validation\Validator;

final class VerifyUserIsEmployer
{
    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->any()) {
            return;
        }

        if (! auth('api')->user()?->type->isEmployer()) {
            $validator->errors()->add('employer', 'Only employers can register job postings.');
        }
    }
}
