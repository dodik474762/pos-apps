<?php

use App\Http\Controllers\api\master\CityController as MasterCityController;
use App\Http\Controllers\api\master\CompanyController as MasterCompanyController;
use App\Http\Controllers\api\master\CustomerCategoryController as MasterCustomerCategoryController;
use App\Http\Controllers\api\master\CustomerController as MasterCustomerController;
use App\Http\Controllers\api\master\DayoffController as MasterDayoffController;
use App\Http\Controllers\api\master\GroupController as MasterGroupController;
use App\Http\Controllers\api\master\ItemController as MasterItemController;
use App\Http\Controllers\api\master\KaryawanController as MasterKaryawanController;
use App\Http\Controllers\api\master\KecamatanController as MasterKecamatanController;
use App\Http\Controllers\api\master\KelurahanController as MasterKelurahanController;
use App\Http\Controllers\api\master\MenuController as MasterMenuController;
use App\Http\Controllers\api\master\PermissionsController as MasterPermissionsController;
use App\Http\Controllers\api\master\ProductController as MasterProductController;
use App\Http\Controllers\api\master\ProductTypeController as MasterProductTypeController;
use App\Http\Controllers\api\master\ProvinceController as MasterProvinceController;
use App\Http\Controllers\api\master\RolesController as MasterRolesController;
use App\Http\Controllers\api\master\RoutingApprovalController as MasterRoutingApprovalController;
use App\Http\Controllers\api\master\UnitController as MasterUnitController;
use App\Http\Controllers\api\master\UsersController as MasterUsersController;
use App\Http\Controllers\api\master\WarehouseController;
use App\Http\Controllers\api\master\WorkingHourController as MasterWorkingHourController;
use App\Http\Controllers\web\auth\LoginController;
use App\Http\Controllers\web\DashboardController;
use App\Http\Controllers\web\master\CityController;
use App\Http\Controllers\web\master\CompanyController;
use App\Http\Controllers\web\master\CustomerCategoryController;
use App\Http\Controllers\web\master\CustomerController;
use App\Http\Controllers\web\master\DayoffController;
use App\Http\Controllers\web\master\GroupController;
use App\Http\Controllers\web\master\ItemController;
use App\Http\Controllers\web\master\KaryawanController;
use App\Http\Controllers\web\master\KecamatanController;
use App\Http\Controllers\web\master\KelurahanController;
use App\Http\Controllers\web\master\MenuController;
use App\Http\Controllers\web\master\PermissionsController;
use App\Http\Controllers\web\master\ProductController;
use App\Http\Controllers\web\master\ProductTypeController;
use App\Http\Controllers\web\master\ProvinceController;
use App\Http\Controllers\web\master\RolesController;
use App\Http\Controllers\web\master\RoutingApprovalController;
use App\Http\Controllers\web\master\UnitController;
use App\Http\Controllers\web\master\UsersController;
use App\Http\Controllers\web\master\WarehouseController as MasterWarehouseController;
use App\Http\Controllers\web\master\WorkingHourController;
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

Route::get('master/unit', [UnitController::class, 'index']);
Route::get('master/unit/add', [UnitController::class, 'add']);
Route::get('master/unit/ubah', [UnitController::class, 'ubah']);

Route::get('master/product', [ProductController::class, 'index']);
Route::get('master/product/add', [ProductController::class, 'add']);
Route::get('master/product/ubah', [ProductController::class, 'ubah']);

Route::get('master/product_type', [ProductTypeController::class, 'index']);
Route::get('master/product_type/add', [ProductTypeController::class, 'add']);
Route::get('master/product_type/ubah', [ProductTypeController::class, 'ubah']);

Route::get('master/item', [ItemController::class, 'index']);
Route::get('master/item/add', [ItemController::class, 'add']);
Route::get('master/item/ubah', [ItemController::class, 'ubah']);

Route::get('master/warehouse', [MasterWarehouseController::class, 'index']);
Route::get('master/warehouse/add', [MasterWarehouseController::class, 'add']);
Route::get('master/warehouse/ubah', [MasterWarehouseController::class, 'ubah']);

Route::get('master/menu', [MenuController::class, 'index']);
Route::get('master/menu/add', [MenuController::class, 'add']);
Route::get('master/menu/ubah', [MenuController::class, 'ubah']);

Route::get('master/roles', [RolesController::class, 'index']);
Route::get('master/roles/add', [RolesController::class, 'add']);
Route::get('master/roles/ubah', [RolesController::class, 'ubah']);

Route::get('master/users', [UsersController::class, 'index']);
Route::get('master/users/add', [UsersController::class, 'add']);
Route::get('master/users/ubah', [UsersController::class, 'ubah']);

