<?php

use Illuminate\Support\Facades\Route;

// Auth & Core
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatisticsController;

// Branch & Users
use App\Http\Controllers\BranchController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\EmployeeController;

// Frontend / Public
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\GalleryController;

// Customers
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerBuildingController;
use App\Http\Controllers\CustomerPpfController;
use App\Http\Controllers\CustomerPpfInstallationController;

// Invoices
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceBuildingController;
use App\Http\Controllers\PpfInvoiceController;

// Warranty
use App\Http\Controllers\WarrantyCardController;
use App\Http\Controllers\PublicWarrantyController;
use App\Http\Controllers\PublicWarrantyCardController;
use App\Http\Controllers\Admin\AdminWarrantyClaimController;
use App\Http\Controllers\Admin\AdminWarrantyClaimInvoiceController;

// PPF
use App\Http\Controllers\PpfProductController;

// Film Stock
use App\Http\Controllers\FilmStockController;
use App\Http\Controllers\FilmStockTransferController;
use App\Http\Controllers\FilmComplaintController;
use App\Http\Controllers\SupportingGoodController;

// Commission & Payroll
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CommissionWithdrawalController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ExportStatusController;
use App\Http\Controllers\MealAllowanceController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\HutangPiutangController;
use App\Http\Controllers\BankReconciliationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\NotificationConfigController;

// Operations
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TeknisiDashboardController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReceiptController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\LeaveRequestController;

// Role Dashboards
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\MarketingDashboardController;
use App\Http\Controllers\CsDashboardController;
use App\Http\Controllers\ManagerOpsDashboardController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\PurchasingDashboardController;
use App\Http\Controllers\EmployeeRankingController;

// CRM
use App\Http\Controllers\LeadController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\JadwalPemasanganController;
use App\Http\Controllers\SpkPublicController;

