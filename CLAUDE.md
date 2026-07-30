# Acegard Sistem

Laravel 12 / PHP 8.2 ERP for an automotive PPF (paint protection film), window tinting, and ceramic coating company. Multi-branch, role-based (via `spatie/laravel-permission`), server-rendered Blade throughout — no SPA layer.

## UI patterns

There are **two intentional, sanctioned** UI patterns. Both are correct — pick based on form complexity, not habit. Do not blend them and do not treat one as "wrong."

### 1. Simple pattern — single card + `x-ui.*` components

Used for standard CRUD: one entity, a flat or lightly-grouped set of fields, a single table.

- Wrapper: `<x-ui.card>`, fields via `<x-ui.form-field>`, actions via `<x-ui.button>`, status via `<x-ui.badge>`, notices via `<x-ui.alert>`, stats via `<x-ui.stats-card>`, empty states via `<x-ui.empty-state>`.
- Component source: `resources/views/components/ui/*.blade.php` + `app/View/Components/Ui/*.php`, registered via `Blade::componentNamespace()` in `AppServiceProvider`.
- Design tokens / classes (`.ui-btn`, `.ui-card`, `.ui-badge`, etc.): `public/assets/css/ui-components.css`.
- Icons: always `<iconify-icon icon="mdi:...">`. Never RemixIcon (`ri-*`) or Bootstrap Icons (`bi-*`) — those were fully removed from the admin panel; only `resources/views/partials/sidebar.blade.php` and `layout/index.blade.php` chrome may still reference `ri-*` classes tied to the base admin theme.
- Reference implementations: `resources/views/purchases/*`, `resources/views/petty-cash/*`, `resources/views/payrolls/*`, `resources/views/suppliers/*`, `resources/views/users/*`.

### 2. Wizard pattern — sticky sidebar + sectioned cards

Used for complex, multi-section entity forms: nested/dynamic sub-arrays (e.g. multiple installations, multiple products), several logically distinct sections (identity, vehicle, installation, documents), or forms long enough to need in-page navigation.

- Layout: `.cust-layout` grid (220px sticky sidebar nav + main content), `.cust-section` cards each with an icon-chip header (`.sec-icon`), raw `form-control`/`form-label` fields (not `x-ui.form-field` — this pattern predates it and hasn't been retrofitted).
- Icons: also `<iconify-icon icon="mdi:...">` — only the *layout/field* markup differs from pattern 1, not the icon convention.
- Reference implementations: `resources/views/customer/{create,edit}.blade.php`, `resources/views/customer/ppf/{create,edit}.blade.php`, `resources/views/customer/building/{create,edit}.blade.php`, `resources/views/branches/{create,edit}.blade.php`, `resources/views/employee/{create,edit}.blade.php`, `resources/views/quotations/_form.blade.php`, `resources/views/sales-orders/_form.blade.php`.

### Choosing

New simple CRUD form → pattern 1. New complex multi-section form → copy the structure of an existing wizard-pattern file, don't invent a third pattern. When in doubt, match the closest existing page in the same module family.

## Branch scoping

Non-superadmin users are restricted to their assigned branch(es) via `app/Http/Middleware/EnsureBranchAccess.php` (session `active_branch_id`, guaranteed to always be a member of the user's `->branches()` set) and `app/Services/BranchContext.php` (`$this->branch->id()` / `idOrNull()`). Every controller method that reads or mutates branch-owned data must check branch ownership explicitly — it is not automatic. The established pattern (see `CustomerController`, `PettyCashController`) is either:

```php
if (!$user->hasRole('superadmin')) {
    $allowedBranchIds = $user->branches()->pluck('branches.id')->all();
    abort_unless(in_array($model->branch_id, $allowedBranchIds), 403);
}
```

or, for a single active-branch check: `(int) $model->branch_id !== $this->branch->id()`.
