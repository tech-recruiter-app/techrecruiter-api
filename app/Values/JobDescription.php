<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\RuleViolationException;
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
     * Educational qualification required for the job.
     */
    public ?string $education;

    /**
     * @var CarbonImmutable|null Timestamp of when the job begins.
     */
    public ?CarbonImmutable $startDate;

    /**
     * @param  array<mixed, mixed>  $responsibilities
     * @param  array<mixed, mixed>  $requirements
     * @param  array<mixed, mixed>  $benefits
     */
    public function __construct(
        array $responsibilities,
        array $requirements,
        array $benefits,
        ?string $education = null,
        ?string $startDate = null
    ) {
        $this->responsibilities = $this->validateStringList($responsibilities, 'responsibility');
        $this->requirements = $this->validateStringList($requirements, 'requirement');
        $this->benefits = $this->validateStringList($benefits, 'benefit');
        $this->education = isset($education) ? $this->validateDegree($education) : null;
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

        return new self(
            self::getList($parsedDescription, 'responsibilities'),
            self::getList($parsedDescription, 'requirements'),
            self::getList($parsedDescription, 'benefits'),
            self::getAttribute($parsedDescription, 'education'),
            self::getAttribute($parsedDescription, 'startDate'),
        );
    }

    public function toArray(): array
    {
        return [
            'responsibilities' => $this->responsibilities,
            'requirements' => $this->requirements,
            'benefits' => $this->benefits,
            'education' => $this->education,
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

    public function withEducation(?string $education): self
    {
        return clone ($this, [
            'education' => isset($education) ? $this->validateDegree($education) : null,
        ]);
    }

    public function withStartDate(?string $startDate): self
    {
        return clone ($this, [
            'startDate' => isset($startDate) ? CarbonImmutable::parse($startDate) : null,
        ]);
    }

    /**
     * Retrieves an attribute from the job description.
     *
     * @param  array<mixed, mixed>  $description  Parsed description.
     * @param  string  $key  The key to extract the attribute from.
     */
    private static function getAttribute(array $description, string $key): ?string
    {
        $attribute = $description[$key] ?? null;

        if (isset($attribute) && (! is_string($attribute) || $attribute === '')) {
            throw new InvalidArgumentException("Invalid job description: $key must be a string.");
        }

        return $attribute;
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

    /**
     * Validates a value as an education degree.
     */
    private function validateDegree(string $value): string
    {
        if (! in_array($value, ["Bachelor's", 'College', "Master's", 'PhD'], true)) {
            throw new RuleViolationException("Invalid degree given: $value");
        }

        return $value;
    }
}