Route::get('master/karyawan', [KaryawanController::class, 'index']);
Route::get('master/karyawan/add', [KaryawanController::class, 'add']);
Route::get('master/karyawan/ubah', [KaryawanController::class, 'ubah']);

Route::get('master/permission', [PermissionsController::class, 'index']);
Route::get('master/permission/add', [PermissionsController::class, 'add']);
Route::get('master/permission/ubah', [PermissionsController::class, 'ubah']);

Route::get('master/group', [GroupController::class, 'index']);
Route::get('master/group/add', [GroupController::class, 'add']);
Route::get('master/group/ubah', [GroupController::class, 'ubah']);

Route::get('master/routing', [RoutingApprovalController::class, 'index']);
Route::get('master/routing/add', [RoutingApprovalController::class, 'add']);
Route::get('master/routing/ubah', [RoutingApprovalController::class, 'ubah']);

Route::get('master/dayoff', [DayoffController::class, 'index']);
Route::get('master/dayoff/add', [DayoffController::class, 'add']);
Route::get('master/dayoff/ubah', [DayoffController::class, 'ubah']);

Route::get('master/working_hours', [WorkingHourController::class, 'index']);
Route::get('master/working_hours/add', [WorkingHourController::class, 'add']);
Route::get('master/working_hours/ubah', [WorkingHourController::class, 'ubah']);

Route::get('master/province', [ProvinceController::class, 'index']);
Route::get('master/province/add', [ProvinceController::class, 'add']);
Route::get('master/province/ubah', [ProvinceController::class, 'ubah']);

Route::get('master/city', [CityController::class, 'index']);
Route::get('master/city/add', [CityController::class, 'add']);
Route::get('master/city/ubah', [CityController::class, 'ubah']);

Route::get('master/kecamatan', [KecamatanController::class, 'index']);
Route::get('master/kecamatan/add', [KecamatanController::class, 'add']);
Route::get('master/kecamatan/ubah', [KecamatanController::class, 'ubah']);

Route::get('master/kelurahan', [KelurahanController::class, 'index']);
Route::get('master/kelurahan/add', [KelurahanController::class, 'add']);
Route::get('master/kelurahan/ubah', [KelurahanController::class, 'ubah']);

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
Route::post('api/master/customer/getCity', [MasterCustomerController::class, 'getCity']);

Route::post('api/master/unit/getData', [MasterUnitController::class, 'getData']);
Route::post('api/master/unit/submit', [MasterUnitController::class, 'submit']);
Route::post('api/master/unit/delete', [MasterUnitController::class, 'delete']);
Route::post('api/master/unit/confirmDelete', [MasterUnitController::class, 'confirmDelete']);

Route::post('api/master/product/getData', [MasterProductController::class, 'getData']);
Route::post('api/master/product/submit', [MasterProductController::class, 'submit']);
Route::post('api/master/product/delete', [MasterProductController::class, 'delete']);
Route::post('api/master/product/confirmDelete', [MasterProductController::class, 'confirmDelete']);

Route::post('api/master/item/getData', [MasterItemController::class, 'getData']);
Route::post('api/master/item/submit', [MasterItemController::class, 'submit'])->name('item-submit');
Route::post('api/master/item/delete', [MasterItemController::class, 'delete']);
Route::post('api/master/item/confirmDelete', [MasterItemController::class, 'confirmDelete']);

Route::post('api/master/product_type/getData', [MasterProductTypeController::class, 'getData']);
Route::post('api/master/product_type/submit', [MasterProductTypeController::class, 'submit']);
Route::post('api/master/product_type/delete', [MasterProductTypeController::class, 'delete']);
Route::post('api/master/product_type/confirmDelete', [MasterProductTypeController::class, 'confirmDelete']);

Route::post('api/master/warehouse/getData', [WarehouseController::class, 'getData']);
Route::post('api/master/warehouse/submit', [WarehouseController::class, 'submit']);
Route::post('api/master/warehouse/delete', [WarehouseController::class, 'delete']);
Route::post('api/master/warehouse/confirmDelete', [WarehouseController::class, 'confirmDelete']);

Route::post('api/master/menu/getData', [MasterMenuController::class, 'getData']);
Route::post('api/master/menu/submit', [MasterMenuController::class, 'submit']);
Route::post('api/master/menu/delete', [MasterMenuController::class, 'delete']);
Route::post('api/master/menu/confirmDelete', [MasterMenuController::class, 'confirmDelete']);

Route::post('api/master/roles/getData', [MasterRolesController::class, 'getData']);
Route::post('api/master/roles/submit', [MasterRolesController::class, 'submit']);
Route::post('api/master/roles/delete', [MasterRolesController::class, 'delete']);
Route::post('api/master/roles/confirmDelete', [MasterRolesController::class, 'confirmDelete']);

