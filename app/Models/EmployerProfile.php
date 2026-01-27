<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsCompanyDomain;
use App\Values\CompanyDomain;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Represents the profile of an employer user.
 *
 * @property-read non-empty-string $id Profile's identifier.
 * @property non-empty-string $company_name Employer's company name.
 * @property CompanyDomain $company_domain Employer's company domain name.
 * @property non-empty-string $company_description Employer's company description.
 * @property-read CarbonImmutable $created_at Profile creation timestamp.
 * @property-read CarbonImmutable|null $updated_at Last profile update timestamp.
 * @property-read User $user Employer's account.
 */
final class EmployerProfile extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'company_domain',
        'company_description',
    ];

    /**
     * Get the employer's user account.
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
            'company_name' => 'string',
            'company_domain' => AsCompanyDomain::class,
            'company_description' => 'string',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
