<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        View::composer(['components.layouts.customer', 'components.layouts.admin'], function ($view): void {
            $userId = auth()->id();

            $notifications = collect();
            $unreadNotificationsCount = 0;

            if ($userId) {
                $notifications = Notification::query()
                    ->where('user_id', $userId)
                    ->latest()
                    ->limit(8)
                    ->get();

                $unreadNotificationsCount = (int) Notification::query()
                    ->where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();
            }

            $view->with(compact('notifications', 'unreadNotificationsCount'));
        });

        Password::defaults(function (): Password {
            return Password::min(10)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols();
        });
    }
}
