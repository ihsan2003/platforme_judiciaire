<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\DossierJudiciaire;
use App\Policies\DossierPolicy;
use App\Models\Avertissement;
use App\Policies\AvertissementPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        DossierJudiciaire::class => DossierPolicy::class,
        Avertissement::class     => AvertissementPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // 🔥 TRÈS IMPORTANT
    }
}