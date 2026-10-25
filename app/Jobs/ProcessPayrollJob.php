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

class ProcessPayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300; // 5 minutes

    public function __construct(private int $exportRequestId) {}

    public function handle(PayrollCalculatorService $calculator): void
    {
        $req = ExportRequest::find($this->exportRequestId);
        if (!$req) return;

        $req->markProcessing();

        try {
            $p = $req->params;

            $payroll = $calculator->generate(
                branchId:     (int)    $p['branch_id'],
                month:        (string) $p['month'],
                workingDays:  (int)    $p['working_days'],
                generatedBy:  isset($p['generated_by']) ? (int) $p['generated_by'] : null,
                notes:        $p['notes'] ?? null,
                regenerate:   (bool)   ($p['regenerate'] ?? false),
            );

            $req->markDone(['result_id' => $payroll->id]);

        } catch (\Throwable $e) {
            Log::error('[ProcessPayrollJob] failed', [
                'export_request_id' => $this->exportRequestId,
                'error'             => $e->getMessage(),
            ]);
            $req->markFailed($e->getMessage());
        }
    }
}
