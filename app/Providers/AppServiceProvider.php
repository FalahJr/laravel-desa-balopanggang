<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // View::composer('*', function ($view) {
        //     $unreadCount = Notifikasi::where('role', Session('user')['role'])
        //         ->where('is_seen', 'N')
        //         ->count();
        //     $view->with('unreadNotifCount', $unreadCount);
        // });
    }
}
