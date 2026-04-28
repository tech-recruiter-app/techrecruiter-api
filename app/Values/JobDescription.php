<?php

declare(strict_types=1);

namespace App\Values;

use App\Support\Validators;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class JobDescription implements Arrayable, JsonSerializable
{
    /**
     * @var non-empty-list<non-empty-string> List of responsibilities of the job.
     */
    public array $responsibilities;

    /**
     * @var non-empty-list<non-empty-string> List of requirements for the job.
     */
    public array $requirements;

    /**
     * @var non-empty-list<non-empty-string> List of benefits of the job.
     */
    public array $benefits;

    /**
     * @var CarbonImmutable|null Timestamp of when the job begins.
     */
    public ?CarbonImmutable $startDate;

    /**
     * @param  array<mixed, mixed>  $responsibilities
     * @param  array<mixed, mixed>  $requirements
     * @param  array<mixed, mixed>  $benefits
     * @param  EducationalQualification|null  $educationalQualification  Educational qualification required for the job.
     */
    public function __construct(
        array $responsibilities,
        array $requirements,
        array $benefits,
        public ?EducationalQualification $educationalQualification = null,
        ?string $startDate = null
    ) {
        $this->responsibilities = $this->validateStringList($responsibilities, 'responsibility');
        $this->requirements = $this->validateStringList($requirements, 'requirement');
        $this->benefits = $this->validateStringList($benefits, 'benefit');
        $this->startDate = isset($startDate) ? CarbonImmutable::parse($startDate) : null;
    }

    /**
     * @param  string  $description  JSON-encoded job description.
     */
    public static function fromJson(string $description): self
    {
        if (is_array($parsedDescription = json_decode($description, true)) === false) {
            throw new LogicException('Invalid job description: not a valid JSON.');
        }

        /** @var mixed|null $educationalQualification */
        $educationalQualification = data_get($parsedDescription, 'educationalQualification');

        if (isset($educationalQualification)) {
            if (! is_array($educationalQualification) || ! isset($educationalQualification['degree'], $educationalQualification['field']) || ! is_string($educationalQualification['degree']) || ! is_string($educationalQualification['field'])) {
                throw new InvalidArgumentException('Invalid job description: educational qualification must be an object with degree and field attributes.');
            }

            $educationalQualification = new EducationalQualification(
                $educationalQualification['degree'],
                $educationalQualification['field']
            );
        }

        return new self(
            self::getList($parsedDescription, 'responsibilities'),
            self::getList($parsedDescription, 'requirements'),
            self::getList($parsedDescription, 'benefits'),
            $educationalQualification,
            data_get($parsedDescription, 'startDate'),
        );
    }

    public function toArray(): array
    {
        return [
            'responsibilities' => $this->responsibilities,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'educational_qualification' => $this->educationalQualification?->toArray(),
            'startDate' => $this->startDate?->toIso8601String(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param  array<mixed, mixed>  $requirements
     */
    public function withRequirements(array $requirements): self
    {
        return clone ($this, [
            'requirements' => $this->validateStringList($requirements, 'requirement'),
        ]);
    }

    /**
     * @param  array<mixed, mixed>  $responsibilities
     */
    public function withResponsibilities(array $responsibilities): self
    {
        return clone ($this, [
            'responsibilities' => $this->validateStringList($responsibilities, 'responsibility'),
        ]);
    }

    /**
     * @param  array<mixed, mixed>  $benefits
     */
    public function withBenefits(array $benefits): self
    {
        return clone ($this, [
            'benefits' => $this->validateStringList($benefits, 'benefit'),
        ]);
    }

    public function withEducationalQualification(?EducationalQualification $educationalQualification): self
    {
        return clone ($this, ['educationalQualification' => $educationalQualification]);
    }

    public function withStartDate(?string $startDate): self
    {
        return clone ($this, [
            'startDate' => isset($startDate) ? CarbonImmutable::parse($startDate) : null,
        ]);
    }

    /**
     * Retrieves a list from the job description.
     *
     * @param  array<mixed, mixed>  $description  The parsed description.
     * @param  string  $key  The key to extract the list from.
     * @return list<string>
     */
    private static function getList(array $description, string $key): array
    {
        if (! (isset($description[$key]) && is_array($list = $description[$key]))) {
            throw new InvalidArgumentException("Invalid job description: $key are missing.");
        }
        if (! Validators::isStringList($list)) {
            $item = str_ends_with($key, 'ies') ? str_replace('ies', 'y', $key) : mb_rtrim($key, 's');
            throw new InvalidArgumentException("Invalid job description: each $item must be strings.");
        }

        return $list;
    }

    /**
     * Validates an array as list of strings.
     *
     * @param  array<mixed, mixed>  $array  Array to validate.
     * @param  string  $item  The name of the list items for error messages.
     * @return non-empty-list<non-empty-string> The validated list.
     */
    private function validateStringList(array $array, string $item): array
    {
        if (! Validators::isStringList($array)) {
            throw new InvalidArgumentException("Invalid job description: each $item must be strings.");
        }

        return $array;
    }
}
