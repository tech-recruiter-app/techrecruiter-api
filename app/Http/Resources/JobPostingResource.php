<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @mixin \App\Models\JobPosting
 */
final class JobPostingResource extends JsonResource
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
            'id' => $this->id,
            'job' => [
                'title' => $this->job->title,
                'type' => $this->job->type->label(),
                'address' => AddressResource::make($this->job->address),
                'compensation' => $this->job->compensation,
                'stack' => $this->job->stack,
                'responsibilities' => $this->whenNotNull($this->job->description?->responsibilities),
                'requirements' => $this->whenNotNull($this->job->description?->requirements),
                'benefits' => $this->whenNotNull($this->job->description?->benefits),
                'educational_qualification' => $this->whenNotNull($this->job->description?->educationalQualification),
                'starts_on' => $this->whenNotNull($this->job->startsOn?->toDateString()),
            ],
            'status' => $this->status->label(),
            'link' => $this->whenNotNull($this->link),
            'created_at' => $this->created_at->toAtomString(),
            'updated_at' => $this->whenNotNull($this->updated_at?->toAtomString()),
        ];
    }
}
