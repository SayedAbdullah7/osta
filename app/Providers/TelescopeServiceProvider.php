<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
//            Log::channel('test')->info('Filtering entry', ['entry' => $entry]);
//            return true;
            if ($this->shouldSilenceRequest($entry)) {
                return false; // Silences the request
            }

            if ($this->app->environment('local')) {
                return true;
            }


            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }
    protected function shouldSilenceRequest(IncomingEntry $entry)
    {
        Log::info('Silencing request');
//        return true;
        if ($entry->type === 'request' &&
            $entry->content['method'] === 'GET' &&
            preg_match('#^/chat/messages/.*#', $entry->content['uri'])) {
            return true; // Silences GET requests to any URI starting with '/chat/messages/'
        }
    }

        /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
