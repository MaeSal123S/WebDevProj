<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\AdvisorController;
use App\Http\Controllers\Admin\RepairOrderController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DatabaseController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Advisor\DashboardController as AdvisorDashboard;
use App\Http\Controllers\Advisor\RepairOrderController as AdvisorRepairOrderController;
use App\Http\Controllers\Advisor\CustomerController as AdvisorCustomerController;
use App\Http\Controllers\Advisor\VehicleController as AdvisorVehicleController;
use App\Http\Controllers\Admin\AppointmentController;
use App\Http\Controllers\Advisor\AppointmentController as AdvisorAppointmentController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;
use App\Http\Controllers\Customer\AppointmentController as CustomerAppointmentController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');
Route::post('/forgot-password', [AuthController::class, 'resetPassword'])->name('forgot.password.reset');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Connection of vehicle to customer
    Route::get('/vehicles-by-customer/{customer_id}', [RepairOrderController::class, 'getVehiclesByCustomer'])->name('vehicles.by.customer');
    // Latest appointment data for pre-filling repair orders
    Route::get('/appointment-by-customer/{customer_id}', [RepairOrderController::class, 'getAppointmentByCustomer'])->name('appointment.by.customer');

    // Dashboard
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Customers
    Route::group(['prefix' => 'customers'], function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Vehicles
    Route::group(['prefix' => 'vehicles'], function () {
        Route::get('/', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::post('/', [VehicleController::class, 'store'])->name('vehicles.store');
        Route::put('/{id}', [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/{id}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // Service Types
    Route::group(['prefix' => 'service-types'], function () {
        Route::get('/', [ServiceTypeController::class, 'index'])->name('service_types.index');
        Route::post('/', [ServiceTypeController::class, 'store'])->name('service_types.store');
        Route::put('/{id}', [ServiceTypeController::class, 'update'])->name('service_types.update');
        Route::delete('/{id}', [ServiceTypeController::class, 'destroy'])->name('service_types.destroy');
    });

    // Advisors
    Route::group(['prefix' => 'advisors'], function () {
        Route::get('/', [AdvisorController::class, 'index'])->name('advisors.index');
        Route::post('/', [AdvisorController::class, 'store'])->name('advisors.store');
        Route::put('/{id}', [AdvisorController::class, 'update'])->name('advisors.update');
        Route::delete('/{id}', [AdvisorController::class, 'destroy'])->name('advisors.destroy');
    });

    // Repair Orders
    Route::group(['prefix' => 'repair-orders'], function () {
        Route::get('/', [RepairOrderController::class, 'index'])->name('repair_orders.index');
        Route::post('/', [RepairOrderController::class, 'store'])->name('repair_orders.store');
        Route::put('/{id}', [RepairOrderController::class, 'update'])->name('repair_orders.update');
        Route::delete('/{id}', [RepairOrderController::class, 'destroy'])->name('repair_orders.destroy');
    });

    // Users
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/{id}/permissions', [UserController::class, 'getPermissions'])->name('users.permissions.get');
        Route::put('/{id}/permissions', [UserController::class, 'savePermissions'])->name('users.permissions.update');
    });

    // Appointments
    Route::group(['prefix' => 'appointments'], function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/', [AppointmentController::class, 'store'])->name('appointments.store');
        Route::put('/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::put('/{id}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    });

    // Calendar
    Route::get('/calendar', [AppointmentController::class, 'calendar'])->name('calendar');
    Route::get('/appointments/data', [AppointmentController::class, 'calendarData'])->name('appointments.data');

    // Inventory
    Route::group(['prefix' => 'inventory'], function () {
        Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/{id}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
        Route::put('/{id}/restock', [InventoryController::class, 'restock'])->name('inventory.restock');
        Route::post('/usage', [InventoryController::class, 'recordUsage'])->name('inventory.usage');
    });

    // Audit Logs
    Route::get('/audit-logs', [AuditController::class, 'index'])->name('audit.index');

    // Login Logs
    Route::get('/login-logs', [LoginLogController::class, 'index'])->name('login_logs.index');

    // Database Viewer
    Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
});

// Advisor routes
Route::middleware(['auth', 'advisor'])->prefix('advisor')->name('advisor.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdvisorDashboard::class, 'index'])->name('dashboard');

    // Vehicles by customer
    Route::get('/vehicles-by-customer/{customer_id}', [AdvisorRepairOrderController::class, 'getVehiclesByCustomer'])->name('advisor.vehicles.by.customer');
    // Appointment prefill for repair orders
    Route::get('/appointment-by-customer/{customer_id}', [AdvisorRepairOrderController::class, 'getAppointmentByCustomer'])->name('advisor.appointment.by.customer');

    // Customers
    Route::group(['prefix' => 'customers'], function () {
        Route::get('/', [AdvisorCustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [AdvisorCustomerController::class, 'store'])->name('customers.store');
        Route::put('/{id}', [AdvisorCustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{id}', [AdvisorCustomerController::class, 'destroy'])->name('customers.destroy');
    });

    // Vehicles
    Route::group(['prefix' => 'vehicles'], function () {
        Route::get('/', [AdvisorVehicleController::class, 'index'])->name('vehicles.index');
        Route::post('/', [AdvisorVehicleController::class, 'store'])->name('vehicles.store');
        Route::put('/{id}', [AdvisorVehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/{id}', [AdvisorVehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    // Repair Orders
    Route::group(['prefix' => 'repair-orders'], function () {
        Route::get('/', [AdvisorRepairOrderController::class, 'index'])->name('repair_orders.index');
        Route::post('/', [AdvisorRepairOrderController::class, 'store'])->name('repair_orders.store');
        Route::put('/{id}', [AdvisorRepairOrderController::class, 'update'])->name('repair_orders.update');
        Route::delete('/{id}', [AdvisorRepairOrderController::class, 'destroy'])->name('repair_orders.destroy');
    });

    // Appointments
    Route::group(['prefix' => 'appointments'], function () {
        Route::get('/', [AdvisorAppointmentController::class, 'index'])->name('appointments.index');
        Route::post('/', [AdvisorAppointmentController::class, 'store'])->name('appointments.store');
        Route::put('/{id}', [AdvisorAppointmentController::class, 'update'])->name('appointments.update');
        Route::delete('/{id}', [AdvisorAppointmentController::class, 'destroy'])->name('appointments.destroy');
    });

    // System pages (visible if admin grants permission)
    Route::get('/audit-logs',  [\App\Http\Controllers\Admin\AuditController::class,    'index'])->name('audit.index');
    Route::get('/login-logs',  [\App\Http\Controllers\Admin\LoginLogController::class, 'index'])->name('login_logs.index');
    Route::get('/database',    [\App\Http\Controllers\Admin\DatabaseController::class, 'index'])->name('database.index');
});

// Fallback route
Route::fallback(function () {
    return redirect()->route('login');
});

// Customer registration
Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.submit');

// Customer portal
Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboard::class, 'index'])->name('dashboard');

    // Profile & vehicles
    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [CustomerProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/vehicles', [CustomerProfileController::class, 'storeVehicle'])->name('vehicles.store');
    Route::put('/vehicles/{id}', [CustomerProfileController::class, 'updateVehicle'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [CustomerProfileController::class, 'destroyVehicle'])->name('vehicles.destroy');

    // Appointments
    Route::get('/appointments', [CustomerAppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments', [CustomerAppointmentController::class, 'store'])->name('appointments.store');
    Route::delete('/appointments/{id}', [CustomerAppointmentController::class, 'destroy'])->name('appointments.destroy');
});
