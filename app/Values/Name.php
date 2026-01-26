<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\ValidationFailedException;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Stringable;

/**
 * @implements Arrayable<string, string>
 */
final readonly class Name implements Arrayable, JsonSerializable, Stringable
{
    public function __construct(
        public string $firstname,
        public string $lastname,
    ) {
        $this->validateName($firstname, 1);
        $this->validateName($lastname, 2);
    }

    public function __toString(): string
    {
        return implode(' ', array_map(mb_ucfirst(...), [$this->firstname, $this->lastname]));
    }

    public function withFirstname(string $firstname): self
    {
        $this->validateName($firstname, 1);

        return clone ($this, ['firstname' => $firstname]);
    }

    public function withLastname(string $lastname): self
    {
        $this->validateName($lastname, 2);

        return clone ($this, ['lastname' => $lastname]);
    }

    /**
     * @return array{firstname: string, lastname: string}
     */
    public function toArray(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
        ];
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * @param  int<1,2>  $part  Indicates which part of a full name is being validated
     */
    private function validateName(string $name, int $part): void
    {
        $part = match ($part) {
            1 => 'First name',
            2 => 'Last name',
        };
        if (! preg_match("/^(?=.*\p{L})[\p{L}\s'\-]+$/u", $name)) {
            throw new ValidationFailedException("$part given [$name] contains invalid characters.");
        }
        if (mb_strlen($name) < 3) {
            throw new ValidationFailedException("$part given [$name] must be at least 3 characters long.");
        }
    }
}
