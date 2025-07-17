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


Route::get('/', [FrontendController::class,'home'])->name('frontend.home');
Route::get('/service/car', [FrontendController::class,'serviceCar'])->name('frontend.service.car');
Route::get('/service/building', [FrontendController::class,'serviceBuilding'])->name('frontend.service.building');
Route::get('/service/franchise', [FrontendController::class,'serviceFranchise'])->name('frontend.service.franchise');
Route::get('/about', [FrontendController::class,'about'])->name('frontend.about');
Route::get('/contact', [FrontendController::class,'contact'])->name('frontend.contact');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'branch.access'])->group(function () {
    Route::post('/switch-branch', [BranchController::class, 'switch'])->name('switch-branch');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('customers/cars', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('customers/cars/create', [CustomerController::class, 'create'])->name('customer.create');
    Route::post('customers/cars/store', [CustomerController::class, 'store'])->name('customer.store');
    Route::get('customers/cars/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
    Route::put('customers/cars/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('customers/cars/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');
    Route::get('/products-by-category/{id}', [CustomerController::class, 'getProductsByCategory']);
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customer.export');
    Route::delete('customer/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customer.bulkDelete');

    Route::get('/invoice/create/{customer}', [InvoiceController::class, 'create'])->name('invoice.create');
    Route::post('/invoice', [InvoiceController::class, 'store'])->name('invoice.store');
    Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('invoice/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
    Route::delete('invoice/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::get('invoice/{invoice}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');

    Route::get('/customers/building',[CustomerBuildingController::class, 'index'])->name('customer.building.index');
    Route::get('/customers/building/create',[CustomerBuildingController::class, 'create'])->name('customer.building.create');
    Route::post('/customers/building/store',[CustomerBuildingController::class, 'store'])->name('customer.building.store');
    Route::get('/customers/building/{customer}/edit',[CustomerBuildingController::class, 'edit'])->name('customer.building.edit');
    Route::delete('/customers/building/bulk-delete', [CustomerBuildingController::class, 'bulkDelete'])->name('customer.building.bulkDelete');
    Route::get('/products-by-category-building/{id}', [CustomerBuildingController::class, 'getProductsByCategory']);
});

Route::get('/promotion/car', [PromotionController::class, 'car'])->name('frontend.promotion.car');
