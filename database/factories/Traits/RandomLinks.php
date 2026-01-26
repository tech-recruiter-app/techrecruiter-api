<?php

declare(strict_types=1);

namespace Database\Factories\Traits;

use App\Values\Link;
use ReflectionProperty;

trait RandomLinks
{
    protected function randomLink(): Link
    {
        $link = new Link(fake()->imageUrl());
        new ReflectionProperty(Link::class, 'exists')->setValue($link, true);

        return $link;
    }
}
