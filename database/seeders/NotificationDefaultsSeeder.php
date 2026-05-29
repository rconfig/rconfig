<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\NotificationDefault;
use Illuminate\Database\Seeder;

class NotificationDefaultsSeeder extends Seeder
{
    public function run()
    {
        foreach (NotificationType::cases() as $type) {
            $defaults = $type->defaultChannels();

            NotificationDefault::updateOrCreate(
                ['notification_type' => $type->value],
                [
                    'category' => $type->category()->value,
                    'default_db' => $defaults['db'] ?? false,
                    'default_mail' => $defaults['mail'] ?? false,
                    'description' => $type->description(),
                ]
            );
        }
    }
}
