<?php

namespace Database\Seeders;

use App\Constants\CommonConst;
use Illuminate\Database\Seeder;
use App\Constants\RoleConst;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # role Create
        $roles = RoleConst::ROLE_LIST;

        # Start the progress bar
        $progressBar = $this->command->getOutput()->createProgressBar(count($roles));
        $progressBar->start();
        foreach ($roles as $role) {
            $role = Role::updateOrCreate(['slug' => $role['slug']], [
                'name' => $role['name'],
                'slug' => $role['slug'],
                "position" => $role['position'],
                "status" => CommonConst::ACTIVE,
                'description' => $role['description'],
            ]);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->command->info("\nRoles seeded successfully!");
    }
}
