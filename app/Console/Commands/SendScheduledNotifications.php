<?php

namespace App\Console\Commands;

use App\Models\NotificationConfig;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendScheduledNotifications extends Command
{
    protected $signature   = 'notify:scheduled {--dry-run : Print what would be sent without actually sending}';
    protected $description = 'Send scheduled automated notifications (hutang/piutang due, warranty, low stock)';

    public function handle(): int
    {
        $configs = NotificationConfig::where('is_active', true)->get();

        if ($configs->isEmpty()) {
            $this->info('No active notification configs found.');
            return 0;
        }

        foreach ($configs as $config) {
            match ($config->type) {
                'hutang_due'      => $this->processHutangDue($config),
                'piutang_due'     => $this->processPiutangDue($config),
                'warranty_expiry' => $this->processWarrantyExpiry($config),
                'low_film_stock'  => $this->processLowFilmStock($config),
                'low_goods_stock' => $this->processLowGoodsStock($config),
                default           => null,
            };
        }

        $this->info('Scheduled notifications processed.');
        return 0;
    }

    // ── Handlers ──────────────────────────────────────────────────────────────

    private function processHutangDue(NotificationConfig $config): void
    {
        $targetDate = now()->addDays($config->days_before)->toDateString();

        $items = DB::table('payables')
            ->whereNotIn('status', ['paid'])
            ->whereDate('due_date', $targetDate)
            ->when($config->branch_id, fn ($q) => $q->where('branch_id', $config->branch_id))
            ->get();

        foreach ($items as $item) {
            $msg = "⚠️ Hutang ke {$item->vendor_name} sebesar Rp ".number_format($item->amount - $item->paid_amount, 0, ',', '.')
                ." jatuh tempo pada {$item->due_date}.";
            $this->dispatch($config, $msg, 'App\Models\Payable', $item->id, $item->branch_id);
        }
    }

    private function processPiutangDue(NotificationConfig $config): void
    {
        $targetDate = now()->addDays($config->days_before)->toDateString();

        $items = DB::table('receivables')
            ->whereNotIn('status', ['paid'])
            ->whereDate('due_date', $targetDate)
            ->when($config->branch_id, fn ($q) => $q->where('branch_id', $config->branch_id))
            ->get();

        foreach ($items as $item) {
            $msg = "💰 Piutang dari {$item->customer_name} sebesar Rp ".number_format($item->amount - $item->paid_amount, 0, ',', '.')
                ." jatuh tempo pada {$item->due_date}.";
            $this->dispatch($config, $msg, 'App\Models\Receivable', $item->id, $item->branch_id);
        }
    }

    private function processWarrantyExpiry(NotificationConfig $config): void
    {
        $targetDate = now()->addDays($config->days_before)->toDateString();

        $items = DB::table('customers')
            ->whereDate('warantee_end', $targetDate)
            ->when($config->branch_id, fn ($q) => $q->where('branch_id', $config->branch_id))
            ->get();

        foreach ($items as $item) {
            $msg = "📋 Garansi pelanggan {$item->name} ({$item->vehicle_brand} {$item->vehicle_model}) berakhir pada {$item->warantee_end}.";
            $this->dispatch($config, $msg, 'App\Models\Customer', $item->id, $item->branch_id);
        }
    }

    private function processLowFilmStock(NotificationConfig $config): void
    {
        $items = DB::table('film_stock_pools')
            ->where('remaining_length_meter', '<=', 10)
            ->when($config->branch_id, fn ($q) => $q->where('branch_id', $config->branch_id))
            ->get();

        foreach ($items as $item) {
            $msg = "🎞️ Stok film '{$item->name}' tinggal {$item->remaining_length_meter}m (kritis).";
            $this->dispatch($config, $msg, null, null, $item->branch_id ?? $config->branch_id);
        }
    }

    private function processLowGoodsStock(NotificationConfig $config): void
    {
        $items = DB::table('supporting_goods')
            ->whereRaw('current_stock <= minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->when($config->branch_id, fn ($q) => $q->where('branch_id', $config->branch_id))
            ->get();

        foreach ($items as $item) {
            $msg = "📦 Stok barang '{$item->name}' ({$item->category}) tinggal {$item->current_stock} {$item->unit} (di bawah minimum {$item->minimum_stock}).";
            $this->dispatch($config, $msg, null, null, $item->branch_id ?? $config->branch_id);
        }
    }

    // ── Dispatch to channels ──────────────────────────────────────────────────

    private function dispatch(NotificationConfig $config, string $message, ?string $relatableType, ?int $relatableId, ?int $branchId): void
    {
        foreach ($config->channels as $channel) {
            try {
                match ($channel) {
                    'in_app'   => $this->sendInApp($config, $message, $branchId),
                    'email'    => $this->sendEmail($config, $message),
                    'whatsapp' => $this->sendWhatsapp($config, $message),
                    default    => null,
                };

                $this->logResult($config, $channel, $message, 'sent', null, $relatableType, $relatableId, $branchId);
                $this->line("  [{$channel}] Sent: ".mb_substr($message, 0, 60).'…');

            } catch (\Throwable $e) {
                $this->logResult($config, $channel, $message, 'failed', $e->getMessage(), $relatableType, $relatableId, $branchId);
                $this->error("  [{$channel}] Failed: ".$e->getMessage());
                Log::error("notify:scheduled [{$channel}] {$e->getMessage()}");
            }
        }
    }

    private function sendInApp(NotificationConfig $config, string $message): void
    {
        if ($this->option('dry-run')) return;

        // Notify superadmin + branch admins
        $users = User::role(['superadmin', 'admin'])
            ->when($config->branch_id, fn ($q) => $q->whereHas('branches', fn ($b) => $b->where('branches.id', $config->branch_id)))
            ->get();

        foreach ($users as $user) {
            $user->notify(new \App\Notifications\AutomatedAlert($message, $config->type));
        }
    }

    private function sendEmail(NotificationConfig $config, string $message): void
    {
        if ($this->option('dry-run')) return;

        $recipients = $config->email_recipients ?? [];
        if (empty($recipients)) return;

        foreach ($recipients as $email) {
            Mail::raw($message, fn ($mail) => $mail->to($email)->subject('[Acegard] '.NotificationConfig::$types[$config->type]));
        }
    }

    private function sendWhatsapp(NotificationConfig $config, string $message): void
    {
        if ($this->option('dry-run')) return;

        $numbers = $config->whatsapp_numbers ?? [];
        $apiUrl  = $config->whatsapp_api_url;
        $token   = $config->whatsapp_api_token;

        if (empty($numbers) || !$apiUrl || !$token) return;

        foreach ($numbers as $number) {
            Http::timeout(10)->post($apiUrl, [
                'target'  => $number,
                'message' => $message,
                'token'   => $token,
            ]);
        }
    }

    private function logResult(NotificationConfig $config, string $channel, string $message, string $status, ?string $error, ?string $relatableType, ?int $relatableId, ?int $branchId): void
    {
        NotificationLog::create([
            'branch_id'      => $branchId,
            'type'           => $config->type,
            'channel'        => $channel,
            'recipient'      => implode(', ', match ($channel) {
                'email'     => $config->email_recipients ?? [],
                'whatsapp'  => $config->whatsapp_numbers ?? [],
                default     => ['in-app'],
            }),
            'message'        => $message,
            'status'         => $status,
            'error'          => $error,
            'relatable_type' => $relatableType,
            'relatable_id'   => $relatableId,
        ]);
    }
}
