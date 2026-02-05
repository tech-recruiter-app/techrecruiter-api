<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsJob;
use App\Casts\AsLink;
use App\Enums\JobPostingStatus;
use App\Values\Job;
use App\Values\Link;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an advertisement of a job opening or vacancy.
 *
 * @property-read non-empty-string $id Job posting's identifier.
 * @property Job $job Job position details.
 * @property JobPostingStatus $status Current status of the job posting.
 * @property Link|null $link Link to the job posting on an external site.
 * @property-read CarbonImmutable $created_at Job posting creation timestamp.
 * @property-read CarbonImmutable|null $updated_at Last job posting update timestamp.
 * @property-read User<EmployerProfile> $employer The user who created the job posting.
 *
 * @method static Builder<self> draft() Indicate it is a draft job posting.
 * @method static Builder<self> published() Scope the query to only include published postings.
 * @method static Builder<self> active() Scope the query to only include active postings.
 * @method static Builder<self> closed() Scope the query to only include closed postings.
 */
final class JobPosting extends Model
{
    use HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'job',
        'link',
    ];

    /**
     * Get the user who created the job posting.
     *
     * @return BelongsTo<User<EmployerProfile>, $this>
     */
    public function employer(): BelongsTo
    {
        /** @var BelongsTo<User<EmployerProfile>, $this> */
        return $this->belongsTo(User::class, 'employer_id');
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
            'job' => AsJob::class,
            'status' => JobPostingStatus::class,
            'link' => AsLink::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function draft(Builder $query): void
    {
        $query->withAttributes([
            'status' => JobPostingStatus::DRAFT->value,
        ], asConditions: false);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', JobPostingStatus::ACTIVE->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function closed(Builder $query): void
    {
        $query->where('status', JobPostingStatus::CLOSED->value);
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNot('status', JobPostingStatus::DRAFT->value);
    }
}
