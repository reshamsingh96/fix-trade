<?php

namespace Database\Seeders;

use App\Constants\CommonConst;
use App\Constants\RoleConst;
use App\Models\Permission;
use App\Models\PermissionCategory;
use App\Models\PermissionType;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Always include RoleConst
        // $prams = ["name" => "PERMISSION", "list" => [], "position" => true];
        $permission_list = RoleConst::ROLE_PERMISSION_LIST; // readConstFileList(...$prams);

        # Clean up old data
        DB::table('role_permissions')->whereNotNull('role_id')->delete();
        Permission::whereNotNull('title')->delete();
        PermissionCategory::whereNotNull('name')->delete();
        PermissionType::whereNotNull('name')->delete();
        $total_permission = 0;
        foreach ($permission_list as $type) {
            $total_permission++;
            foreach ($type['category'] as $category) {
                $total_permission++;
                foreach ($category['permission_list'] as $permission) {
                    $total_permission++;
                }
            }
        }

        $progressBar = $this->command->getOutput()->createProgressBar($total_permission);
        $progressBar->start();

        foreach ($permission_list as $type) {
            $permission_type = PermissionType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'slug' => Str::slug($type['name']),
                    'icon' => $type['icon'],
                ]
            );
            $progressBar->advance();
            foreach ($type['category'] as $category) {
                $permission_category = PermissionCategory::updateOrCreate(
                    ['permission_type_id' => $permission_type->id, 'name' => $category['name']],
                    [
                        'slug' => Str::slug($category['name']),
                        'permission_type_id' => $permission_type->id,
                    ]
                );
                $progressBar->advance();
                foreach ($category['permission_list'] as $permission) {
                    $full_permission = $permission['action'] . '_' . $permission['slug'];

                    Permission::updateOrCreate(
                        [
                            'permission_type_id' => $permission_type->id,
                            'permission_category_id' => $permission_category->id,
                            'title' => $permission['name'],
                        ],
                        [
                            'permission' => $full_permission,
                            'slug' => $permission['slug'],
                            'action' => $permission['action'],
                            'description' => $permission['name'] . ' description',
                            'permission_type_id' => $permission_type->id,
                            'permission_category_id' => $permission_category->id,
                        ]
                    );
                    $progressBar->advance();
                }
            }
        }

        $progressBar->finish();
        $this->command->info("\nPermission seeded successfully!");

        # Role Permission assign
        $role_list = Role::get();
        foreach ($role_list as $role) {
            $permissions_ids = Permission::pluck('id')->toArray();
            # remove move old permission assign in $role  
            switch ($role->slug) {
                case RoleConst::SLUG_SUPER_ADMIN:
                    $permissions_ids = Permission::pluck('id')->toArray();
                    break;
                case RoleConst::SLUG_ADMIN:
                    $permissions_ids = Permission::whereIn('permission', RoleConst::ADMIN_PERMISSION)->pluck('id')->toArray();
                    break;
                case RoleConst::SLUG_USER:
                    $permissions_ids = Permission::whereIn('permission', RoleConst::USER_PERMISSION)->pluck('id')->toArray();
                    break;
                case RoleConst::SLUG_SERVICE_PROVIDER:
                    $permissions_ids = Permission::whereIn('permission', RoleConst::SERVICE_PROVIDER_PERMISSION)->pluck('id')->toArray();
                    break;
                default:
                    $permissions_ids = [];
                    break;
            }
            $role->permissions()->sync($permissions_ids);
        }
    }
}
