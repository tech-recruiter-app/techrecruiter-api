<?php

declare(strict_types=1);

namespace App\Traits;

use App\Contracts\LinkVerifier;
use Mockery;
use Mockery\MockInterface;

/**
 * @phpstan-require-extends \Tests\TestCase
 */
trait MocksLinkVerifier
{
    protected function mockLinkVerifier(): void
    {
        $this->mock(LinkVerifier::class, function (MockInterface $mock): void {
            /** @var Mockery\Expectation $expectation */
            $expectation = $mock->expects('verify');
            $expectation->atLeast()->once();
        });
    }
}
