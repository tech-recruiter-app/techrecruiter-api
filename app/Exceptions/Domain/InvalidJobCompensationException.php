<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use RuntimeException;

final class InvalidJobCompensationException extends RuntimeException
{
    public const int INVALID_MAXIMUM_COMPENSATION = 3001;

    public const int INVALID_MINIMUM_COMPENSATION = 3002;

    public const int INVALID_COMPENSATION_RANGE = 3003;
}
