<?php

namespace App\Services;

use App\Models\Otp;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\UnauthorizedException;
class OTPService
{
    /**
     * Generate and store a new OTP for a user.
     *
     * @param mixed $user The user for whom the OTP is generated (can be user or provider).
     * @param int $expirationMinutes The expiration time for the OTP in minutes.
     * @return string|null The generated OTP, or null if a new OTP cannot be generated.
     * @throws ValidationException
     */
    public function generateOTP($user, int $expirationMinutes = 5): ?string
    {
        if ($this->isLastOTPGeneratedWithinMinutes($user, 2) || $this->isOTPCountExceededLastHour($user, 3)) {
//            return null; // Return null to indicate that a new OTP cannot be generated yet
            // reutne error user can't generate otp
//            throw ValidationException::withMessages([
//                'otp' => ['Cannot generate OTP at this time. Please try again later.'],
//            ]);
            throw new UnauthorizedException('Cannot generate OTP at this time. Please try again later.');

        }

//        $otp = Str::random(6);
        $otp = '1234';
        $expiresAt = Carbon::now()->addMinutes($expirationMinutes);

        return Otp::create([
            'code' => $otp,
            'expires_at' => $expiresAt,
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'phone'=>$user->phone
        ])->otp;
    }

    /**
     * Verify the provided OTP for a user.
     *
     * @param mixed $user The user for whom the OTP is being verified (can be user or provider).
     * @param string $otp The OTP to be verified.
     * @return bool True if the OTP is valid, false otherwise.
     */
    public static function verifyOTP($user, string $otp): bool
    {
        $otpRecord = Otp::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->where('code', $otp)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->latest()
            ->first();

        if ($otpRecord) {
            $otpRecord->update(['used_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Check if the last OTP for the user was generated within the specified minutes.
     *
     * @param mixed $user The user for whom the OTP is being checked (can be user or provider).
     * @param int $minutes The time frame within which to check for the last OTP.
     * @return bool True if the last OTP was generated within the specified minutes, false otherwise.
     */
    protected function isLastOTPGeneratedWithinMinutes($user, $minutes): bool
    {
        return Otp::where('user_type', get_class($user))
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    /**
     * Check if the OTP count for the user exceeds the specified count within the last hour.
     *
     * @param mixed $user The user for whom the OTP count is being checked (can be user or provider).
     * @param int $count The maximum allowed OTP count within the last hour.
     * @return bool True if the OTP count exceeds the specified count, false otherwise.
     */
    protected function isOTPCountExceededLastHour($user, $count): bool
    {
        return Otp::where('user_type', get_class($user))
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->subHour())
                ->count() >= $count;
    }


    public static function destroyOTPs($user): void
    {
        Otp::where('user_type', get_class($user))
            ->where('user_id', $user->id)
//            ->where('code', $otp)
//            ->where('expires_at', '>=', now())
//            ->whereNull('used_at')
            ->delete();

    }
}
