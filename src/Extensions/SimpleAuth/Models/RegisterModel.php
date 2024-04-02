<?php

namespace VulcanPhp\PhpAdmin\Extensions\SimpleAuth\Models;

use App\Models\User;
use App\Models\UserMeta;
use VulcanPhp\Core\Crypto\Encryption;
use VulcanPhp\Core\Crypto\Hash;
use VulcanPhp\Core\Helpers\Mail;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;

class RegisterModel extends User
{
    public $confirmPassword, $token;

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

    public function register(bool $keepLogin = false): bool
    {
        $this->save();

        if (isset($this->id) && $this->id > 0) {
            if ($keepLogin && $this->status === config('auth.status.activated')) {
                auth()->attempLogin($this);
            }
            return true;
        }

        return false;
    }

    public function sendVerification(?string $link = null, ?string $template = null): bool
    {
        $token = Hash::random(32, 'alphanumeric');
        $data  = [
            'link'      => ($link ?? auth_url('verification')) . '?token=' . Encryption::encryptArray(['id' => $this->id, 'token' => $token]),
            'subject'   => sprintf('Hi %s, Welcome to %s!', $this->getDisplayName(), setting('site_title', 'VulcanPhp')),
            'email'     => $this->email,
            'name'      => $this->getDisplayName(),
        ];

        if (Mail::template(($template ?? 'verification'), $data)->to($data)->send()) {
            return UserMeta::saveMeta(['verification_token' => encode_string(['token' => $token, 'expire' => strtotime('+ 1 day')])], $this->id);
        }

        return false;
    }

    public function tokenValidate(): bool
    {
        $user_token = [];
        $token      = (array) Encryption::decryptArray($this->token ?? '');
        $this->user = User::find($token['id'] ?? 0);

        if ($this->user !== false) {
            $user_token = (array) decode_string($this->user->meta('verification_token'));
        }

        // check if token is valid and expire
        return ($token['token'] ?? '') === ($user_token['token'] ?? null) && time() < ($user_token['expire'] ?? 0);
    }

    public function verified(): bool
    {
        $this->user->status = config('auth.status.activated');
        unset($this->user->password);

        if ($this->user->save() && UserMeta::removeMeta(['verification_token'], $this->user->id)) {
            return true;
        } else {
            session()->setFlash('error', 'Something wen\'t wrong, please try again later.');
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
