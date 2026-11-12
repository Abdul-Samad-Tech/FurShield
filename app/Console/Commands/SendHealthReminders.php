<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class SendHealthReminders extends Command
{
    protected $signature = 'furshield:send-reminders';

    protected $description = 'Notify owners about health care that is due soon or overdue';

    public function handle(ReminderService $reminders): int
    {
        $sent = $reminders->sendDueReminders();

        $this->info("Sent {$sent} health reminders.");

        return self::SUCCESS;
    }
}
