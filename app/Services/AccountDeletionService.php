<?php

namespace App\Services;

use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class AccountDeletionService
{
    /**
     * Request account deletion and logout the user.
     *
     * @param string $userType
     * @return string
     */
    public static function requestDeletion(): string
    {
        $user = Auth::user();

//        if (get_class($user) == Provider::class && $user->is_approved == 0 && $user->created_at == $user->updated_at &&$user->created_at->gt(Carbon::now()->subDays(2))) {
        if (get_class($user) == Provider::class && $user->is_approved == 0  &&$user->created_at->gt(Carbon::now()->subDays(2))) {
            $user->delete();
        }

        // Determine the deletable model (either User or Provider)
//        $deletable = $userType === 'user' ? $user : Provider::find($user->id); // Or logic for provider
        $deletable= \auth()->user();
        // Check if the deletion request already exists
        $existingRequest = AccountDeletionRequest::where('deletable_id', $deletable->id)
            ->where('deletable_type', get_class($deletable))
            ->whereNull('deleted_at')
            ->first();

        if ($existingRequest) {
            return 'You have already requested account deletion.';
        }

        // Create a deletion request
        $deletionRequest = AccountDeletionRequest::create([
            'deletable_id' => $deletable->id,
            'deletable_type' => get_class($deletable),
            'requested_at' => now(),
        ]);

        // Log the creation of the deletion request
//        Log::info("Account deletion request created for {$deletable->id} ({$userType})");

        // Send an email notification about the deletion request
//        Mail::to($user->email)->send(new AccountDeletionRequestMail($deletionRequest));

        // Log out the user
        auth()->user()->tokens()->delete();

        return 'Your account deletion request has been received. You will be logged out.';
    }

    /**
     * Delete the account if the user has requested deletion after the grace period.
     *
     * @return string
     */
    public function deleteAccount(): string
    {
        $user = Auth::user();

        // Check if the user has a pending deletion request
        $deletionRequest = AccountDeletionRequest::where('deletable_id', $user->id)
            ->where('deletable_type', get_class($user))
            ->whereNull('deleted_at')
            ->first();

        if (!$deletionRequest) {
            return 'No pending account deletion request found.';
        }

        // Check if the deletion request has passed the grace period (30 days)
        if ($deletionRequest->requested_at->addDays(30)->isFuture()) {
            return 'You are still within the grace period and can cancel the deletion request.';
        }

        // Perform account deletion
        if ($user instanceof User) {
            $user->delete();
        } elseif ($user instanceof Provider) {
            $user->delete();
        }

        // Update deletion request to reflect account deletion
        $deletionRequest->update(['deleted_at' => now()]);

        // Log the account deletion
        Log::info("Account for {$user->id} ({$user->email}) has been deleted.");

        // Log out the user
//        Auth::logout();
        auth()->user()->tokens()->delete();
        return 'Your account has been permanently deleted.';
    }

    /**
     * Cancel the account deletion request if the user decides to change their mind.
     *
     * @return string
     */
    public static function cancelDeletionRequest($user): string
    {
//        $user = Auth::user();

        // Check if the user has a pending deletion request
        $deletionRequest = AccountDeletionRequest::where('deletable_id', $user->id)
            ->where('deletable_type', get_class($user))
            ->whereNull('deleted_at')
            ->delete();

//        if (!$deletionRequest) {
//            return 'No deletion request found to cancel.';
//        }

        // Cancel the deletion request by deleting it
//        $deletionRequest->delete();

        // Log the cancellation
        Log::info("Account deletion request cancelled for {$user->id}");

        return 'Your account deletion request has been cancelled.';
    }



}
