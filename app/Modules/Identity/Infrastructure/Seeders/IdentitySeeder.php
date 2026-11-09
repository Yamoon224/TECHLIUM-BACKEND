<?php

declare(strict_types=1);

namespace App\Modules\Identity\Infrastructure\Seeders;

use App\Modules\Identity\Domain\Role as RoleEnum;
use App\Modules\Identity\Infrastructure\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class IdentitySeeder extends Seeder
{
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::findOrCreate($role->value, 'web');
        }

        $admin = User::query()->firstOrCreate(
<<<<<<< HEAD
            ['email' => 'admin@TechLium.app'],
            [
                'name' => 'Admin TechLium',
=======
            ['email' => 'admin@TechniLium.app'],
            [
                'name' => 'Admin TechniLium',
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole(RoleEnum::Administrateur->value)) {
            $admin->assignRole(RoleEnum::Administrateur->value);
        }

        // The only account that manages access (feature switches, groups). Change its password at once.
        $super = User::query()->firstOrCreate(
<<<<<<< HEAD
            ['email' => 'superadmin@TechLium.app'],
            [
                'name' => 'Super Admin TechLium',
=======
            ['email' => 'superadmin@TechniLium.app'],
            [
                'name' => 'Super Admin TechniLium',
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if (! $super->hasRole(RoleEnum::SuperAdmin->value)) {
            $super->assignRole(RoleEnum::SuperAdmin->value);
        }

<<<<<<< HEAD
        // One demo account per remaining profile (<role>@TechLium.app / password), to try each
=======
        // One demo account per remaining profile (<role>@TechniLium.app / password), to try each
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
        // backoffice view and the storefront. Existing accounts are left untouched (password included).
        foreach (RoleEnum::cases() as $role) {
            if (in_array($role, [RoleEnum::SuperAdmin, RoleEnum::Administrateur], true)) {
                continue;
            }

            $user = User::query()->firstOrCreate(
<<<<<<< HEAD
                ['email' => str_replace('_', '.', $role->value).'@TechLium.app'],
=======
                ['email' => str_replace('_', '.', $role->value).'@TechniLium.app'],
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
                [
                    'name' => $role->label().' (démo)',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ],
            );

            if (! $user->hasRole($role->value)) {
                $user->assignRole($role->value);
            }
        }
    }
}
