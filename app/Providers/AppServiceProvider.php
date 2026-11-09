<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
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
        $this->configureDefaults();
        $this->configureAuthEvents();
    }

    /**
     * Configure authentication SweetAlert notifications
     */
    protected function configureAuthEvents(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $roleName = $event->user->role?->label() ?? 'Pengguna';
            session()->flash('toast', [
                'type' => 'success',
                'title' => 'Login Berhasil!',
                'message' => "Selamat datang kembali, {$event->user->name} ({$roleName}).",
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            session()->flash('toast', [
                'type' => 'error',
                'title' => 'Login Gagal!',
                'message' => 'Username atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            session()->flash('toast', [
                'type' => 'success',
                'title' => 'Logout Berhasil!',
                'message' => 'Anda telah berhasil keluar dari sistem TC-Procure secara aman.',
            ]);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
