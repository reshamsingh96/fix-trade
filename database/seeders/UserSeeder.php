<?php

namespace Database\Seeders;

use App\Constants\CommonConst;
use App\Constants\RoleConst;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\Setting;
use App\Models\State;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserAddress;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $users = [
            [
                "gender" => 'Male',
                "name" => 'Resham Singh',
                "email" => 'singhresham393@gmail.com',
                "phone_code" => '91',
                "phone" => '9876919758',
                "account_type" => CommonConst::SUPER_ADMIN,
                "status" => CommonConst::ACTIVE,
                "image_url" => '',
                "password" => 'qwerty123',
                "ip_address" => '',
                "user_name"=> "resham96",
                "phone_verified_at" => now()->format('Y-m-d H:i:s'),
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
                'date_of_birth' => '1996-02-15',
                'anniversary_date' => '2020-09-20',
                "roles" => [RoleConst::SLUG_SUPER_ADMIN],
            ],
            [
                "gender" => 'Male',
                "name" => 'Ramesh Singh',
                "email" => 'ramesh@gmail.com',
                "phone_code" => '91',
                "phone" => '9874563210',
                "account_type" => CommonConst::SUPER_ADMIN,
                "status" => CommonConst::ACTIVE,
                "image_url" => '',
                "password" => 'qwerty123',
                "ip_address" => '',
                "phone_verified_at" => now()->format('Y-m-d H:i:s'),
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
                'date_of_birth' => '1990-01-01',
                'anniversary_date' => '2015-01-01',
                "user_name"=> "ramesh94",
                "roles" => [RoleConst::SLUG_ADMIN],
            ],
            [
                "gender" => 'Male',
                "name" => 'User',
                "email" => 'user@gmail.com',
                "phone_code" => '91',
                "phone" => '9874563212',
                "account_type" => CommonConst::USER,
                "status" => CommonConst::ACTIVE,
                "image_url" => '',
                "password" => 'qwerty123',
                "ip_address" => '',
                "phone_verified_at" => now()->format('Y-m-d H:i:s'),
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
                'date_of_birth' => '1990-01-01',
                'anniversary_date' => '2015-01-01',
                "user_name"=> "user01",
                "roles" => [RoleConst::SLUG_USER],
            ],
            [
                "gender" => 'Male',
                "name" => 'Service Provider Singh',
                "email" => 'provider@gmail.com',
                "phone_code" => '91',
                "phone" => '9874563213',
                "account_type" => CommonConst::SERVICE_PROVIDER,
                "status" => CommonConst::ACTIVE,
                "image_url" => '',
                "password" => 'qwerty123',
                "ip_address" => '',
                "phone_verified_at" => now()->format('Y-m-d H:i:s'),
                'email_verified_at' => now()->format('Y-m-d H:i:s'),
                'date_of_birth' => '1990-01-01',
                'anniversary_date' => '2015-01-01',
                "user_name"=> "provider01",
                "roles" => [RoleConst::SLUG_SERVICE_PROVIDER],
            ],
        ];

        # Ensure necessary data exists
        $countryId = Country::where('name', 'India')->pluck('id')->first() ?? null;
        $stateId = State::where('country_id', $countryId)->where('name', 'Punjab')->pluck('id')->first() ?? null;
        $cityId = City::where('state_id', $stateId)->pluck('id')->first() ?? null; # Assuming you want the first city in the database

        # Create or update roles with a progress bar
        $output = new ConsoleOutput();
        $output->writeln('Seeding users and assigning roles...');
        $progressBar = new ProgressBar($output, count($users));
        $progressBar->start();

        foreach ($users as $userData) {
            # Find roles by their names
            $roleIds = Role::whereIn('slug', $userData['roles'])->pluck('id')->toArray();

            # Create or update the user
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'gender' => $userData['gender'],
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone_code' => $userData['phone_code'] ?? null,
                    'phone' => $userData['phone'] ?? null,
                    'account_type' => $userData['account_type'],
                    'status' => $userData['status'],
                    'image_url' => $userData['image_url'],
                    'password' => Hash::make($userData['password']),
                    'user_name' => $userData['email'],
                    'search_key' => $userData['name'].$userData['email'].$userData['phone'],
                    'ip_address' => $userData['ip_address'],
                    'phone_verified_at' => $userData['phone_verified_at'],
                    'email_verified_at' => $userData['email_verified_at'],
                    'date_of_birth' => $userData['date_of_birth'],
                    'anniversary_date' => $userData['anniversary_date'],
                ]
            );

            storeCreateDefault($user->uuid);

            # Create two dummy addresses for each user
            for ($i = 1; $i <= 1; $i++) {
                UserAddress::create([
                    'user_id' => $user->uuid,
                    'country_id' => $countryId,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                    'pin_code' => fake()->postcode(),
                    'home_no' => fake()->buildingNumber(),
                    'full_address' => fake()->address(),
                ]);
            }

            # Remove old roles and assign new ones
            $user->roles()->sync($roleIds);
            $progressBar->advance();
        }

        # Add setting in info 
        $user_id = User::where('email', 'singhresham393@gmail.com')->pluck('uuid')->first();
        $setting_list = [
            ["key" => "company_name", "value" => "Home Service", "created_by" => $user_id],
            ["key" => "phone", "value" => "9876919758", "created_by" => $user_id],
            ["key" => "address", "value" => "Dodan Wali, Sri Muktsar Sahib, Punjab, India, 152026", "created_by" => $user_id],
            ["key" => "company_logo", "value" => asset('images\logo\logo.jpg'), "created_by" => $user_id],
            ["key" => "email_color", "value" => "#7367f0", "created_by" => $user_id],
        ];
        foreach ($setting_list as $setting) {
            $existingSetting = Setting::where('key', $setting['key'])->first();
            if ($existingSetting) {
                $existingSetting->value = $setting['value'];
                $existingSetting->updated_by = $setting['created_by'];
                $existingSetting->save();
            } else {
                # Insert with created_by
                Setting::create(['key' => $setting['key'], 'value' => $setting['value'], 'created_by' => $setting['created_by']]);
            }
        }

        $progressBar->finish();

        $output->writeln("\nUsers seeded and roles assigned successfully!");
    }

    public function runs()
    {
        $roles = Role::get();
        $genders = ['Male'];

        # Ensure necessary data exists
        $countryId = Country::where('name', 'India')->pluck('id')->first() ?? null;
        $stateId = State::where('country_id', $countryId)->where('name', 'Punjab')->pluck('id')->first() ?? null;
        $cityId = City::where('state_id', $stateId)->pluck('id')->first() ?? null; # Assuming you want the first city in the database

        # Create dummy users with different roles and account types
        foreach ($roles as $key => $role) {
            foreach ($genders as $gender) {
                $name = fake()->name();

                $user = User::create([
                    'name' => $name,
                    'email' => strtolower(str_replace('-', '', $role->slug . "@gmail.com")),
                    'gender' => $gender,
                    'account_type' => $role->name,
                    'phone_code' => fake()->countryCode(),
                    'phone' => fake()->unique()->phoneNumber(),
                    'zip_code' => '140301',
                    'secondary_number' => fake()->optional()->phoneNumber(),
                    'search_key' => $name,
                    'phone_verified_at' => now(),
                    'email_verified_at' => now(),
                    'password' => Hash::make('qwerty123'), # Use a default password
                ]);
                // storeCreateDefault($user->uuid);
                # Create two dummy addresses for each user
                for ($i = 1; $i <= 1; $i++) {
                    UserAddress::create([
                        'user_id' => $user->uuid,
                        'country_id' => $countryId,
                        'state_id' => $stateId,
                        'city_id' => $cityId,
                        'pin_code' => fake()->postcode(),
                        'home_no' => fake()->buildingNumber(),
                        'full_address' => fake()->address(),
                    ]);
                }

                # Assign roles to users
                updateUserRoles([$role->uuid], $user);
            }
        }
    }
}
