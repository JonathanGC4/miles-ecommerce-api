<?php

namespace Database\Seeders;

use App\Models\Tier;
use App\Models\User;
use App\Models\MilesAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $bronze   = Tier::where('name', 'Bronze')->first();
        $silver   = Tier::where('name', 'Silver')->first();
        $gold     = Tier::where('name', 'Gold')->first();
        $platinum = Tier::where('name', 'Platinum')->first();

        // Admin
        $admin = User::create([
            'name'     => 'Administrador',
            'email'    => 'admin@miles.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);
        MilesAccount::create([
            'user_id'        => $admin->id,
            'tier_id'        => $bronze->id,
            'balance'        => 0,
            'lifetime_miles' => 0,
        ]);

        // Sellers
        $sellers = [
            ['name' => 'Carlos Vendedor', 'email' => 'carlos@miles.com'],
            ['name' => 'Laura Vendedora', 'email' => 'laura@miles.com'],
        ];

        foreach ($sellers as $seller) {
            $user = User::create([
                'name'     => $seller['name'],
                'email'    => $seller['email'],
                'password' => Hash::make('password123'),
                'role'     => 'seller',
            ]);
            MilesAccount::create([
                'user_id'        => $user->id,
                'tier_id'        => $bronze->id,
                'balance'        => 0,
                'lifetime_miles' => 0,
            ]);
        }

        // Clients con distintos tiers
        $clients = [
            [
                'name'           => 'Ana Bronze',
                'email'          => 'ana@miles.com',
                'tier'           => $bronze,
                'balance'        => 1500,
                'lifetime_miles' => 1500,
            ],
            [
                'name'           => 'Pedro Silver',
                'email'          => 'pedro@miles.com',
                'tier'           => $silver,
                'balance'        => 3000,
                'lifetime_miles' => 7500,
            ],
            [
                'name'           => 'María Gold',
                'email'          => 'maria@miles.com',
                'tier'           => $gold,
                'balance'        => 8000,
                'lifetime_miles' => 20000,
            ],
            [
                'name'           => 'Juan Platinum',
                'email'          => 'juan@miles.com',
                'tier'           => $platinum,
                'balance'        => 25000,
                'lifetime_miles' => 60000,
            ],
            [
                'name'           => 'Sofia Bronze',
                'email'          => 'sofia@miles.com',
                'tier'           => $bronze,
                'balance'        => 500,
                'lifetime_miles' => 500,
            ],
        ];

        foreach ($clients as $client) {
            $user = User::create([
                'name'     => $client['name'],
                'email'    => $client['email'],
                'password' => Hash::make('password123'),
                'role'     => 'client',
            ]);
            MilesAccount::create([
                'user_id'        => $user->id,
                'tier_id'        => $client['tier']->id,
                'balance'        => $client['balance'],
                'lifetime_miles' => $client['lifetime_miles'],
            ]);
        }
    }
}
