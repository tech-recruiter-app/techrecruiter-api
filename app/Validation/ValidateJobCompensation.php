<?php

declare(strict_types=1);

namespace App\Validation;

use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Exceptions\Domain\InvalidJobCompensationException;
use App\Values\JobCompensation;
use Illuminate\Validation\Validator;

final class ValidateJobCompensation
{
    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->any()) {
            return;
        }

        $compensationData = $validator->getValue('job_compensation');

        if (! is_array($compensationData) || ! isset($compensationData['minimum'], $compensationData['maximum'], $compensationData['currency'], $compensationData['type'])) {
            return;
        }

        try {
            new JobCompensation(
                $compensationData['minimum'],
                $compensationData['maximum'],
                JobCompensationCurrency::from($compensationData['currency']),
                JobCompensationType::from($compensationData['type']),
            );
        } catch (InvalidJobCompensationException $e) {
            $validator->errors()->addIf(
                $e->getCode() === InvalidJobCompensationException::INVALID_MINIMUM_COMPENSATION,
                'job_compensation.minimum',
                $e->getMessage()
            );
            $validator->errors()->addIf(
                $e->getCode() === InvalidJobCompensationException::INVALID_MAXIMUM_COMPENSATION || $e->getCode() === InvalidJobCompensationException::INVALID_COMPENSATION_RANGE,
                'job_compensation.maximum',
                $e->getMessage()
            );
        }
    }
}
