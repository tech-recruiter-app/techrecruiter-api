<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use DomainException;

final class InvalidAddress extends DomainException
{
    public const int INVALID_COUNTRY = 1001;

    public const int INVALID_ADMINISTRATIVE_AREA = 1002;

    public const int INVALID_MUNICIPALITY = 1003;

    public const int INVALID_STREET = 1004;

    public const int INVALID_POSTAL_CODE = 1005;
}
