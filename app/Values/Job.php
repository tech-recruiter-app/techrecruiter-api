<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\JobType;
use App\Exceptions\Domain\ValidationFailedException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use ValueError;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class Job implements Arrayable, JsonSerializable
{
    /**
     * @var JobTitle Job title
     */
    public JobTitle $title;

    /**
     * @var JobType Job type.
     */
    public JobType $type;

    /**
     * @param  JobTitle|string  $title  Job title.
     * @param  JobType|string  $type  Job type.
     * @param  Address  $address  Physical address of the place where the job is carried out.
     * @param  JobCompensation  $compensation  Compensation details of the job.
     * @param  TechStack  $stack  Technology stack used in the job.
     * @param  JobDescription|null  $description  Job description.
     */
    public function __construct(
        JobTitle|string $title,
        JobType|string $type,
        public Address $address,
        public JobCompensation $compensation,
        public TechStack $stack,
        public ?JobDescription $description,
    ) {
        $this->title = is_string($title) ? $this->toJobTitle($title) : $title;
        $this->type = is_string($type) ? $this->toJobType($type) : $type;
    }

    public function withTitle(JobTitle|string $title): self
    {
        return clone ($this, [
            'title' => is_string($title) ? $this->toJobTitle($title) : $title,
        ]);
    }

    public function withType(JobType|string $type): self
    {
        return clone ($this, [
            'type' => is_string($type) ? $this->toJobType($type) : $type,
        ]);
    }

    public function withAddress(Address $address): self
    {
        return clone ($this, ['address' => $address]);
    }

    public function withCompensation(JobCompensation $compensation): self
    {
        return clone ($this, ['compensation' => $compensation]);
    }

    public function withStack(TechStack $stack): self
    {
        return clone ($this, ['stack' => $stack]);
    }

    public function withDescription(?JobDescription $description): self
    {
        return clone ($this, ['description' => $description]);
    }

    public function toArray(): array
    {
        return [
            'title' => (string) $this->title,
            'type' => $this->type->value,
            'address' => $this->address->toArray(),
            'compensation' => $this->compensation->toArray(),
            'stack' => $this->stack->toArray(),
            'description' => $this->description?->toArray(),
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    private function toJobTitle(string $value): JobTitle
    {
        return new JobTitle($value);
    }

    private function toJobType(string $value): JobType
    {
        try {
            return JobType::from($value);
        } catch (ValueError) {
            throw new ValidationFailedException("The job type provided is not valid: $value");
        }
    }
}
