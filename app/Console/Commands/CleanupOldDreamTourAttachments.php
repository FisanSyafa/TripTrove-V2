<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DreamTourRequest;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupOldDreamTourAttachments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dreamtour:cleanup-attachments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up dream tour request attachments older than 14 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(14);
        
        $requests = DreamTourRequest::whereNotNull('attachments')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        $count = 0;
        foreach ($requests as $request) {
            if ($request->attachments) {
                foreach ($request->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment);
                }
            }
            $request->update(['attachments' => null]);
            $count++;
        }

        $this->info("Cleaned up attachments for {$count} dream tour requests.");
    }
}
