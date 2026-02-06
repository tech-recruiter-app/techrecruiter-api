<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsLink;
use App\Enums\JobApplicationStatus;
use App\Values\Link;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a job application.
 *
 * @property-read non-empty-string $id Job application's identifier.
 * @property Link $resume_link Link to the job seeker's resume used for this application.
 * @property Link|null $cover_letter_link Link to cover letter provided by the job seeker.
 * @property JobApplicationStatus $status Job application's current status.
 * @property-read CarbonImmutable $created_at Job application creation timestamp.
 * @property-read CarbonImmutable|null $updated_at Job application last update timestamp.
 * @property-read JobPosting $jobPosting The job posting associated with this job application.
 * @property-read User<JobSeekerProfile> $applicant The job seeker who submitted this job application.
 *
 * @method static Builder<self> withStatus(JobApplicationStatus $status) Scope a query to only include applications of a given status.
 * @method static Builder<self> published() Scope the query to only include non-draft applications.
 */
final class JobApplication extends Model
{
    /**
     * @use HasFactory<\Database\Factories\JobApplicationFactory>
     */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'resume_link',
        'cover_letter_link',
    ];

    /**
     * Get the job posting associated with this job application.
     *
     * @return BelongsTo<JobPosting, $this>
     */
    public function jobPosting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    /**
     * Get the job seeker who submitted this job application.
     *
     * @return BelongsTo<User<JobSeekerProfile>, $this>
     */
    public function applicant(): BelongsTo
    {
        /** @var BelongsTo<User<JobSeekerProfile>, $this> */
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function withStatus(Builder $query, JobApplicationStatus $status): void
    {
        $query->withAttributes([
            'status' => $status->value,
        ], asConditions: true);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNot('status', JobApplicationStatus::DRAFT->value);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'job_posting_id' => 'string',
            'applicant_id' => 'string',
            'resume_link' => AsLink::class,
            'cover_letter_link' => AsLink::class,
            'status' => JobApplicationStatus::class,
            'updated_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }
}
