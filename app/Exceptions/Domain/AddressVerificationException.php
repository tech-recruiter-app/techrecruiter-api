<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Exception;

final class AddressVerificationException extends Exception
{
    public const int NONEXISTENT_COUNTRY = 2001;

    public const int NONEXISTENT_ADMINISTRATIVE_AREA = 2002;

    public const int NONEXISTENT_MUNICIPALITY = 2003;

    public const int NONEXISTENT_STREET = 2004;

    public const int NONEXISTENT_POSTAL_CODE = 2005;
}
