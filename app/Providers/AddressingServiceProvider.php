<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AddressVerifier;
use App\Services\DefaultAddressVerifier;
use App\Services\GeocodioGeocoder;
use Geocodio\Geocodio;
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
        // Register the address verifier
        $this->app->singleton(fn (): AddressVerifier => new DefaultAddressVerifier);
    }

    /**
     * Bootstrap addressing services.
     */
    public function boot(): void
    {
        $this->callAfterResolving(
            AddressVerifier::class,
            function (AddressVerifier $verifier): void {
                if ($verifier instanceof DefaultAddressVerifier) {
                    $verifier->setGeocoder(
                        new GeocodioGeocoder($this->app->make(Geocodio::class))
                    );
                }
            }
        );
    }
}
