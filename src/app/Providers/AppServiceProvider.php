<?php

namespace App\Providers;

use App\StateMachines\CommentStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем State Machines как singleton для использования через DI
        $this->app->singleton(TaskStatusStateMachine::class, function () {
            return new TaskStatusStateMachine();
        });

        $this->app->singleton(CommentStatusStateMachine::class, function () {
            return new CommentStatusStateMachine();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
