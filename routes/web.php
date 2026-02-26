<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerBuildingController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\InvoiceBuildingController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerPpfController;
use App\Http\Controllers\PpfProductController;
use App\Http\Controllers\CustomerPpfInstallationController;
use App\Http\Controllers\PpfInvoiceController;
use App\Http\Controllers\PublicWarrantyController;
use App\Http\Controllers\WarrantyClaimController;
use App\Http\Controllers\WarrantyClaimInvoiceController;
use App\Http\Controllers\WarrantyClaimInvoiceItemController;
use App\Http\Controllers\WarrantyCardController;
use App\Http\Controllers\PublicWarrantyCardController;
use App\Http\Controllers\Admin\AdminWarrantyClaimController;
use App\Http\Controllers\Admin\AdminWarrantyClaimInvoiceController;
use Barryvdh\DomPDF\Facade\Pdf;

Route::get('/', [FrontendController::class,'home'])->name('frontend.home');
Route::get('/service/car', [FrontendController::class,'serviceCar'])->name('frontend.service.car');
Route::get('/service/building', [FrontendController::class,'serviceBuilding'])->name('frontend.service.building');
Route::get('/service/franchise', [FrontendController::class,'serviceFranchise'])->name('frontend.service.franchise');
Route::get('/about', [FrontendController::class,'about'])->name('frontend.about');
Route::get('/contact', [FrontendController::class,'contact'])->name('frontend.contact');
// Route::get('/warranty', [WarrantyController::class,'index'])->name('frontend.warranty');
// Route::get('/warranty/invoice/{invoice}', [WarrantyController::class,'showInvoice'])->name('frontend.warranty.invoice');

Route::get('/warranty', [PublicWarrantyController::class, 'form'])->name('warranty.form');

Route::post('/warranty/lookup', [PublicWarrantyController::class, 'lookup'])
    ->middleware('throttle:10,1')
    ->name('warranty.lookup');

Route::get('/warranty/result', [PublicWarrantyController::class, 'result'])
    ->middleware('throttle:20,1')
    ->name('warranty.result');

Route::post('/warranty/claim', [PublicWarrantyController::class, 'storeClaim'])
    ->middleware('throttle:5,1')
    ->name('warranty.claim.store');

    Route::get('/warranty/card/{type}/{id}/download', [PublicWarrantyController::class, 'downloadCard'])
    ->middleware('throttle:20,1')
    ->name('warranty.card.download.public');

    Route::get('/warranty/card/{cardNumber}/preview/{cp?}', [WarrantyCardController::class, 'preview'])
    ->name('warranty.card.preview');

    Route::get('/warranty/card/{cardNumber}/download/{cp?}', [WarrantyCardController::class, 'download'])
        ->name('warranty.card.download');

    // PPF: front/back (preview & download)
    Route::get('/warranty/ppf-card/{installationId}/{side}/preview', [WarrantyCardController::class, 'ppfPreview'])
        ->whereIn('side', ['front','back'])
        ->name('warranty.ppf.card.preview');

    Route::get('/warranty/ppf-card/{installationId}/{side}/download', [WarrantyCardController::class, 'ppfDownload'])
        ->whereIn('side', ['front','back'])
        ->name('warranty.ppf.card.download');


Route::get('/warranty/cards/{cardNumber}/preview', [PublicWarrantyCardController::class, 'previewFilm'])
    ->name('public.warranty.card.preview');

Route::get('/warranty/cards/{cardNumber}/download', [PublicWarrantyCardController::class, 'downloadFilm'])
    ->name('public.warranty.card.download');

Route::get('/warranty/ppf-cards/{installationId}/preview', [PublicWarrantyCardController::class, 'previewPpf'])
    ->name('public.warranty.ppf.preview');

Route::get('/warranty/ppf-cards/{installationId}/download', [PublicWarrantyCardController::class, 'downloadPpf'])
    ->name('public.warranty.ppf.download');

