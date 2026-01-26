<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsLink;
use App\Casts\AsName;
use App\Values\Link;
use App\Values\Name;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Represents the profile of a jobseeker user.
 *
 * @property-read non-empty-string $id Profile identifier.
 * @property Name $name Job seeker's full name.
 * @property PhoneNumber|null $phone_number Job seeker's phone number.
 * @property Link $resume_link Link to the job seeker's resume document.
 * @property-read CarbonImmutable $created_at Profile creation timestamp.
 * @property-read CarbonImmutable|null $updated_at Last profile update timestamp.
 * @property-read User $user Job seeker's account.
 */
final class JobSeekerProfile extends Model
{
    /**
     * @use HasFactory<\Database\Factories\JobSeekerProfileFactory>
     */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'resume_link',
    ];

    /**
     * Get the job seeker's user account.
     *
     * @return MorphOne<User, $this>
     */
    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profile');
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
            'name' => AsName::class,
            'phone_number' => E164PhoneNumberCast::class.':CA,US',
            'resume_link' => AsLink::class,
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
