<?php

declare(strict_types=1);

namespace App\Validation;

use App\Values\EducationalQualification;
use DomainException;
use Illuminate\Validation\Validator;

final class ValidateEducationalQualification
{
    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->any()) {
            return;
        }

        $educationalQualificationData = $validator->getValue('job_educational_requirement');

        if (! is_array($educationalQualificationData) || ! isset($educationalQualificationData['degree'], $educationalQualificationData['field'])) {
            return;
        }

        try {
            new EducationalQualification(
                $educationalQualificationData['degree'],
                $educationalQualificationData['field']
            );
        } catch (DomainException) {
            $validator->errors()->add(
                'job.description.educationalQualification.degree',
                'The educational qualification degree is invalid'
            );
        }
    }
}
