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
            ['email' => 'admin@TechLium.app'],
            [
                'name' => 'Admin TechLium',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if (! $admin->hasRole(RoleEnum::Administrateur->value)) {
            $admin->assignRole(RoleEnum::Administrateur->value);
        }

        // The only account that manages access (feature switches, groups). Change its password at once.
        $super = User::query()->firstOrCreate(
            ['email' => 'superadmin@TechLium.app'],
            [
                'name' => 'Super Admin TechLium',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        if (! $super->hasRole(RoleEnum::SuperAdmin->value)) {
            $super->assignRole(RoleEnum::SuperAdmin->value);
        }

        // One demo account per remaining profile (<role>@TechLium.app / password), to try each
        // backoffice view and the storefront. Existing accounts are left untouched (password included).
        foreach (RoleEnum::cases() as $role) {
            if (in_array($role, [RoleEnum::SuperAdmin, RoleEnum::Administrateur], true)) {
                continue;
            }

            $user = User::query()->firstOrCreate(
                ['email' => str_replace('_', '.', $role->value).'@TechLium.app'],
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
