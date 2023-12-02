<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models;

use App\Models\User;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;

class LoginModel extends User
{
    public function labels(): array
    {
        return [
            'user'    => 'Username/Email',
            'password' => 'Password',
        ];
    }

    public function rules(): array
    {
        return [
            'password' => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 6], [self::RULE_MAX, 'max' => 48]],
            'user' => [self::RULE_REQUIRED],
        ];
    }

    public static function fillable(): array
    {
        return ['user', 'password'];
    }

    public function login(): bool
    {
        $user = parent::find("username = '{$this->user}' OR email = '{$this->user}'");

        if ($user === false) {
            $this->addError('user', 'User does not exist with this username/email address');
            return false;
        } elseif ($user->status != config('auth.status.activated')) {
            $this->addError('user', sprintf("Your Account Has Been %s.", array_search($user->status, config('auth.status'))));
            return false;
        }

        if (password(strval($this->password), strval($user->password)) !== true) {
            $this->addError('password', 'Incorrect Password');
            return false;
        }

        auth()->attempLogin($user);

        return true;
    }

    public function getQForm(): QForm
    {
        return QForm::begin($this, ['method' => 'post', 'action' => auth_url('login')])
            ->addInput(['type' => 'text', 'name' => 'user'])
            ->addInput(['type' => 'password', 'name' => 'password'])
            ->submit(['name' => 'Sign In', 'class' => 'tw-btn-block']);
    }
}
