<?php

namespace Database\Seeders;

use App\Enums\HeroClass;
use App\Enums\HeroStatus;
use App\Enums\MissionStatus;
use App\Enums\UserRole;
use App\Models\Hero;
use App\Models\User;
use Illuminate\Database\Seeder;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $hasPersonalAccessClient = Client::query()
            ->where('revoked', false)
            ->get()
            ->contains(
                fn (Client $client) => $client->hasGrantType('personal_access')
            );

        if (! $hasPersonalAccessClient) {
            app(ClientRepository::class)
                ->createPersonalAccessGrantClient(
                    'Guild API Personal Access Client',
                    'users',
                );
        }

        $admin = User::factory()->create([
            'name' => 'Guild Master',
            'email' => 'admin@guild.test',
            'password' => 'password',
            'role' => UserRole::Admin,
        ]);

        $arthas = User::factory()->create([
            'name' => 'Arthas Menethil',
            'email' => 'arthas@guild.test',
            'password' => 'password',
            'role' => UserRole::User,
        ]);

        $jaina = User::factory()->create([
            'name' => 'Jaina Proudmoore',
            'email' => 'jaina@guild.test',
            'password' => 'password',
            'role' => UserRole::User,
        ]);

        $dragonQuest = $admin->createdQuests()->create([
            'title' => 'Defeat the Ancient Dragon',
            'description' => 'An ancient dragon is terrorizing the northern villages.',
            'boss' => 'Ancient Dragon',
            'difficulty' => 8,
            'is_active' => true,
        ]);

        $lichQuest = $admin->createdQuests()->create([
            'title' => 'Defeat the Lich King',
            'description' => 'A powerful lich has raised an undead army.',
            'boss' => 'The Lich King',
            'difficulty' => 6,
            'is_active' => true,
        ]);

        $goblinQuest = $admin->createdQuests()->create([
            'title' => 'Clear the Goblin Caves',
            'description' => 'Goblin raiders are attacking merchant caravans.',
            'boss' => 'Goblin Chieftain',
            'difficulty' => 3,
            'is_active' => true,
        ]);

        $admin->createdQuests()->create([
            'title' => 'Investigate the Forbidden Tower',
            'description' => 'The tower has been sealed while the guild investigates.',
            'boss' => 'Shadow Sorcerer',
            'difficulty' => 9,
            'is_active' => false,
        ]);

        $arthasWarrior = Hero::factory()->create([
            'user_id' => $arthas->id,
            'name' => 'Arthas',
            'hero_class' => HeroClass::Warrior,
            'level' => 5,
            'status' => HeroStatus::Available,
        ]);

        $arthasMage = Hero::factory()->create([
            'user_id' => $arthas->id,
            'name' => 'Kelthuzad',
            'hero_class' => HeroClass::Mage,
            'level' => 4,
            'status' => HeroStatus::Available,
        ]);

        Hero::factory()->create([
            'user_id' => $arthas->id,
            'name' => 'Uther',
            'hero_class' => HeroClass::Cleric,
            'level' => 3,
            'status' => HeroStatus::Injured,
        ]);

        Hero::factory()->create([
            'user_id' => $arthas->id,
            'name' => 'Valeera',
            'hero_class' => HeroClass::Rogue,
            'level' => 3,
            'status' => HeroStatus::Deactivated,
        ]);

        $jainaMage = Hero::factory()->create([
            'user_id' => $jaina->id,
            'name' => 'Jaina',
            'hero_class' => HeroClass::Mage,
            'level' => 4,
            'status' => HeroStatus::OnMission,
        ]);

        $jainaWarrior = Hero::factory()->create([
            'user_id' => $jaina->id,
            'name' => 'Muradin',
            'hero_class' => HeroClass::Warrior,
            'level' => 4,
            'status' => HeroStatus::OnMission,
        ]);

        Hero::factory()->create([
            'user_id' => $jaina->id,
            'name' => 'Maiev',
            'hero_class' => HeroClass::Rogue,
            'level' => 2,
            'status' => HeroStatus::Available,
        ]);

        $jainaCleric = Hero::factory()->create([
            'user_id' => $jaina->id,
            'name' => 'Tyrande',
            'hero_class' => HeroClass::Cleric,
            'level' => 2,
            'status' => HeroStatus::Injured,
        ]);

        $assignedMission = $jaina->missions()->create([
            'quest_id' => $dragonQuest->id,
        ]);

        $assignedMission->heroes()->attach([
            $jainaMage->id,
            $jainaWarrior->id,
        ]);

        $successfulMission = $arthas->missions()->create([
            'quest_id' => $goblinQuest->id,
        ]);

        $successfulMission->heroes()->attach([
            $arthasWarrior->id,
            $arthasMage->id,
        ]);

        $successfulMission->forceFill([
            'status' => MissionStatus::Success,
            'completed_at' => now()->subDays(2),
        ])->save();

        $failedMission = $jaina->missions()->create([
            'quest_id' => $lichQuest->id,
        ]);

        $failedMission->heroes()->attach([
            $jainaCleric->id,
        ]);

        $failedMission->forceFill([
            'status' => MissionStatus::Failure,
            'completed_at' => now()->subDay(),
        ])->save();

        $this->command->info('Demo guild data seeded successfully.');
        $this->command->info('Admin: admin@guild.test / password');
        $this->command->info('User: arthas@guild.test / password');
        $this->command->info('User: jaina@guild.test / password');
    }
}