<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Validator;

class EmailVerificationRequest extends \Illuminate\Foundation\Auth\EmailVerificationRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if( !auth()->loginUsingId($this->id) || $this->user()->hasVerifiedEmail() )
        {
            auth()->check() && auth()->logout();

            return false;
        }

        /** @var \Carbon\Carbon $ca */
        $ca = $this->user()->created_at;
        if($ca->addMinutes(Config::get('auth.verification.expire', 60))->isPast())
        {
            $this->user()->delete();
            return false;
        }

        if (! hash_equals((string) $this->user()->getKey(), (string) $this->route('id'))) {
            auth()->logout();
            return false;
        }

        if (! hash_equals(sha1($this->user()->getEmailForVerification()), (string) $this->route('hash'))) {
            auth()->logout();
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill()
    {
        // dd($this->user()->hasVerifiedEmail(),)
        if (! $this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();

            !$this->user()->hasVerifiedEmail() && $this->user()->sendEmailVerificationNotification();

            event(new Verified($this->user()));
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        return $validator;
    }
}
