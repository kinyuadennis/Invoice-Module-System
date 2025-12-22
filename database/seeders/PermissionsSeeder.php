<?php

namespace Database\Seeders;

use App\Http\Services\RoleService;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleService = new RoleService;
        $roleService->seedDefaultPermissions();
    }
}
