<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\QuestionnaireRepository;
use App\Interfaces\QuestionnaireRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(QuestionnaireRepositoryInterface::class, QuestionnaireRepository::class);

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
