<?php

use App\Models\User;
use App\Services\AisensyCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('aisensy:send-incomplete-application-reminders', function (AisensyCampaignService $aisensy) {
    $campaign = (string) config('services.aisensy.incomplete_application_campaign');
    $applicationUrl = (string) config('services.aisensy.incomplete_application_url');

    if (! filter_var($applicationUrl, FILTER_VALIDATE_URL) || ! str_starts_with($applicationUrl, 'https://')) {
        $this->warn('No valid HTTPS incomplete application URL is configured; no reminders were sent.');

        return;
    }

    $cutoff = now()->subMinutes((int) config('services.aisensy.incomplete_application_delay_minutes', 30));
    $sent = 0;

    User::query()
        ->where('role', User::ROLE_CUSTOMER)
        ->whereNull('cibil_profile_completed_at')
        ->whereNull('incomplete_application_reminded_at')
        ->where('created_at', '<=', $cutoff)
        ->whereNotNull('mobile')
        ->eachById(function (User $user) use ($aisensy, $campaign, $applicationUrl, &$sent) {
            $name = trim((string) $user->name);
            $name = $name === '' || $name === 'Applicant' ? 'user' : $name;

            try {
                $aisensy->send($campaign, $user->mobile, $name, ['$FirstName', $applicationUrl]);
                $user->forceFill(['incomplete_application_reminded_at' => now()])->save();
                $sent++;
            } catch (Throwable $exception) {
                Log::warning('AiSensy incomplete application reminder failed.', [
                    'user_id' => $user->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        });

    $this->info("Sent {$sent} incomplete application reminder(s).");
})->purpose('Send one WhatsApp reminder to customers who did not complete their application.');

Schedule::command('aisensy:send-incomplete-application-reminders')
    ->everyFiveMinutes()
    ->withoutOverlapping();
