<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Stores pre-save originals keyed by spl_object_id to survive the updating→updated gap.
     * Static per-class (each model class using this trait gets its own copy).
     */
    private static array $_auditPending = [];

    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->_writeAuditLog('created', [], $model->getAttributes());
        });

        static::updating(function ($model) {
            static::$_auditPending[spl_object_id($model)] = $model->getOriginal();
        });

        static::updated(function ($model) {
            $key      = spl_object_id($model);
            $original = static::$_auditPending[$key] ?? [];
            unset(static::$_auditPending[$key]);

            $changed = array_keys($model->getChanges());
            if (empty($changed)) return;

            $old = array_intersect_key($original,            array_flip($changed));
            $new = array_intersect_key($model->getChanges(), array_flip($changed));

            $model->_writeAuditLog('updated', $old, $new);
        });

        static::deleted(function ($model) {
            $model->_writeAuditLog('deleted', $model->getOriginal(), []);
        });

        // Register restored only for models that use SoftDeletes.
        if (method_exists(static::class, 'restore')) {
            static::restored(function ($model) {
                $model->_writeAuditLog('restored', [], []);
            });
        }
    }

    /**
     * @param array<string,mixed> $old
     * @param array<string,mixed> $new
     */
    public function _writeAuditLog(string $event, array $old, array $new): void
    {
        $globalExclude = ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'];
        $modelExclude  = property_exists($this, 'auditExclude') ? $this->auditExclude : [];
        $exclude       = array_flip(array_merge($globalExclude, $modelExclude));

        $filter = static fn(array $data): array => array_diff_key($data, $exclude);

        $old = $filter($old);
        $new = $filter($new);

        // Suppress 'updated' when only excluded fields changed (e.g. touch())
        if ($event === 'updated' && empty($old) && empty($new)) {
            return;
        }

        try {
            $user   = auth()->user();
            $req    = request();
            $module = property_exists($this, 'auditModule') ? $this->auditModule : class_basename($this);

            DB::table('audit_logs')->insert([
                'auditable_type' => class_basename($this),
                'auditable_id'   => $this->getKey(),
                'event'          => $event,
                'module'         => $module,
                'old_values'     => $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                'new_values'     => $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                'user_id'        => $user?->id,
                'user_name'      => $user?->name,
                'role'           => $user?->getRoleNames()->first(),
                'branch_id'      => $this->branch_id ?? null,
                'branch_name'    => null, // resolved by observer if needed
                'ip_address'     => $req?->ip(),
                'user_agent'     => $req?->userAgent(),
                'url'            => $req?->fullUrl(),
                'method'         => $req?->method(),
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            // Audit failure must never break the main financial flow — log and continue.
            Log::error('Audit log write failed', [
                'model'  => class_basename($this),
                'id'     => $this->getKey(),
                'event'  => $event,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