Route::post('api/master/users/getData', [MasterUsersController::class, 'getData']);
Route::post('api/master/users/submit', [MasterUsersController::class, 'submit']);
Route::post('api/master/users/delete', [MasterUsersController::class, 'delete']);
Route::post('api/master/users/confirmDelete', [MasterUsersController::class, 'confirmDelete']);
Route::post('api/master/users/showDataKaryawan', [MasterUsersController::class, 'showDataKaryawan']);

Route::post('api/master/karyawan/getData', [MasterKaryawanController::class, 'getData']);
Route::post('api/master/karyawan/submit', [MasterKaryawanController::class, 'submit']);
Route::post('api/master/karyawan/delete', [MasterKaryawanController::class, 'delete']);
Route::post('api/master/karyawan/confirmDelete', [MasterKaryawanController::class, 'confirmDelete']);

Route::post('api/master/permission/getData', [MasterPermissionsController::class, 'getData']);
Route::post('api/master/permission/submit', [MasterPermissionsController::class, 'submit']);
Route::post('api/master/permission/delete', [MasterPermissionsController::class, 'delete']);
Route::post('api/master/permission/confirmDelete', [MasterPermissionsController::class, 'confirmDelete']);
Route::post('api/master/permission/showMenu', [MasterPermissionsController::class, 'showMenu']);

Route::post('api/master/group/getData', [MasterGroupController::class, 'getData']);
Route::post('api/master/group/submit', [MasterGroupController::class, 'submit']);
Route::post('api/master/group/delete', [MasterGroupController::class, 'delete']);
Route::post('api/master/group/confirmDelete', [MasterGroupController::class, 'confirmDelete']);

Route::post('api/master/routing/getData', [MasterRoutingApprovalController::class, 'getData']);
Route::post('api/master/routing/submit', [MasterRoutingApprovalController::class, 'submit']);
Route::post('api/master/routing/delete', [MasterRoutingApprovalController::class, 'delete']);
Route::post('api/master/routing/confirmDelete', [MasterRoutingApprovalController::class, 'confirmDelete']);
Route::post('api/master/routing/showDataUsers', [MasterRoutingApprovalController::class, 'showDataUsers']);

Route::post('api/master/dayoff/getData', [MasterDayoffController::class, 'getData']);
Route::post('api/master/dayoff/submit', [MasterDayoffController::class, 'submit']);
Route::post('api/master/dayoff/delete', [MasterDayoffController::class, 'delete']);
Route::post('api/master/dayoff/confirmDelete', [MasterDayoffController::class, 'confirmDelete']);

Route::post('api/master/working_hours/getData', [MasterWorkingHourController::class, 'getData']);
Route::post('api/master/working_hours/submit', [MasterWorkingHourController::class, 'submit']);
Route::post('api/master/working_hours/delete', [MasterWorkingHourController::class, 'delete']);
Route::post('api/master/working_hours/confirmDelete', [MasterWorkingHourController::class, 'confirmDelete']);

Route::post('api/master/province/getData', [MasterProvinceController::class, 'getData']);
Route::post('api/master/province/submit', [MasterProvinceController::class, 'submit']);
Route::post('api/master/province/delete', [MasterProvinceController::class, 'delete']);
Route::post('api/master/province/confirmDelete', [MasterProvinceController::class, 'confirmDelete']);

Route::post('api/master/city/getData', [MasterCityController::class, 'getData']);
Route::post('api/master/city/submit', [MasterCityController::class, 'submit']);
Route::post('api/master/city/delete', [MasterCityController::class, 'delete']);
Route::post('api/master/city/confirmDelete', [MasterCityController::class, 'confirmDelete']);

Route::post('api/master/kecamatan/getData', [MasterKecamatanController::class, 'getData']);
Route::post('api/master/kecamatan/submit', [MasterKecamatanController::class, 'submit']);
Route::post('api/master/kecamatan/delete', [MasterKecamatanController::class, 'delete']);
Route::post('api/master/kecamatan/confirmDelete', [MasterKecamatanController::class, 'confirmDelete']);
Route::post('api/master/kecamatan/getCity', [MasterKecamatanController::class, 'getCity']);

Route::post('api/master/kelurahan/getData', [MasterKelurahanController::class, 'getData']);
Route::post('api/master/kelurahan/submit', [MasterKelurahanController::class, 'submit']);
Route::post('api/master/kelurahan/delete', [MasterKelurahanController::class, 'delete']);
Route::post('api/master/kelurahan/confirmDelete', [MasterKelurahanController::class, 'confirmDelete']);
Route::post('api/master/kelurahan/getCity', [MasterKelurahanController::class, 'getCity']);
Route::post('api/master/kelurahan/getKecamatan', [MasterKelurahanController::class, 'getKecamatan']);
/*API */
