<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin \App\Models\User<EmployerProfile|JobSeekerProfile>
 */
final class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $profile = $this->whenLoaded('profile', function (EmployerProfile|JobSeekerProfile $profile): array {
            $data = match ($profile::class) {
                EmployerProfile::class => [
                    'company_name' => $profile->company_name,
                    'company_domain' => $profile->company_domain,
                    'company_description' => $profile->company_description,
                ],
                JobSeekerProfile::class => [
                    'name' => $profile->name,
                    'phone_number' => $this->whenNotNull($profile->phone_number),
                    'resume_link' => $profile->resume_link,
                ]
            };

            $data['updated_at'] = $this->whenNotNull($profile->updated_at?->toAtomString());

            return $data;
        });

        return [
            'id' => $this->id,
            'email' => $this->email,
            'address' => AddressResource::make($this->address),
            'profile' => $profile,
            'registered_at' => $this->created_at->toAtomString(),
            'updated_at' => $this->whenNotNull($this->updated_at?->toAtomString()),
        ];
    }
}
