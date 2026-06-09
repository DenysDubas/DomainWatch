<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\DomainStatusChanged;
use App\Listeners\SendDomainStatusNotification;
use App\Models\Domain;
use App\Policies\DomainPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Domain::class, DomainPolicy::class);

        Route::bind('domain', function (string $value) {
            $userId = auth()->id();

            if (! $userId) {
                abort(401);
            }

            return Domain::query()
                ->where('id', $value)
                ->where('user_id', $userId)
                ->firstOrFail();
        });

        Event::listen(DomainStatusChanged::class, SendDomainStatusNotification::class);

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
