<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateExportJob;
use App\Models\ExportRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportStatusController extends Controller
{
    public function show(ExportRequest $exportRequest)
    {
        $this->authorizeRequest($exportRequest);

        return view('export-status.show', compact('exportRequest'));
    }

    public function check(ExportRequest $exportRequest)
    {
        $this->authorizeRequest($exportRequest);

        // If the job is still pending and no queue worker has picked it up,
        // process it synchronously here so it works without a running queue worker.
        if ($exportRequest->isPending()) {
            $claimed = DB::table('export_requests')
                ->where('id', $exportRequest->id)
                ->where('status', 'pending')
                ->update(['status' => 'processing', 'updated_at' => now()]);

            if ($claimed) {
                $jobClass = match ($exportRequest->type) {
                    'payroll_generate' => \App\Jobs\ProcessPayrollJob::class,
                    'payroll_refresh'  => \App\Jobs\ProcessPayrollRefreshJob::class,
                    default            => GenerateExportJob::class,
                };
                app()->call([new $jobClass($exportRequest->id), 'handle']);
                $exportRequest->refresh();
            }
        }

        $response = ['status' => $exportRequest->status];

        if ($exportRequest->isDone()) {
            if ($exportRequest->result_id) {
                $response['redirect_url'] = route('payrolls.show', $exportRequest->result_id);
            } elseif ($exportRequest->file_path) {
                $response['download_url'] = route('export-status.download', $exportRequest->id);
            }
        }

        if ($exportRequest->isFailed()) {
            $response['error'] = $exportRequest->error_message;
        }

        return response()->json($response);
    }

    public function download(ExportRequest $exportRequest)
    {
        $this->authorizeRequest($exportRequest);

        abort_unless($exportRequest->isDone() && $exportRequest->file_path, 404);
        abort_unless(Storage::disk('local')->exists($exportRequest->file_path), 404);

        return Storage::disk('local')->download(
            $exportRequest->file_path,
            $exportRequest->file_name
        );
    }

    private function authorizeRequest(ExportRequest $exportRequest): void
    {
        $user = auth()->user();
        if (!$user->hasRole('superadmin') && $exportRequest->user_id !== $user->id) {
            abort(403);
        }
    }
}
