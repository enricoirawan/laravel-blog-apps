<?php

use App\Events\ChatMessage;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\MustBeLoggedin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/admins-only', function () {
  if (Gate::allows('visitAdminPages')) {
    return "You're an admin!";
  }
})->middleware('can:visitAdminPages');

Route::get('/', [UserController::class, 'correctShowHomepage'])->name('login');
Route::post('/register', [UserController::class, 'register'])->middleware('guest');
Route::post('/login', [UserController::class, 'login'])->middleware('guest');
Route::post('/logout', [UserController::class, 'logout'])->middleware(MustBeLoggedin::class);
Route::get('/manage-avatar', [UserController::class, 'showAvatarForm'])->middleware(MustBeLoggedin::class);
Route::post('/manage-avatar', [UserController::class, 'storeAvatar'])->middleware(MustBeLoggedin::class);

Route::post('/create-follow/{user:username}', [FollowController::class, 'createFollow'])->middleware(MustBeLoggedin::class);
Route::post('/remove-follow/{user:username}', [FollowController::class, 'removeFollow'])->middleware(MustBeLoggedin::class);

Route::get('/create-post', [PostController::class, 'showCreateForm'])->middleware(MustBeLoggedin::class);
Route::post('/create-post', [PostController::class, 'storeNewPost'])->middleware(MustBeLoggedin::class);
Route::get('/post/{post}', [PostController::class, 'viewSinglePost'])->middleware('auth');
Route::delete('/post/{post}', [PostController::class, 'delete'])->middleware('can:delete,post');
Route::get('/post/{post}/edit', [PostController::class, 'showEditForm'])->middleware('can:update,post');
Route::put('/post/{post}', [PostController::class, 'actuallyUpdate'])->middleware('can:update,post');
Route::get('/search/{term}', [PostController::class, 'search'])->middleware(MustBeLoggedin::class);

Route::get("/profile/{user:username}", [UserController::class, 'profile']);
Route::get("/profile/{user:username}/followers", [UserController::class, 'profileFollowers']);
Route::get("/profile/{user:username}/following", [UserController::class, 'profileFollowing']);

Route::post("/send-chat-message", function (Request $request) {
  $formFields = $request->validate([
    'textvalue' => 'required'
  ]);

  if (!trim(strip_tags($formFields['textvalue']))) {
    return response()->noContent();
  }

  broadcast(new ChatMessage(['username' => auth()->user()->username, 'textvalue' => strip_tags($request->textvalue), 'avatar' => auth()->user()->avatar]))
    ->toOthers();
  return response()->noContent();
})->middleware(MustBeLoggedin::class);
