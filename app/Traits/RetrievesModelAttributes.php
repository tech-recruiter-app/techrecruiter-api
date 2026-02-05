<?php

declare(strict_types=1);

namespace App\Traits;

use DomainException;
use InvalidArgumentException;
use OutOfRangeException;

trait RetrievesModelAttributes
{
    /**
     * Retrieves an attribute from a model's attribute list, ensuring it exists and is of the expected type.
     *
     * @param  string  $key  The attribute key to retrieve.
     * @param  array<string, mixed>  $attributes  The model attribute list.
     * @param  string  $type  The expected type of the attribute ('integer' or 'string')
     * @return ($type is 'integer' ? ($nullable is true ? int|null : int) : ($nullable is true ? string|null : string))
     */
    protected function getModelAttribute(
        string $key,
        array $attributes,
        string $type,
        bool $nullable = false
    ): int|string|null {
        if (! array_key_exists($key, $attributes)) {
            throw new OutOfRangeException("Missing required '$key' attribute.");
        }

        $value = $attributes[$key];

        if ($value === null) {
            if ($nullable) {
                return null;
            }
            throw new InvalidArgumentException("The attribute '$key' cannot be null.");
        }

        if ($type === 'integer') {
            if (! is_int($value)) {
                throw new InvalidArgumentException("The value of '$key' attribute must be an integer.");
            }

            return $value;
        }

        if ($type === 'string') {
            if (! is_string($value)) {
                throw new InvalidArgumentException("The value of '$key' attribute must be a string.");
            }

            return $value;
        }

        throw new DomainException("Unexpected type '$type' given.");
    }
}
