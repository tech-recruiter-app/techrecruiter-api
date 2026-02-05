<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use App\Values\Link;

trait RandomLinks
{
    protected function randomLink(): Link
    {
        return new Link(fake()->imageUrl());
    }
}
