<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-logs:prune {--days=90}';
    protected $description = 'Remove activity logs older than the specified days';

    public function handle()
    {
        $days = (int) $this->option('days');

        $count = DB::table('activity_logs')
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->delete();

        $this->info("Deleted {$count} old activity log records.");
    }
}