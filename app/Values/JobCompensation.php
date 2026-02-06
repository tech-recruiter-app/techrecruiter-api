<?php

declare(strict_types=1);

namespace App\Values;

use App\Enums\JobCompensationCurrency;
use App\Enums\JobCompensationType;
use App\Exceptions\Domain\RuleViolationException;
use App\Exceptions\Domain\ValidationFailedException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use ValueError;

/**
 * @implements Arrayable<string, string|int>
 */
final readonly class JobCompensation implements Arrayable, JsonSerializable
{
    /**
     * @var JobCompensationCurrency Currency of the compensation.
     */
    public JobCompensationCurrency $currency;

    /**
     * @var JobCompensationType Type of compensation.
     */
    public JobCompensationType $type;

    /**
     * @param  int  $minimum  Minimum compensation amount.
     * @param  int  $maximum  Maximum compensation amount.
     * @param  JobCompensationCurrency|string  $currency  Currency of the compensation.
     * @param  JobCompensationType|string  $type  Type of compensation.
     *
     * @throws RuleViolationException
     */
    public function __construct(
        public int $minimum,
        public int $maximum,
        JobCompensationCurrency|string $currency,
        JobCompensationType|string $type
    ) {
        $this->currency = is_string($currency) ? $this->toCompensationCurrency($currency) : $currency;
        $this->type = is_string($type) ? $this->toCompensationType($type) : $type;

        $this->validate($minimum, $maximum, $this->type);
    }

    /**
     * @throws RuleViolationException
     */
    public function withMinimumCompensation(int $amount): self
    {
        $this->validate($amount, $this->maximum, $this->type);

        return clone ($this, ['minimum' => $amount]);
    }

    public function withMaximumCompensation(int $amount): self
    {
        $this->validate($this->minimum, $amount, $this->type);

        return clone ($this, ['maximum' => $amount]);
    }

    public function withCurrency(JobCompensationCurrency|string $currency): self
    {
        return clone ($this, [
            'currency' => is_string($currency) ? $this->toCompensationCurrency($currency) : $currency,
        ]);
    }

    /**
     * @return array{minimum: int, maximum: int, currency: string, type: string}
     */
    public function toArray(): array
    {
        return [
            'minimum' => $this->minimum,
            'maximum' => $this->maximum,
            'currency' => $this->currency->value,
            'type' => $this->type->value,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    private function toCompensationCurrency(string $value): JobCompensationCurrency
    {
        try {
            return JobCompensationCurrency::from($value);
        } catch (ValueError) {
            throw new ValidationFailedException("The currency provided is not supported: $value");
        }
    }

    private function toCompensationType(string $value): JobCompensationType
    {
        try {
            return JobCompensationType::from($value);
        } catch (ValueError) {
            throw new ValidationFailedException("The compensation type provided is not valid: $value");
        }
    }

    private function validate(int $minimum, int $maximum, JobCompensationType $type): void
    {
        if ($minimum > $maximum) {
            throw new RuleViolationException("Minimum compensation [$minimum] must be less than maximum compensation [$maximum].");
        }
        if ($maximum < $minimum) {
            throw new RuleViolationException("Maximum compensation [$maximum] must be greater than the minimum compensation [$minimum].");
        }
        if ($minimum === $maximum) {
            throw new RuleViolationException('Maximum and minimum compensation should not be equal.');
        }

        if ($type->isSalary() && $minimum < 20000) {
            throw new RuleViolationException("Minimum salary given [$minimum] must not be less than 20,000");
        }
        if ($type->isHourly() && $minimum < 20) {
            throw new RuleViolationException("Minimum hourly pay given [$minimum] must not be less than 20.");
        }

        $range = $maximum - $minimum;
        if ($type->isSalary() && $range < 10000) {
            throw new RuleViolationException('The salary range difference must not be lower than 10,000');
        }
        if ($type->isHourly() && $range < 10) {
            throw new RuleViolationException('The hourly pay range difference must not be lower than 10');
        }
    }
}
