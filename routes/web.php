<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VehicleInspectionController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes (for demo)
Route::get('/login', function () {
    // Auto-login for demo
    return redirect()->route('dashboard');
})->name('login');

Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// Protected Routes (with auto-login middleware)
Route::middleware([\App\Http\Middleware\EnsureUserIsAuthenticated::class])->group(function () {
    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [DashboardController::class, 'generateReport'])->name('reports.generate');

    // Customer Routes
    Route::resource('customers', CustomerController::class);
    Route::post('/customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.notes.store');
    Route::post('/customers/{customer}/loyalty', [CustomerController::class, 'updateLoyalty'])->name('customers.loyalty.update');
    Route::get('/customers/{customer}/service-history', [CustomerController::class, 'serviceHistory'])->name('customers.service-history');
    Route::get('/customers/{customer}/vehicles', [CustomerController::class, 'vehicles'])->name('customers.vehicles');
    Route::get('/customers/{customer}/notes', [CustomerController::class, 'notes'])->name('customers.notes');
    Route::get('/customers/{customer}/export/{format?}', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('/customers/{customer}/send-reminder/{vehicle?}', [CustomerController::class, 'sendReminder'])->name('customers.send-reminder');

    // Appointment Routes
    Route::resource('appointments', AppointmentController::class);
    Route::get('/appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::get('/appointments/statistics', [AppointmentController::class, 'statistics'])->name('appointments.statistics');
    Route::post('/appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn'])->name('appointments.check-in');
    Route::post('/appointments/{appointment}/start', [AppointmentController::class, 'start'])->name('appointments.start');
    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('/appointments/{appointment}/mark-no-show', [AppointmentController::class, 'markNoShow'])->name('appointments.mark-no-show');
    Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/convert-waitlist', [AppointmentController::class, 'convertFromWaitlist'])->name('appointments.convert-waitlist');
    Route::post('/appointments/{appointment}/send-reminder', [AppointmentController::class, 'sendReminder'])->name('appointments.send-reminder');
    Route::post('/appointments/{appointment}/send-confirmation', [AppointmentController::class, 'sendConfirmation'])->name('appointments.send-confirmation');

    // Work Order Routes
    Route::resource('work-orders', WorkOrderController::class);
    Route::get('/work-orders/statistics', [WorkOrderController::class, 'statistics'])->name('work-orders.statistics');
    Route::post('/work-orders/{work_order}/approve-estimate', [WorkOrderController::class, 'approveEstimate'])->name('work-orders.approve-estimate');
    Route::post('/work-orders/{work_order}/start-work', [WorkOrderController::class, 'startWork'])->name('work-orders.start-work');
    Route::post('/work-orders/{work_order}/complete-work', [WorkOrderController::class, 'completeWork'])->name('work-orders.complete-work');
    Route::post('/work-orders/{work_order}/mark-invoiced', [WorkOrderController::class, 'markAsInvoiced'])->name('work-orders.mark-invoiced');
    Route::post('/work-orders/{work_order}/add-payment', [WorkOrderController::class, 'addPayment'])->name('work-orders.add-payment');
    Route::get('/work-orders/{work_order}/print', [WorkOrderController::class, 'print'])->name('work-orders.print');

    // Estimate Routes
    Route::get('/estimates/statistics', [EstimateController::class, 'statistics'])->name('estimates.statistics');
    Route::resource('estimates', EstimateController::class);
    Route::post('/estimates/{estimate}/approve', [EstimateController::class, 'approve'])->name('estimates.approve');
    Route::post('/estimates/{estimate}/reject', [EstimateController::class, 'reject'])->name('estimates.reject');
    Route::patch('/estimates/{estimate}/update-status', [EstimateController::class, 'updateStatus'])->name('estimates.update-status');
    Route::post('/estimates/{estimate}/convert-to-work-order', [EstimateController::class, 'convertToWorkOrder'])->name('estimates.convert-to-work-order');
    Route::post('/estimates/{estimate}/send', [EstimateController::class, 'send'])->name('estimates.send');
    Route::get('/estimates/{estimate}/print', [EstimateController::class, 'print'])->name('estimates.print');

    // Invoice Routes
    Route::resource('invoices', InvoiceController::class);
    Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid');
    Route::post('/invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/statistics', [InvoiceController::class, 'statistics'])->name('invoices.statistics');

    // Payment Routes
    Route::resource('payments', PaymentController::class);
    Route::post('/payments/{payment}/mark-as-completed', [PaymentController::class, 'markAsCompleted'])->name('payments.mark-as-completed');
    Route::post('/payments/{payment}/mark-as-failed', [PaymentController::class, 'markAsFailed'])->name('payments.mark-as-failed');
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
    Route::get('/payments/{payment}/print', [PaymentController::class, 'print'])->name('payments.print');
    Route::get('/payments/statistics', [PaymentController::class, 'statistics'])->name('payments.statistics');
    Route::get('/payments/by-date-range', [PaymentController::class, 'byDateRange'])->name('payments.by-date-range');

    // Vehicle Inspection Routes
    Route::resource('inspections', VehicleInspectionController::class);
    Route::get('/inspections/statistics', [VehicleInspectionController::class, 'statistics'])->name('inspections.statistics');
    Route::post('/inspections/{inspection}/start', [VehicleInspectionController::class, 'startInspection'])->name('inspections.start');
    Route::post('/inspections/{inspection}/complete', [VehicleInspectionController::class, 'completeInspection'])->name('inspections.complete');
    Route::post('/inspections/{inspection}/undo-complete', [VehicleInspectionController::class, 'undoCompleteInspection'])->name('inspections.undo-complete');
    Route::post('/inspections/{inspection}/approve', [VehicleInspectionController::class, 'approveInspection'])->name('inspections.approve');
    Route::post('/inspections/{inspection}/request-customer-approval', [VehicleInspectionController::class, 'requestCustomerApproval'])->name('inspections.request-customer-approval');
    Route::post('/inspections/{inspection}/approve-by-customer', [VehicleInspectionController::class, 'approveByCustomer'])->name('inspections.approve-by-customer');
    Route::get('/inspections/{inspection}/report', [VehicleInspectionController::class, 'generateReport'])->name('inspections.report');
    Route::get('/inspections/{inspection}/manage-items', [VehicleInspectionController::class, 'manageItems'])->name('inspections.manage-items');
    Route::post('/inspections/{inspection}/items', [VehicleInspectionController::class, 'storeItem'])->name('inspections.store-item');
    Route::post('/inspections/{inspection}/items/{item}/update', [VehicleInspectionController::class, 'updateItem'])->name('inspections.update-item');
    Route::post('/inspections/{inspection}/upload-photo', [VehicleInspectionController::class, 'uploadPhoto'])->name('inspections.upload-photo');
    Route::post('/inspections/{inspection}/update-mileage', [VehicleInspectionController::class, 'updateMileage'])->name('inspections.update-mileage');

    // Inventory Routes
    Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
    Route::get('/inventory/statistics', [InventoryController::class, 'statistics'])->name('inventory.statistics');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::resource('inventory', InventoryController::class);
    Route::post('/inventory/{inventory}/adjust-quantity', [InventoryController::class, 'adjustQuantity'])->name('inventory.adjust-quantity');
    Route::get('/inventory/{inventory}/generate-barcode', [InventoryController::class, 'generateBarcode'])->name('inventory.generate-barcode');
    
    // Categories Management Routes (WORKAROUND for Apache /inventory/ issue)
    Route::resource('categories-management', \App\Http\Controllers\InventoryCategoryController::class)->names([
        'index' => 'inventory.categories.index',
        'create' => 'inventory.categories.create',
        'store' => 'inventory.categories.store',
        'show' => 'inventory.categories.show',
        'edit' => 'inventory.categories.edit',
        'update' => 'inventory.categories.update',
        'destroy' => 'inventory.categories.destroy',
    ]);
    Route::get('/categories-management/api/list', [\App\Http\Controllers\InventoryCategoryController::class, 'getCategories'])->name('inventory.categories.api.list');
    
    // Suppliers Management Routes (WORKAROUND for Apache /inventory/ issue)
    Route::resource('suppliers-management', \App\Http\Controllers\InventorySupplierController::class)->names([
        'index' => 'inventory.suppliers.index',
        'create' => 'inventory.suppliers.create',
        'store' => 'inventory.suppliers.store',
        'show' => 'inventory.suppliers.show',
        'edit' => 'inventory.suppliers.edit',
        'update' => 'inventory.suppliers.update',
        'destroy' => 'inventory.suppliers.destroy',
    ]);
    Route::post('/suppliers-management/{supplier}/update-balance', [\App\Http\Controllers\InventorySupplierController::class, 'updateBalance'])->name('inventory.suppliers.update-balance');
    Route::get('/suppliers-management/api/list', [\App\Http\Controllers\InventorySupplierController::class, 'getSuppliers'])->name('inventory.suppliers.api.list');

    // Parts Procurement Routes
    Route::resource('parts-procurement', \App\Http\Controllers\PartsProcurementController::class);
    Route::get('/parts-procurement/lookup', [\App\Http\Controllers\PartsProcurementController::class, 'lookup'])->name('parts-procurement.lookup');
    Route::post('/parts-procurement/lookup', [\App\Http\Controllers\PartsProcurementController::class, 'lookup'])->name('parts-procurement.lookup.post');
    Route::get('/parts-procurement/returns', [\App\Http\Controllers\PartsProcurementController::class, 'returnsIndex'])->name('parts-procurement.returns.index');
    Route::get('/parts-procurement/core-returns', [\App\Http\Controllers\PartsProcurementController::class, 'coreReturnsIndex'])->name('parts-procurement.core-returns.index');
    Route::get('/parts-procurement/{partsOrder}/create-return', [\App\Http\Controllers\PartsProcurementController::class, 'createReturn'])->name('parts-procurement.create-return');
    Route::post('/parts-procurement/{partsOrder}/store-return', [\App\Http\Controllers\PartsProcurementController::class, 'storeReturn'])->name('parts-procurement.store-return');
    Route::get('/parts-procurement/returns/{partsReturn}', [\App\Http\Controllers\PartsProcurementController::class, 'showReturn'])->name('parts-procurement.returns.show');
    Route::get('/parts-procurement/{partsOrder}/create-core-return', [\App\Http\Controllers\PartsProcurementController::class, 'createCoreReturn'])->name('parts-procurement.create-core-return');
    Route::post('/parts-procurement/{partsOrder}/store-core-return', [\App\Http\Controllers\PartsProcurementController::class, 'storeCoreReturn'])->name('parts-procurement.store-core-return');
    Route::get('/parts-procurement/core-returns/{coreReturn}', [\App\Http\Controllers\PartsProcurementController::class, 'showCoreReturn'])->name('parts-procurement.core-returns.show');
    Route::post('/parts-procurement/{partsOrder}/submit', [\App\Http\Controllers\PartsProcurementController::class, 'submit'])->name('parts-procurement.submit');
    Route::post('/parts-procurement/{partsOrder}/approve', [\App\Http\Controllers\PartsProcurementController::class, 'approve'])->name('parts-procurement.approve');
    Route::post('/parts-procurement/{partsOrder}/ship', [\App\Http\Controllers\PartsProcurementController::class, 'ship'])->name('parts-procurement.ship');
    Route::post('/parts-procurement/{partsOrder}/deliver', [\App\Http\Controllers\PartsProcurementController::class, 'deliver'])->name('parts-procurement.deliver');

    // Customer Portal Routes
    Route::prefix('portal')->name('portal.')->group(function () {
        // Authentication routes
        Route::get('/login', [\App\Http\Controllers\CustomerPortalController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [\App\Http\Controllers\CustomerPortalController::class, 'login'])->name('login.post');
        Route::post('/logout', [\App\Http\Controllers\CustomerPortalController::class, 'logout'])->name('logout');
        
        // Registration routes
        Route::get('/register', [\App\Http\Controllers\CustomerPortalController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [\App\Http\Controllers\CustomerPortalController::class, 'register'])->name('register.post');
        Route::get('/verify-email/{token}', [\App\Http\Controllers\CustomerPortalController::class, 'verifyEmail'])->name('verify-email');
        
        // Password reset routes
        Route::get('/forgot-password', [\App\Http\Controllers\CustomerPortalController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [\App\Http\Controllers\CustomerPortalController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset-password/{token}', [\App\Http\Controllers\CustomerPortalController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [\App\Http\Controllers\CustomerPortalController::class, 'resetPassword'])->name('password.update');
        
        // Protected portal routes (require authentication)
        Route::middleware(['portal.auth'])->group(function () {
            // Dashboard
            Route::get('/dashboard', [\App\Http\Controllers\CustomerPortalController::class, 'dashboard'])->name('dashboard');
            
            // Profile management
            Route::get('/profile', [\App\Http\Controllers\CustomerPortalController::class, 'profile'])->name('profile');
            Route::put('/profile', [\App\Http\Controllers\CustomerPortalController::class, 'updateProfile'])->name('profile.update');
            Route::get('/profile/preferences', [\App\Http\Controllers\CustomerPortalController::class, 'preferences'])->name('profile.preferences');
            Route::put('/profile/preferences', [\App\Http\Controllers\CustomerPortalController::class, 'updatePreferences'])->name('profile.preferences.update');
            
            // Vehicles
            Route::get('/vehicles', [\App\Http\Controllers\CustomerPortalController::class, 'vehicles'])->name('vehicles');
            Route::get('/vehicles/{vehicle}', [\App\Http\Controllers\CustomerPortalController::class, 'showVehicle'])->name('vehicles.show');
            Route::get('/vehicles/{vehicle}/service-history', [\App\Http\Controllers\CustomerPortalController::class, 'vehicleServiceHistory'])->name('vehicles.service-history');
            
            // Appointments
            Route::get('/appointments', [\App\Http\Controllers\CustomerPortalController::class, 'appointments'])->name('appointments');
            Route::get('/appointments/{appointment}', [\App\Http\Controllers\CustomerPortalController::class, 'showAppointment'])->name('appointments.show');
            Route::post('/appointments/{appointment}/cancel', [\App\Http\Controllers\CustomerPortalController::class, 'cancelAppointment'])->name('appointments.cancel');
            Route::post('/appointments/{appointment}/reschedule', [\App\Http\Controllers\CustomerPortalController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
            Route::get('/appointments/create', [\App\Http\Controllers\CustomerPortalController::class, 'createAppointment'])->name('appointments.create');
            Route::post('/appointments', [\App\Http\Controllers\CustomerPortalController::class, 'storeAppointment'])->name('appointments.store');
            
            // Work orders
            Route::get('/work-orders', [\App\Http\Controllers\CustomerPortalController::class, 'workOrders'])->name('work-orders');
            Route::get('/work-orders/{workOrder}', [\App\Http\Controllers\CustomerPortalController::class, 'showWorkOrder'])->name('work-orders.show');
            Route::post('/work-orders/{workOrder}/approve-estimate', [\App\Http\Controllers\CustomerPortalController::class, 'approveWorkOrderEstimate'])->name('work-orders.approve-estimate');
            Route::get('/work-orders/{workOrder}/invoice', [\App\Http\Controllers\CustomerPortalController::class, 'showInvoice'])->name('work-orders.invoice');
            
            // Inspections
            Route::get('/inspections', [\App\Http\Controllers\CustomerPortalController::class, 'inspections'])->name('inspections');
            Route::get('/inspections/{inspection}', [\App\Http\Controllers\CustomerPortalController::class, 'showInspection'])->name('inspections.show');
            Route::post('/inspections/{inspection}/approve', [\App\Http\Controllers\CustomerPortalController::class, 'approveInspection'])->name('inspections.approve');
            Route::get('/inspections/{inspection}/report', [\App\Http\Controllers\CustomerPortalController::class, 'inspectionReport'])->name('inspections.report');
            
            // Documents
            Route::get('/documents', [\App\Http\Controllers\CustomerPortalController::class, 'documents'])->name('documents');
            Route::get('/documents/{document}', [\App\Http\Controllers\CustomerPortalController::class, 'showDocument'])->name('documents.show');
            Route::post('/documents/{document}/download', [\App\Http\Controllers\CustomerPortalController::class, 'downloadDocument'])->name('documents.download');
            
            // Messages
            Route::get('/messages', [\App\Http\Controllers\CustomerPortalController::class, 'messages'])->name('messages');
            Route::get('/messages/{message}', [\App\Http\Controllers\CustomerPortalController::class, 'showMessage'])->name('messages.show');
            Route::post('/messages/{message}/mark-read', [\App\Http\Controllers\CustomerPortalController::class, 'markMessageAsRead'])->name('messages.mark-read');
            Route::post('/messages', [\App\Http\Controllers\CustomerPortalController::class, 'sendMessage'])->name('messages.send');
            
            // Service requests
            Route::get('/service-requests', [\App\Http\Controllers\CustomerPortalController::class, 'serviceRequests'])->name('service-requests');
            Route::get('/service-requests/create', [\App\Http\Controllers\CustomerPortalController::class, 'createServiceRequest'])->name('service-requests.create');
            Route::post('/service-requests', [\App\Http\Controllers\CustomerPortalController::class, 'storeServiceRequest'])->name('service-requests.store');
            Route::get('/service-requests/{serviceRequest}', [\App\Http\Controllers\CustomerPortalController::class, 'showServiceRequest'])->name('service-requests.show');
            Route::post('/service-requests/{serviceRequest}/cancel', [\App\Http\Controllers\CustomerPortalController::class, 'cancelServiceRequest'])->name('service-requests.cancel');
            
            // Reviews
            Route::get('/reviews', [\App\Http\Controllers\CustomerPortalController::class, 'reviews'])->name('reviews');
            Route::get('/reviews/create', [\App\Http\Controllers\CustomerPortalController::class, 'createReview'])->name('reviews.create');
            Route::post('/reviews', [\App\Http\Controllers\CustomerPortalController::class, 'storeReview'])->name('reviews.store');
            
            // Loyalty program
            Route::get('/loyalty', [\App\Http\Controllers\CustomerPortalController::class, 'loyalty'])->name('loyalty');
            Route::get('/loyalty/rewards', [\App\Http\Controllers\CustomerPortalController::class, 'loyaltyRewards'])->name('loyalty.rewards');
            Route::post('/loyalty/redeem', [\App\Http\Controllers\CustomerPortalController::class, 'redeemLoyaltyPoints'])->name('loyalty.redeem');
            
            // Billing
            Route::get('/billing', [\App\Http\Controllers\CustomerPortalController::class, 'billing'])->name('billing');
            Route::get('/billing/invoices', [\App\Http\Controllers\CustomerPortalController::class, 'invoices'])->name('billing.invoices');
            Route::get('/billing/invoices/{invoice}', [\App\Http\Controllers\CustomerPortalController::class, 'showInvoice'])->name('billing.invoices.show');
            Route::post('/billing/invoices/{invoice}/pay', [\App\Http\Controllers\CustomerPortalController::class, 'payInvoice'])->name('billing.invoices.pay');
            Route::get('/billing/payment-methods', [\App\Http\Controllers\CustomerPortalController::class, 'paymentMethods'])->name('billing.payment-methods');
            Route::post('/billing/payment-methods', [\App\Http\Controllers\CustomerPortalController::class, 'storePaymentMethod'])->name('billing.payment-methods.store');
            Route::delete('/billing/payment-methods/{paymentMethod}', [\App\Http\Controllers\CustomerPortalController::class, 'deletePaymentMethod'])->name('billing.payment-methods.delete');
        });
    });

    // Performance Metrics Routes
    Route::resource('performance', \App\Http\Controllers\PerformanceController::class);
    Route::get('/performance/dashboard', [\App\Http\Controllers\PerformanceController::class, 'dashboard'])->name('performance.dashboard');
    Route::get('/performance/analytics', [\App\Http\Controllers\PerformanceController::class, 'analytics'])->name('performance.analytics');
    Route::get('/performance/technician/{technicianId}/report', [\App\Http\Controllers\PerformanceController::class, 'generateReport'])->name('performance.report');
    Route::get('/performance/export', [\App\Http\Controllers\PerformanceController::class, 'export'])->name('performance.export');
    Route::post('/performance/calculate-automated', [\App\Http\Controllers\PerformanceController::class, 'calculateAutomatedMetrics'])->name('performance.calculate-automated');
    Route::get('/performance/trends', [\App\Http\Controllers\PerformanceController::class, 'trends'])->name('performance.trends');

    // Skill Management Routes
    Route::resource('skills', \App\Http\Controllers\SkillController::class);
    Route::get('/skills/dashboard', [\App\Http\Controllers\SkillController::class, 'dashboard'])->name('skills.dashboard');
    Route::get('/skills/analytics', [\App\Http\Controllers\SkillController::class, 'analytics'])->name('skills.analytics');
    Route::get('/skills/export', [\App\Http\Controllers\SkillController::class, 'export'])->name('skills.export');
    Route::get('/skills/technician/{technicianId}/recommendations', [\App\Http\Controllers\SkillController::class, 'getRecommendations'])->name('skills.recommendations');
    Route::post('/skills/bulk-update', [\App\Http\Controllers\SkillController::class, 'bulkUpdate'])->name('skills.bulk-update');
    Route::post('/skills/{id}/mark-as-used', [\App\Http\Controllers\SkillController::class, 'markAsUsed'])->name('skills.mark-as-used');

    // Certification Management Routes
    Route::resource('certifications', \App\Http\Controllers\CertificationController::class);
    Route::get('/certifications/dashboard', [\App\Http\Controllers\CertificationController::class, 'dashboard'])->name('certifications.dashboard');
    Route::get('/certifications/analytics', [\App\Http\Controllers\CertificationController::class, 'analytics'])->name('certifications.analytics');
    Route::get('/certifications/export', [\App\Http\Controllers\CertificationController::class, 'export'])->name('certifications.export');
    Route::get('/certifications/alerts', [\App\Http\Controllers\CertificationController::class, 'getAlerts'])->name('certifications.alerts');
    Route::post('/certifications/bulk-update', [\App\Http\Controllers\CertificationController::class, 'bulkUpdate'])->name('certifications.bulk-update');
    Route::post('/certifications/{id}/verify', [\App\Http\Controllers\CertificationController::class, 'verify'])->name('certifications.verify');
    Route::post('/certifications/{id}/unverify', [\App\Http\Controllers\CertificationController::class, 'unverify'])->name('certifications.unverify');
    Route::post('/certifications/{id}/renew', [\App\Http\Controllers\CertificationController::class, 'renew'])->name('certifications.renew');
    Route::get('/certifications/{id}/download-certificate', [\App\Http\Controllers\CertificationController::class, 'downloadCertificate'])->name('certifications.download-certificate');

    // Training Routes
    Route::prefix('training')->name('training.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TrainingController::class, 'index'])->name('index');
        Route::get('/{module}', [\App\Http\Controllers\TrainingController::class, 'show'])->name('show');
        Route::post('/{module}/start', [\App\Http\Controllers\TrainingController::class, 'start'])->name('start');
        Route::post('/{module}/complete', [\App\Http\Controllers\TrainingController::class, 'complete'])->name('complete');
        Route::get('/progress', [\App\Http\Controllers\TrainingController::class, 'progress'])->name('progress');
        Route::get('/dashboard', [\App\Http\Controllers\TrainingController::class, 'dashboard'])->name('dashboard');
        Route::post('/assign', [\App\Http\Controllers\TrainingController::class, 'assign'])->name('assign');
    });

    // Parts Request Routes
    Route::resource('parts-requests', \App\Http\Controllers\PartsRequestController::class);
    Route::get('/parts-requests/dashboard', [\App\Http\Controllers\PartsRequestController::class, 'dashboard'])->name('parts-requests.dashboard');
    Route::get('/parts-requests/statistics', [\App\Http\Controllers\PartsRequestController::class, 'statistics'])->name('parts-requests.statistics');
    Route::post('/parts-requests/{partsRequest}/approve', [\App\Http\Controllers\PartsRequestController::class, 'approve'])->name('parts-requests.approve');
    Route::post('/parts-requests/{partsRequest}/mark-ordered', [\App\Http\Controllers\PartsRequestController::class, 'markAsOrdered'])->name('parts-requests.mark-ordered');
    Route::post('/parts-requests/{partsRequest}/mark-received', [\App\Http\Controllers\PartsRequestController::class, 'markAsReceived'])->name('parts-requests.mark-received');
    Route::post('/parts-requests/{partsRequest}/mark-installed', [\App\Http\Controllers\PartsRequestController::class, 'markAsInstalled'])->name('parts-requests.mark-installed');

    // Technician Routes
    Route::prefix('technician')->name('technician.')->group(function () {
        Route::get('/overview', [\App\Http\Controllers\TechnicianController::class, 'overview'])->name('overview');
        Route::get('/profile', [\App\Http\Controllers\TechnicianController::class, 'profile'])->name('profile');
        Route::put('/profile', [\App\Http\Controllers\TechnicianController::class, 'updateProfile'])->name('profile.update');
        Route::get('/statistics', [\App\Http\Controllers\TechnicianController::class, 'statistics'])->name('statistics');
    });

    // Technician Management Routes (for admin to manage technician database)
    Route::prefix('technicians')->name('technicians.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TechnicianController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\TechnicianController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\TechnicianController::class, 'store'])->name('store');
        Route::get('/{user}', [\App\Http\Controllers\TechnicianController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [\App\Http\Controllers\TechnicianController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\TechnicianController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\TechnicianController::class, 'destroy'])->name('destroy');
    });

    // Personnel Management Routes (single page for all staff with position tagging)
    Route::prefix('personnel')->name('personnel.')->group(function () {
        // Main personnel dashboard with filtering and sorting
        Route::get('/', [\App\Http\Controllers\PersonnelController::class, 'index'])->name('index');
        
        // Personnel CRUD operations
        Route::get('/create', [\App\Http\Controllers\PersonnelController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\PersonnelController::class, 'store'])->name('store');
        Route::get('/{user}', [\App\Http\Controllers\PersonnelController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [\App\Http\Controllers\PersonnelController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\PersonnelController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\PersonnelController::class, 'destroy'])->name('destroy');
        
        // Personnel-specific actions
        Route::post('/{user}/assign-role', [\App\Http\Controllers\PersonnelController::class, 'assignRole'])->name('assign-role');
        Route::get('/{user}/performance', [\App\Http\Controllers\PersonnelController::class, 'performance'])->name('performance');
        Route::get('/export', [\App\Http\Controllers\PersonnelController::class, 'export'])->name('export');
    });

    // Quality Control Routes
    Route::prefix('quality-control')->name('quality-control.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\QualityControlController::class, 'dashboard'])->name('dashboard');
        
        // Quality Check Templates
        Route::resource('quality-checks', \App\Http\Controllers\QualityCheckController::class);
        Route::get('/quality-checks/{id}/duplicate', [\App\Http\Controllers\QualityCheckController::class, 'duplicate'])->name('quality-checks.duplicate');
        Route::get('/quality-checks/{id}/statistics', [\App\Http\Controllers\QualityCheckController::class, 'statistics'])->name('quality-checks.statistics');
        Route::get('/quality-checks/export', [\App\Http\Controllers\QualityCheckController::class, 'export'])->name('quality-checks.export');
        Route::post('/quality-checks/bulk-update', [\App\Http\Controllers\QualityCheckController::class, 'bulkUpdate'])->name('quality-checks.bulk-update');
        
        // Work Order Quality Checks
        Route::resource('work-order-quality', \App\Http\Controllers\WorkOrderQualityController::class);
        Route::post('/work-order-quality/{id}/approve', [\App\Http\Controllers\WorkOrderQualityController::class, 'approve'])->name('work-order-quality.approve');
        Route::post('/work-order-quality/{id}/reject', [\App\Http\Controllers\WorkOrderQualityController::class, 'reject'])->name('work-order-quality.reject');
        Route::post('/work-order-quality/bulk-approve', [\App\Http\Controllers\WorkOrderQualityController::class, 'bulkApprove'])->name('work-order-quality.bulk-approve');
        Route::post('/work-order-quality/bulk-reject', [\App\Http\Controllers\WorkOrderQualityController::class, 'bulkReject'])->name('work-order-quality.bulk-reject');
        Route::post('/work-order-quality/bulk-delete', [\App\Http\Controllers\WorkOrderQualityController::class, 'bulkDelete'])->name('work-order-quality.bulk-delete');
        Route::get('/work-order-quality/{id}/duplicate', [\App\Http\Controllers\WorkOrderQualityController::class, 'duplicate'])->name('work-order-quality.duplicate');
        Route::get('/work-order-quality/{id}/export-pdf', [\App\Http\Controllers\WorkOrderQualityController::class, 'exportPdf'])->name('work-order-quality.export-pdf');
        Route::get('/work-order-quality/export', [\App\Http\Controllers\WorkOrderQualityController::class, 'export'])->name('work-order-quality.export');
        
        // Compliance Documents
        Route::resource('compliance', \App\Http\Controllers\ComplianceController::class);
        Route::post('/compliance/{id}/renew', [\App\Http\Controllers\ComplianceController::class, 'renew'])->name('compliance.renew');
        Route::post('/compliance/{id}/verify', [\App\Http\Controllers\ComplianceController::class, 'verify'])->name('compliance.verify');
        Route::get('/compliance/{id}/download', [\App\Http\Controllers\ComplianceController::class, 'download'])->name('compliance.download');
        Route::get('/compliance/dashboard', [\App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('compliance.dashboard');
        Route::get('/compliance/alerts', [\App\Http\Controllers\ComplianceController::class, 'alerts'])->name('compliance.alerts');
        Route::get('/compliance/export', [\App\Http\Controllers\ComplianceController::class, 'export'])->name('compliance.export');
        Route::post('/compliance/bulk-update', [\App\Http\Controllers\ComplianceController::class, 'bulkUpdate'])->name('compliance.bulk-update');
        
        // Customer Satisfaction Surveys
        Route::resource('customer-satisfaction', \App\Http\Controllers\CustomerSatisfactionController::class);
        Route::post('/customer-satisfaction/{id}/send-reminder', [\App\Http\Controllers\CustomerSatisfactionController::class, 'sendReminder'])->name('customer-satisfaction.send-reminder');
        Route::post('/customer-satisfaction/{id}/mark-followed-up', [\App\Http\Controllers\CustomerSatisfactionController::class, 'markFollowedUp'])->name('customer-satisfaction.mark-followed-up');
        Route::get('/customer-satisfaction/dashboard', [\App\Http\Controllers\CustomerSatisfactionController::class, 'dashboard'])->name('customer-satisfaction.dashboard');
        Route::get('/customer-satisfaction/statistics', [\App\Http\Controllers\CustomerSatisfactionController::class, 'statistics'])->name('customer-satisfaction.statistics');
        Route::get('/customer-satisfaction/export', [\App\Http\Controllers\CustomerSatisfactionController::class, 'export'])->name('customer-satisfaction.export');
        Route::post('/customer-satisfaction/bulk-update', [\App\Http\Controllers\CustomerSatisfactionController::class, 'bulkUpdate'])->name('customer-satisfaction.bulk-update');
        
        // Quality Control Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\QualityControlDashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/metrics', [\App\Http\Controllers\QualityControlDashboardController::class, 'metrics'])->name('dashboard.metrics');
        Route::get('/dashboard/alerts', [\App\Http\Controllers\QualityControlDashboardController::class, 'alerts'])->name('dashboard.alerts');
        Route::get('/dashboard/export', [\App\Http\Controllers\QualityControlDashboardController::class, 'export'])->name('dashboard.export');
        Route::get('/dashboard/technician/{id}', [\App\Http\Controllers\QualityControlDashboardController::class, 'technicianReport'])->name('dashboard.technician-report');
        
        // Quality Control Settings
        Route::resource('settings', \App\Http\Controllers\QualityControlSettingsController::class);
        Route::post('/settings/{id}/reset-default', [\App\Http\Controllers\QualityControlSettingsController::class, 'resetToDefault'])->name('settings.reset-default');
        Route::post('/settings/reset-all', [\App\Http\Controllers\QualityControlSettingsController::class, 'resetAll'])->name('settings.reset-all');
        Route::post('/settings/bulk-update', [\App\Http\Controllers\QualityControlSettingsController::class, 'bulkUpdate'])->name('settings.bulk-update');
        Route::get('/settings/export', [\App\Http\Controllers\QualityControlSettingsController::class, 'export'])->name('settings.export');
        Route::post('/settings/import', [\App\Http\Controllers\QualityControlSettingsController::class, 'import'])->name('settings.import');
        Route::get('/api/settings/{key}', [\App\Http\Controllers\QualityControlSettingsController::class, 'getSetting'])->name('settings.api.get');
        Route::post('/api/settings/{key}', [\App\Http\Controllers\QualityControlSettingsController::class, 'setSetting'])->name('settings.api.set');
        Route::get('/api/settings', [\App\Http\Controllers\QualityControlSettingsController::class, 'getAllSettings'])->name('settings.api.all');
        
        // Checklists Management
        Route::resource('checklists', \App\Http\Controllers\QualityControlController::class);
        Route::post('/checklists/{checklist}/clone', [\App\Http\Controllers\QualityControlController::class, 'cloneChecklist'])->name('checklists.clone');
        Route::post('/checklists/{checklist}/activate', [\App\Http\Controllers\QualityControlController::class, 'activateChecklist'])->name('checklists.activate');
        Route::post('/checklists/{checklist}/archive', [\App\Http\Controllers\QualityControlController::class, 'archiveChecklist'])->name('checklists.archive');
        Route::get('/checklists/{checklist}/export/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportChecklist'])->name('checklists.export');
        
        // Quality Audits
        Route::get('/audits', [\App\Http\Controllers\QualityControlController::class, 'audits'])->name('audits.index');
        Route::get('/audits/create', [\App\Http\Controllers\QualityControlController::class, 'createAudit'])->name('audits.create');
        Route::post('/audits', [\App\Http\Controllers\QualityControlController::class, 'storeAudit'])->name('audits.store');
        Route::get('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'showAudit'])->name('audits.show');
        Route::get('/audits/{audit}/edit', [\App\Http\Controllers\QualityControlController::class, 'editAudit'])->name('audits.edit');
        Route::put('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'updateAudit'])->name('audits.update');
        Route::delete('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'destroyAudit'])->name('audits.destroy');
        
        // NCRs (Non-Conformance Reports)
        Route::get('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'ncrs'])->name('ncrs.index');
        Route::get('/ncrs/create', [\App\Http\Controllers\QualityControlController::class, 'createNcr'])->name('ncrs.create');
        Route::post('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'storeNcr'])->name('ncrs.store');
        Route::get('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'showNcr'])->name('ncrs.show');
        Route::get('/ncrs/{ncr}/edit', [\App\Http\Controllers\QualityControlController::class, 'editNcr'])->name('ncrs.edit');
        Route::put('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'updateNcr'])->name('ncrs.update');
        Route::delete('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'destroyNcr'])->name('ncrs.destroy');
        
        // Corrective Actions
        Route::get('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'correctiveActions'])->name('corrective-actions.index');
        Route::get('/corrective-actions/create', [\App\Http\Controllers\QualityControlController::class, 'createCorrectiveAction'])->name('corrective-actions.create');
        Route::post('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'storeCorrectiveAction'])->name('corrective-actions.store');
        Route::get('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'showCorrectiveAction'])->name('corrective-actions.show');
        Route::get('/corrective-actions/{action}/edit', [\App\Http\Controllers\QualityControlController::class, 'editCorrectiveAction'])->name('corrective-actions.edit');
        Route::put('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'updateCorrectiveAction'])->name('corrective-actions.update');
        Route::delete('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'destroyCorrectiveAction'])->name('corrective-actions.destroy');
    });

    // Test Routes
    Route::get('/test-parts-procurement', [\App\Http\Controllers\TestPartsProcurementController::class, 'testPage'])->name('test.parts-procurement');
    Route::get('/test-parts-procurement-final', function() {
        return view('test.parts-procurement-final');
    })->name('test.parts-procurement-final');
    Route::get('/api/test/parts-procurement/database', [\App\Http\Controllers\TestPartsProcurementController::class, 'testDatabase']);
    Route::get('/api/test/parts-procurement/models', [\App\Http\Controllers\TestPartsProcurementController::class, 'testModels']);
    Route::get('/api/test/parts-procurement/controllers', [\App\Http\Controllers\TestPartsProcurementController::class, 'testControllers']);
    Route::get('/api/test/parts-procurement/routes', [\App\Http\Controllers\TestPartsProcurementController::class, 'testRoutes']);
    Route::get('/api/test/parts-procurement/views', [\App\Http\Controllers\TestPartsProcurementController::class, 'testViews']);
    Route::get('/api/test/parts-procurement/integration', [\App\Http\Controllers\TestPartsProcurementController::class, 'testIntegration']);
    Route::get('/api/test/parts-procurement/requirements', [\App\Http\Controllers\TestPartsProcurementController::class, 'testRequirements']);
    Route::get('/api/test/parts-procurement/summary', [\App\Http\Controllers\TestPartsProcurementController::class, 'testSummary']);
});

// Public test route (outside auth)
Route::get('/test-parts-procurement-public', [\App\Http\Controllers\TestPartsProcurementController::class, 'testPage'])->name('test.parts-procurement-public');

// Pricing & Profit Management Routes
Route::prefix('pricing')->name('pricing.')->middleware(['auth'])->group(function () {
    // Pricing Dashboard
    Route::get('/', [\App\Http\Controllers\PricingController::class, 'index'])->name('index');
    
    // Labor Rates Management
    Route::prefix('labor-rates')->name('labor-rates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PricingController::class, 'laborRatesIndex'])->name('index');
        Route::post('/', [\App\Http\Controllers\PricingController::class, 'laborRatesStore'])->name('store');
        Route::get('/create', [\App\Http\Controllers\PricingController::class, 'laborRatesCreate'])->name('create');
        Route::get('/{laborRate}', [\App\Http\Controllers\PricingController::class, 'laborRatesShow'])->name('show');
        Route::put('/{laborRate}', [\App\Http\Controllers\PricingController::class, 'laborRatesUpdate'])->name('update');
        Route::delete('/{laborRate}', [\App\Http\Controllers\PricingController::class, 'laborRatesDestroy'])->name('destroy');
        Route::get('/{laborRate}/edit', [\App\Http\Controllers\PricingController::class, 'laborRatesEdit'])->name('edit');
    });
    
    // Parts Markup Management
    Route::prefix('parts-markup')->name('parts-markup.')->group(function () {
        Route::get('/', [\App\Http\Controllers\PricingController::class, 'partsMarkupIndex'])->name('index');
        Route::post('/', [\App\Http\Controllers\PricingController::class, 'partsMarkupStore'])->name('store');
        Route::get('/create', [\App\Http\Controllers\PricingController::class, 'partsMarkupCreate'])->name('create');
        Route::get('/{partsMarkup}', [\App\Http\Controllers\PricingController::class, 'partsMarkupShow'])->name('show');
        Route::put('/{partsMarkup}', [\App\Http\Controllers\PricingController::class, 'partsMarkupUpdate'])->name('update');
        Route::delete('/{partsMarkup}', [\App\Http\Controllers\PricingController::class, 'partsMarkupDestroy'])->name('destroy');
        Route::get('/{partsMarkup}/edit', [\App\Http\Controllers\PricingController::class, 'partsMarkupEdit'])->name('edit');
    });
    
    // Price Calculation APIs
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/calculate-labor-cost', [\App\Http\Controllers\PricingController::class, 'calculateLaborCost'])->name('calculate-labor-cost');
        Route::post('/calculate-retail-price', [\App\Http\Controllers\PricingController::class, 'calculateRetailPrice'])->name('calculate-retail-price');
        Route::post('/calculate-job-price', [\App\Http\Controllers\PricingController::class, 'calculateJobPrice'])->name('calculate-job-price');
        Route::get('/get-effective-labor-rate', [\App\Http\Controllers\PricingController::class, 'getEffectiveLaborRate'])->name('get-effective-labor-rate');
        Route::get('/get-applicable-markup-rules', [\App\Http\Controllers\PricingController::class, 'getApplicableMarkupRules'])->name('get-applicable-markup-rules');
    });
});

// Profit Analysis Routes
Route::prefix('profit-analysis')->name('profit-analysis.')->middleware(['auth'])->group(function () {
    // Profit Analysis Dashboard
    Route::get('/', [\App\Http\Controllers\ProfitAnalysisController::class, 'index'])->name('index');
    
    // Job Profitability Reports
    Route::get('/job-profitability', [\App\Http\Controllers\ProfitAnalysisController::class, 'jobProfitability'])->name('job-profitability');
    
    // Individual Analysis Management
    Route::get('/create', [\App\Http\Controllers\ProfitAnalysisController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\ProfitAnalysisController::class, 'store'])->name('store');
    Route::get('/{profitAnalysis}', [\App\Http\Controllers\ProfitAnalysisController::class, 'show'])->name('show');
    Route::put('/{profitAnalysis}', [\App\Http\Controllers\ProfitAnalysisController::class, 'update'])->name('update');
    Route::delete('/{profitAnalysis}', [\App\Http\Controllers\ProfitAnalysisController::class, 'destroy'])->name('destroy');
    Route::get('/{profitAnalysis}/edit', [\App\Http\Controllers\ProfitAnalysisController::class, 'edit'])->name('edit');
    
    // Analysis Actions
    Route::post('/{profitAnalysis}/finalize', [\App\Http\Controllers\ProfitAnalysisController::class, 'finalize'])->name('finalize');
    Route::post('/{profitAnalysis}/recalculate', [\App\Http\Controllers\ProfitAnalysisController::class, 'recalculate'])->name('recalculate');
    Route::post('/{profitAnalysis}/generate-from-work-order/{workOrder}', [\App\Http\Controllers\ProfitAnalysisController::class, 'generateFromWorkOrder'])->name('generate-from-work-order');
    
    // Data Export
    Route::get('/export', [\App\Http\Controllers\ProfitAnalysisController::class, 'export'])->name('export');
    Route::get('/export/{format}', [\App\Http\Controllers\ProfitAnalysisController::class, 'exportFormat'])->name('export.format');
    
    // Trend Analysis
    Route::get('/trends', [\App\Http\Controllers\ProfitAnalysisController::class, 'trends'])->name('trends');
    Route::get('/trends/{period}', [\App\Http\Controllers\ProfitAnalysisController::class, 'trendsByPeriod'])->name('trends.period');
    
    // Analytics APIs
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/summary', [\App\Http\Controllers\ProfitAnalysisController::class, 'apiSummary'])->name('summary');
        Route::get('/trend-data', [\App\Http\Controllers\ProfitAnalysisController::class, 'apiTrendData'])->name('trend-data');
        Route::get('/profit-by-service', [\App\Http\Controllers\ProfitAnalysisController::class, 'apiProfitByService'])->name('profit-by-service');
        Route::get('/profit-by-technician', [\App\Http\Controllers\ProfitAnalysisController::class, 'apiProfitByTechnician'])->name('profit-by-technician');
        Route::get('/margin-distribution', [\App\Http\Controllers\ProfitAnalysisController::class, 'apiMarginDistribution'])->name('margin-distribution');
    });
});


// Quality Control & Compliance Routes
// This duplicate quality-control route block should be removed
// Route::prefix('quality-control')->name('quality-control.')->middleware(['auth'])->group(function () {
//     // Quality Control Dashboard
//     Route::get('/', [\App\Http\Controllers\QualityControlController::class, 'dashboard'])->name('dashboard');
//     Route::get('/dashboard', [\App\Http\Controllers\QualityControlController::class, 'dashboard'])->name('dashboard');
//     Route::get('/metrics', [\App\Http\Controllers\QualityControlController::class, 'metrics'])->name('metrics');
//     Route::get('/reports', [\App\Http\Controllers\QualityControlController::class, 'reports'])->name('reports');
//     Route::post('/reports/generate', [\App\Http\Controllers\QualityControlController::class, 'generateReport'])->name('reports.generate');
//     
//     // Checklists Management
//     Route::resource('checklists', \App\Http\Controllers\QualityControlController::class, [
//         'names' => [
//             'index' => 'checklists.index',
//             'create' => 'checklists.create',
//             'store' => 'checklists.store',
//             'show' => 'checklists.show',
//             'edit' => 'checklists.edit',
//             'update' => 'checklists.update',
//             'destroy' => 'checklists.destroy'
//         ]
//     ]);
//     Route::post('/checklists/{checklist}/clone', [\App\Http\Controllers\QualityControlController::class, 'clone'])->name('checklists.clone');
//     Route::post('/checklists/{checklist}/activate', [\App\Http\Controllers\QualityControlController::class, 'activate'])->name('checklists.activate');
//     Route::post('/checklists/{checklist}/archive', [\App\Http\Controllers\QualityControlController::class, 'archive'])->name('checklists.archive');
//     Route::get('/checklists/{checklist}/export/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportChecklist'])->name('checklists.export');
//     
//     // Quality Audits
//     Route::get('/audits', [\App\Http\Controllers\QualityControlController::class, 'audits'])->name('audits.index');
//     Route::get('/audits/create', [\App\Http\Controllers\QualityControlController::class, 'createAudit'])->name('audits.create');
//     Route::post('/audits', [\App\Http\Controllers\QualityControlController::class, 'storeAudit'])->name('audits.store');
//     Route::get('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'showAudit'])->name('audits.show');
//     Route::get('/audits/{audit}/edit', [\App\Http\Controllers\QualityControlController::class, 'editAudit'])->name('audits.edit');
//     Route::put('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'updateAudit'])->name('audits.update');
//     Route::delete('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'destroyAudit'])->name('audits.destroy');
//     Route::post('/audits/{audit}/complete', [\App\Http\Controllers\QualityControlController::class, 'completeAudit'])->name('audits.complete');
//     Route::post('/audits/{audit}/cancel', [\App\Http\Controllers\QualityControlController::class, 'cancelAudit'])->name('audits.cancel');
//     Route::get('/audits/{audit}/export/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportAudit'])->name('audits.export');
//     
//     // Non-Conformance Reports (NCRs)
//     Route::get('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'ncrs'])->name('ncrs.index');
//     Route::get('/ncrs/create', [\App\Http\Controllers\QualityControlController::class, 'createNcr'])->name('ncrs.create');
//     Route::post('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'storeNcr'])->name('ncrs.store');
//     Route::get('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'showNcr'])->name('ncrs.show');
//     Route::get('/ncrs/{ncr}/edit', [\App\Http\Controllers\QualityControlController::class, 'editNcr'])->name('ncrs.edit');
//     Route::put('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'updateNcr'])->name('ncrs.update');
//     Route::delete('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'destroyNcr'])->name('ncrs.destroy');
//     Route::post('/ncrs/{ncr}/close', [\App\Http\Controllers\QualityControlController::class, 'closeNcr'])->name('ncrs.close');
//     Route::post('/ncrs/{ncr}/escalate', [\App\Http\Controllers\QualityControlController::class, 'escalateNcr'])->name('ncrs.escalate');
//     
//     // Corrective Actions
//     Route::get('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'correctiveActions'])->name('corrective-actions.index');
//     Route::get('/corrective-actions/create', [\App\Http\Controllers\QualityControlController::class, 'createCorrectiveAction'])->name('corrective-actions.create');
//     Route::post('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'storeCorrectiveAction'])->name('corrective-actions.store');
//     Route::get('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'showCorrectiveAction'])->name('corrective-actions.show');
//     Route::get('/corrective-actions/{action}/edit', [\App\Http\Controllers\QualityControlController::class, 'editCorrectiveAction'])->name('corrective-actions.edit');
//     Route::put('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'updateCorrectiveAction'])->name('corrective-actions.update');
//     Route::delete('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'destroyCorrectiveAction'])->name('corrective-actions.destroy');
//     Route::post('/corrective-actions/{action}/complete', [\App\Http\Controllers\QualityControlController::class, 'completeCorrectiveAction'])->name('corrective-actions.complete');
//     Route::post('/corrective-actions/{action}/verify', [\App\Http\Controllers\QualityControlController::class, 'verifyCorrectiveAction'])->name('corrective-actions.verify');
//     
//     // Work Order Quality
//     Route::get('/work-order-quality', [\App\Http\Controllers\QualityControlController::class, 'workOrderQuality'])->name('work-order-quality.index');
//     Route::get('/work-order-quality/create', [\App\Http\Controllers\QualityControlController::class, 'createWorkOrderQuality'])->name('work-order-quality.create');
//     Route::post('/work-order-quality', [\App\Http\Controllers\QualityControlController::class, 'storeWorkOrderQuality'])->name('work-order-quality.store');
//     Route::get('/work-order-quality/{quality}', [\App\Http\Controllers\QualityControlController::class, 'showWorkOrderQuality'])->name('work-order-quality.show');
//     
//     // Export APIs
//     Route::prefix('export')->name('export.')->group(function () {
//         Route::get('/summary/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportSummary'])->name('summary');
//         Route::get('/audits/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportAudits'])->name('audits');
//         Route::get('/ncrs/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportNcrs'])->name('ncrs');
//         Route::get('/corrective-actions/{format?}', [\App\Http\Controllers\QualityControlController::class, 'exportCorrectiveActions'])->name('corrective-actions');
//     });
//     
//     // Analytics APIs
//     Route::prefix('api')->name('api.')->group(function () {
//         Route::get('/metrics', [\App\Http\Controllers\QualityControlController::class, 'apiMetrics'])->name('metrics');
//         Route::get('/trends', [\App\Http\Controllers\QualityControlController::class, 'apiTrends'])->name('trends');
//         Route::get('/performance', [\App\Http\Controllers\QualityControlController::class, 'apiPerformance'])->name('performance');
//         Route::get('/compliance', [\App\Http\Controllers\QualityControlController::class, 'apiCompliance'])->name('compliance');
//     });
// });

// Compliance Routes
Route::prefix('compliance')->name('compliance.')->middleware(['auth'])->group(function () {
    // Compliance Dashboard
    Route::get('/', [\App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [\App\Http\Controllers\ComplianceController::class, 'metrics'])->name('metrics');
    Route::get('/reports', [\App\Http\Controllers\ComplianceController::class, 'reports'])->name('reports');
    Route::post('/reports/generate', [\App\Http\Controllers\ComplianceController::class, 'generateReport'])->name('reports.generate');
    
    // Standards Management
    Route::resource('standards', \App\Http\Controllers\ComplianceController::class, [
        'names' => [
            'index' => 'standards.index',
            'create' => 'standards.create',
            'store' => 'standards.store',
            'show' => 'standards.show',
            'edit' => 'standards.edit',
            'update' => 'standards.update',
            'destroy' => 'standards.destroy'
        ]
    ]);
    Route::post('/standards/{standard}/activate', [\App\Http\Controllers\ComplianceController::class, 'activateStandard'])->name('standards.activate');
    Route::post('/standards/{standard}/archive', [\App\Http\Controllers\ComplianceController::class, 'archiveStandard'])->name('standards.archive');
    Route::get('/standards/{standard}/export/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportStandard'])->name('standards.export');
    
    // Document Management
    Route::get('/documents', [\App\Http\Controllers\ComplianceController::class, 'documents'])->name('documents.index');
    Route::get('/documents/create', [\App\Http\Controllers\ComplianceController::class, 'createDocument'])->name('documents.create');
    Route::post('/documents', [\App\Http\Controllers\ComplianceController::class, 'storeDocument'])->name('documents.store');
    Route::get('/documents/{document}', [\App\Http\Controllers\ComplianceController::class, 'showDocument'])->name('documents.show');
    Route::get('/documents/{document}/edit', [\App\Http\Controllers\ComplianceController::class, 'editDocument'])->name('documents.edit');
    Route::put('/documents/{document}', [\App\Http\Controllers\ComplianceController::class, 'updateDocument'])->name('documents.update');
    Route::delete('/documents/{document}', [\App\Http\Controllers\ComplianceController::class, 'destroyDocument'])->name('documents.destroy');
    Route::post('/documents/{document}/approve', [\App\Http\Controllers\ComplianceController::class, 'approveDocument'])->name('documents.approve');
    Route::post('/documents/{document}/reject', [\App\Http\Controllers\ComplianceController::class, 'rejectDocument'])->name('documents.reject');
    Route::post('/documents/{document}/renew', [\App\Http\Controllers\ComplianceController::class, 'renewDocument'])->name('documents.renew');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\ComplianceController::class, 'downloadDocument'])->name('documents.download');
    
    // Compliance Tracking
    Route::get('/tracking', [\App\Http\Controllers\ComplianceController::class, 'tracking'])->name('tracking');
    Route::get('/tracking/{standard}', [\App\Http\Controllers\ComplianceController::class, 'standardTracking'])->name('tracking.standard');
    Route::post('/tracking/{standard}/record', [\App\Http\Controllers\ComplianceController::class, 'recordCompliance'])->name('tracking.record');
    Route::post('/tracking/{record}/verify', [\App\Http\Controllers\ComplianceController::class, 'verifyCompliance'])->name('tracking.verify');
    
    // Audit Management
    Route::get('/audits', [\App\Http\Controllers\ComplianceController::class, 'audits'])->name('audits.index');
    Route::get('/audits/create', [\App\Http\Controllers\ComplianceController::class, 'createAudit'])->name('audits.create');
    Route::post('/audits', [\App\Http\Controllers\ComplianceController::class, 'storeAudit'])->name('audits.store');
    Route::get('/audits/{audit}', [\App\Http\Controllers\ComplianceController::class, 'showAudit'])->name('audits.show');
    Route::get('/audits/{audit}/edit', [\App\Http\Controllers\ComplianceController::class, 'editAudit'])->name('audits.edit');
    Route::put('/audits/{audit}', [\App\Http\Controllers\ComplianceController::class, 'updateAudit'])->name('audits.update');
    Route::delete('/audits/{audit}', [\App\Http\Controllers\ComplianceController::class, 'destroyAudit'])->name('audits.destroy');
    Route::post('/audits/{audit}/complete', [\App\Http\Controllers\ComplianceController::class, 'completeAudit'])->name('audits.complete');
    
    // Export APIs
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('/summary/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportSummary'])->name('summary');
        Route::get('/standards/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportStandards'])->name('standards');
        Route::get('/documents/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportDocuments'])->name('documents');
        Route::get('/audits/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportAudits'])->name('audits');
        Route::get('/compliance-status/{format?}', [\App\Http\Controllers\ComplianceController::class, 'exportComplianceStatus'])->name('compliance-status');
    });
    
    // Analytics APIs
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/metrics', [\App\Http\Controllers\ComplianceController::class, 'apiMetrics'])->name('metrics');
        Route::get('/compliance-status', [\App\Http\Controllers\ComplianceController::class, 'apiComplianceStatus'])->name('compliance-status');
        Route::get('/document-status', [\App\Http\Controllers\ComplianceController::class, 'apiDocumentStatus'])->name('document-status');
        Route::get('/audit-trends', [\App\Http\Controllers\ComplianceController::class, 'apiAuditTrends'])->name('audit-trends');
        Route::get('/upcoming-renewals', [\App\Http\Controllers\ComplianceController::class, 'apiUpcomingRenewals'])->name('upcoming-renewals');
    });
});

// Audit Management Routes (Separate from Quality Control)
Route::prefix('audit')->name('audit.')->middleware(['auth'])->group(function () {
    // Audit Dashboard
    Route::get('/', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [\App\Http\Controllers\AuditController::class, 'metrics'])->name('metrics');
    Route::get('/calendar', [\App\Http\Controllers\AuditController::class, 'calendar'])->name('calendar');
    
    // Audit Management
    Route::resource('/', \App\Http\Controllers\AuditController::class, [
        'names' => [
            'index' => 'index',
            'create' => 'create',
            'store' => 'store',
            'show' => 'show',
            'edit' => 'edit',
            'update' => 'update',
            'destroy' => 'destroy'
        ]
    ]);
    Route::post('/{audit}/clone', [\App\Http\Controllers\AuditController::class, 'clone'])->name('clone');
    Route::post('/{audit}/schedule', [\App\Http\Controllers\AuditController::class, 'schedule'])->name('schedule');
    Route::post('/{audit}/start', [\App\Http\Controllers\AuditController::class, 'start'])->name('start');
    Route::post('/{audit}/complete', [\App\Http\Controllers\AuditController::class, 'complete'])->name('complete');
    Route::post('/{audit}/cancel', [\App\Http\Controllers\AuditController::class, 'cancel'])->name('cancel');
    Route::post('/{audit}/reschedule', [\App\Http\Controllers\AuditController::class, 'reschedule'])->name('reschedule');
    
    // Audit Results
    Route::get('/{audit}/results', [\App\Http\Controllers\AuditController::class, 'results'])->name('results');
    Route::post('/{audit}/results', [\App\Http\Controllers\AuditController::class, 'saveResults'])->name('results.save');
});

// Quality Control & Compliance Routes
// This duplicate quality-control route block should be removed
// Route::prefix('quality-control')->name('quality-control.')->middleware(['auth'])->group(function () {
//     Route::get('/', [\App\Http\Controllers\QualityControlController::class, 'dashboard'])->name('dashboard');
//     Route::get('/dashboard', [\App\Http\Controllers\QualityControlController::class, 'dashboard'])->name('dashboard');
//     Route::get('/metrics', [\App\Http\Controllers\QualityControlController::class, 'metrics'])->name('metrics');
//     Route::get('/reports', [\App\Http\Controllers\QualityControlController::class, 'reports'])->name('reports');
//     
//     // Checklists
//     Route::resource('checklists', \App\Http\Controllers\QualityControlController::class);
//     Route::post('/checklists/{checklist}/clone', [\App\Http\Controllers\QualityControlController::class, 'clone'])->name('checklists.clone');
    
    // Audits
    Route::get('/audits', [\App\Http\Controllers\QualityControlController::class, 'audits'])->name('audits.index');
    Route::get('/audits/create', [\App\Http\Controllers\QualityControlController::class, 'createAudit'])->name('audits.create');
    Route::post('/audits', [\App\Http\Controllers\QualityControlController::class, 'storeAudit'])->name('audits.store');
    Route::get('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'showAudit'])->name('audits.show');
    Route::get('/audits/{audit}/edit', [\App\Http\Controllers\QualityControlController::class, 'editAudit'])->name('audits.edit');
    Route::put('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'updateAudit'])->name('audits.update');
    Route::delete('/audits/{audit}', [\App\Http\Controllers\QualityControlController::class, 'destroyAudit'])->name('audits.destroy');
    
    // NCRs
    Route::get('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'ncrs'])->name('ncrs.index');
    Route::get('/ncrs/create', [\App\Http\Controllers\QualityControlController::class, 'createNcr'])->name('ncrs.create');
    Route::post('/ncrs', [\App\Http\Controllers\QualityControlController::class, 'storeNcr'])->name('ncrs.store');
    Route::get('/ncrs/{ncr}', [\App\Http\Controllers\QualityControlController::class, 'showNcr'])->name('ncrs.show');
    
    // Corrective Actions
    Route::get('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'correctiveActions'])->name('corrective-actions.index');
    Route::get('/corrective-actions/create', [\App\Http\Controllers\QualityControlController::class, 'createCorrectiveAction'])->name('corrective-actions.create');
    Route::post('/corrective-actions', [\App\Http\Controllers\QualityControlController::class, 'storeCorrectiveAction'])->name('corrective-actions.store');
    Route::get('/corrective-actions/{action}', [\App\Http\Controllers\QualityControlController::class, 'showCorrectiveAction'])->name('corrective-actions.show');

// Compliance Routes
Route::prefix('compliance')->name('compliance.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\ComplianceController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [\App\Http\Controllers\ComplianceController::class, 'metrics'])->name('metrics');
    Route::get('/reports', [\App\Http\Controllers\ComplianceController::class, 'reports'])->name('reports');
    
    // Standards
    Route::resource('standards', \App\Http\Controllers\ComplianceController::class);
    
    // Documents
    Route::get('/documents', [\App\Http\Controllers\ComplianceController::class, 'documents'])->name('documents.index');
    Route::get('/documents/create', [\App\Http\Controllers\ComplianceController::class, 'createDocument'])->name('documents.create');
    Route::post('/documents', [\App\Http\Controllers\ComplianceController::class, 'storeDocument'])->name('documents.store');
    Route::get('/documents/{document}', [\App\Http\Controllers\ComplianceController::class, 'showDocument'])->name('documents.show');
});

// Audit Routes
Route::prefix('audit')->name('audit.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\AuditController::class, 'dashboard'])->name('dashboard');
    Route::get('/metrics', [\App\Http\Controllers\AuditController::class, 'metrics'])->name('metrics');
    
    Route::resource('/', \App\Http\Controllers\AuditController::class);
    Route::post('/{audit}/clone', [\App\Http\Controllers\AuditController::class, 'clone'])->name('clone');
    Route::post('/{audit}/complete', [\App\Http\Controllers\AuditController::class, 'complete'])->name('complete');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->middleware(['auth'])->group(function () {
    Route::get('/', [ReportsController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [ReportsController::class, 'dashboard'])->name('dashboard');
    
    // Daily Activity Report
    Route::get('/daily-activity', [ReportsController::class, 'dailyActivity'])->name('daily-activity');
    
    // Monthly Performance Report
    Route::get('/monthly-performance', [ReportsController::class, 'monthlyPerformance'])->name('monthly-performance');
    
    // Customer History Report
    Route::get('/customer-history', [ReportsController::class, 'customerHistory'])->name('customer-history');
    
    // Export functionality
    Route::post('/export', [ReportsController::class, 'export'])->name('export');
    
    // Settings management
    Route::post('/settings', [ReportsController::class, 'saveSettings'])->name('settings.save');
    Route::get('/settings/{report_type}', [ReportsController::class, 'getSettings'])->name('settings.get');
    
    // Scheduling
    Route::post('/schedule', [ReportsController::class, 'schedule'])->name('schedule');
    
    // API endpoints for preview and data
    Route::get('/types', [ReportsController::class, 'getReportTypes'])->name('types');
    Route::post('/preview', [ReportsController::class, 'preview'])->name('preview');
});

// HR Payroll Routes
Route::prefix('hr-payroll')->name('hr-payroll.')->middleware([\App\Http\Middleware\EnsureUserIsAuthenticated::class])->group(function () {
    // Dashboard
    Route::get('/', [\App\Http\Controllers\HrPayrollController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\HrPayrollController::class, 'dashboard'])->name('dashboard');
    
    // Employee Management
    Route::get('/employees', [\App\Http\Controllers\HrPayrollController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [\App\Http\Controllers\HrPayrollController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [\App\Http\Controllers\HrPayrollController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{id}', [\App\Http\Controllers\HrPayrollController::class, 'showEmployee'])->name('employees.show');
    Route::get('/employees/{id}/edit', [\App\Http\Controllers\HrPayrollController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{id}', [\App\Http\Controllers\HrPayrollController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{id}', [\App\Http\Controllers\HrPayrollController::class, 'destroyEmployee'])->name('employees.destroy');
    
    // Payroll Management
    Route::get('/payroll/periods', [\App\Http\Controllers\HrPayrollController::class, 'payrollPeriods'])->name('payroll.periods');
    Route::get('/payroll/periods/create', [\App\Http\Controllers\HrPayrollController::class, 'createPayrollPeriod'])->name('payroll.periods.create');
    Route::post('/payroll/periods', [\App\Http\Controllers\HrPayrollController::class, 'storePayrollPeriod'])->name('payroll.periods.store');
    Route::get('/payroll/periods/{id}', [\App\Http\Controllers\HrPayrollController::class, 'showPayrollPeriod'])->name('payroll.periods.show');
    Route::get('/payroll/periods/{id}/edit', [\App\Http\Controllers\HrPayrollController::class, 'editPayrollPeriod'])->name('payroll.periods.edit');
    Route::put('/payroll/periods/{id}', [\App\Http\Controllers\HrPayrollController::class, 'updatePayrollPeriod'])->name('payroll.periods.update');
    Route::delete('/payroll/periods/{id}', [\App\Http\Controllers\HrPayrollController::class, 'destroyPayrollPeriod'])->name('payroll.periods.destroy');
    Route::post('/payroll/periods/{id}/process', [\App\Http\Controllers\HrPayrollController::class, 'processPayroll'])->name('payroll.periods.process');
    Route::get('/payroll/process', [\App\Http\Controllers\HrPayrollController::class, 'processPayrollPage'])->name('payroll.process');
    
    // Time & Attendance
    Route::get('/time-attendance', [\App\Http\Controllers\HrPayrollController::class, 'timeAttendance'])->name('time-attendance');
    Route::post('/time-attendance', [\App\Http\Controllers\HrPayrollController::class, 'storeTimeAttendance'])->name('time-attendance.store');
    Route::get('/time-attendance/export', [\App\Http\Controllers\HrPayrollController::class, 'exportTimeAttendance'])->name('time-attendance.export');
    
    // Leave Management
    Route::get('/leave', [\App\Http\Controllers\HrPayrollController::class, 'leaveManagement'])->name('leave');
    Route::get('/leave/balances', [\App\Http\Controllers\HrPayrollController::class, 'leaveBalances'])->name('leave.balances');
    Route::post('/leave/{id}/update', [\App\Http\Controllers\HrPayrollController::class, 'updateLeaveRequest'])->name('leave.update');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\HrPayrollController::class, 'reports'])->name('reports');
    Route::get('/reports/payroll', [\App\Http\Controllers\HrPayrollController::class, 'generatePayrollReport'])->name('reports.payroll');
    
    // Employee Self-Service Portal
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HrPayrollController::class, 'employeePortal'])->name('dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\HrPayrollController::class, 'employeePortal'])->name('dashboard');
        Route::get('/payslips', [\App\Http\Controllers\HrPayrollController::class, 'employeePayslips'])->name('payslips');
        Route::get('/leave-requests', [\App\Http\Controllers\HrPayrollController::class, 'employeeLeaveRequests'])->name('leave-requests');
        Route::post('/leave-requests', [\App\Http\Controllers\HrPayrollController::class, 'submitLeaveRequest'])->name('leave-requests.submit');
        Route::post('/clock-in-out', [\App\Http\Controllers\HrPayrollController::class, 'clockInOut'])->name('clock-in-out');
        Route::get('/time-attendance', [\App\Http\Controllers\HrPayrollController::class, 'employeeTimeAttendance'])->name('time-attendance');
    });
    
    // Test route (can be removed after verification)
    Route::get('/test-installation', function () {
        return view('hr-payroll.test');
    })->name('test-installation');
});

// Public HR Payroll test route (no authentication required)
Route::get('/hr-payroll-public-test', function () {
    return view('hr-payroll.public-test');
})->name('hr-payroll.public-test');

// HR Payroll system health check (no authentication)
Route::get('/hr-payroll-health-check', function () {
    try {
        // Test database connection and models
        $employeeCount = \App\Models\User::where('role', 'employee')->count();
        $payrollPeriods = \App\Models\PayrollPeriod::count();
        $timeAttendance = \App\Models\TimeAttendance::count();
        $leaveRequests = \App\Models\LeaveRequest::count();
        
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'database' => [
                'employees' => $employeeCount,
                'payroll_periods' => $payrollPeriods,
                'time_attendance_records' => $timeAttendance,
                'leave_requests' => $leaveRequests,
            ],
            'models' => [
                'User' => class_exists(\App\Models\User::class),
                'EmployeeHrDetail' => class_exists(\App\Models\EmployeeHrDetail::class),
                'PayrollPeriod' => class_exists(\App\Models\PayrollPeriod::class),
                'PayrollRecord' => class_exists(\App\Models\PayrollRecord::class),
                'TimeAttendance' => class_exists(\App\Models\TimeAttendance::class),
                'LeaveRequest' => class_exists(\App\Models\LeaveRequest::class),
                'LeaveBalance' => class_exists(\App\Models\LeaveBalance::class),
                'TaxSetting' => class_exists(\App\Models\TaxSetting::class),
                'DeductionSetting' => class_exists(\App\Models\DeductionSetting::class),
                'EmployeeDeduction' => class_exists(\App\Models\EmployeeDeduction::class),
            ],
            'message' => 'HR Payroll System is working correctly'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'timestamp' => now(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Service Workflow Routes
Route::prefix('services')->name('services.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ServiceController::class, 'index'])->name('index');
    Route::get('/{serviceId}', [\App\Http\Controllers\ServiceController::class, 'show'])->name('show');
    Route::get('/{serviceId}/timeline', [\App\Http\Controllers\ServiceController::class, 'timeline'])->name('timeline');
    Route::get('/{serviceId}/details', [\App\Http\Controllers\ServiceController::class, 'getServiceDetails'])->name('details');
    Route::get('/statistics', [\App\Http\Controllers\ServiceController::class, 'statistics'])->name('statistics');
    Route::post('/create-workflow', [\App\Http\Controllers\ServiceController::class, 'createWorkflow'])->name('create-workflow');
    Route::post('/{serviceId}/advance-stage', [\App\Http\Controllers\ServiceController::class, 'advanceStage'])->name('advance-stage');
});

// Appointment AJAX Routes
Route::prefix('appointments')->name('appointments.')->middleware(['auth'])->group(function () {
    Route::post('/{id}/ajax-check-in', [\App\Http\Controllers\AppointmentController::class, 'ajaxCheckIn'])->name('ajax-check-in');
    Route::post('/{id}/ajax-cancel', [\App\Http\Controllers\AppointmentController::class, 'ajaxCancel'])->name('ajax-cancel');
    Route::post('/{id}/ajax-mark-no-show', [\App\Http\Controllers\AppointmentController::class, 'ajaxMarkNoShow'])->name('ajax-mark-no-show');
});