/*
|--------------------------------------------------------------------------
| FRONTEND / PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::controller(FrontendController::class)->group(function () {
    Route::get('/', 'home')->name('frontend.home');
    Route::get('/service/car', 'serviceCar')->name('frontend.service.car');
    Route::get('/service/building', 'serviceBuilding')->name('frontend.service.building');
    Route::get('/service/franchise', 'serviceFranchise')->name('frontend.service.franchise');
    Route::get('/about', 'about')->name('frontend.about');
    Route::get('/contact', 'contact')->name('frontend.contact');
    Route::get('/kaca-film-mobil', 'kacaFilmMobil')->name('frontend.kaca.film.mobil');
    Route::get('/ppf-mobil', 'ppfAds')->name('frontend.ppf.ads');
});

Route::get('/promo/kaca-film-mobil', [PromotionController::class, 'car'])
    ->name('frontend.promotion.car');


/*
|--------------------------------------------------------------------------
| PUBLIC WARRANTY ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('warranty')->name('warranty.')->group(function () {
    Route::get('/', [PublicWarrantyController::class, 'form'])->name('form');
    Route::post('/lookup', [PublicWarrantyController::class, 'lookup'])->middleware('throttle:10,1')->name('lookup');
    Route::get('/result', [PublicWarrantyController::class, 'result'])->middleware('throttle:20,1')->name('result');
    Route::post('/claim', [PublicWarrantyController::class, 'storeClaim'])->middleware('throttle:5,1')->name('claim.store');
    Route::get('/card/{type}/{id}/download', [PublicWarrantyController::class, 'downloadCard'])->middleware('throttle:20,1')->name('card.download.public');

    Route::get('/card/{cardNumber}/preview/{cp?}', [WarrantyCardController::class, 'preview'])->middleware('throttle:10,1')->name('card.preview');
    Route::get('/card/{cardNumber}/download/{cp?}', [WarrantyCardController::class, 'download'])->middleware('throttle:10,1')->name('card.download');

    Route::get('/ppf-card/{installationId}/{side}/preview', [WarrantyCardController::class, 'ppfPreview'])
        ->whereIn('side', ['front', 'back'])->middleware('throttle:10,1')->name('ppf.card.preview');
    Route::get('/ppf-card/{installationId}/{side}/download', [WarrantyCardController::class, 'ppfDownload'])
        ->whereIn('side', ['front', 'back'])->middleware('throttle:10,1')->name('ppf.card.download');
});

// Public warranty cards — di luar named group agar nama route tidak dapat prefix 'warranty.'
Route::prefix('warranty/cards')->name('public.warranty.card.')->middleware('throttle:10,1')->group(function () {
    Route::get('/{cardNumber}/preview', [PublicWarrantyCardController::class, 'previewFilm'])->name('preview');
    Route::get('/{cardNumber}/download', [PublicWarrantyCardController::class, 'downloadFilm'])->name('download');
});

Route::prefix('warranty/ppf-cards')->name('public.warranty.ppf.')->middleware('throttle:10,1')->group(function () {
    Route::get('/{installationId}/preview', [PublicWarrantyCardController::class, 'previewPpf'])->name('preview');
    Route::get('/{installationId}/download', [PublicWarrantyCardController::class, 'downloadPpf'])->name('download');
});


/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES (all roles)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::prefix('notifications')->name('notifications.')->controller(NotificationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/open/{id}', 'open')->name('open');
        Route::get('/read-all', 'readAll')->name('readAll');
    });

    // Self-service leave requests (semua role)
    Route::prefix('my-leave-requests')->name('my-leave-requests.')->controller(\App\Http\Controllers\MyLeaveRequestController::class)->group(function () {
        Route::get('/',                         'index')->name('index');
        Route::get('/create',                   'create')->name('create');
        Route::post('/',                        'store')->name('store');
        Route::get('/{leaveRequest}',           'show')->name('show');
        Route::put('/{leaveRequest}/cancel',    'cancel')->name('cancel');
    });
});


/*
|--------------------------------------------------------------------------
| TEKNISI & KENEK ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:teknisi|kenek'])
    ->prefix('teknisi')->name('teknisi.')
    ->group(function () {
        Route::get('/dashboard', [TeknisiDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/komisi', [TeknisiDashboardController::class, 'komisSaya'])->name('komisi');
        Route::get('/pencairan', [TeknisiDashboardController::class, 'riwayatPencairan'])->name('pencairan');
        Route::get('/orders', [TeknisiDashboardController::class, 'ordersList'])->name('orders.index');
        Route::get('/orders/{customer}', [TeknisiDashboardController::class, 'orderDetail'])->name('orders.show');
        Route::post('/job/{customer}/accept', [TeknisiDashboardController::class, 'acceptJob'])->name('job.accept');
        Route::get('/job/{customer}/complete', [TeknisiDashboardController::class, 'showCompleteForm'])->name('job.complete.form');
        Route::post('/job/{customer}/complete', [TeknisiDashboardController::class, 'completeJob'])->name('job.complete');
        Route::post('/withdrawal/request', [TeknisiDashboardController::class, 'requestWithdrawal'])->name('withdrawal.request');
    });


/*
|--------------------------------------------------------------------------
| LEADS / CRM ROUTES (semua staff yang punya branch access)
|--------------------------------------------------------------------------
*/

$crmRoles = 'superadmin|admin|marketing|manager_operasional|manager_marketing|finance|purchasing|customer_service';

Route::middleware(['auth', 'branch.access', "role:{$crmRoles}"])
    ->get('/dashboard/crm', [DashboardController::class, 'crmDashboard'])
    ->name('dashboard.crm');

