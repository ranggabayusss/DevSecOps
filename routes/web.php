<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CommunityJoinController;
use App\Http\Controllers\CommunityRequestController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventExportController;
use App\Http\Controllers\EventRsvpController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Models\Community;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:registration')
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    $ledCommunity = null;

    if ($user->role === 'ketua') {
        $ledCommunity = Community::where(
            'leader_id',
            $user->id
        )->first();
    }

    return view('dashboard', compact('ledCommunity'));
})->middleware('auth')->name('dashboard');

// Member: melihat dan mengajukan bergabung ke komunitas
Route::middleware('auth')->group(function () {
    Route::get('/communities', [CommunityJoinController::class, 'index'])
        ->name('communities.index');

    Route::post('/communities/{community}/join', [CommunityJoinController::class, 'store'])
        ->middleware('throttle:community-join')
        ->name('communities.join');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:profile-update')
        ->name('profile.update');

    Route::get('/events', [EventController::class, 'index'])
        ->name('events.index');

    Route::get('/events/{event}', [EventController::class, 'show'])
        ->whereNumber('event')
        ->name('events.show');

    Route::post('/events/{event}/rsvp', [EventRsvpController::class, 'store'])
        ->whereNumber('event')
        ->middleware('throttle:rsvp')
        ->name('events.rsvp');
});

Route::middleware(['auth', 'role:admin,ketua'])->group(function () {
    Route::get('/events/create', [EventController::class, 'create'])
        ->middleware('throttle:event-write')
        ->name('events.create');

    Route::post('/events', [EventController::class, 'store'])
        ->middleware('throttle:event-write')
        ->name('events.store');

    Route::get('/events/{event}/edit', [EventController::class, 'edit'])
        ->whereNumber('event')
        ->middleware('throttle:event-write')
        ->name('events.edit');

    Route::put('/events/{event}', [EventController::class, 'update'])
        ->whereNumber('event')
        ->middleware('throttle:event-write')
        ->name('events.update');

    Route::delete('/events/{event}', [EventController::class, 'destroy'])
        ->whereNumber('event')
        ->middleware('throttle:event-write')
        ->name('events.destroy');

    Route::get('/events/{event}/attendance/export', [EventExportController::class, 'download'])
        ->whereNumber('event')
        ->middleware('throttle:event-export')
        ->name('events.export');
});

Route::middleware(['auth', 'role:ketua'])
    ->prefix('ketua')
    ->name('ketua.')
    ->group(function () {
        Route::get('/communities/{community}/requests',
            [CommunityRequestController::class, 'index'])
            ->name('communities.requests.index');

        Route::patch('/communities/{community}/requests/{membership}/approve',
            [CommunityRequestController::class, 'approve'])
            ->middleware('throttle:leader-actions')
            ->name('communities.requests.approve');

        Route::patch('/communities/{community}/requests/{membership}/reject',
            [CommunityRequestController::class, 'reject'])
            ->middleware('throttle:leader-actions')
            ->name('communities.requests.reject');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])
            ->whereNumber('user')
            ->middleware('throttle:member-role')
            ->name('users.role.update');

        Route::get('/communities', [CommunityController::class, 'index'])
            ->name('communities.index');

        Route::get('/communities/select', [CommunityController::class, 'select'])
            ->name('communities.select');

        Route::get('/communities/create', [CommunityController::class, 'create'])
            ->name('communities.create');

        Route::post('/leader-accounts', [AdminController::class, 'storeLeaderAccount'])
            ->middleware('throttle:admin-actions')
            ->name('leader-accounts.store');

        Route::post('/communities', [CommunityController::class, 'store'])
            ->middleware('throttle:admin-actions')
            ->name('communities.store');

        Route::get('/communities/{community}/members', [MemberController::class, 'index'])
            ->name('communities.members.index');

        Route::get('/communities/{community}/members/create', [MemberController::class, 'create'])
            ->name('communities.members.create');

        Route::post('/communities/{community}/members', [MemberController::class, 'store'])
            ->middleware('throttle:admin-actions')
            ->name('communities.members.store');

        Route::delete('/communities/{community}/members/{membership}', [MemberController::class, 'destroy'])
            ->middleware('throttle:admin-actions')
            ->name('communities.members.destroy');

        Route::put('/communities/{community}/leader', [CommunityController::class, 'updateLeader'])
            ->middleware('throttle:admin-actions')
            ->name('communities.leader.update');
    });
