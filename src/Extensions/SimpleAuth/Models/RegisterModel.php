<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models;

use App\Models\User;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;

class RegisterModel extends User
{
    public $confirmPassword;

    public function labels(): array
    {
        return [
            'username'        => 'Username',
            'email'           => 'Email Address',
            'password'        => 'Password',
            'confirmPassword' => 'Confirm Password',
        ];
    }

    public function rules(): array
    {
        return [
            'username'        => [self::RULE_REQUIRED, [self::RULE_UNIQUE, 'class' => parent::class]],
            'email'           => [self::RULE_REQUIRED, self::RULE_EMAIL, [self::RULE_UNIQUE, 'class' => parent::class]],
            'password'        => [self::RULE_REQUIRED, [self::RULE_MIN, 'min' => 6], [self::RULE_MAX, 'max' => 48]],
            'confirmPassword' => [self::RULE_REQUIRED, [self::RULE_MATCH, 'match' => 'password']],
        ];
    }

    public function register(): bool
    {
        $this->password = password($this->password);

        $this->save();

        if (isset($this->id) && $this->id > 0) {
            auth()->attempLogin($this);
            return true;
        }

        return false;
    }

    public function getQForm(): QForm
    {
        return QForm::begin($this, ['method' => 'post', 'action' => auth_url('register')])
            ->addInput(['type' => 'text', 'name' => 'username'])
            ->addInput(['type' => 'email', 'name' => 'email'])
            ->addInput(['type' => 'password', 'name' => 'password'])
            ->addInput(['type' => 'password', 'name' => 'confirmPassword'])
            ->submit(['name' => 'Register', 'class' => 'tw-btn-block']);
    }
}
