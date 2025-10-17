<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $data = [
            [
                'name' => 'developer',
                'permissions' => [],
            ],
            [
                'name' => 'mentor',
                'permissions' => [
                    'view-mentee-projects',
                    'view-mentee-time-entries',
                    'retrieve-mentor-notifications',
                    'send-mentorship-invite',
                ],
            ],
            [
                'name' => 'admin',
                'permissions' => [
                    'view-mentee-projects',
                    'view-mentee-time-entries',
                    'retrieve-mentor-notifications',
                    'send-mentorship-invite',
                    'approve-mentorship-invite',
                    'view-logs',
                    'view-users',
                ],
            ],
            [
                'name' => 'super-admin',
                'permissions' => [
                    'view-horizon',
                ],
            ],
        ];

        foreach ($data as $roleData) {
            $role = Role::create(['name' => $roleData['name']]);

            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $role->givePermissionTo($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
