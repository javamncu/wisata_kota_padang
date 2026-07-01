<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ConciergeController as AdminConciergeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DestinationController as AdminDestinationController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConciergeController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\NearbyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// -- Public ------------------------------------------------------------

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/explore', [ExploreController::class, 'index'])->name('explore');
Route::get('/peta', [MapController::class, 'index'])->name('map.index');
Route::get('/sekitar', [NearbyController::class, 'index'])->name('nearby.index');

Route::get('/asisten', [ConciergeController::class, 'index'])->name('concierge.index');
Route::post('/asisten/tanya', [ConciergeController::class, 'ask'])->name('concierge.ask');

Route::get('/kuis', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/kuis/hasil', [QuizController::class, 'result'])->name('quiz.result');

Route::get('/tentang', [HomeController::class, 'about'])->name('about');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article}', [BlogController::class, 'show'])->name('blog.show');

// Tanya Jawab (public Q&A) — anyone may ask; throttled against spam.
Route::get('/tanya-jawab', [QuestionController::class, 'index'])->name('questions.index');
Route::post('/tanya-jawab', [QuestionController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('questions.store');

Route::get('/kategori/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/destinasi/{destination}', [DestinationController::class, 'show'])->name('destinations.show');

// -- Authenticated users ----------------------------------------------

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My reviews
    Route::get('/review-saya', [ReviewController::class, 'mine'])->name('reviews.mine');

    // Favorites / wishlist
    Route::get('/favorit', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/destinasi/{destination}/favorite', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

    // Reviews
    Route::post('/destinasi/{destination}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');
    Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// -- Admin panel ------------------------------------------------------

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::post('destinations/{destination}/toggle-status', [AdminDestinationController::class, 'toggleStatus'])
        ->name('destinations.toggle-status');
    Route::resource('destinations', AdminDestinationController::class)->except('show');
    Route::resource('categories', AdminCategoryController::class)->except('show');
    Route::resource('tags', AdminTagController::class)->except('show');

    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
    Route::patch('users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('questions', [AdminQuestionController::class, 'index'])->name('questions.index');
    Route::put('questions/{question}/answer', [AdminQuestionController::class, 'answer'])->name('questions.answer');
    Route::post('questions/{question}/toggle-hide', [AdminQuestionController::class, 'toggleHide'])->name('questions.toggle-hide');
    Route::delete('questions/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');

    Route::get('settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [AdminSettingController::class, 'update'])->name('settings.update');

    Route::get('concierge', [AdminConciergeController::class, 'index'])->name('concierge.index');
    Route::put('concierge', [AdminConciergeController::class, 'update'])->name('concierge.update');
    Route::post('concierge/check', [AdminConciergeController::class, 'check'])->name('concierge.check');

    Route::post('articles/{article}/toggle-publish', [AdminArticleController::class, 'togglePublish'])
        ->name('articles.toggle-publish');
    Route::resource('articles', AdminArticleController::class)->except('show');
});

require __DIR__.'/auth.php';
