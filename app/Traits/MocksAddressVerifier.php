<?php

declare(strict_types=1);

namespace App\Traits;

use App\Contracts\AddressVerifier;
use Mockery;
use Mockery\MockInterface;

/**
 * @phpstan-require-extends \Tests\TestCase
 */
trait MocksAddressVerifier
{
    protected function mockAddressVerifier(): void
    {
        $this->mock(AddressVerifier::class, function (MockInterface $mock): void {
            /** @var Mockery\Expectation $expectation */
            $expectation = $mock->expects('verify');
            $expectation->atLeast()->once();
        });
    }
}
