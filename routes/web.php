<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AdoptionBrowseController;
use App\Http\Controllers\CareArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicPetController;
use App\Http\Controllers\Owner\AppointmentController as OwnerAppointmentController;
use App\Http\Controllers\Owner\AssistantController;
use App\Http\Controllers\Owner\CartController;
use App\Http\Controllers\Owner\CheckoutController;
use App\Http\Controllers\Owner\HealthRecordController;
use App\Http\Controllers\Owner\PetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Shelter\ShelterController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\Vet\VetDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// The component style guide is a local tool. testing, staging, and production do not register it.
if (app()->environment('local')) {
    Route::view('/dev/ui', 'dev.ui')->name('dev.ui');
}
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/care', [CareArticleController::class, 'index'])->name('care.index');
Route::get('/care/{article}', [CareArticleController::class, 'show'])->name('care.show');

Route::get('/adopt', [AdoptionBrowseController::class, 'index'])->name('adopt.index');
Route::get('/adopt/{listing}', [AdoptionBrowseController::class, 'show'])->name('adopt.show');

Route::get('/triage', [TriageController::class, 'show'])->name('triage');
Route::post('/triage', [TriageController::class, 'store'])->middleware('throttle:30,1')->name('triage.store');
Route::get('/triage/result', [TriageController::class, 'result'])->name('triage.result');
Route::post('/triage/save', [TriageController::class, 'save'])->middleware(['auth', 'role:owner'])->name('triage.save');

Route::get('/search', SearchController::class)->middleware('throttle:search')->name('search');

Route::get('/vets', [DirectoryController::class, 'vets'])->name('vets.index');
Route::get('/vets/{user}', [DirectoryController::class, 'showVet'])->name('vets.show');
Route::get('/shelters', [DirectoryController::class, 'shelters'])->name('shelters.index');
Route::get('/shelters/{user}', [DirectoryController::class, 'showShelter'])->name('shelters.show');

Route::get('/p/{token}', [PublicPetController::class, 'show'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:pet-public')
    ->name('pets.public');
Route::post('/p/{token}/found', [PublicPetController::class, 'found'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:pet-found')
    ->name('pets.public.found');

Route::get('/dashboard', DashboardController::class)->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::post('/products/{product}/rate', [ProductController::class, 'rate'])->name('products.rate');
    Route::post('/feedback/users/{user}', [FeedbackController::class, 'storeForUser'])->name('feedback.user');
    Route::post('/adopt/{listing}/interest', [AdoptionBrowseController::class, 'expressInterest'])->name('adopt.interest');

    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::resource('pets', PetController::class);
        Route::get('pets/{pet}/health', [HealthRecordController::class, 'index'])->name('pets.health');
        Route::post('pets/{pet}/health', [HealthRecordController::class, 'store'])->name('pets.health.store');
        Route::post('pets/{pet}/insurance', [HealthRecordController::class, 'storeInsurance'])->name('pets.insurance.store');
        Route::patch('pets/{pet}/public-id', [PetController::class, 'updatePublicId'])->name('pets.public-id.update');
        Route::post('pets/{pet}/public-token', [PetController::class, 'regeneratePublicToken'])->name('pets.public-token.regenerate');
        Route::get('pets/{pet}/tag', [PetController::class, 'tag'])->name('pets.tag');

        Route::get('appointments', [OwnerAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('appointments/create', [OwnerAppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [OwnerAppointmentController::class, 'store'])->name('appointments.store');
        Route::patch('appointments/{appointment}/reschedule', [OwnerAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::patch('appointments/{appointment}/cancel', [OwnerAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::get('appointments/{appointment}/calendar.ics', [OwnerAppointmentController::class, 'ics'])->name('appointments.ics');

        Route::get('cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
        Route::get('cart/items/{orderItem}', [CartController::class, 'showItem'])->name('cart.items.show');
        Route::match(['put', 'patch'], 'cart/items/{orderItem}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('cart/items/{orderItem}', [CartController::class, 'remove'])->name('cart.remove');
        Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
        Route::get('orders', [CartController::class, 'orders'])->name('cart.orders');
        Route::get('orders/{order}', [CheckoutController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/receipt', [CheckoutController::class, 'download'])->name('orders.receipt');
        Route::post('orders/{order}/cancel', [CheckoutController::class, 'cancel'])->name('orders.cancel');

        Route::post('assistant', [AssistantController::class, 'store'])
            ->middleware('throttle:assistant')
            ->name('assistant.store');
        Route::post('assistant/reset', [AssistantController::class, 'reset'])->name('assistant.reset');
    });

    Route::middleware('role:vet')->prefix('vet')->name('vet.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/appointments', [VetDashboardController::class, 'appointments'])->name('appointments');
        Route::patch('/appointments/{appointment}', [VetDashboardController::class, 'updateAppointment'])->name('appointments.update');
        Route::patch('/appointments/{appointment}/reschedule', [VetDashboardController::class, 'reschedule'])->name('appointments.reschedule');
        Route::get('/appointments/{appointment}/calendar.ics', [VetDashboardController::class, 'ics'])->name('appointments.ics');
        Route::post('/appointments/{appointment}/treatment', [VetDashboardController::class, 'storeTreatment'])->name('appointments.treatment');
        Route::get('/pets/{pet}/history', [VetDashboardController::class, 'petHistory'])->name('pets.history');
        Route::get('/profile', [VetDashboardController::class, 'editProfile'])->name('profile');
        Route::patch('/profile', [VetDashboardController::class, 'updateProfile'])->name('profile.update');
    });

    Route::middleware('role:shelter')->prefix('shelter')->name('shelter.')->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/listings', [ShelterController::class, 'listings'])->name('listings');
        Route::get('/listings/create', [ShelterController::class, 'create'])->name('listings.create');
        Route::post('/listings', [ShelterController::class, 'store'])->name('listings.store');
        Route::get('/listings/{listing}', [ShelterController::class, 'show'])->name('listings.show');
        Route::post('/listings/{listing}/care-logs', [ShelterController::class, 'storeCareLog'])->name('listings.care');
        Route::patch('/listings/{listing}/status', [ShelterController::class, 'updateStatus'])->name('listings.status');
        Route::patch('/interests/{interest}', [ShelterController::class, 'updateInterest'])->name('interests.update');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/users/export', [AdminDashboardController::class, 'exportUsers'])->name('users.export');
        Route::get('/orders/export', [AdminDashboardController::class, 'exportOrders'])->name('orders.export');
        Route::get('/user-privileges', [AdminDashboardController::class, 'userPrivileges'])->name('user-privileges');
        Route::get('/user-privileges/{user}', [AdminDashboardController::class, 'editUserPrivileges'])->name('user-privileges.edit');
        Route::put('/user-privileges/{user}', [AdminDashboardController::class, 'updateUserPrivileges'])->name('user-privileges.update');
        Route::get('/privileges', [AdminDashboardController::class, 'privileges'])->name('privileges');
        Route::put('/privileges', [AdminDashboardController::class, 'updatePrivileges'])->name('privileges.update');
    });
});

require __DIR__.'/auth.php';
