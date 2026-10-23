<?php

namespace App\Console\Commands;

use App\Services\WebPushService;
use Illuminate\Console\Command;

class SendLearningRemindersCommand extends Command
{
    protected $signature = 'learning:send-reminders';
    protected $description = 'Send scheduled web push learning and vocabulary reminders based on user timezone and activity.';

    public function handle(WebPushService $pushService): int
    {
        $this->info('Starting scheduled learning reminder dispatcher...');

        $result = $pushService->sendScheduledReminders();

        $this->info("Completed reminder pass: Checked {$result['users_checked']} users, dispatched {$result['reminders_sent']} reminders.");

        return Command::SUCCESS;
    }
}
