<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Geocoder;
use App\Data\GeocodingResult;
use App\Enums\LocationType;
use App\Values\Address;
use DomainException;
use Geocodio\Exceptions\GeocodioException;
use Geocodio\Geocodio;
use RuntimeException;
use UnexpectedValueException;

final readonly class GeocodioGeocoder implements Geocoder
{
    public function __construct(private Geocodio $geocoder) {}

    public function geocode(Address $address): GeocodingResult
    {
        // Prepare address for verification
        $query = [
            'country' => $address->country,
            'city' => $address->municipality,
        ];
        if (filled($address->administrativeArea)) {
            $query['state'] = $address->administrativeArea;
        }

        // Send address verification request
        try {
            $firstResponse = $this->geocoder->geocode($query);
            $firstResponse = $this->parseGeocodingResponse($firstResponse);

            if (filled($address->street)) {
                $secondResponse = $this->geocoder->geocode($query + ['street' => $address->street]);
                $secondResponse = $this->parseGeocodingResponse($secondResponse);
            }
        } catch (GeocodioException $e) {
            throw new RuntimeException('Address validation failed due to geocoding service error.', $e->getCode(), $e);
        }

        $locationType = isset($secondResponse) ?
            $this->toLocationType($secondResponse['accuracy_type']) :
            $this->toLocationType($firstResponse['accuracy_type']);

        return new GeocodingResult(
            $locationType,
            strcasecmp($firstResponse['address_components']['city'] ?? '', $address->municipality) === 0 && $firstResponse['accuracy_score'] === 1,
            isset($secondResponse) ? (float) $secondResponse['accuracy_score'] === 1.0 : null
        );
    }

    /**
     * @return array{accuracy_score: int|float, accuracy_type: string, address_components: array<string, string>}
     */
    private function parseGeocodingResponse(array $response): array // @phpstan-ignore missingType.iterableValue
    {
        if (! isset($response['results']) || ! (is_array($response['results']) && is_array($result = array_first($response['results'])))) {
            throw new UnexpectedValueException('Geocoding response is missing geocoding results.');
        }
        if (! isset($result['accuracy'], $result['accuracy_type'], $result['address_components'])) {
            throw new UnexpectedValueException('Geocoding results is missing required fields.');
        }
        if (! is_int($result['accuracy']) && ! is_float($result['accuracy'])) {
            throw new UnexpectedValueException('Expected an integer or float as accuracy score.');
        }
        if (! is_string($result['accuracy_type'])) {
            throw new UnexpectedValueException('Expected a string as accuracy type.');
        }
        if (! is_array($result['address_components'])) {
            throw new UnexpectedValueException('Expected an array as address components.');
        }
        /** @var array<string, string> */
        $address_components = $result['address_components'];

        return [
            'accuracy_score' => $result['accuracy'],
            'accuracy_type' => $result['accuracy_type'],
            'address_components' => $address_components,
        ];
    }

    private function toLocationType(string $geocodio_accuracy_type): LocationType
    {
        return match ($geocodio_accuracy_type) {
            'country' => LocationType::Country,
            'state' => LocationType::AdministrativeArea,
            'place' => LocationType::Municipality,
            'street_center' => LocationType::Street,
            'intersection' => LocationType::Street,
            'rooftop' => LocationType::Building,
            'point' => LocationType::Building,
            'range_interpolation' => LocationType::Building,
            'nearest_rooftop_match' => LocationType::Approximate,
            default => throw new DomainException("Unknown location type: $geocodio_accuracy_type")
        };
    }
}
