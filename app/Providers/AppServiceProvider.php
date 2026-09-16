<?php

namespace App\Providers;

use App\Models\PengajuanCuti;
use App\Observers\PengajuanCutiObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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
        PengajuanCuti::observe(PengajuanCutiObserver::class);

        // Sediakan jumlah notifikasi belum dibaca untuk kedua navbar,
        // pakai relasi ->unreadNotifications() (query COUNT), bukan koleksi,
        // supaya tidak menarik seluruh baris notifikasi di tiap request.
        View::composer(['layouts.navigation', 'components.admin-layout'], function ($view) {
            $view->with(
                'jumlahNotifBelumDibaca',
                Auth::check() ? Auth::user()->unreadNotifications()->count() : 0
            );
        });
    }
}