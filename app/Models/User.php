<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 *
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email2',
        'email',
        'password',
        'session',
        'app_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'session',
        'app_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'email2_verified_at' => 'datetime',
    ];

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail());
    }

    /**
     * Route notifications for the mail channel.
     *
     * @return  array<string, string>|string
     */
    public function routeNotificationForMail(Notification $notification): array|string
    {
        return $this->getEmailForVerification();
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email2 && !$this->email2_verified_at ? $this->email2 : $this->email;
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at) && (
                $this->email2 && !is_null($this->email_verified_at) || !$this->email2
            );
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        $data = [];
        if( $this->email2 && !$this->email2_verified_at ) {
            $data[ 'email2_verified_at' ] = $this->freshTimestamp();
        } elseif( $this->email && !$this->email_verified_at ) {
            $data[ 'email_verified_at' ] = $this->freshTimestamp();
        }

        return $this->forceFill($data)->save();
    }

    /**
     * @param bool $save
     *
     * @return $this
     */
    public function clearSessionHash(bool $save = true)
    {
        $this->session = null;

        if( $save ) {
            $this->save();
            $this->refresh();
        }

        return $this;
    }

    /**
     * @param bool $save
     *
     * @return $this
     */
    public function setSessionHash(bool $save = true)
    {
        $request = request();
        $this->session = bcrypt($request->ip() . $request->userAgent());

        if( $save ) {
            $this->save();
            $this->refresh();
        }

        return $this;
    }

    public function hasValidSessionHash(): bool
    {
        $request = request();

        return Hash::check($request->ip() . $request->userAgent(), $this->session);
    }

    public function hasSessionHash(): bool
    {
        return !is_null($this->session);
    }

    /**
     * @return mixed|string
     */
    public function getAppTokenAttribute()
    {
        if( !($this->attributes[ 'app_token' ] ?? false) ) {
            $this->attributes[ 'app_token' ] = Str::random(5);
            $this->save();
            $this->refresh();
        }

        return $this->attributes[ 'app_token' ];
    }
}
