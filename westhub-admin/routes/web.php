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
use App\Livewire\Admin\PromoClaims\Index as PromoClaimsIndex;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Subscribers\Index as SubscribersIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Admin\Users\Studio as UsersStudio;
use App\Models\County;
use App\Models\Township;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('admin.dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

/*
| `admin.role` keeps anyone without an admin role out entirely. The per-route
| `permission:` middleware then decides which modules a role can open.
|
| Route middleware only protects the first page load. Every Livewire button
| click is a separate request that does not pass back through it, so each
| component also re-checks with Gate::authorize in mount() and in its actions.
*/
Route::middleware(['auth', 'admin.role'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard')->middleware('permission:dashboard.view');

    Route::get('/articles', ArticlesIndex::class)->name('articles.index')->middleware('permission:articles.view');
    Route::get('/articles/create', ArticlesStudio::class)->name('articles.create')->middleware('permission:articles.edit');
    Route::get('/articles/{article}/edit', ArticlesStudio::class)->name('articles.edit')->middleware('permission:articles.edit');

    Route::get('/join-requests', JoinRequestsIndex::class)->name('join-requests.index')->middleware('permission:join_requests.view');

    Route::get('/appointments', AppointmentsIndex::class)->name('appointments.index')->middleware('permission:appointments.view');

    Route::get('/promo-claims', PromoClaimsIndex::class)->name('promo-claims.index')->middleware('permission:promos.view');

    Route::get('/gallery', GalleryIndex::class)->name('gallery.index')->middleware('permission:gallery.view');

    Route::get('/care-services', CareServicesIndex::class)->name('care-services.index')->middleware('permission:care_services.view');

    Route::middleware('permission:locations.view')->group(function () {
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

    Route::get('/users', UsersIndex::class)->name('users.index')->middleware('permission:users.view');
    Route::get('/users/create', UsersStudio::class)->name('users.create')->middleware('permission:users.manage');
    Route::get('/users/{user}/edit', UsersStudio::class)->name('users.edit')->middleware('permission:users.manage');

    Route::get('/subscribers', SubscribersIndex::class)->name('subscribers.index')->middleware('permission:subscribers.view');

    // Deliberately open to every admin: it holds each person's own password
    // change. The site-configuration groups inside are gated in the component.
    Route::get('/settings', SettingsIndex::class)->name('settings.index');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
