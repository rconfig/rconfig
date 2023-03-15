<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestDatabaseNotification extends Notification
{
    use Queueable;

    protected $faker;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'PHPUnit Test Notification - '.$this->faker->randomNumber($nbDigits = 3),
            'description' => 'PHPUnit Test Notification Description',
            'category' => 'system',
            'severity' => 'info',
            'icon' => 'pficon-info',
            'resolve_link' => '/settings',
            'resolved' => 0,
        ];
    }
}
