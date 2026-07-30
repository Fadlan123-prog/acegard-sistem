<?php

namespace App\Jobs;

use App\Models\ExportRequest;
use App\Models\Payroll;
use App\Services\PayrollCalculatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayrollRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300;

    public function __construct(private int $exportRequestId) {}

    public function handle(PayrollCalculatorService $calculator): void
    {
        $req = ExportRequest::find($this->exportRequestId);
        if (!$req) return;

        $req->markProcessing();

        try {
            $payroll = Payroll::findOrFail($req->params['payroll_id']);
            $calculator->refresh($payroll);
            $req->markDone(['result_id' => $payroll->id]);
        } catch (\Throwable $e) {
            Log::error('[ProcessPayrollRefreshJob] failed', [
                'export_request_id' => $this->exportRequestId,
                'error'             => $e->getMessage(),
            ]);
            $req->markFailed($e->getMessage());
        }
    }
}
