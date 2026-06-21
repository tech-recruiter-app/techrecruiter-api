<?php

declare(strict_types=1);

namespace App\Validation;

use App\Exceptions\Domain\InvalidTechnologiesException;
use App\Values\TechStack;
use Illuminate\Validation\Validator;
use LengthException;

final class ValidateTechStack
{
    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->any()) {
            return;
        }

        $techStackData = $validator->getValue('job_stack');

        if (! is_array($techStackData)) {
            return;
        }

        try {
            new TechStack($techStackData)->verify();
        } catch (LengthException $e) {
            $validator->errors()->add('job.stack', $e->getMessage());
        } catch (InvalidTechnologiesException $e) {
            foreach ($e->invalidTechnologies as $technology) {
                $index = array_find_key($e->stack, fn (string $item): bool => $item === $technology);
                $validator->errors()->add(
                    "job.stack.$index",
                    "The technology [$technology] is not recognized as a valid technology."
                );
            }
        }
    }
}
