<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendMaintenanceDueReminders extends Command
{
    protected $signature = 'reminders:maintenance-due
                            {--days=3 : How many days ahead to warn about scheduled work}
                            {--no-email : Record in-app notifications only}';

    protected $description = 'Chase overdue maintenance and warn about work scheduled in the next few days';

    public function handle(ReminderService $reminders): int
    {
        $days = (int) $this->option('days');

        $result = $reminders->runMaintenanceCheck($days, ! $this->option('no-email'));

        $this->info(sprintf(
            'Maintenance check complete: %d overdue, %d due within %d days, %d notifications sent.',
            $result['overdue'],
            $result['due_soon'],
            $days,
            $result['notified'],
        ));

        return self::SUCCESS;
    }
}
