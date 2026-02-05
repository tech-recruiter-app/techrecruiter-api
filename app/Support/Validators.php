<?php

declare(strict_types=1);

namespace App\Support;

final class Validators
{
    /**
     * Determines if an array is a list containing only strings.
     *
     * @param  array<mixed, mixed>  $array
     *
     * @phpstan-assert-if-true non-empty-list<non-empty-string> $array
     */
    public static function isStringList(array $array): bool
    {
        return $array !== [] && array_is_list($array) && array_all($array, fn ($value): bool => is_string($value) && $value !== '');
    }
}
