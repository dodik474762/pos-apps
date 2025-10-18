<?php

use App\Http\Controllers\api\master\CompanyController as MasterCompanyController;
use App\Http\Controllers\api\master\CustomerCategoryController as MasterCustomerCategoryController;
use App\Http\Controllers\api\master\CustomerController as MasterCustomerController;
use App\Http\Controllers\web\auth\LoginController;
use App\Http\Controllers\web\DashboardController;
use App\Http\Controllers\web\master\CompanyController;
use App\Http\Controllers\web\master\CustomerCategoryController;
use App\Http\Controllers\web\master\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginController::class, 'index']);
Route::post('/user/signIn', [LoginController::class, 'signIn']);
Route::get('/user/signOut', [LoginController::class, 'signOut']);

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('master/company', [CompanyController::class, 'index']);
Route::get('master/company/add', [CompanyController::class, 'add']);
Route::get('master/company/ubah', [CompanyController::class, 'ubah']);

Route::get('master/customer_category', [CustomerCategoryController::class, 'index']);
Route::get('master/customer_category/add', [CustomerCategoryController::class, 'add']);
Route::get('master/customer_category/ubah', [CustomerCategoryController::class, 'ubah']);

Route::get('master/customer', [CustomerController::class, 'index']);
Route::get('master/customer/add', [CustomerController::class, 'add']);
Route::get('master/customer/ubah', [CustomerController::class, 'ubah']);

/*API */

Route::post('api/master/company/getData', [MasterCompanyController::class, 'getData']);
Route::post('api/master/company/submit', [MasterCompanyController::class, 'submit']);
Route::post('api/master/company/delete', [MasterCompanyController::class, 'delete']);
Route::post('api/master/company/confirmDelete', [MasterCompanyController::class, 'confirmDelete']);
Route::post('api/master/company/uploadLogo', [MasterCompanyController::class, 'uploadLogo'])->name('company-upload-logo');

Route::post('api/master/customer_category/getData', [MasterCustomerCategoryController::class, 'getData']);
Route::post('api/master/customer_category/submit', [MasterCustomerCategoryController::class, 'submit']);
Route::post('api/master/customer_category/delete', [MasterCustomerCategoryController::class, 'delete']);
Route::post('api/master/customer_category/confirmDelete', [MasterCustomerCategoryController::class, 'confirmDelete']);

Route::post('api/master/customer/getData', [MasterCustomerController::class, 'getData']);
Route::post('api/master/customer/submit', [MasterCustomerController::class, 'submit']);
Route::post('api/master/customer/delete', [MasterCustomerController::class, 'delete']);
Route::post('api/master/customer/confirmDelete', [MasterCustomerController::class, 'confirmDelete']);
/*API */
