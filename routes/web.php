<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CareServicesController;
use App\Http\Controllers\Services\HomeCareController;
use App\Http\Controllers\EnquiriesController;
use App\Http\Controllers\JoinRequestController;
use App\Http\Controllers\NewsletterSubscriptionController;

use App\Http\Controllers\LocationController;

use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PrivacyPolicyController;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/application-form', [JoinRequestController::class, 'create'])->name('application-form');
Route::post('/join-requests', [JoinRequestController::class, 'store'])->name('join-requests.store');
Route::post('/newsletter/subscribers', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.store');
Route::get('/newsletter/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/enquiries', EnquiriesController::class)->name('enquiries');
Route::get('/privacy-policy', PrivacyPolicyController::class)->name('privacy.policy');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/gallery', GalleryController::class)->name('gallery');
Route::get('/services', CareServicesController::class)->name('services');
Route::get('/services/home-care', [HomeCareController::class, 'index'])->name('services.home-care');
Route::get('/services/therapeutic-services', [ServiceController::class, 'therapeutic'])->name('services.therapeutic');
Route::get('/services/nursing-care', [ServiceController::class, 'nursingCare'])->name('services.nursing-care');
Route::get('/location/{county}', [LocationController::class, 'countyShow'])->name('location.county');
Route::get('/locations/{county}', [LocationController::class, 'countyShow'])->name('locations.county');
Route::get('/locations/{county}/{township}', LocationController::class)->name('locations.township');
