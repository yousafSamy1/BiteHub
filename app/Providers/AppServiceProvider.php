<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('*', function ($view) {
            $notifications = collect();
            if (Auth::check()) {
                $notifications = Notification::where('UserID', Auth::id())
                    ->where('IsRead', false)
                    ->orderBy('CreatedAt', 'desc')
                    ->take(10)
                    ->get();
            }
            $view->with('headerNotifications', $notifications);
        });
    }
}
