<?php

declare(strict_types=1);

namespace App\Values;

use DomainException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, string>
 */
final readonly class EducationalQualification implements Arrayable, JsonSerializable
{
    private const array VALID_DEGREES = [
        'associate',
        'bachelor',
        'master',
    ];

    private const array ACCEPTABLE_FIELDS = [
        'computer science',
        'software engineering',
        'computer engineering',
        'computer systems',
        'information technology',
        'informatics',
        'information systems',
        'data science',
        'cybersecurity',
        'information security',
    ];

    /**
     * @param  string  $degree  Degree of education
     * @param  string  $field  Field of study
     */
    public function __construct(
        public string $degree,
        public string $field,
    ) {
        if (! in_array(mb_strtolower($degree), self::VALID_DEGREES)) {
            throw new DomainException("The degree [$degree] is not valid.");
        }
        if (! in_array(mb_strtolower($field), self::ACCEPTABLE_FIELDS)) {
            throw new DomainException("The field of study [{$field}] is not acceptable.");
        }
    }

    public function toArray()
    {
        return [
            'degree' => $this->degree,
            'field' => $this->field,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
