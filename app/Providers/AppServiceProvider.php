<?php

namespace App\Providers;

use App\Contracts\JobRepositoryInterface;
use App\Contracts\ResumeAnalysisRepositoryInterface;
use App\Repositories\JobRepository;
use App\Repositories\ResumeAnalysisRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(JobRepositoryInterface::class, JobRepository::class);
        $this->app->bind(ResumeAnalysisRepositoryInterface::class, ResumeAnalysisRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
