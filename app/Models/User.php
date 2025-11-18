<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role'

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'external_links' => 'array',
        'onboarding_status' => 'array',
        'get_notifications' => 'boolean',
        'is_socialite' => 'integer',
        'is_socialite_approved' => 'integer',
    ];

    //Make it available in the json response
    protected $appends = ['view_url'];

    // view url for search results
    protected function viewUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => '/settings/users',
        );
    }

    public static function allUsersAndRecipients()
    {
        $users = User::all();

        $recipients = Setting::select('mail_to_email')->where('id', 1)->first();

        if (!empty($recipients)) {
            $recipientsArr = explode(';', $recipients['mail_to_email']);
            foreach ($recipientsArr as $recipient) {
                if ($users->contains('email', $recipient)) {
                    continue;
                }

                $users->push((new User)->forceFill([
                    'id' => crc32(trim($recipient)),
                    'name' => $recipient,
                    'email' => trim($recipient),
                ]));
            }
        }

        return $users;
    }
}
