<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin \App\Values\Address
 */
final class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'country' => $this->country,
            'administrative_area' => $this->whenNotNull($this->administrativeArea),
            'municipality' => $this->municipality,
            'street' => $this->whenNotNull($this->street),
            'postal_code' => $this->whenNotNull($this->postalCode),
        ];
    }
}