Route::middleware(['auth', 'branch.access', "role:{$crmRoles}"])
    ->prefix('leads')->name('leads.')
    ->controller(LeadController::class)
    ->group(function () {
        Route::get('/',                 'index')->name('index');
        Route::get('/create',           'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::get('/{lead}',           'show')->name('show');
        Route::get('/{lead}/edit',      'edit')->name('edit');
        Route::put('/{lead}',           'update')->name('update');
        Route::delete('/{lead}',        'destroy')->name('destroy');
        Route::post('/{lead}/activity', 'addActivity')->name('activity');
    });

Route::middleware(['auth', 'branch.access', "role:{$crmRoles}"])
    ->prefix('quotations')->name('quotations.')
    ->controller(QuotationController::class)
    ->group(function () {
        Route::get('/',                    'index')->name('index');
        Route::get('/create',              'create')->name('create');
        Route::post('/',                   'store')->name('store');
        Route::get('/{quotation}',         'show')->name('show');
        Route::get('/{quotation}/edit',    'edit')->name('edit');
        Route::put('/{quotation}',         'update')->name('update');
        Route::delete('/{quotation}',      'destroy')->name('destroy');
        Route::post('/{quotation}/send',   'send')->name('send');
        Route::post('/{quotation}/accept', 'accept')->name('accept');
        Route::post('/{quotation}/reject', 'reject')->name('reject');
        Route::get('/{quotation}/print',   'print')->name('print');
    });

Route::middleware(['auth', 'branch.access', "role:{$crmRoles}"])
    ->prefix('sales-orders')->name('sales-orders.')
    ->controller(SalesOrderController::class)
    ->group(function () {
        Route::get('/',                       'index')->name('index');
        Route::get('/create',                 'create')->name('create');
        Route::post('/',                      'store')->name('store');
        Route::get('/{salesOrder}',           'show')->name('show');
        Route::get('/{salesOrder}/edit',      'edit')->name('edit');
        Route::put('/{salesOrder}',           'update')->name('update');
        Route::delete('/{salesOrder}',        'destroy')->name('destroy');
        Route::post('/{salesOrder}/confirm',    'confirm')->name('confirm');
        Route::post('/{salesOrder}/start-work', 'startWork')->name('start-work');
        Route::post('/{salesOrder}/complete',   'complete')->name('complete');
        Route::post('/{salesOrder}/cancel',     'cancel')->name('cancel');
        Route::post('/{salesOrder}/issue-spk',  'issueSpk')->name('issue-spk');
        Route::get('/{salesOrder}/spk',         'printSpk')->name('spk');
    });

// Public SPK verification (no auth required)
Route::get('/spk/{token}', [SpkPublicController::class, 'show'])
    ->name('spk.public')
    ->middleware('throttle:60,1');

Route::middleware(['auth', 'branch.access', "role:{$crmRoles}"])
    ->get('/jadwal-pemasangan', [JadwalPemasanganController::class, 'index'])
    ->name('jadwal.index');


/*
|--------------------------------------------------------------------------
| SUPERADMIN + ADMIN ROUTES
|--------------------------------------------------------------------------
*/

// Dashboard accessible to all staff roles (superadmin|admin handle their views inside the controller)
$allStaffRoles = 'superadmin|admin|manager_operasional|manager_marketing|finance|purchasing';
Route::middleware(['auth', 'branch.access', "role:{$allStaffRoles}"])
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Broader staff access: superadmin, admin, and additional management roles.
// Sidebar limits visible links; controller-level permission gates (Task 8) add fine-grained checks.
Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|owner|manager_operasional|manager_marketing|marketing|finance|purchasing|customer_service'])->group(function () {

    // Dashboard & Utilities (switch-branch stays superadmin|admin only)
    // NOTE: /dashboard is above this group with its own middleware
    Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('switch-branch');
    Route::get('/reset-active-branch', [BranchController::class, 'resetActiveBranch'])->name('branch.reset');

    // Work Orders
    Route::prefix('work-orders')->name('work-orders.')->group(function () {
        Route::get('/', [WorkOrderController::class, 'index'])->name('index');
        Route::post('/{customer}/assign', [WorkOrderController::class, 'assign'])->name('assign');
        Route::post('/{customer}/qc-approve', [WorkOrderController::class, 'qcApprove'])->name('qc-approve');
        Route::post('/{customer}/qc-reject', [WorkOrderController::class, 'qcReject'])->name('qc-reject');
    });

    // Customer - Mobil
    Route::prefix('customers/cars')->name('customer.')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/export', 'export')->name('export');
        Route::delete('/bulk-delete', 'bulkDelete')->name('bulkDelete');
        Route::get('/card/{custCode}/{catproductCode}', 'download')->name('card.download');
        Route::get('/{customer}/edit', 'edit')->name('edit');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    Route::get('/products-by-category/{id}', [CustomerController::class, 'getProductsByCategory'])
        ->name('products.by-category');

    // Customer Warranty Card (internal)
    Route::get('/warranty/card/{cardNumber}', [CustomerController::class, 'download'])
        ->name('customer.warranty.card.download');
    Route::get('/warranty/card/{cardNumber}/cp/{cp}', [CustomerController::class, 'download'])
        ->whereNumber('cp')->name('customer.warranty.card.download.cp');

    // Invoice - Mobil
    Route::prefix('invoice')->name('invoice.')->controller(InvoiceController::class)->group(function () {
        Route::get('/create/{customer}', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{invoice}', 'show')->name('show');
        Route::get('/{invoice}/download', 'download')->name('download');
        Route::get('/{invoice}/edit', 'edit')->name('edit');
        Route::put('/{invoice}', 'update')->name('update');
        Route::delete('/{invoice}', 'destroy')->name('destroy');
        Route::post('/{invoice}/pelunasan', 'recordPelunasan')->name('pelunasan');
        Route::post('/{invoice}/release-commission', 'releaseCommission')->name('release-commission');
        Route::post('/{invoice}/rerelease-commission', 'rereleaseCommission')->name('rerelease-commission');
    });

    // Customer - Building
    Route::prefix('customers/building')->name('customer.building.')->controller(CustomerBuildingController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::delete('/bulk-delete', 'bulkDelete')->name('bulkDelete');
        Route::get('/{customer}/edit', 'edit')->name('edit');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    Route::get('/products-by-category-building/{id}', [CustomerBuildingController::class, 'getProductsByCategory'])
        ->name('products.by-category-building');

    // Invoice - Building
    Route::prefix('invoice-building')->name('invoice.building.')->controller(InvoiceBuildingController::class)->group(function () {
        Route::get('/create/{customer}', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{invoice}', 'show')->name('show');
        Route::get('/{invoice}/edit', 'edit')->name('edit');
        Route::put('/{invoice}', 'update')->name('update');
        Route::get('/{invoice}/download', 'download')->name('download');
        Route::delete('/{invoice}', 'destroy')->name('destroy');
        Route::post('/{invoice}/pelunasan', 'recordPelunasan')->name('pelunasan');
    });

    // Customer - PPF
    Route::resource('ppf-customers', CustomerPpfController::class)
        ->only(['index', 'create', 'edit', 'update', 'store', 'show', 'destroy'])
        ->names('ppf-customers');

    Route::resource('ppf-installations', CustomerPpfInstallationController::class);

    // Invoice - PPF
    Route::prefix('ppf-invoices')->name('ppf-invoices.')->controller(PpfInvoiceController::class)->group(function () {
        Route::get('/create/{installationId}', 'create')->name('create');
        Route::post('/store/{installationId}', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/print', 'print')->name('print');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/{invoice}/download', 'download')->name('download');
        Route::post('/{invoice}/release-commission', 'releaseCommission')->name('release-commission');
    });

    // Commission Withdrawals (superadmin only)
    Route::prefix('commission/withdrawals')->name('commission.withdrawals.')
        ->middleware('role:superadmin')
        ->controller(CommissionWithdrawalController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{batch}', 'show')->name('show');
            Route::post('/{batch}/approve', 'approve')->name('approve');
            Route::post('/{batch}/reject', 'reject')->name('reject');
        });
});


/*
|--------------------------------------------------------------------------
| SUPERADMIN + ADMIN ONLY (management & settings)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin'])->group(function () {

    // Branches
    Route::delete('branches/bulk-delete', [BranchController::class, 'bulkDelete'])->name('branches.bulkDelete');
    Route::resource('branches', BranchController::class)->except(['show']);

    // Users
    Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');
    Route::resource('users', UserController::class)->except(['show']);

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->except(['show']);

    // Audit Log
    Route::prefix('audit-logs')->name('audit-logs.')->controller(AuditLogController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{auditLog}', 'show')->name('show');
    });

    // Employees
    Route::prefix('employees')->name('employee.')->controller(EmployeeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{employee}/edit', 'edit')->name('edit');
        Route::put('/{employee}', 'update')->name('update');
        Route::patch('/{employee}/toggle', 'toggle')->name('toggle');
        Route::delete('/{employee}', 'destroy')->name('destroy');
    });

    // PPF Products
    Route::resource('ppf-products', PpfProductController::class);

    // Warranty Claims
    Route::prefix('warranty-claims')->name('admin.claims.')->controller(AdminWarrantyClaimController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{claim}', 'show')->name('show');
        Route::put('/{claim}/inspect', 'inspect')->name('inspect');
        Route::put('/{claim}/status', 'updateStatus')->name('status');
    });

    Route::prefix('warranty-claims/{claim}/invoice')->name('admin.claim-invoices.')->controller(AdminWarrantyClaimInvoiceController::class)->group(function () {
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
    });

    Route::prefix('claim-invoices')->name('admin.claim-invoices.')->controller(AdminWarrantyClaimInvoiceController::class)->group(function () {
        Route::get('/{invoice}', 'show')->name('show');
        Route::get('/{invoice}/print', 'print')->name('print');
        Route::get('/{invoice}/download', 'download')->name('download');
        Route::get('/{invoice}/edit', 'edit')->name('edit');
        Route::put('/{invoice}', 'update')->name('update');
        Route::delete('/{invoice}', 'destroy')->name('destroy');
    });

    // Commission settings (superadmin/admin configure rules)
    Route::prefix('commission-override')->name('commission.override.')->controller(\App\Http\Controllers\CommissionOverrideController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/{trigger}/toggle', 'toggleActive')->name('toggle');
        Route::post('/{trigger}/rate', 'updateRate')->name('rate');
        Route::delete('/{trigger}', 'destroy')->name('destroy');
    });

    Route::prefix('commission-periods')->name('commission.periods.')->controller(\App\Http\Controllers\CommissionPeriodController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{period}', 'show')->name('show')->where('period', '\d{4}-\d{2}-\d{2}');
        Route::post('/{period}/pay', 'pay')->name('pay')->where('period', '\d{4}-\d{2}-\d{2}');
        Route::post('/{period}/pay-all', 'payAll')->name('pay-all')->where('period', '\d{4}-\d{2}-\d{2}');
    });

    // HR management (admin manages attendance, shifts, leave)
    Route::prefix('attendances')->name('attendances.')->controller(AttendanceController::class)->group(function () {
        Route::get('/daily', 'daily')->name('daily');
        Route::post('/daily', 'saveDaily')->name('saveDaily');
        Route::get('/monthly-report', 'monthlyReport')->name('monthlyReport');
        Route::post('/monthly-report/adjustments', 'saveAdjustments')->name('monthlyReport.adjustments');
        Route::get('/monthly-report/export', 'exportPayroll')->name('monthlyReport.export');
        Route::get('/report', 'attendanceReport')->name('report');
    });

    Route::prefix('meal-allowances')->name('meal-allowances.')->controller(MealAllowanceController::class)->group(function () {
        Route::get('/weekly', 'weekly')->name('weekly');
        Route::get('/weekly/pdf', 'weeklyPdf')->name('weekly.pdf');
    });

    Route::prefix('shifts')->name('shifts.')->controller(ShiftController::class)->group(function () {
        Route::get('/',             'index')->name('index');
        Route::get('/create',       'create')->name('create');
        Route::post('/',            'store')->name('store');
        Route::get('/{shift}/edit', 'edit')->name('edit');
        Route::put('/{shift}',      'update')->name('update');
        Route::delete('/{shift}',   'destroy')->name('destroy');
    });

    Route::prefix('leave-requests')->name('leave-requests.')->controller(LeaveRequestController::class)->group(function () {
        Route::get('/',                      'index')->name('index');
        Route::get('/create',                'create')->name('create');
        Route::post('/',                     'store')->name('store');
        Route::get('/{leaveRequest}',        'show')->name('show');
        Route::put('/{leaveRequest}/approve','approve')->name('approve');
        Route::put('/{leaveRequest}/reject', 'reject')->name('reject');
        Route::put('/{leaveRequest}/cancel', 'cancel')->name('cancel');
    });

    // Export status / download
    Route::prefix('export-status')->name('export-status.')->controller(ExportStatusController::class)->group(function () {
        Route::get('/{exportRequest}',          'show')->name('show');
        Route::get('/{exportRequest}/check',    'check')->name('check');
        Route::get('/{exportRequest}/download', 'download')->name('download');
    });

    // Gallery
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    // Notification Config
    Route::get('/notification-config',      [NotificationConfigController::class, 'index'])->name('notification-config.index');
    Route::post('/notification-config',     [NotificationConfigController::class, 'update'])->name('notification-config.update');
    Route::get('/notification-config/logs', [NotificationConfigController::class, 'logs'])->name('notification-config.logs');
});


/*
|--------------------------------------------------------------------------
| COMMISSIONS — superadmin, admin, owner, manager_operasional, finance, marketing
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|owner|manager_operasional|manager_marketing|finance|marketing'])
    ->group(function () {
        Route::prefix('commissions')->name('commission.')->controller(CommissionController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export', 'exportByEmployee')->name('export');
            Route::get('/print', 'print')->name('print');
            Route::get('/{commission}/move', 'moveForm')->name('move');
            Route::put('/{commission}/move', 'moveUpdate')->name('move.update');
            Route::patch('/{commission}/toggle-active', 'toggleActive')->name('toggle-active');
        });
    });


/*
|--------------------------------------------------------------------------
| COMMISSION WITHDRAWALS — superadmin, finance
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|finance'])
    ->prefix('commission/withdrawals')->name('commission.withdrawals.')
    ->controller(CommissionWithdrawalController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{batch}', 'show')->name('show');
        Route::post('/{batch}/approve', 'approve')->name('approve');
        Route::post('/{batch}/reject', 'reject')->name('reject');
    });


/*
|--------------------------------------------------------------------------
| FILM STOCK & TRANSFERS — superadmin, admin, manager_operasional, purchasing
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|manager_operasional|purchasing'])
    ->group(function () {
        Route::prefix('film-stock')->name('film-stock.')->controller(FilmStockController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/usages', 'usages')->name('usages');
            Route::get('/{pool}', 'show')->name('show');
        });

        Route::prefix('film-stock-transfers')->name('film-stock-transfers.')->controller(FilmStockTransferController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{transfer}', 'show')->name('show');
            Route::post('/{transfer}/receive', 'receive')->name('receive');
        });
    });


/*
|--------------------------------------------------------------------------
| FILM COMPLAINTS — superadmin, admin, manager_operasional
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|manager_operasional'])
    ->group(function () {
        Route::prefix('film-complaints')->name('film-complaints.')->controller(FilmComplaintController::class)->group(function () {
            Route::get('/search-customers', 'searchCustomers')->name('search-customers');
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{complaint}', 'show')->name('show');
            Route::get('/{complaint}/edit', 'edit')->name('edit');
            Route::put('/{complaint}', 'update')->name('update');
            Route::patch('/{complaint}/status', 'updateStatus')->name('update-status');
        });
    });


/*
|--------------------------------------------------------------------------
| SUPPORTING GOODS — superadmin, admin, purchasing
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|purchasing'])
    ->group(function () {
        Route::get('supporting-goods/export', [SupportingGoodController::class, 'export'])->name('supporting-goods.export');
        Route::prefix('supporting-goods')->name('supporting-goods.')->controller(SupportingGoodController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{supportingGood}/edit', 'edit')->name('edit');
            Route::put('/{supportingGood}', 'update')->name('update');
            Route::delete('/{supportingGood}', 'destroy')->name('destroy');
            Route::post('/{supportingGood}/mutate', 'mutate')->name('mutate');
            Route::get('/{supportingGood}/history', 'history')->name('history');
        });
    });


/*
|--------------------------------------------------------------------------
| PURCHASES & SUPPLIERS — superadmin, admin, purchasing
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|purchasing'])
    ->group(function () {
        Route::resource('purchases', PurchaseController::class);
        Route::get('/purchase-reports/monthly', [PurchaseReportController::class, 'monthly'])->name('purchase.reports.monthly');

        Route::prefix('purchases/{purchase}/receipts')->name('purchase-receipts.')->controller(PurchaseReceiptController::class)->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/',      'store')->name('store');
        });
        Route::get('/purchase-receipts/{receipt}', [PurchaseReceiptController::class, 'show'])->name('purchase-receipts.show');
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });


/*
|--------------------------------------------------------------------------
| PAYROLLS — superadmin, admin, finance
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|finance'])
    ->group(function () {
        Route::prefix('payrolls')->name('payrolls.')->controller(PayrollController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{payroll}', 'show')->name('show');
            Route::get('/{payroll}/edit', 'edit')->name('edit');
            Route::put('/{payroll}', 'update')->name('update');
            Route::post('/{payroll}/approve', 'approve')->name('approve');
            Route::post('/{payroll}/paid', 'markPaid')->name('paid');
            Route::post('/{payroll}/refresh', 'refresh')->name('refresh');
            Route::delete('/{payroll}', 'destroy')->name('destroy');
            Route::get('/{payroll}/slip-all', 'downloadSlipAll')->name('slip.all');
            Route::get('/{payroll}/slip/{itemId}', 'downloadSlip')->name('slip')->where('itemId', '[0-9]+');
        });
    });


/*
|--------------------------------------------------------------------------
| FINANCE — petty cash, hutang/piutang, bank reconciliation (superadmin, admin, finance)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|finance'])
    ->group(function () {
        // Petty Cash
        Route::prefix('petty-cash')->name('petty-cash.')->controller(PettyCashController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/pdf', 'downloadPdf')->name('pdf');
            Route::get('/in', 'createIn')->name('create-in');
            Route::post('/in', 'storeIn')->name('store-in');
            Route::get('/out', 'createOut')->name('create-out');
            Route::post('/out', 'storeOut')->name('store-out');
            Route::post('/out/bulk', 'storeBulkOut')->name('store-bulk-out');
            Route::get('/purchase', 'createPurchase')->name('create-purchase');
            Route::post('/purchase', 'storePurchase')->name('store-purchase');
            Route::delete('/{pettyCash}', 'destroy')->name('destroy');
        });

        // Hutang & Piutang
        Route::get('hutang/export',  [HutangPiutangController::class, 'hutangExport'])->name('hutang.export');
        Route::get('piutang/export', [HutangPiutangController::class, 'piutangExport'])->name('piutang.export');

        Route::prefix('hutang')->name('hutang.')->controller(HutangPiutangController::class)->group(function () {
            Route::get('/', 'hutangIndex')->name('index');
            Route::get('/create', 'hutangCreate')->name('create');
            Route::post('/', 'hutangStore')->name('store');
            Route::get('/{payable}', 'hutangShow')->name('show');
            Route::get('/{payable}/edit', 'hutangEdit')->name('edit');
            Route::put('/{payable}', 'hutangUpdate')->name('update');
            Route::delete('/{payable}', 'hutangDestroy')->name('destroy');
            Route::post('/{id}/payment', 'recordPayment')->name('payment')->defaults('type', 'hutang');
        });

        Route::prefix('piutang')->name('piutang.')->controller(HutangPiutangController::class)->group(function () {
            Route::get('/', 'piutangIndex')->name('index');
            Route::get('/create', 'piutangCreate')->name('create');
            Route::post('/', 'piutangStore')->name('store');
            Route::get('/{receivable}', 'piutangShow')->name('show');
            Route::get('/{receivable}/edit', 'piutangEdit')->name('edit');
            Route::put('/{receivable}', 'piutangUpdate')->name('update');
            Route::delete('/{receivable}', 'piutangDestroy')->name('destroy');
            Route::post('/{id}/payment', 'recordPayment')->name('payment')->defaults('type', 'piutang');
        });

        Route::delete('debt-payments/{payment}', [HutangPiutangController::class, 'deletePayment'])
            ->name('debt-payments.destroy');

        // Bank Reconciliation
        Route::prefix('bank-accounts')->name('bank-accounts.')->controller(BankReconciliationController::class)->group(function () {
            Route::get('/',                          'accountIndex')->name('index');
            Route::get('/create',                    'accountCreate')->name('create');
            Route::post('/',                         'accountStore')->name('store');
            Route::get('/{bankAccount}/edit',        'accountEdit')->name('edit');
            Route::put('/{bankAccount}',             'accountUpdate')->name('update');
            Route::delete('/{bankAccount}',          'accountDestroy')->name('destroy');

            Route::get('/{bankAccount}/entries',         'entryIndex')->name('entries');
            Route::get('/{bankAccount}/entries/create',  'entryCreate')->name('entries.create');
            Route::post('/{bankAccount}/entries',        'entryStore')->name('entries.store');
            Route::post('/{bankAccount}/reconcile-many', 'reconcileMany')->name('reconcile-many');
            Route::get('/{bankAccount}/entries/export',  'entryExport')->name('entries.export');
        });

        Route::patch('bank-entries/{entry}/reconcile', [BankReconciliationController::class, 'reconcile'])->name('bank-entries.reconcile');
        Route::delete('bank-entries/{entry}',          [BankReconciliationController::class, 'entryDestroy'])->name('bank-entries.destroy');
    });


/*
|--------------------------------------------------------------------------
| ANALYTICS — superadmin, admin, owner
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|owner'])
    ->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    });


/*
|--------------------------------------------------------------------------
| ROLE-SPECIFIC DASHBOARDS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:owner'])
    ->prefix('owner')->name('owner.')
    ->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:marketing|manager_marketing'])
    ->prefix('marketing')->name('marketing.')
    ->group(function () {
        Route::get('/dashboard', [MarketingDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/komisi', [MarketingDashboardController::class, 'komisSaya'])->name('komisi');
        Route::post('/withdrawal/request', [MarketingDashboardController::class, 'requestWithdrawal'])->name('withdrawal.request');
    });

Route::middleware(['auth', 'branch.access', 'role:customer_service'])
    ->prefix('cs')->name('cs.')
    ->group(function () {
        Route::get('/dashboard', [CsDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:manager_operasional'])
    ->prefix('manager-ops')->name('manager-ops.')
    ->group(function () {
        Route::get('/dashboard', [ManagerOpsDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:finance'])
    ->prefix('finance')->name('finance.')
    ->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:purchasing'])
    ->prefix('purchasing')->name('purchasing.')
    ->group(function () {
        Route::get('/dashboard', [PurchasingDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|owner|manager_operasional|manager_marketing'])
    ->prefix('ranking')->name('ranking.')
    ->group(function () {
        Route::get('/teknisi', [EmployeeRankingController::class, 'teknisi'])->name('teknisi');
        Route::get('/marketing', [EmployeeRankingController::class, 'marketing'])->name('marketing');
    });
