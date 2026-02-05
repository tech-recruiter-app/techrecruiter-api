<?php

declare(strict_types=1);

namespace App\Values;

use App\Exceptions\Domain\RuleViolationException;
use App\Exceptions\Domain\ValidationFailedException;
use App\Support\Validators;
use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use RuntimeException;
use Traversable;

/**
 * @implements Arrayable<int, string>
 * @implements IteratorAggregate<int, string>
 */
final readonly class TechStack implements Arrayable, IteratorAggregate, JsonSerializable
{
    /**
     * @var list<string> Technology stack used in the job.
     */
    private array $technologies;

    /**
     * @param  array<mixed, mixed>  $stack  Technology stack used in the job.
     */
    public function __construct(array $stack)
    {
        if (! Validators::isStringList($stack)) {
            throw new ValidationFailedException('The technology stack must be a list of strings.');
        }
        if (count($stack) < 3) {
            throw new ValidationFailedException('The technology stack must contain at least three technologies.');
        }
        $invalids = array_filter($stack, fn (string $item): bool => mb_strlen($item) < 2 || mb_strlen($item) > 50);
        if (count($invalids) > 0) {
            throw new RuleViolationException('Invalid technology names found in the stack: '.implode(', ', $invalids));
        }

        $this->technologies = array_map(strtolower(...), $stack);
    }

    /**
     * @param  string  $stack  JSON-encoded list of technologies.
     */
    public static function fromJson(string $stack): self
    {
        if (! is_array($stack = json_decode($stack, true))) {
            throw new LogicException('The json value did not decode to a list.');
        }

        return new self($stack);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->technologies);
    }

    public function toArray(): array
    {
        return $this->technologies;
    }

    public function jsonSerialize(): mixed
    {
        return $this->technologies;
    }

    /**
     * Verifies that all technologies in the stack exists.
     */
    public function verify(): void
    {
        $valid_technologies = $this->validTechnologies();

        $invalids = array_filter(
            $this->technologies,
            fn (string $tech): bool => ! in_array(mb_strtolower($tech), $valid_technologies, true)
        );

        if (count($invalids) > 0) {
            throw new RuleViolationException('The following technologies are not recognized: '.implode(', ', $invalids));
        }
    }

    /**
     * @return string[]
     */
    private function validTechnologies(): array
    {
        if (is_null($list = Storage::json('valid_technologies.json'))) {
            throw new RuntimeException('Failed to load valid technologies from cache.');
        }
        if (! Validators::isStringList($list)) {
            throw new RuntimeException('The valid technologies cache is corrupted.');
        }

        return $list;
    }
}
