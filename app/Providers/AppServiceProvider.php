<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Login regenerates the session ID, which would lock a guest out of the
        // business cards they just designed. The Login event fires before the
        // regenerate, so claim this session's guest cards for the user here.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if (!app()->bound('session') || !session()->isStarted()) {
                return;
            }
            \App\Models\UserDesign::whereNull('user_id')
                ->where('session_id', session()->getId())
                ->get()
                ->filter(fn($d) => \App\Services\BusinessCard\BusinessCardDesignService::isBusinessCardDesign($d))
                ->each(fn($d) => $d->forceFill(['user_id' => $event->user->getAuthIdentifier()])->save());
        });
    }
}
