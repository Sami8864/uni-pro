<?php

namespace App\Services;

use App\Events\SendSmsEvent;
use App\Events\VerifyUserEmail;
use App\Models\User;

class VerificationService
{
    public static function sendEmailVerificationCode($user)
    {
        $emailVerificationCode = User::generateRandomCode();
        $user->update([
            'email_verification_code' => $emailVerificationCode,
            'email_verification_code_expires' => now()->addMinutes(config('auth.verification.expire'))
        ]);
        event(new VerifyUserEmail($user, $emailVerificationCode));
    }

    public static function verifyEmail($email, $code)
    {
        $user = User::findByEmail($email);
        if (!$user) {
            return response()->json(['message' => 'Email address not found.']);
        }
        if (!self::isEmailVerificationCodeValid($user, $code)) {
            return response()->json(['message' => 'Verification code is  incorrect', 'code' => 400]);
        }
        $user->update([
            'email_verification_code' => Null,
            'email_verification_code_expires' => Null,
            'email_verified_at' => now()
        ]);
        return true;
    }
    public static function isEmailVerificationCodeValid($user, $code)
    {
        if ($user->email_verification_code === $code) {
            return true;
        }
        return false;
    }
}
