<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AddressValidator;
use App\Services\DefaultAddressValidator;
use Illuminate\Support\ServiceProvider;
use Override;

final class AddressingServiceProvider extends ServiceProvider
{
    /**
     * Register addressing services.
     */
    #[Override]
    public function register(): void
    {
        // Register the address validator
        $this->app->singleton(fn (): AddressValidator => new DefaultAddressValidator);
    }
}
