<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AddressValidator;
use App\Services\DefaultAddressValidator;
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
        // Register the address validator
        $this->app->singleton(fn (): AddressValidator => new DefaultAddressValidator);
    }

    /**
     * Bootstrap addressing services.
     */
    public function boot(): void
    {
        $this->callAfterResolving(
            AddressValidator::class,
            function (AddressValidator $validator): void {
                if ($validator instanceof DefaultAddressValidator) {
                    $validator->setGeocoder(
                        new GeocodioGeocoder($this->app->make(Geocodio::class))
                    );
                }
            }
        );
    }
}
