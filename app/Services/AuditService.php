<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditService
{
    public static function log(
        string $event,
        string $module,
        string $description = '',
        array  $old = [],
        array  $new = [],
        ?int   $auditableId   = null,
        string $auditableType = '',
        ?int   $branchId      = null,
        string $branchName    = ''
    ): void {
        try {
            $user   = auth()->user();
            $req    = request();

            DB::table('audit_logs')->insert([
                'auditable_type' => $auditableType ?: 'System',
                'auditable_id'   => $auditableId,
                'event'          => $event,
                'module'         => $module,
                'description'    => $description ?: null,
                'old_values'     => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'new_values'     => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                'user_id'        => $user?->id,
                'user_name'      => $user?->name,
                'role'           => $user?->getRoleNames()->first(),
                'branch_id'      => $branchId ?? session('active_branch_id'),
                'branch_name'    => $branchName ?: null,
                'ip_address'     => $req?->ip(),
                'user_agent'     => $req?->userAgent(),
                'url'            => $req?->fullUrl(),
                'method'         => $req?->method(),
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditService write failed', [
                'event'  => $event,
                'module' => $module,
                'error'  => $e->getMessage(),
            ]);
        }
    }

    public static function logLogin($user, $request): void
    {
        try {
            DB::table('audit_logs')->insert([
                'auditable_type' => 'User',
                'auditable_id'   => $user->id,
                'event'          => 'login',
                'module'         => 'Auth',
                'description'    => 'User login',
                'old_values'     => null,
                'new_values'     => null,
                'user_id'        => $user->id,
                'user_name'      => $user->name,
                'role'           => $user->getRoleNames()->first(),
                'branch_id'      => session('active_branch_id'),
                'branch_name'    => null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'url'            => $request->fullUrl(),
                'method'         => 'POST',
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditService login failed', ['error' => $e->getMessage()]);
        }
    }

    public static function logLogout($user, $request): void
    {
        try {
            DB::table('audit_logs')->insert([
                'auditable_type' => 'User',
                'auditable_id'   => $user->id,
                'event'          => 'logout',
                'module'         => 'Auth',
                'description'    => 'User logout',
                'old_values'     => null,
                'new_values'     => null,
                'user_id'        => $user->id,
                'user_name'      => $user->name,
                'role'           => $user->getRoleNames()->first(),
                'branch_id'      => session('active_branch_id'),
                'branch_name'    => null,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'url'            => $request->fullUrl(),
                'method'         => 'POST',
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditService logout failed', ['error' => $e->getMessage()]);
        }
    }

    public static function logExport(string $module, string $description = ''): void
    {
        static::log('export', $module, $description ?: "Export $module");
    }

    public static function logPrint(string $module, string $description = '', ?int $resourceId = null): void
    {
        static::log('print', $module, $description ?: "Print $module", [], [], $resourceId, $module);
    }
}
