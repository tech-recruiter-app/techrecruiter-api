<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\EmployerRegistrationData;
use App\Data\JobseekerRegistrationData;
use App\Models\EmployerProfile;
use App\Models\JobSeekerProfile;
use App\Models\User;
use App\Values\Address;
use App\Values\CompanyDomain;
use App\Values\Email;
use App\Values\Link;
use App\Values\Name;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Propaganistas\LaravelPhone\PhoneNumber;

final readonly class RegisterUser
{
    /**
     * Execute the action.
     *
     * @return ($dto is JobseekerRegistrationData ? User<JobSeekerProfile> : User<EmployerProfile>)
     */
    public function execute(JobseekerRegistrationData|EmployerRegistrationData $dto): User
    {
        $user = DB::transaction(function () use ($dto): User {
            $user = match ($dto::class) {
                JobseekerRegistrationData::class => $this->newJobseeker($dto),
                EmployerRegistrationData::class => $this->newEmployer($dto),
            };

            $user->save();

            return $user;
        });

        DB::afterCommit(fn () => event(new Registered($user)));

        return $user;
    }

    /**
     * @return User<JobSeekerProfile>
     */
    private function newJobseeker(JobseekerRegistrationData $dto): User
    {
        $profile = JobSeekerProfile::query()->create([
            'name' => new Name($dto->firstname, $dto->lastname),
            'resume_link' => new Link($dto->resumeLink),
        ]);
        if (isset($dto->phoneNumber)) {
            $profile->phone_number = new PhoneNumber($dto->phoneNumber);
        }

        return $this->setProfile($this->newUser($dto), $profile);
    }

    /**
     * @return User<EmployerProfile>
     */
    private function newEmployer(EmployerRegistrationData $dto): User
    {
        $profile = EmployerProfile::query()->create([
            'company_name' => $dto->companyName,
            'company_domain' => new CompanyDomain($dto->companyDomain),
            'company_description' => $dto->companyDescription,
        ]);

        return $this->setProfile($this->newUser($dto), $profile);
    }

    /**
     * @return User<EmployerProfile|JobSeekerProfile>
     */
    private function newUser(JobseekerRegistrationData|EmployerRegistrationData $dto): User
    {
        $address = new Address(
            $dto->country,
            $dto->administrativeArea,
            $dto->municipality,
            $dto->street,
            $dto->postalCode
        );

        return new User([
            'email' => new Email($dto->email),
            'password' => $dto->password,
            'address' => $address,
        ]);
    }

    /**
     * @template TProfile of EmployerProfile|JobSeekerProfile
     *
     * @param  User<EmployerProfile|JobSeekerProfile>  $user
     * @param  TProfile  $profile
     * @return User<TProfile>
     */
    private function setProfile(User $user, EmployerProfile|JobSeekerProfile $profile): User
    {
        /** @var User<TProfile> */
        return $user->profile()->associate($profile);
    }
}
