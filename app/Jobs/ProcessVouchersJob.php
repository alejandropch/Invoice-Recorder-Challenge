<?php

namespace App\Jobs;


use App\Models\User;
use App\Services\VoucherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVouchersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $xmlContent;
    private $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $xmlContent, User $user)
    {
        $this->xmlContent= $xmlContent;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(VoucherService $voucherService): void
    {
        // $user = User::findOrFail($this->userId);  <- find method commented out to avoid redundancy in db calls. Open to suggestions
        $voucherService->storeVouchersFromXmlContents($this->xmlContent, $this->user);
    }
}
