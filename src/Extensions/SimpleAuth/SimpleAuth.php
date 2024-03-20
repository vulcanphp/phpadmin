<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth;

use VulcanPhp\Core\Auth\Auth;
use VulcanPhp\PhpRouter\Route;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Controllers\AuthController;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Middlewares\AuthMiddleware;

class SimpleAuth
{
    /**
     * Setup SimpleAuth Manager
     * @return void 
     */
    public static function setup(): void
    {
        // create Auth as a component
        app()->setComponent('auth', new Auth);

        // include auth routes
        Route::group(['prefix' => config('auth.prefix'), 'name' => 'auth.'], function () {
            // register auth routes
            Route::group(['middlewares' => [AuthMiddleware::class]], function () {
                if (auth_enabled('login')) {
                    Route::form(config('auth.urls.login'), [AuthController::class, 'login'])->setName('login');
                }
                if (auth_enabled('register')) {
                    Route::form(config('auth.urls.register'), [AuthController::class, 'register'])->setName('register');
                }
                if (auth_enabled('forget')) {
                    Route::form(config('auth.urls.forget'), [AuthController::class, 'forget'])->setName('forget');
                    Route::form(config('auth.urls.reset'), [AuthController::class, 'reset'])->setName('reset');
                }
            });

            // logout route
            if (auth_enabled('logout')) {
                Route::post(config('auth.urls.logout'), [AuthController::class, 'logout'])->setName('logout');
            }
        });
    }
}
