<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\Domain\LinkVerificationException;
use App\Values\Link;

interface LinkVerifier
{
    /**
     * Verifies that a link exists.
     *
     * @param  Link  $link  The link to verify
     *
     * @throws LinkVerificationException If verification fails
     */
    public function verify(Link $link): void;
}
