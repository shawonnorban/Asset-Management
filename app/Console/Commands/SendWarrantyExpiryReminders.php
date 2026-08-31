<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendWarrantyExpiryReminders extends Command
{
    protected $signature = 'reminders:warranty-expiry
                            {--days= : How many days ahead to warn about expiry}
                            {--no-email : Record in-app notifications only}';

    protected $description = 'Flag expiring and expired warranties and alert the people who manage them';

    public function handle(ReminderService $reminders): int
    {
        $days = (int) ($this->option('days') ?: Warranty::WARNING_DAYS);

        $result = $reminders->runWarrantyCheck($days, ! $this->option('no-email'));

        $this->info(sprintf(
            'Warranty check complete: %d expiring within %d days, %d newly expired, %d notifications sent.',
            $result['expiring'],
            $days,
            $result['expired'],
            $result['notified'],
        ));

        return self::SUCCESS;
    }
}
