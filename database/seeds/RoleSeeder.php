<?php

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['code' => 'admin', 'name' => 'Administrador'],
            ['code' => 'porcionamento', 'name' => 'Porcionamento'],
            ['code' => 'nfce', 'name' => 'NFC-e'],
            ['code' => 'InternConsumption.create', 'name' => 'Requisitar consumo interno'],
            ['code' => 'InternConsumption.authorize', 'name' => 'Autorizar consumo interno'],
        ];

        foreach ($roles as $role) {
            echo "criando {$role['code']}";
            try {
                $r = \App\User\Role::create($role);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        $admin = \App\User\Group::create(['name' => 'Administradores']);
        $admin->roles()->sync(\App\User\Role::whereCode('admin')->pluck('id')->toArray());

        $porcionamento = \App\User\Group::create(['name' => 'Porcionamento']);
        $porcionamento->roles()->sync(\App\User\Role::whereCode('porcionamento')->pluck('id')->toArray());

        $nfc = \App\User\Group::create(['name' => 'NFCe']);
        $nfc->roles()->sync(\App\User\Role::whereCode('nfce')->pluck('id')->toArray());

        $nfcAndPorc = \App\User\Group::create(['name' => 'NFCe + Porcionamento']);
        $nfcAndPorc->roles()->sync(\App\User\Role::whereIn('code', ['nfce', 'porcionamento'])->pluck('id')->toArray());

        foreach (\App\User::all() as $user) {
            if($user->admin) {
                $user->group_id = $admin->id;
                $user->save();
                continue;
            }

            if($user->porcionamento && $user->xml_colibri_sap) {
                $user->group_id = $nfcAndPorc->id;
                $user->save();
                continue;
            }

            if($user->porcionamento) {
                $user->group_id = $porcionamento->id;
                $user->save();
                continue;
            }

            if($user->xml_colibri_sap) {
                $user->group_id = $nfc->id;
                $user->save();
                continue;
            }
        }
    }
}
