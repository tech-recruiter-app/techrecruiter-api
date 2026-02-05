<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Casts\AsAddress;
use App\Casts\AsEmail;
use App\Values\Address;
use App\Values\Email;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

/**
 * @template TProfile of EmployerProfile|JobSeekerProfile
 *
 * Represents an application user account.
 *
 * @property-read non-empty-string $id User account identifier.
 * @property Email $email User's email address.
 * @property non-empty-string $password User's password.
 * @property Address $address User's physical address.
 * @property-read CarbonImmutable $created_at Account creation timestamp.
 * @property-read CarbonImmutable|null $updated_at Last account update timestamp.
 * @property-read CarbonImmutable|null $email_verified_at Successful email verification timestamp.
 * @property-read TProfile $profile User's profile
 *
 * @method static Builder<self<EmployerProfile>> employers() Scope the query to only include employer users.
 * @method static Builder<self<JobSeekerProfile>> jobseekers() Scope the query to only include jobseeker users.
 */
final class User extends Authenticatable implements JWTSubject
{
    /**
     * @use HasFactory<\Database\Factories\UserFactory>
     */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the user's profile.
     *
     * @return MorphTo<Model, $this>
     */
    public function profile(): MorphTo
    {
        return $this->morphTo();
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, string>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
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
            'email' => AsEmail::class,
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
            'address' => AsAddress::class,
            'updated_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    /**
     * @param  Builder<self<EmployerProfile>>  $query
     */
    #[Scope]
    protected function employers(Builder $query): void
    {
        $query->where('profile_type', 'Employer');
    }

    /**
     * @param  Builder<self<JobSeekerProfile>>  $query
     */
    #[Scope]
    protected function jobseekers(Builder $query): void
    {
        $query->where('profile_type', 'Jobseeker');
    }
}
