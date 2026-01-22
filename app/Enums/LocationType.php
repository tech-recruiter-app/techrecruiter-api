<?php

declare(strict_types=1);

namespace App\Enums;

enum LocationType
{
    case Country;
    case AdministrativeArea;
    case Municipality;
    case Street;
    case Building;
    case Approximate;
}
