<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\NotificationConfig;
use App\Models\NotificationLog;
use App\Services\BranchContext;
use Illuminate\Http\Request;

class NotificationConfigController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    public function index(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');
        $branches     = $isSuperadmin ? Branch::orderBy('name')->get() : collect();

        $branchId = $isSuperadmin
            ? ($request->filled('branch_id') ? (int) $request->branch_id : null)
            : ($this->branch->idOrNull() ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id') ?: null);

        // Load or build default configs for this branch
        $configs = [];
        foreach (NotificationConfig::$types as $type => $label) {
            $config = NotificationConfig::firstOrNew(
                ['branch_id' => $branchId, 'type' => $type],
                [
                    'days_before'       => 3,
                    'channels'          => ['in_app'],
                    'is_active'         => false,
                    'email_recipients'  => [],
                    'whatsapp_numbers'  => [],
                ]
            );
            $configs[$type] = $config;
        }

        $recentLogs = NotificationLog::query()
            ->when($branchId && !$isSuperadmin, fn ($q) => $q->where('branch_id', $branchId))
            ->when($branchId && $isSuperadmin,  fn ($q) => $q->where('branch_id', $branchId))
            ->latest()
            ->limit(30)
            ->get();

        return view('notification-config.index', compact(
            'configs', 'recentLogs', 'branches', 'branchId', 'isSuperadmin'
        ) + ['filters' => $request->only(['branch_id'])]);
    }

    public function update(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');

        $branchId = $isSuperadmin
            ? ($request->filled('branch_id') ? (int) $request->branch_id : null)
            : ($this->branch->idOrNull() ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id') ?: null);

        if (!$isSuperadmin && $branchId) {
            abort_unless(
                $user->branches()->where('branches.id', $branchId)->exists(),
                403
            );
        }

        $types = NotificationConfig::$types;

        foreach (array_keys($types) as $type) {
            $typeData = $request->input("configs.{$type}", []);

            $channels = [];
            if (!empty($typeData['channel_in_app']))   $channels[] = 'in_app';
            if (!empty($typeData['channel_email']))     $channels[] = 'email';
            if (!empty($typeData['channel_whatsapp']))  $channels[] = 'whatsapp';
            if (empty($channels)) $channels = ['in_app'];

            $emailRecipients = collect(explode("\n", $typeData['email_recipients'] ?? ''))
                ->map(fn ($e) => trim($e))
                ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
                ->values()
                ->all();

            $whatsappNumbers = collect(explode("\n", $typeData['whatsapp_numbers'] ?? ''))
                ->map(fn ($n) => trim($n))
                ->filter()
                ->values()
                ->all();

            $isTimeBased = in_array($type, NotificationConfig::$timeBased);

            NotificationConfig::updateOrCreate(
                ['branch_id' => $branchId, 'type' => $type],
                [
                    'is_active'          => !empty($typeData['is_active']),
                    'days_before'        => $isTimeBased ? max(0, (int) ($typeData['days_before'] ?? 3)) : 0,
                    'channels'           => $channels,
                    'email_recipients'   => $emailRecipients,
                    'whatsapp_numbers'   => $whatsappNumbers,
                    'whatsapp_api_url'   => $typeData['whatsapp_api_url'] ?? null,
                    'whatsapp_api_token' => $typeData['whatsapp_api_token'] ?? null,
                ]
            );
        }

        return redirect()->route('notification-config.index', $request->only(['branch_id']))
            ->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }

    public function logs(Request $request)
    {
        $user         = auth()->user();
        $isSuperadmin = $user->hasRole('superadmin');
        $branches     = $isSuperadmin ? Branch::orderBy('name')->get() : collect();

        $branchId = $isSuperadmin
            ? ($request->filled('branch_id') ? (int) $request->branch_id : null)
            : ($this->branch->idOrNull() ?? (int) $user->branches()->orderBy('branches.id')->value('branches.id') ?: null);

        $logs = NotificationLog::query()
            ->when(!$isSuperadmin && $branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($isSuperadmin && $branchId,  fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->filled('type'),    fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('status'),  fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('notification-config.logs', compact(
            'logs', 'branches', 'branchId', 'isSuperadmin'
        ) + ['filters' => $request->only(['branch_id', 'type', 'status'])]);
    }
}
