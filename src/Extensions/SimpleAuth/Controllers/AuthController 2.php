<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Controllers;

use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models\LoginModel;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models\RegisterModel;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models\RecoverPasswordModel;
use VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models\ResetPasswordModel;
use VulcanPhp\Core\Auth\Interfaces\IAuthController;
use VulcanPhp\Core\Foundation\Controller;

class AuthController extends Controller implements IAuthController
{
    public function login()
    {
        $model = new LoginModel;

        if (request()->isMethod('post') && $model->inputValidate(['user', 'password']) && $model->login()) {
            session()->setFlash('success', config('auth.message.login'));
            return redirect(config('auth.redirect_in'));
        }

        return authView('login', ['model' => $model]);
    }

    public function logout()
    {
        auth()->attemptLogout();

        return redirect(config('auth.redirect_out'));
    }

    public function register()
    {
        $model = new RegisterModel;

        if (request()->isMethod('post') && $model->inputValidate() && $model->register()) {
            session()->setFlash('success', config('auth.message.register'));
            return redirect(auth_url('login'));
        }

        return authView('register', ['model' => $model]);
    }

    public function forget()
    {
        $model = new RecoverPasswordModel;

        if (request()->isMethod('post') && $model->inputValidate(['email']) && $model->sendResetLink()) {
            session()->setFlash('success', config('auth.message.forget'));
            return redirect(auth_url('forget'));
        }

        return authView('forget', ['model' => $model]);
    }

    public function reset()
    {
        $model = new ResetPasswordModel;
        $model->load(['token' => input('token')]);

        if (!$model->tokenValidate()) {
            session()->setFlash('error', translate('Invalid Token: reset token must be valid'));
            return redirect(auth_url('login'));
        }

        if (request()->isMethod('post') && $model->inputValidate(['password', 'confirmPassword', 'token']) && $model->reset()) {
            session()->setFlash('success', config('auth.message.reset'));
            return redirect(auth_url('login'));
        }


        return authView('reset', ['model' => $model]);
    }
}
