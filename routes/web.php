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
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MitraInvoiceController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\ArticleController;
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
    // Landing page Google Ads — kaca film rumah/bangunan
    Route::get('/kaca-film-rumah', 'kacaFilmRumah')->name('frontend.kaca.film.rumah');
    Route::get('/ppf-mobil', 'ppfAds')->name('frontend.ppf.ads');
    Route::get('/ppf', 'ppfCatalog')->name('frontend.ppf.catalog');
    // Note: URI cannot be '/dealers' — collides with the admin Dealer CRUD resource route below.
    Route::get('/lokasi-dealer', 'dealers')->name('frontend.dealers');
    Route::get('/faq', 'faq')->name('frontend.faq');
    Route::get('/artikel', 'articles')->name('frontend.artikel');
    Route::get('/artikel/{article}', 'articleShow')->name('frontend.artikel.show');
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

    // Note: card.preview/card.download to WarrantyCardController were removed —
    // that controller never defined preview()/download() methods (dead routes,
    // always 500'd). PPF card routes below cover the live functionality.

    // 'signed' requires a valid Laravel signature on the URL (see URL::signedRoute()
    // call sites in result.blade.php / customer/ppf/index.blade.php) so the raw
    // installation id can no longer be enumerated to pull another customer's card.
    Route::get('/ppf-card/{installationId}/{side}/preview', [WarrantyCardController::class, 'ppfPreview'])
        ->whereIn('side', ['front', 'back'])->middleware(['throttle:10,1', 'signed'])->name('ppf.card.preview');
    Route::get('/ppf-card/{installationId}/{side}/download', [WarrantyCardController::class, 'ppfDownload'])
        ->whereIn('side', ['front', 'back'])->middleware(['throttle:10,1', 'signed'])->name('ppf.card.download');
});

// Public warranty cards — di luar named group agar nama route tidak dapat prefix 'warranty.'
// 'signed': cardNumber is a sequential, guessable identifier — the signature
// (generated only by our own code via URL::signedRoute()) is what actually
// prevents enumeration of other customers' cards, not the cardNumber itself.
Route::prefix('warranty/cards')->name('public.warranty.card.')->middleware(['throttle:10,1', 'signed'])->group(function () {
    Route::get('/{cardNumber}/preview', [PublicWarrantyCardController::class, 'previewFilm'])->name('preview');
    Route::get('/{cardNumber}/download', [PublicWarrantyCardController::class, 'downloadFilm'])->name('download');
});

