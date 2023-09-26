<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Classes;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Support\Classes\PermissionsClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const SuperAdmin='Super administrateur';

    const Secretaire='Secrétaire';

    const Directeur_general='Directeur général';

    public function run(): void
    {
        $permissions = PermissionsClass::toValues();

        foreach ($permissions as $key => $name) {
            Permission::firstOrCreate([
                'name' => $name,
            ]);
        }
        
//==================Roles=======================================

        $secretaireRole = Role::Create([
            'name' => self::Secretaire,
            'guard_name' => 'web',
        ]);

        $directeurGeneralRole = Role::Create([
            'name'=>self::Directeur_general,
            'guard_name'=>'web',
        ]);

        $superAdminRole = Role::Create([
            'name'=>self::SuperAdmin,
            'guard_name'=>'web',
        ]);      

//==================Users====================================

        $superAdminUser  = User::firstOrCreate([
            "email" => "superadministrateur@laposte.tg",
            'password' => Hash::make('11111111'),
            'name' => 'Super_administrateur',
        ]);
        
        $utilisateurUser  = User::firstOrCreate([
            "email" => "utilisateur@laposte.tg",
            'password'=> Hash::make('11111111'),
            'name' => 'Utilisateur',
        ]);

        $secretaireUser = User::firstOrCreate([
            "email" => "secretaire@laposte.tg",
            'password' => Hash::make('11111111'),
            'name' => 'secretaire',   
        ]);
       
        $directeurGeneralUser =User::firstOrCreate([
            "email" => "directeur@laposte.tg",
            'password' => Hash::make('11111111'),
            'name' => 'DG',
        ]);

//=================Permisisons synching==========================================
        $superAdminRole->syncPermissions($permissions);

        $superAdminUser->syncRoles(self::SuperAdmin);

        $directeurGeneralUser->syncRoles(self::Directeur_general);

        $secretaireUser->syncRoles( self::SuperAdmin, self::Secretaire); 
    }
}