Route::get('/kaca-film-mobil', [FrontendController::class,'kacaFilmMobil'])->name('frontend.kaca.film.mobil');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/open/{id}', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::get('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.readAll');
});

Route::middleware(['auth', 'role:superadmin'])->group(function () {

    // Master Cabang
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::delete('branches/bulk-delete', [BranchController::class, 'bulkDelete'])
        ->name('branches.bulkDelete');

    // Manajemen User
    Route::resource('users', UserController::class)->except(['show']);

    Route::delete('users/bulk-delete', [UserController::class, 'bulkDelete'])
        ->name('users.bulkDelete');


    Route::resource('ppf-customers', CustomerPpfController::class)
    ->only(['index','create', 'edit', 'update','store','show','destroy'])
    ->names('ppf-customers');
    Route::resource('ppf-products', PpfProductController::class);
    Route::resource('ppf-installations', CustomerPpfInstallationController::class);

});

Route::middleware(['auth', 'branch.access'])->group(function () {
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('switch-branch');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('customers/cars', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('customers/cars/card/{custCode}/{catproductCode}', [CustomerController::class, 'download'])->name('customer.card.download');
    Route::get('customers/cars/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::post('customers/cars/store', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('customers/cars/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('customers/cars/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('customers/cars/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');
    Route::get('/products-by-category/{id}', [CustomerController::class, 'getProductsByCategory']);
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customer.export');
    Route::delete('customer/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customer.bulkDelete');
    Route::get('warranty/card/{cardNumber}', [CustomerController::class, 'download'])->name('warranty.card.download');

    Route::get('/warranty/card/{cardNumber}/cp/{cp}', [CustomerController::class, 'download'])
        ->whereNumber('cp')
        ->name('warranty.card.download.cp'); // per item (cp)
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employee.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employee.create');
    Route::post('/employees/store', [EmployeeController::class, 'store'])->name('employee.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employee.destroy');

    Route::get('/invoice/create/{customer}', [InvoiceController::class, 'create'])->name('invoice.create');
    Route::post('/invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
    Route::put('invoice/{invoice}', [InvoiceController::class, 'update'])->name('invoice.update');
    Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');

    Route::get('/customers/building',[CustomerBuildingController::class, 'index'])->name('customer.building.index');
    Route::get('/customers/building/create',[CustomerBuildingController::class, 'create'])->name('customer.building.create');
    Route::post('/customers/building/store',[CustomerBuildingController::class, 'store'])->name('customer.building.store');
    Route::get('/customers/building/{customer}/edit',[CustomerBuildingController::class, 'edit'])->name('customer.building.edit');
    Route::put('/customers/building/{customer}',[CustomerBuildingController::class, 'update'])->name('customer.building.update');
    Route::delete('/customers/building/{customer}',[CustomerBuildingController::class, 'destroy'])->name('customer.building.destroy');
    Route::delete('/customers/building/bulk-delete', [CustomerBuildingController::class, 'bulkDelete'])->name('customer.building.bulkDelete');
    Route::get('/products-by-category-building/{id}', [CustomerBuildingController::class, 'getProductsByCategory']);

    Route::get('/invoice-building/create/{customer}', [InvoiceBuildingController::class, 'create'])->name('invoice.building.create');
    Route::post('/invoice-building/store', [InvoiceBuildingController::class, 'store'])->name('invoice.building.store');
    Route::get('/invoice-building/{invoice}', [InvoiceBuildingController::class, 'show'])->name('invoice.building.show');
    Route::get('/invoice-building/{invoice}/edit', [InvoiceBuildingController::class, 'edit'])->name('invoice.building.edit');
    Route::put('/invoice-building/{invoice}', [InvoiceBuildingController::class, 'update'])->name('invoice.building.update');
    Route::get('/invoice-building/{invoice}/download', [InvoiceBuildingController::class, 'download'])->name('invoice.building.download');
    Route::delete('/invoice-building/{invoice}', [InvoiceBuildingController::class, 'destroy'])->name('invoice.building.destroy');

    Route::get('/warranty-claims', [AdminWarrantyClaimController::class, 'index'])->name('admin.claims.index');
    Route::get('/warranty-claims/{claim}', [AdminWarrantyClaimController::class, 'show'])->name('admin.claims.show');
    Route::put('/warranty-claims/{claim}/inspect', [AdminWarrantyClaimController::class, 'inspect'])->name('admin.claims.inspect');
    Route::put('/warranty-claims/{claim}/status', [AdminWarrantyClaimController::class, 'updateStatus'])->name('admin.claims.status');



    // INVOICE CLAIM
    Route::get('/warranty-claims/{claim}/invoice/create', [AdminWarrantyClaimInvoiceController::class, 'create'])->name('admin.claim-invoices.create');
    Route::post('/warranty-claims/{claim}/invoice', [AdminWarrantyClaimInvoiceController::class, 'store'])->name('admin.claim-invoices.store');

    Route::get('/claim-invoices/{invoice}', [AdminWarrantyClaimInvoiceController::class, 'show'])->name('admin.claim-invoices.show');
    Route::get('/claim-invoices/{invoice}/print', [AdminWarrantyClaimInvoiceController::class, 'print'])->name('admin.claim-invoices.print');
    Route::get('/claim-invoices/{invoice}/download', [AdminWarrantyClaimInvoiceController::class, 'download'])->name('admin.claim-invoices.download');

    Route::get('/claim-invoices/{invoice}/edit', [AdminWarrantyClaimInvoiceController::class, 'edit'])->name('admin.claim-invoices.edit');
    Route::put('/claim-invoices/{invoice}', [AdminWarrantyClaimInvoiceController::class, 'update'])->name('admin.claim-invoices.update');
    Route::delete('/claim-invoices/{invoice}', [AdminWarrantyClaimInvoiceController::class, 'destroy'])->name('admin.claim-invoices.destroy');

    Route::get('/commission', [CommissionController::class, 'index'])->name('commission.index');
    Route::get('/commissions/export', [CommissionController::class, 'exportByEmployee'])->name('commission.export');

    Route::get('ppf-invoices/create/{installationId}', [PpfInvoiceController::class, 'create'])->name('ppf-invoices.create');
    Route::post('ppf-invoices/store/{installationId}', [PpfInvoiceController::class, 'store'])->name('ppf-invoices.store');

    Route::get('ppf-invoices/{id}', [PpfInvoiceController::class, 'show'])->name('ppf-invoices.show');
    Route::get('ppf-invoices/{id}/print', [PpfInvoiceController::class, 'print'])->name('ppf-invoices.print');

    Route::get('ppf-invoices/{id}/edit', [PpfInvoiceController::class, 'edit'])->name('ppf-invoices.edit');
    Route::put('ppf-invoices/{id}', [PpfInvoiceController::class, 'update'])->name('ppf-invoices.update');

    Route::delete('ppf-invoices/{id}', [PpfInvoiceController::class, 'destroy'])->name('ppf-invoices.destroy');
    Route::get('ppf-invoices/{invoice}/download', [PpfInvoiceController::class, 'download'])
    ->name('ppf-invoices.download');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/_debug-public', fn () => public_path());
    Route::get('/_pdf-ping', function () {
        // (opsional) paksa lagi saat runtime
        app()->usePublicPath('/home/acegardi/public_html');
        app()->instance('path.public', '/home/acegardi/public_html');

        \Config::set('dompdf.public_path', '/home/acegardi/public_html');
        \Config::set('dompdf.chroot', '/home/acegardi/public_html');

        return Pdf::setOptions([
            'chroot'          => '/home/acegardi/public_html',
            'isRemoteEnabled' => true,
        ])->loadHtml('<h1>OK</h1>')->stream('ping.pdf');
    });
    Route::get('/_debug-binding', function () {
        return "helper=" . public_path() . " | binding=" . app('path.public');
    });

});

Route::get('/promo/kaca-film-mobil', [PromotionController::class, 'car'])->name('frontend.promotion.car');
