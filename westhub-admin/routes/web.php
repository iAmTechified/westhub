<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Admin\Appointments\Index as AppointmentsIndex;
use App\Livewire\Admin\Articles\Index as ArticlesIndex;
use App\Livewire\Admin\Articles\Studio as ArticlesStudio;
use App\Livewire\Admin\CareServices\Index as CareServicesIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Gallery\Index as GalleryIndex;
use App\Livewire\Admin\JoinRequests\Index as JoinRequestsIndex;
use App\Livewire\Admin\LocationsServices\Index as LocationsIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Subscribers\Index as SubscribersIndex;

use App\Models\County;
use App\Models\Township;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'admin.role'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware('permission:access_articles')->group(function () {
        Route::get('/articles', ArticlesIndex::class)->name('articles.index');
        Route::get('/articles/create', ArticlesStudio::class)->name('articles.create');
        Route::get('/articles/{article}/edit', ArticlesStudio::class)->name('articles.edit');
    });

    Route::middleware('permission:access_applications')->group(function () {
        Route::get('/join-requests', JoinRequestsIndex::class)->name('join-requests.index');
    });

    Route::middleware('permission:access_appointments')->group(function () {
        Route::get('/appointments', AppointmentsIndex::class)->name('appointments.index');
    });

    Route::middleware('permission:access_gallery')->group(function () {
        Route::get('/gallery', GalleryIndex::class)->name('gallery.index');
    });

    Route::middleware('permission:access_care_services')->group(function () {
        Route::get('/care-services', CareServicesIndex::class)->name('care-services.index');
    });

    Route::middleware('permission:access_locations')->group(function () {
        Route::get('/locations', LocationsIndex::class)->name('locations.index');
        Route::get('/locations/counties/{county}/townships', function (County $county) {
            $townships = Township::query()
                ->where('county_id', $county->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'county_id', 'name', 'sort_order', 'is_active']);

            return response()->json(['data' => $townships]);
        })->name('locations.townships');
        Route::redirect('/locations-services', '/admin/locations')->name('locations-services.redirect');
    });

    Route::middleware('role:super_admin')->group(function () {
        Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
        Route::get('/users/create', \App\Livewire\Admin\Users\Studio::class)->name('users.create');
        Route::get('/users/{user}/edit', \App\Livewire\Admin\Users\Studio::class)->name('users.edit');
    });

    Route::get('/settings', SettingsIndex::class)->name('settings.index');

    Route::middleware('permission:access_subscribers')->group(function () {
        Route::get('/subscribers', SubscribersIndex::class)->name('subscribers.index');
    });

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