Route::prefix('warranty/ppf-cards')->name('public.warranty.ppf.')->middleware(['throttle:10,1', 'signed'])->group(function () {
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
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Lupa password & atur password dari undangan (publik). Hanya akun role
// config('password_setup.self_service_roles') yang benar-benar dikirimi tautan.
Route::middleware('guest')->controller(\App\Http\Controllers\PasswordResetController::class)->group(function () {
    Route::get('/lupa-password', 'showForgotForm')->name('password.request');
    Route::post('/lupa-password', 'sendLink')->middleware('throttle:password-link')->name('password.email');
    Route::get('/atur-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/atur-password', 'reset')->middleware('throttle:password-reset')->name('password.update');
});

// Ganti password (wajib untuk akun dengan password sementara dari admin).
Route::middleware('auth')->controller(\App\Http\Controllers\PasswordChangeController::class)->group(function () {
    Route::get('/ganti-password', 'show')->name('password.change');
    Route::post('/ganti-password', 'update')->middleware('throttle:5,1')->name('password.change.update');
});

// Pendaftaran sales mandiri lewat kode referral — publik, tidak perlu login
// (kandidat belum punya akun). Akun yang terbentuk tetap nonaktif sampai
// admin approve, sama seperti jalur sponsor-merekrut yang sudah ada.
Route::prefix('daftar-sales')->name('sales-network.')->controller(\App\Http\Controllers\SalesNetworkController::class)->group(function () {
    Route::get('/', 'publicRegisterForm')->name('public-register')->middleware('throttle:20,1');
    Route::post('/', 'publicRegister')->name('public-register.store')->middleware('throttle:5,1');
});


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

    // Generic approval queue — "Menunggu Approval Saya" (siapa pun yang jadi approver di flow apa pun)
    Route::prefix('approvals')->name('approvals.')->controller(\App\Http\Controllers\ApprovalController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{approvalRequest}/approve', 'approve')->name('approve');
        Route::post('/{approvalRequest}/reject', 'reject')->name('reject');
    });

    // Self-service leave requests (semua role)
    Route::prefix('my-leave-requests')->name('my-leave-requests.')->controller(\App\Http\Controllers\MyLeaveRequestController::class)->group(function () {
        Route::get('/',                         'index')->name('index');
        Route::get('/create',                   'create')->name('create');
        Route::post('/',                        'store')->name('store');
        Route::get('/{leaveRequest}',           'show')->name('show');
        Route::put('/{leaveRequest}/cancel',    'cancel')->name('cancel');
    });

    // Self-service kasbon (semua role dengan data employee) — pengajuan + approval
    // lewat flow 'kasbon' (lihat ApprovalFlowSeeder), potongan aktual dieksekusi
    // oleh KasbonDeductionService saat payroll/komisi diproses, bukan di sini.
    Route::prefix('my-kasbon')->name('my-kasbon.')->controller(\App\Http\Controllers\KasbonController::class)->group(function () {
        Route::get('/',             'index')->name('index');
        Route::get('/create',       'create')->name('create');
        Route::post('/',            'store')->name('store');
        Route::get('/{kasbon}',     'show')->name('show');
    });

    // Self-service claims — Teknisi/Kenek only (Claim Pembelian, Bensin, Saldo Kesehatan)
    Route::prefix('my-claims')->name('my-claims.')->middleware('role:teknisi|kenek')
        ->controller(\App\Http\Controllers\EmployeeClaimController::class)->group(function () {
            Route::get('/',        'index')->name('index');
            Route::get('/create',  'create')->name('create');
            Route::post('/',       'store')->name('store');
        });

    // Finance / IT Manager — cairkan klaim yang sudah disetujui Manager Operasional
    Route::prefix('claims-disbursement')->name('claims-disbursement.')
        ->middleware('role:superadmin|admin|finance|it_manager')
        ->controller(\App\Http\Controllers\EmployeeClaimController::class)->group(function () {
            Route::get('/',                'readyForDisbursement')->name('index');
            Route::post('/{claim}/disburse', 'disburse')->name('disburse');
        });

    // Team Creative — Strategi Pengembangan meng-assign, Designer/Videographer mengerjakan
    Route::prefix('creative-tasks')->name('creative-tasks.')
        ->middleware('role:superadmin|strategi_pengembangan|designer|videographer')
        ->controller(\App\Http\Controllers\CreativeTaskController::class)->group(function () {
            Route::get('/',                       'index')->name('index');
            Route::get('/create',                 'create')->name('create');
            Route::post('/',                      'store')->name('store');
            Route::get('/{creativeTask}',          'show')->name('show');
            Route::post('/{creativeTask}/deliverables', 'storeDeliverable')->name('deliverables.store');
            Route::post('/{creativeTask}/approve', 'approve')->name('approve');
            Route::post('/{creativeTask}/revision', 'requestRevision')->name('revision');
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
        Route::post('/quick',           'quickStore')->name('quick-store');
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
        Route::post('/{salesOrder}/assign',     'assign')->name('assign');
        Route::post('/{salesOrder}/issue-spk',  'issueSpk')->name('issue-spk');
        Route::get('/{salesOrder}/spk',         'printSpk')->name('spk');
    });

// Public SPK verification (no auth required)
Route::get('/spk/{token}', [SpkPublicController::class, 'show'])
    ->name('spk.public')
    ->middleware('throttle:60,1');

// Teknisi/kenek disertakan di role list, TANPA branch.access — middleware itu
// sengaja menolak akun teknisi/kenek (lihat EnsureBranchAccess), dan cabang
// mereka sudah divalidasi langsung dari data karyawan di JadwalPemasanganController.
Route::middleware(['auth', "role:{$crmRoles}|teknisi|kenek"])
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

    // Dealers (public website directory)
    Route::resource('dealers', DealerController::class)->except(['show']);

    // Articles (public website blog)
    Route::resource('articles', ArticleController::class)->except(['show']);

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

    // Saldo Biaya Kesehatan (Teknisi/Kenek)
    Route::prefix('employee-health-balances')->name('employee-health-balances.')
        ->controller(\App\Http\Controllers\EmployeeHealthBalanceController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/{employeeHealthBalance}', 'update')->name('update');
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

    // Per-employee commission mode (Sistem Saat Ini / Persentase Kustom / Tidak Dapat Komisi)
    Route::prefix('commission-settings')->name('commission.settings.')->controller(\App\Http\Controllers\CommissionSettingController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/{employee}', 'update')->name('update');
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
        Route::get('/schedule',              'schedule')->name('schedule');
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

Route::middleware(['auth', 'branch.access', 'role:superadmin|finance|it_manager'])
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

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|manager_operasional|purchasing|it_manager'])
    ->group(function () {
        Route::prefix('film-stock')->name('film-stock.')->controller(FilmStockController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/usages', 'usages')->name('usages');
            Route::get('/suggest-roll-codes', 'suggestRollCodes')->name('suggest-roll-codes');
            Route::get('/{pool}', 'show')->name('show');
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

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|purchasing|it_manager'])
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

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|purchasing|it_manager'])
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
| MITRA & INVOICE MITRA — pembelian bahan dari pusat ke mitra/non-franchise.
| Tidak pakai middleware branch.access: mitra bukan branch, ini transaksi
| pusat (tidak terikat cabang aktif user).
| superadmin, admin, purchasing, finance
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin|admin|purchasing|finance|it_manager'])
    ->group(function () {
        Route::resource('mitras', MitraController::class)->except(['show']);

        Route::resource('mitra-invoices', MitraInvoiceController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('mitra-invoices/{mitraInvoice}/mark-paid', [MitraInvoiceController::class, 'markPaid'])->name('mitra-invoices.mark-paid');
        Route::get('mitra-invoices/{mitraInvoice}/pdf', [MitraInvoiceController::class, 'download'])->name('mitra-invoices.pdf');
        Route::get('mitra-invoices-available-stock', [MitraInvoiceController::class, 'availableStock'])->name('mitra-invoices.available-stock');
    });

/*
|--------------------------------------------------------------------------
| EXPORT & IMPORT — superadmin, export_import
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin|export_import'])->group(function () {
    Route::resource('international-partners', \App\Http\Controllers\InternationalPartnerController::class)->except(['show']);

    Route::prefix('import-orders')->name('import-orders.')
        ->controller(\App\Http\Controllers\ImportOrderController::class)->group(function () {
            Route::get('/',                  'index')->name('index');
            Route::get('/create',            'create')->name('create');
            Route::post('/',                 'store')->name('store');
            Route::get('/{importOrder}',      'show')->name('show');
            Route::put('/{importOrder}/status', 'updateStatus')->name('status');
        });

    Route::prefix('export-orders')->name('export-orders.')
        ->controller(\App\Http\Controllers\ExportOrderController::class)->group(function () {
            Route::get('/',                  'index')->name('index');
            Route::get('/create',            'create')->name('create');
            Route::post('/',                 'store')->name('store');
            Route::get('/{exportOrder}',      'show')->name('show');
            Route::put('/{exportOrder}/status', 'updateStatus')->name('status');
        });
});

/*
|--------------------------------------------------------------------------
| DEVELOPMENT — superadmin, development; Owner can view (laporan ke Owner)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin|development|owner'])->group(function () {
    Route::prefix('development-reports')->name('development-reports.')
        ->controller(\App\Http\Controllers\DevelopmentReportController::class)->group(function () {
            Route::get('/',                       'index')->name('index');
            Route::get('/create',                 'create')->name('create');
            Route::post('/',                      'store')->name('store');
            Route::get('/{developmentReport}',     'show')->name('show');
        });

    Route::prefix('development-plans')->name('development-plans.')
        ->controller(\App\Http\Controllers\DevelopmentAnnualPlanController::class)->group(function () {
            Route::get('/',                       'index')->name('index');
            Route::get('/create',                 'create')->name('create');
            Route::post('/',                      'store')->name('store');
            Route::get('/{developmentPlan}',       'show')->name('show');
            Route::post('/{developmentPlan}/initiatives', 'storeInitiative')->name('initiatives.store');
            Route::put('/{developmentPlan}/initiatives/{initiative}/status', 'updateInitiativeStatus')->name('initiatives.status');
        });
});


/*
|--------------------------------------------------------------------------
| PAYROLLS — superadmin, admin, finance
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|finance|it_manager'])
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
| KASBON MANAGEMENT — superadmin, admin, finance (audit lintas karyawan;
| persetujuan tetap lewat /approvals, ini cuma listing read-only)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|finance|it_manager'])
    ->group(function () {
        Route::prefix('kasbons')->name('kasbons.')->controller(\App\Http\Controllers\KasbonController::class)->group(function () {
            Route::get('/', 'manage')->name('index');
        });
    });


/*
|--------------------------------------------------------------------------
| FINANCE — petty cash, hutang/piutang, bank reconciliation (superadmin, admin, finance)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'branch.access', 'role:superadmin|admin|finance|it_manager'])
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

// Owner monitoring — progres lintas-role (approval queue, claims, Team
// Creative, Export & Import, Development) dalam satu layar. Company-wide,
// tidak branch-scoped, konsisten dengan modul baru lainnya.
Route::middleware(['auth', 'role:superadmin|owner'])->group(function () {
    Route::get('/owner-monitoring', [\App\Http\Controllers\OwnerMonitoringController::class, 'index'])
        ->name('owner-monitoring.index');
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

Route::middleware(['auth', 'branch.access', 'role:finance|it_manager'])
    ->prefix('finance')->name('finance.')
    ->group(function () {
        Route::get('/dashboard', [FinanceDashboardController::class, 'dashboard'])->name('dashboard');
    });

Route::middleware(['auth', 'branch.access', 'role:purchasing|it_manager'])
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


/*
|--------------------------------------------------------------------------
| REFERRAL & HIERARKI SALES (2026-10) — superadmin, admin, sales, marketing
|--------------------------------------------------------------------------
*/

// Sales/marketing: dashboard sendiri, kelola jaringan sendiri.
Route::middleware(['auth', 'branch.access', 'role:sales|marketing'])
    ->group(function () {
        Route::get('/sales/dashboard', [\App\Http\Controllers\SalesDashboardController::class, 'dashboard'])
            ->name('sales.dashboard');

        Route::prefix('my-sales-network')->name('sales-network.')
            ->controller(\App\Http\Controllers\SalesNetworkController::class)
            ->group(function () {
                Route::get('/', 'myNetwork')->name('my-network');
                Route::get('/recruit', 'create')->name('create');
                Route::post('/recruit', 'store')->name('store');
            });
    });

// Transaksi sales: sales bikin & catat pembayaran sendiri; superadmin/admin bisa
// lihat semua (oversight/laporan) — dibatasi lagi di controller lewat authorizeAccess().
Route::middleware(['auth', 'branch.access', 'role:sales|superadmin|admin'])
    ->prefix('sales-transactions')->name('sales-transactions.')
    ->controller(\App\Http\Controllers\SalesTransactionController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{salesTransaction}', 'show')->name('show');
        Route::post('/{salesTransaction}/payment', 'recordPayment')->name('payment');
        Route::post('/{salesTransaction}/release-commission', 'releaseCommission')->name('release-commission');
    });

// Admin: approve rekrutan, kelola anggota & produk jaringan sales.
Route::middleware(['auth', 'branch.access', 'role:superadmin|admin'])
    ->prefix('sales-network')->name('sales-network.')
    ->controller(\App\Http\Controllers\SalesNetworkController::class)
    ->group(function () {
        Route::get('/', 'adminIndex')->name('admin-index');
        Route::get('/create', 'adminCreate')->name('admin-create');
        Route::post('/', 'adminStore')->name('admin-store');
        Route::get('/pending', 'pendingApprovals')->name('pending');
        Route::post('/{profile}/approve', 'approve')->name('approve');
        Route::post('/{profile}/reject', 'reject')->name('reject');
        Route::post('/{profile}/resend-invite', 'resendInvite')->name('resend-invite')->middleware('throttle:6,1');
    });

Route::middleware(['auth', 'role:superadmin|admin'])
    ->prefix('sales-products')->name('sales-products.')
    ->controller(\App\Http\Controllers\SalesProductController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{salesProduct}/edit', 'edit')->name('edit');
        Route::put('/{salesProduct}', 'update')->name('update');
        Route::post('/{salesProduct}/toggle', 'toggle')->name('toggle');
    });
