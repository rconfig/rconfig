<?php

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Notifications\DBNotification;
use App\Notifications\MailHealthCheckNotification;
use App\Services\Notifications\Shared\HealthCheckAlert;
use App\Traits\NotificationDispatcher;
use Illuminate\Support\Facades\Log;

/**
 * Raises the health check failed / recovered notifications on the in-app and mail channels.
 *
 * Called from HealthAlertResultStore, which only invokes this on a real status
 * transition, so each outage notifies once and each recovery once.
 */
class HealthCheckNotifier
{
    use NotificationDispatcher;

    public function notify(HealthCheckAlert $alert): bool
    {
        $type = $alert->type();

        try {
            $this->sendToSingleChannel(
                $type,
                NotificationChannel::DB,
                $alert->recovered
                    ? new DBNotification($alert->title(), $alert->summary(), 'system', 'info', 'pficon-info')
                    : new DBNotification($alert->title(), $alert->summary(), 'system', 'error', 'pficon-error-circle-o')
            );

            $this->sendToSingleChannel($type, NotificationChannel::MAIL, new MailHealthCheckNotification($alert));

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch health check notification: ' . $e->getMessage(), [
                'type' => $type->value,
                'checks' => $alert->checkLabels(),
            ]);

            return false;
        }
    }
}
